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

$_mode = $_GET["mode"];
$title = $_POST["title"] = htmlspecialchars(substr($_POST["title"], 0, 255));
if ($_mode == "addfilter" && isset($_POST["doPost"])) {
/*******************************************************************************
 * ADD FILTER BEGIN
 ******************************************************************************/
    $required = (int)$_POST["required"];
    $sort_order = (int)$_POST["sort_order"];

    if ($required < 0 || $required > 1) {
        $required = 0;
    }

    if (!empty($title)) {
        $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "filters`
            VALUES (
                0,
                '" . mysql_escape_string($title) . "',
                " . $required . ",
                " . $sort_order . "
            )";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=filters");
    exit;
/*******************************************************************************
 * ADD FILTER END
 ******************************************************************************/
} elseif ($_mode == "addvalue" && isset($_POST["doPost"])) {
/*******************************************************************************
 * ADD VALUE BEGIN
 ******************************************************************************/
    $filter_id = (int)$_POST["filter_id"];
    $sort_order = (int)$_POST["sort_order"];

    $query = "SELECT COUNT(*) AS `count` FROM `" . $CNCAT["config"]["db"]["prefix"] . "filters`
        WHERE `id`=" . $filter_id;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $row = mysql_fetch_assoc($res);

    if ($row["count"] && !empty($title)) {
        $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "filtvals`
            VALUES (
                0,
                " . $filter_id . ",
                '" . mysql_escape_string($title) . "',
                " . $sort_order . "
            )";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=filters");
    exit;
/*******************************************************************************
 * ADD VALUE END
 ******************************************************************************/
} elseif ($_mode == "editfilter" && isset($_POST["doPost"])) {
/*******************************************************************************
 * EDIT FILTER BEGIN
 ******************************************************************************/
    $filter_id = (int)$_GET["id"];
    $required = (int)$_POST["required"];
    $sort_order = (int)$_POST["sort_order"];

    if ($required < 0 || $required > 1) {
        $required = 0;
    }

    if (!empty($title)) {
        $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "filters` SET
            `title`='" . mysql_escape_string($title) . "',
            `required`=" . $required . ",
            `sort_order`=" . $sort_order . "
            WHERE `id`=" . $filter_id;
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=filters");
    exit;
/*******************************************************************************
 * EDIT FILTER END
 ******************************************************************************/
} elseif ($_mode == "editvalue" && isset($_POST["doPost"])) {
/*******************************************************************************
 * EDIT VALUE BEGIN
 ******************************************************************************/
    $value_id = (int)$_GET["id"];
    $sort_order = (int)$_POST["sort_order"];

    if ($required < 0 || $required > 1) {
        $required = 0;
    }

    if (!empty($title)) {
        $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "filtvals` SET
            `title`='" . mysql_escape_string($title) . "',
            `sort_order`=" . $sort_order . "
            WHERE `id`=" . $value_id;
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=filters");
    exit;
/*******************************************************************************
 * EDIT VALUE END
 ******************************************************************************/
} elseif ($_mode == "delfilter") {
/*******************************************************************************
 * DELETE FILTER BEGIN
 ******************************************************************************/
    $id = (int)$_GET["id"];

    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "filters`
        WHERE `id`=" . $id;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    $query = "SELECT `id` FROM `" . $CNCAT["config"]["db"]["prefix"] . "filtvals`
        WHERE `filter_id`=" . $id;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $vals_id = array();

    while ($row = mysql_fetch_assoc($res)) {
        $vals_id[] = $row["id"];
    }

    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "filtvals`
        WHERE `filter_id`=" . $id;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    if ($vals_id) {
        $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemfilt`
            WHERE `filtval_id` IN (" . join(",", $vals_id) . ")";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=filters");
    exit;
/*******************************************************************************
 * DELETE FILTER END
 ******************************************************************************/
} elseif ($_mode == "delvalue") {
/*******************************************************************************
 * DELETE VALUE BEGIN
 ******************************************************************************/
    $id = (int)$_GET["id"];

    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "filtvals`
        WHERE `id`=" . $id;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemfilt`
        WHERE `filtval_id`=" . $id;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=filters");
    exit;
/*******************************************************************************
 * DELETE VALUE END
 ******************************************************************************/
}elseif ($_mode == "settings" && isset($_POST["doPost"])) {
    $query = "
        UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "config`
        SET `value`=" . (!empty($_POST["use_filters"]) ? 1 : 0) . "
        WHERE `name`='use_filters'
    ";
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=filters");
    exit;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["filters"]?></h1>
<?php
$query = "SELECT
            f.id `fid`, f.title `ftitle`, f.sort_order `forder`, f.required,
            v.id `vid`, v.title `vtitle`, v.sort_order `vorder`, v.filter_id
    FROM `" . $CNCAT["config"]["db"]["prefix"] . "filters` f
    LEFT JOIN `" . $CNCAT["config"]["db"]["prefix"] . "filtvals` v
    ON (v.filter_id=f.id)";
$res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
$_filters = array();

while ($filter = mysql_fetch_assoc($res)) {
    if (!isset($_filters[$filter["fid"]])) {
        $_filters[$filter["fid"]] = array(
            'title' => $filter["ftitle"],
            'sort_order' => $filter["forder"],
            'required' => $filter["required"],
            'values' => array()
        );
    }

    if ($filter["vid"]) {
        $_filters[$filter["fid"]]["values"][$filter["vid"]] = array(
            'title' => $filter["vtitle"],
            'sort_order' => $filter["vorder"]
        );
    }
}

if ($_filters) {
?>
    <form action="index.php?act=filters&mode=settings" method="post">
        <p>
            <input type="checkbox" name="use_filters" id="use_filters" <?php print !empty($CNCAT["config"]["use_filters"]) ? "checked=\"checked\"" : ""?> /> <label for="use_filters"><?php print $CNCAT["lang"]["use_filters"]?></label>
            <input type="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_save"]?>" />
        </p>
    </form>
    <div class="deline"></div>
    
<table class="list">
    <tr>
        <td class="title"><?php print $CNCAT["lang"]["title"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["required"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["sort"]?></td>
        <td class="title">&nbsp;</td>
        <td class="title">&nbsp;</td>
    </tr>
    <?php foreach ($_filters as $filter_id => $filter) {?>
    <tr>
        <td class="item"><?php print htmlspecialchars($filter["title"])?></td>
        <td class="item"><?php print $filter["required"] ? $CNCAT["lang"]["yes"] : $CNCAT["lang"]["no"]?></td>
        <td class="item"><?php print htmlspecialchars($filter["sort_order"])?></td>
        <td class="item"><a href="index.php?act=filters&mode=editfilter&id=<?php print $filter_id?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/edit.gif" alt="<?php print $CNCAT["lang"]["do_edit"]?>" /></a></td>
        <td class="item"><a href="index.php?act=filters&mode=delfilter&id=<?php print $filter_id?>" onclick="return confirm('<?php print cn_str_replace("%NAME%", htmlspecialchars(cn_str_replace("'", "\'", $filter["title"])), $CNCAT["lang"]["really_delete"])?>');"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/delete.gif" alt="<?php print $CNCAT["lang"]["do_delete"]?>" /></a></td>
    </tr>
    <?php }?>
</table>
<?php
} else {
    print "<span class=\"not_found\">" . $CNCAT["lang"]["no_fils"] . "</span>";
}

$_filter = array();

if ($_mode == "editfilter" && isset($_GET["id"])) {
    $id = (int)$_GET["id"];

    $query = "SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "filters` WHERE `id`=" . $id;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $_filter = mysql_fetch_assoc($res);
}

if ($_filter) {
?>
<form action="index.php?act=filters&mode=editfilter&id=<?php print $_filter["id"]?>" method="post">
<p>
    <input type="text" name="title" class="text" style="width: 150px;" value="<?php print htmlspecialchars($_filter["title"])?>" />
    <select name="required">
        <option value="0" <?php print $_filter["required"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["not_required"]?></option>
        <option value="1" <?php print $_filter["required"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["required"]?></option>
    </select>
    <input type="text" name="sort_order" class="text" style="width: 60px;" value="1000" value="<?php print htmlspecialchars($_filter["sort_order"])?>" />
    <input type="submit" name="doPost" class="submit" value="<?php print $CNCAT["lang"]["do_save"]?>" />
    <input type="button" class="submit" onclick="location.href='index.php?act=filters';" value="<?php print $CNCAT["lang"]["do_cancel"]?>" />
</p>
</form>
<?php
} else {
?>
<form action="index.php?act=filters&mode=addfilter" method="post">
<p>
    <input type="text" class="text" style="width: 150px;" name="title" size="20" />
    <select name="required">
        <option value="0"><?php print $CNCAT["lang"]["not_required"]?></option>
        <option value="1"><?php print $CNCAT["lang"]["required"]?></option>
    </select>
    <input type="text" class="text" style="width: 60px;" name="sort_order" value="1000" />
    <input type="submit" name="doPost" class="submit" value="<?php print $CNCAT["lang"]["do_submit"]?>" />
</p>
</form>
<?php
}
?>
<div class="deline"></div>
<?php
$_values = false;

foreach ($_filters as $filter_id => $filter) {
    if ($filter["values"]) {
        $_values = true;
        break;
    }
}

if ($_values) {
?>
<table class="items">
    <tr>
        <td class="title"><?php print $CNCAT["lang"]["filter"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["value"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["sort"]?></td>
        <td class="title">&nbsp;</td>
        <td class="title">&nbsp;</td>
    </tr>
<?php
    foreach ($_filters as $filter_id => $filter) {
        if ($filter["values"]) {
?>
    <tr><td class="item" rowspan="<?php print (count($filter["values"]) + 1)?>"><?php print $filter["title"]?></td></tr>
    <?php foreach ($filter["values"] as $value_id => $value) {?>
    <tr>
        <td class="item"><?php print $value["title"]?></td>
        <td class="item"><?php print $value["sort_order"]?></td>
        <td class="item"><a href="index.php?act=filters&mode=editvalue&id=<?php print $value_id?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit.gif" alt="" /></a></td>
        <td class="item"><a href="index.php?act=filters&mode=delvalue&id=<?php print $value_id?>" onclick="return confirm('<?php print cn_str_replace("%NAME%", htmlspecialchars(cn_str_replace("'", "\'", $value["title"])), $CNCAT["lang"]["really_delete"])?>')"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete.gif" alt="" /></a></td>
    </tr>
    <?php }?>
<?php
        }
    }
?>
</table>
<?php
} else {
    print "<span class=\"not_found\">" . $CNCAT["lang"]["no_fils_values"] . "</span>";
}

$_value = array();

if ($_mode == "editvalue" && isset($_GET["id"])) {
    $id = (int)$_GET["id"];

    $query = "SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "filtvals` WHERE `id`=" . $id;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $_value = mysql_fetch_assoc($res);
}

if ($_filters) {
    if ($_value) {
?>
<form action="index.php?act=filters&mode=editvalue&id=<?php print $_value["id"]?>" method="post">
<p>
    <input type="text" class="text" style="width: 150px;" name="title" value="<?php print $_value["title"]?>" />
    <input type="text" name="sort_order" class="text" style="width: 60px;" value="1000" value="<?php print $_value["sort_order"]?>" />
    <input type="submit" name="doPost" class="submit" value="<?php print $CNCAT["lang"]["do_save"]?>" />
    <input type="button" class="submit" onclick="this.disabled=true; location.href='index.php?act=filters';" value="<?php print $CNCAT["lang"]["do_cancel"]?>" />
</p>
</form>
<?php
    } else {
?>
<form action="index.php?act=filters&mode=addvalue" method="post">
<p>
    <select name="filter_id">
    <?php foreach ($_filters as $id => $filter) {?>
        <option value="<?php print $id?>"><?php print $filter["title"]?></option>
    <?php } ?>
    </select>
    <input type="text" name="title" class="text" style="width: 150px;" />
    <input type="text" name="sort_order" class="text" style="width: 60px;" value="1000" />
    <input type="submit" name="doPost" class="submit" value="<?php print $CNCAT["lang"]["do_submit"]?>" />
</p>
</form>
<?php
    }
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
