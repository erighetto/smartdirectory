<?php
/*******************************************************************************
 * CNCat 4.4 
 * Copyright (c) "CN-Software" Ltd. 
 * http://www.cn-software.com/cncat/
 * ----------------------------------------------------------------------------
 * Please do not modify this header!
 *
 * If you change the original code, we do not guarantee the correct functioning
 * of the program and correct updates.
 * See full text of license agreement in cncat-license.txt file located at the 
 * root folder of the web directory.
*******************************************************************************/

if (!defined("ADMIN_INTERFACE")) die();
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "./auth.php";

if (!isAdmin()) {
    accessDenied();
}

require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "sync_all.php";
$_mode = $_GET["mode"];

if ($_mode == "optimize") {
/*******************************************************************************
 * OPTIMIZE BEGIN
 ******************************************************************************/
    $query = "SHOW TABLE STATUS LIKE '" . $CNCAT["config"]["db"]["prefix"] . "%'";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $tables = array();

    while ($row = mysql_fetch_assoc($res)) {
        if ($row["Data_free"] > 0) {
            $tables[] = "`" . $row["Name"] . "`";
        }
    }

    if ($tables) {
        $query = "OPTIMIZE TABLE " . join(",", $tables);
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=service");
    exit;
/*******************************************************************************
 * OPTIMIZE END
 ******************************************************************************/
} elseif ($_mode == "sync") {
/*******************************************************************************
 * SYNC BEGIN
 ******************************************************************************/
    cn_syncAll();

    header("Location: index.php?act=service");
    exit;
/*******************************************************************************
 * SYNC END
 ******************************************************************************/
} elseif ($_mode == "cleartrash") {
/*******************************************************************************
 * CLEAR TRASH BEGIN
 ******************************************************************************/
    $items_id = array();

    foreach (itemSelect("`item_id`", "`item_status`=2") as $item) {
        $items_id[] = $item["item_id"];
    }

    if ($items_id) {
        $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat`
            WHERE `item_id` IN (" . join(",", $items_id) . ")";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemfilt`
            WHERE `item_id` IN (" . join(",", $items_id) . ")
            " . (isAdmin() ? "" : "AND `moder_id`=" . getModerId());
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        itemDelete("`item_status`=2");
        cn_syncAll();

        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=service");
    exit;
/*******************************************************************************
 * CLEAR TRASH END
 ******************************************************************************/
} elseif ($_mode == "clearcat") {
/*******************************************************************************
 * CLEAR CAT BEGIN
 ******************************************************************************/
    if ((int)$_POST["accept"] == 1) {
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "backlinks`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "bwlist`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "cats`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "filters`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "filtvals`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "itemcat`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "itemfilt`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "items`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "jumps`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "modercat`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "moders`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "banners`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "images`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        $query = "TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "img_cats`";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "cats`
            (`id`, `title`, `title_full`, `parent_id`, `path_full`, `child_id_list`, `image`)
            VALUES (
                0, '" . $CNCAT["lang"]["default_root_cat_title"] . "', '', '-1', '', '', ''
            )";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    cn_syncAll();

    header("Location: index.php?act=service");
    exit;
/*******************************************************************************
 * CLEAR CAT END
 ******************************************************************************/
} elseif ($_mode == "checkmail") {
    if ($_POST["doPost"]) {
        $email = $_POST["email"];
        
        if (!empty($email)) {
            @mail($email, "CNCat " . $CNCAT_PRODUCT_VERSION . " test message.", "This is message send by robot. Don't reply to this.");
        }
    }

    header("Location: index.php?act=service");
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";

$query = "SHOW TABLE STATUS LIKE '" . $CNCAT["config"]["db"]["prefix"] . "%'";
$res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
$db_size = 0;
$db_data_size = 0;
$db_index_size = 0;
$db_free_size = 0;
$db_rows = 0;
$db_items_rows = 0;
$db_cats_rows = 0;

while ($row = mysql_fetch_assoc($res)) {
    $db_data_size += $row["Data_length"];
    $db_index_size += $row["Index_length"];
    $db_free_size += $row["Data_free"];
    $db_rows += $row["Rows"];

    if ($row["Name"] == $CNCAT["config"]["db"]["prefix"] . "items") {
        $db_items_rows = $row["Rows"];
    } elseif ($row["Name"] == $CNCAT["config"]["db"]["prefix"] . "cats") {
        $db_cats_rows = $row["Rows"];
    }
}

$db_size = $db_data_size + $db_index_size;
?>
<h1><?php print $CNCAT["lang"]["service"]?></h1>
<table class="form">
<form action="index.php?act=service&mode=optimize" method="post">
    <tr><td class="title"><?php print $CNCAT["lang"]["database"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["database_size"]?>: <strong><?php print sizeToStr($db_size)?></strong><br /></td></tr>
    <tr>
        <td class="name" style="padding-left: 20px;">
            <?php print $CNCAT["lang"]["data"]?>: <strong><?php print sizeToStr($db_data_size)?></strong><br />
            <?php print $CNCAT["lang"]["index"]?>: <strong><?php print sizeToStr($db_index_size)?></strong><br />
            <?php print $CNCAT["lang"]["fragment"]?>: <strong><?php print sizeToStr($db_free_size)?></strong><br />
        </td>
    </tr>
    <tr><td class="deline"></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["rows_count"]?>: <strong><?php print $db_rows?></strong><br /></td></tr>
    <tr>
        <td class="name" style="padding-left: 20px;">
                <?php print $CNCAT["lang"]["records"]?>: <strong><?php print $db_items_rows?></strong><br />
                <?php print $CNCAT["lang"]["categories"]?>: <strong><?php print $db_cats_rows?></strong><br />
        </td>
    </tr>
    <tr><td class="deline"></td></tr>
    <tr><td class="name"><input type="submit" class="submit" value="<?php print $CNCAT["lang"]["do_optimize"]?>" <?php print !$db_free_size ? "disabled=\"disabled\"" : ""?> /></td></tr>
</form>
</table>
<table class="form">
<form action="index.php?act=service&mode=clearcat" method="post">
    <tr><td class="title"><?php print $CNCAT["lang"]["cat_clearing"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["clearing_hint"]?><br /></td></tr>
    <tr><td class="name"><input type="checkbox" name="accept" id="clearing_accept" value="1" onclick="this.form.clrbtn.disabled=!this.checked" /> <label for="clearing_accept"><?php print $CNCAT["lang"]["clearing_accept"]?></label></td></tr>
    <tr><td class="name"><input type="button" class="submit" name="clrbtn" disabled="disabled" value="<?php print $CNCAT["lang"]["clear_cat"]?>" onclick="if (confirm('<?php print $CNCAT["lang"]["really_clearing"]?>')) { this.form.submit(); }" /></td></tr>
</form>
</table>
<table class="form">
<form action="index.php?act=service&mode=sync" method="post">
    <tr><td class="title"><?php print $CNCAT["lang"]["sync"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["sync_hint"]?></td></tr>
    <tr><td class="name"><input type="submit" class="submit" value="<?php print $CNCAT["lang"]["do_sync"]?>" /></td></tr>
</form>
</table>
<?php
$query = "SHOW TABLE STATUS LIKE '" . $CNCAT["config"]["db"]["prefix"] . "items'";
$res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
$status = mysql_fetch_assoc($res);

$count = itemCount("`item_status`=2");
?>
<table class="form">
<form action="index.php?act=service&mode=cleartrash" method="post">
    <tr><td class="title"><?php print $CNCAT["lang"]["trash"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["trash_hint"]?><br /></td></tr>
    <tr>
        <td class="name">
            <?php print $CNCAT["lang"]["del_rows_count"]?>: <strong><?php print $count?></strong><br />
            <?php print $CNCAT["lang"]["free_after_del"]?>: <strong>~<?php print sizeToStr($count * $status["Avg_row_length"])?></strong>
        </td>
    </tr>
    <tr><td class="name"><input type="button" class="submit" value="<?php print $CNCAT["lang"]["do_delete"]?>" <?php print !$count ? "disabled=\"disabled\"" : ""?> onclick="if (confirm('<?php print $CNCAT["lang"]["really_clear_trash"]?>')) { this.form.submit(); }"  /></td></tr>
</form>
</table>

<table class="form">
<form action="index.php?act=service&mode=checkmail" method="post">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["mail_send_check"]?></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["email"]?></td>
        <td class="field"><input type="text" name="email" class="text" value="<?php print htmlspecialchars($CNCAT["config"]["admin_email"])?>" /></td>
    </tr>
    <tr><td class="submit" colspan="2"><input type="submit" name="doPost" class="submit" value="<?php print $CNCAT["lang"]["do_send"]?>"  /></td></tr>
</form>
</table>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
