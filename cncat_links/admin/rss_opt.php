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

require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "./auth.php";

if (isset($_POST["doPost"])) {
    $config = array();
    $config["rss_display"] = abs((int)$_POST["rss_display"]);

    if ($config["rss_display"] > 3) {
        $config["rss_display"] = 3;
    }
    
    $config["rss_show_items"] = abs((int)$_POST["rss_show_items"]);

    if ($config["rss_show_items"] > 2) {
        $config["rss_show_items"] = 2;
    }

    $config["rss_title"] = cn_substr(cn_trim($_POST["rss_title"]), 0, 255);
    $config["rss_descr"] = cn_substr(cn_trim($_POST["rss_descr"]), 0, 255);

    $config["rss_show_child_items"] = !empty($_POST["rss_show_child_items"]) ? 1 : 0;
    $config["rss_items_count"] = abs((int)$_POST["rss_items_count"]);

    if ($config["rss_items_count"] > 255) {
        $config["rss_items_count"] = 255;
    }

    $config["rss_item_link"] = abs((int)$_POST["rss_item_link"]);

    if ($config["rss_item_link"] > 2) {
        $config["rss_item_link"] = 2;
    }

    foreach ($config as $name => $value) {
        $query = "REPLACE `" . $CNCAT["config"]["db"]["prefix"] . "config` SET `name`='" . $name . "', `value`='" . mysql_escape_string($value) . "'";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=rss_opt");
    exit;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["settings"]?> / <?php print $CNCAT["lang"]["rss_feed"]?></h1>
<div class="ok_box"><?php print $CNCAT["lang"]["rss_hint"]?></div>
<table class="form">
<form action="index.php?act=rss_opt" method="post">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["rss_feed"]?></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["display"]?></td>
        <td class="field">
            <select name="rss_display">
                <option value="0" <?php print $CNCAT["config"]["rss_display"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["not_display"]?></option>
                <option value="1" <?php print $CNCAT["config"]["rss_display"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["display_on_main"]?></option>
                <option value="2" <?php print $CNCAT["config"]["rss_display"] == 2 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["display_except_main"]?></option>
                <option value="3" <?php print $CNCAT["config"]["rss_display"] == 3 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["display_on_all"]?></option>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["display"]?></td>
        <td class="field">
            <select name="rss_show_items">
                <option value="0" <?php print $CNCAT["config"]["rss_show_items"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["links_and_articles"]?></option>
                <option value="1" <?php print $CNCAT["config"]["rss_show_items"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["links"]?></option>
                <option value="2" <?php print $CNCAT["config"]["rss_show_items"] == 2 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["articles"]?></option>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["rss_title"]?></td><td class="field"><input type="text" class="text" name="rss_title" value="<?php print htmlspecialchars($CNCAT["config"]["rss_title"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["rss_descr"]?></td><td class="field"><input type="text" class="text" name="rss_descr" value="<?php print htmlspecialchars($CNCAT["config"]["rss_descr"])?>" /></td></tr>
    <tr><td colspan="2" class="deline"></td></tr>

    <tr><td class="name"><?php print $CNCAT["lang"]["show_child_items"]?></td><td class="field"><input type="checkbox" name="rss_show_child_items" <?php print !empty($CNCAT["config"]["rss_show_child_items"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["display_items_count"]?></td><td class="field"><input type="text" class="text" name="rss_items_count" value="<?php print htmlspecialchars($CNCAT["config"]["rss_items_count"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["display_link"]?></td>
        <td class="field">
            <select name="rss_item_link">
                <option value="0" <?php print $CNCAT["config"]["rss_item_link"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["to_ext_descr"]?></option>
                <option value="1" <?php print $CNCAT["config"]["rss_item_link"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["to_category"]?></option>
                <option value="2" <?php print $CNCAT["config"]["rss_item_link"] == 2 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["to_main_page"]?></option>
            </select>
        </td>
    </tr>

    <tr><td class="deline" colspan="2"></td>
    <tr><td class="submit" colspan="2"><input type="submit" name="doPost" class="submit" value="<?php print $CNCAT["lang"]["do_save"]?>" /></td></tr>
</table>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
