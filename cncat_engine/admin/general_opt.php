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

if (isset($_POST["doPost"])) {
    $config = array();

    // GENERAL OPTIONS
    $config["catalog_title"]        = cn_substr(cn_trim($_POST["catalog_title"]), 0, 255);
    $config["language"]             = cn_substr(cn_trim($_POST["language"]), 0, 255);
    $config["default_theme"]        = cn_substr(cn_trim($_POST["default_theme"]), 0, 255);
    $config["admin_email"]          = cn_substr($_POST["admin_email"], 0, 255);
    $config["cat_title_delim"]      = cn_substr($_POST["cat_title_delim"], 0, 255);
    $config["use_filters"]          = !empty($_POST["use_filters"]) ? 1 : 0;

    $config["show_link_cat"]        = empty($_POST["show_link_cat"]) ? 0: 1;
    $config["add_disable"]          = empty($_POST["add_disable"]) ? 1: 0;
    $config["add_article_enable"]   = empty($_POST["add_article_enable"]) ? 0: 1;
    $config["show_link_admin"]      = empty($_POST["show_link_admin"]) ? 0: 1;
    $config["show_link_map"]        = empty($_POST["show_link_map"]) ? 0: 1;

    $config["show_path_on_main"]    = !empty($_POST["show_path_on_main"]) ? 1 : 0;
    $config["show_items_on_main"]   = !empty($_POST["show_items_on_main"]) ? 1 : 0;
    $config["show_child_items"]     = !empty($_POST["show_child_items"]) ? 1 : 0;
    $config["item_type_on_main"]    = (int)$_POST["item_type_on_main"];
    $config["show_banners"]         = !empty($_POST["show_banners"]) ? 1 : 0;
    $config["display_ext"]          = !empty($_POST["display_ext"]) ? 1 : 0;
    $config["show_new_items"]       = abs((int)$_POST["show_new_items"]);
    $config["show_new_items_count"] = abs((int)$_POST["show_new_items_count"]);
    $config["show_new_items_period"] = abs((int)$_POST["show_new_items_period"]);
    $config["show_new_articles_count"] = abs((int)$_POST["show_new_articles_count"]);
    $config["show_new_articles_period"] = abs((int)$_POST["show_new_articles_period"]);
    $config["new_items_descr_len"]  = abs((int)$_POST["new_items_descr_len"]);
    $config["items_per_page"]       = abs((int)$_POST["items_per_page"]);
    $config["items_per_page_admin"] = abs((int)$_POST["items_per_page_admin"]);
    $config["use_sort_orders"]      = (array)$_POST["use_sort_orders"];
    $config["default_sort_order"]   = abs((int)$_POST["default_sort_order"]);
    $config["default_sort_order_admin"] = abs((int)$_POST["default_sort_order_admin"]);
    $config["default_sort_dir_admin"] = $_POST["default_sort_dir_admin"] ? 1 : 0;
    $config["show_subcats"]         = abs((int)$_POST["show_subcats"]);
    $config["show_subcats_count"]   = abs((int)$_POST["show_subcats_count"]);
    $config["cat_sort"]             = abs((int)$_POST["cat_sort"]);
    $config["cat_col_count_root"]   = abs((int)$_POST["cat_col_count_root"]);
    $config["cat_col_count"]        = abs((int)$_POST["cat_col_count"]);
    $config["date_format"]          = cn_trim($_POST["date_format"]);
    $config["search_method"]        = abs((int)$_POST["search_method"]);
    $config["handle_not_exists"]    = !empty($_POST["handle_not_exists"]) ? 1 : 0;
    $config["show_stats"]    = abs((int)$_POST["show_stats"]);
    $config["image_width"] = abs((int)$_POST["image_width"]);
    $config["image_height"] = abs((int)$_POST["image_height"]);
    $config["image_twidth"] = abs((int)$_POST["image_twidth"]);
    $config["image_theight"] = abs((int)$_POST["image_theight"]);
    $config["cat_admin_view"] = abs((int)$_POST["cat_admin_view"]);

    if ($config["show_new_items"] > 3) {
        $config["show_new_items"] = 3;
    }
    if ($config["show_subcats"] > 3) {
        $config["show_subcats"] = 3;
    }
    if ($config["cat_sort"] > 1) {
        $config["cat_sort"] = 1;
    }
    if ($config["items_per_page"] == 0) {
        $config["items_per_page"] = 1;
    }
    if ($config["items_per_page_admin"] == 0) {
        $config["items_per_page_admin"] = 1;
    }
    if ($config["item_type_on_main"] > 1 || $config["item_type_on_main"] < -1) {
        $config["item_type_on_main"] = -1;
    }
    $config["use_sort_orders"] = join(",", array_keys($config["use_sort_orders"]));
    if ($config["cat_admin_view"] > 1) {
        $config["cat_admin_view"] = 1;
    }
    foreach ($config as $name => $value) {
        $query = "REPLACE `" . $CNCAT["config"]["db"]["prefix"] . "config` SET `name`='" . $name . "', `value`='" . mysql_escape_string($value) . "'";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=general_opt&lang=" . $CNCAT["config"]["language"]);
    exit;
}else {
    $query = "SELECT `name`, `value` FROM `" . $CNCAT["config"]["db"]["prefix"] . "config`";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
    while ($row = mysql_fetch_assoc($res)) {
        $_POST[$row["name"]] = $row["value"];
    }

    $sort = explode(",", $_POST["use_sort_orders"]);
    $_POST["use_sort_orders"] = array();

    foreach ($sort as $key => $sort) {
        $_POST["use_sort_orders"][$sort] = 1;
    }

    if (!isset($_POST["item_type_on_main"])) {
        $_POST["item_type_on_main"] = -1;
    }
}
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["settings_general"]?></h1>
<?php
    if (isset($_GET["lang"]) && $CNCAT["config"]["language"] != $_GET["lang"]) {
        print "<div class=\"error_box\">" . $CNCAT["lang"]["select_lang_notify"] . "</div>";
    }
?>
<form action="index.php?act=general_opt" method="post">
<table class="form">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["settings_gerenal"]?></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["catalog_title"]?></td><td class="field"><input type="text" class="text" name="catalog_title" value="<?php print htmlspecialchars($_POST["catalog_title"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["admin_email"]?></td><td class="field"><input type="text" class="text" name="admin_email" value="<?php print htmlspecialchars($_POST["admin_email"])?>" /></td></tr>
<?php
// read all languages
$dh = @opendir($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_engine_lang"]);

if ($dh) {
?>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["language"]?></td>
        <td class="field">
            <select name="language">
<?php
    while (false !== ($filename = readdir($dh))) {
        if ($filename == "." || $filename == "..") continue;

        $fh = @fopen($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_product_lang"] . $filename, "rt");

        if ($fh) {
            $line = fgets($fh);
            @fclose($fh);

            if (!preg_match("/^lang_(.*)\.php$/Ui", $filename, $m)) {
                continue;
            }

            $lang = $m[1];

            if (preg_match("#<\?php\s+/\*(.*)\*/\s*\?>#Ui" . $CN_STRINGS["preg"], $line, $m)) {//<?{
                print "<option value=\"" . htmlspecialchars($lang) . "\"" . ($_POST["language"] == $lang ? " selected=\"selected\" " : "") . ">" . cn_trim(htmlspecialchars($m[1])) . "</option>";
            }
        }
    }
?>
            </select>
        </td>
    </tr>
<?php
}

// read all themes
$dh = @opendir($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_config_themes"]);

if ($dh) {
?>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["theme"]?></td>
        <td class="field">
            <select name="default_theme">
<?php
    while (false !== ($dirname = readdir($dh))) {
        if ($dirname == "." || $dirname == "..") continue;

        if (@is_dir($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_config_themes"] . $dirname)) {
            $theme = "";
            $title = "";

            $lines = file($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_config_themes"] . $dirname . "/" . "theme.txt");

            foreach ((array)$lines as $line) {
                $line = cn_trim($line);

                if (cn_substr($line, 0, 6) == "title=") {
                    $title = cn_substr($line, 6);
                }
            }

            $theme = cn_trim($dirname);

            if (!empty($title)) {
                print "<option value=\"" . htmlspecialchars($theme) . "\"" . ($_POST["default_theme"] == $theme ? " selected=\"selected\" " : "") . ">" . cn_trim(htmlspecialchars($title)) . "</option>";
            }
        }
    }
?>
            </select>
        </td>
    </tr>
<?php
}
?>
    <tr><td class="name"><?php print $CNCAT["lang"]["date_format"]?></td><td class="field"><input type="text" class="text" name="date_format" value="<?php print htmlspecialchars($_POST["date_format"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["use_filters"]?></td><td class="field"><input type="checkbox" name="use_filters" <?php print !empty($_POST["use_filters"]) ? "checked=\"checked\"" : ""?> /></td></tr>

    <tr><td class="name" rowspan="5"><?php print $CNCAT["lang"]["show_link_menu"]?></td>
        <td class="field"><input type="checkbox" name="show_link_cat" <?php print $_POST["show_link_cat"] ? "checked=\"checked\"" : ""?> /><?php print $CNCAT["lang"]["menu_main"]?></td></tr>
    <tr><td class="field"><input type="checkbox" name="add_disable" <?php print $_POST["add_disable"] ? "" : "checked=\"checked\""?> /><?php print $CNCAT["lang"]["menu_add_link"]?></td></tr>
    <tr><td class="field"><input type="checkbox" name="add_article_enable" <?php print $_POST["add_article_enable"] ? "checked=\"checked\"" : ""?> /><?php print $CNCAT["lang"]["menu_add_article"]?></td></tr>
    <tr><td class="field"><input type="checkbox" name="show_link_admin" <?php print $_POST["show_link_admin"] ? "checked=\"checked\"" : ""?> /><?php print $CNCAT["lang"]["menu_admin"]?></td></tr>
    <tr><td class="field"><input type="checkbox" name="show_link_map" <?php print $_POST["show_link_map"] ? "checked=\"checked\"" : ""?> /><?php print $CNCAT["lang"]["menu_map"]?></td></tr>


    <tr><td class="name"><?php print $CNCAT["lang"]["show_banners"]?></td><td class="field"><input type="checkbox" name="show_banners" <?php print !empty($_POST["show_banners"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["display_ext"]?></td><td class="field"><input type="checkbox" name="display_ext" <?php print !empty($_POST["display_ext"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["root_if_not_exists"]?></td><td class="field"><input type="checkbox" name="handle_not_exists" <?php print !empty($_POST["handle_not_exists"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["display_stats"]?></td>
        <td class="field">
            <select name="show_stats">
                <option value="0" <?php print $_POST["show_stats"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["not_display"]?></option>
                <option value="1" <?php print $_POST["show_stats"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["display_on_main"]?></option>
                <option value="2" <?php print $_POST["show_stats"] == 2 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["display_except_main"]?></option>
                <option value="3" <?php print $_POST["show_stats"] == 3 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["display_on_all"]?></option>
            </select>
        </td>
    </tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["search_method"]?></td>
        <td class="field">
            <select name="search_method">
                <option value="0" <?php print $_POST["search_method"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["search_fulltext"]?></option>
                <option value="1" <?php print $_POST["search_method"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["search_simple"]?></option>
            </select>
        </td>
    </tr>

    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["settings_items"]?></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["items_per_page"]?></td><td class="field"><input type="text" class="text" name="items_per_page" value="<?php print htmlspecialchars($_POST["items_per_page"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["items_per_page_admin"]?></td><td class="field"><input type="text" class="text" name="items_per_page_admin" value="<?php print htmlspecialchars($_POST["items_per_page_admin"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["show_child_items"]?></td>
        <td class="field">
            <input type="checkbox" name="show_child_items" <?php print !empty($_POST["show_child_items"]) ? "checked=\"checked\"" : ""?> />
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["items_type_on_main"]?></td>
        <td class="field">
            <select name="item_type_on_main">
                <option value="-1" <?php print $_POST["item_type_on_main"] == -1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["all"]?></option>
                <option value="0" <?php print $_POST["item_type_on_main"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["links"]?></option>
                <option value="1" <?php print $_POST["item_type_on_main"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["articles"]?></option>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["display_sort_mode"]?></td>
        <td class="field">
<?php
foreach ($CNCAT["sql"]["itemorder"] as $sortId => $sort) {
    if ($sort[4] == 1) continue;
?>
            <input type="checkbox" name="use_sort_orders[<?php print $sortId?>]" <?php print $_POST["use_sort_orders"][$sortId] == 1 ? "checked=\"checked\"" : ""?> /> <?php print $CNCAT["lang"]["by"]?> &laquo;<?php print $CNCAT["lang"]["sort_by_" . $sortId]?>&raquo;<br />
<?php
    }
?>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["default_sort_order"]?></td>
        <td class="field">
            <select name="default_sort_order">
<?php
foreach ($CNCAT["sql"]["itemorder"] as $sortId => $sort) {
    if ($sort[4] == 1) continue;
?>
                <option value="<?php print $sortId?>" <?php print $_POST["default_sort_order"] == $sortId ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["sort_by_" . $sortId]?></option>
<?php
    }
?>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["default_sort_order_admin"]?></td>
        <td class="field">
            <select name="default_sort_order_admin">
<?php
foreach ($CNCAT["sql"]["itemorder"] as $sortId => $sort) {
    if ($sort[4] == 1) continue;
?>
                <option value="<?php print $sortId?>" <?php print $_POST["default_sort_order_admin"] == $sortId ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["sort_by_" . $sortId]?></option>
<?php
    }
?>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["default_sort_dir_admin"]?></td>
        <td class="field">
            <select name="default_sort_dir_admin">
                <option value="0" <?php print $CNCAT["config"]["default_sort_dir_admin"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["sort_desc"]?></option>
                <option value="1" <?php print $CNCAT["config"]["default_sort_dir_admin"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["sort_asc"]?></option>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["show_items_on_main"]?></td><td class="field"><input type="checkbox" name="show_items_on_main" <?php print !empty($_POST["show_items_on_main"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["show_new_items"]?></td>
        <td class="field">
            <select name="show_new_items">
                <option value="0" <?php print $_POST["show_new_items"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["not_display"]?></option>
                <option value="1" <?php print $_POST["show_new_items"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["display_on_main"]?></option>
                <option value="2" <?php print $_POST["show_new_items"] == 2 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["display_except_main"]?></option>
                <option value="3" <?php print $_POST["show_new_items"] == 3 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["display_on_all"]?></option>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["show_new_links_count"]?></td><td class="field"><input type="text" class="text" name="show_new_items_count" value="<?php print intval($_POST["show_new_items_count"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["show_new_links_in_peiod"]?></td><td class="field"><input type="text" class="text" name="show_new_items_period" value="<?php print intval($_POST["show_new_items_period"])?>" /></td></tr>

    <tr><td class="name"><?php print $CNCAT["lang"]["show_new_articles_count"]?></td><td class="field"><input type="text" class="text" name="show_new_articles_count" value="<?php print intval($_POST["show_new_articles_count"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["show_new_articles_in_peiod"]?></td><td class="field"><input type="text" class="text" name="show_new_articles_period" value="<?php print intval($_POST["show_new_articles_period"])?>" /></td></tr>

    <tr><td class="name"><?php print $CNCAT["lang"]["new_items_max_descr"]?></td><td class="field"><input type="text" class="text" name="new_items_descr_len" value="<?php print (int)$_POST["new_items_descr_len"]?>" /></td></tr>

    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["settings_cats"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["show_subcats"]?></td>
        <td class="field">
            <select name="show_subcats">
                <option value="0" <?php print $_POST["show_subcats"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["not_display"]?></option>
                <option value="1" <?php print $_POST["show_subcats"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["display_on_main"]?></option>
                <option value="2" <?php print $_POST["show_subcats"] == 2 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["display_except_main"]?></option>
                <option value="3" <?php print $_POST["show_subcats"] == 3 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["display_on_all"]?></option>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["show_subcats_count"]?></td><td class="field"><input type="text" class="text" name="show_subcats_count" value="<?php print htmlspecialchars($_POST["show_subcats_count"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["cat_sort"]?></td>
        <td class="field">
            <select name="cat_sort">
                <option value="0" <?php print $_POST["cat_sort"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["sort_by_order_title"]?></option>
                <option value="1" <?php print $_POST["cat_sort"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["sort_by_title"]?></option>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["cat_col_count_root"]?></td><td class="field"><input type="text" class="text" name="cat_col_count_root" value="<?php print htmlspecialchars($_POST["cat_col_count_root"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["cat_col_count"]?></td><td class="field"><input type="text" class="text" name="cat_col_count" value="<?php print htmlspecialchars($_POST["cat_col_count"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["cat_title_delim"]?></td><td class="field"><input type="text" class="text" name="cat_title_delim" value="<?php print htmlspecialchars($_POST["cat_title_delim"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["show_path_on_main"]?></td><td class="field"><input type="checkbox" name="show_path_on_main" <?php print !empty($_POST["show_path_on_main"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["admin_cats_view"]?></td>
        <td class="field">
            <select name="cat_admin_view">
                <option value="0" <?php print $_POST["cat_admin_view"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["table"]?></option>
                <option value="1" <?php print $_POST["cat_admin_view"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["tree"]?></option>
            </select>
        </td>
    </tr>
    <tr><td class="deline" colspan="2"></td>

    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["images"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["image_size"]?></td><td class="field">
        <input type="text" class="text" name="image_width" value="<?php print (int)$_POST["image_width"]?>" style="width: 40%;" />
        x
        <input type="text" class="text" name="image_height" value="<?php print (int)$_POST["image_height"]?>" style="width: 40%;" />
    </td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["image_thumb_size"]?></td><td class="field">
        <input type="text" class="text" name="image_twidth" value="<?php print (int)$_POST["image_twidth"]?>" style="width: 40%;" />
        x
        <input type="text" class="text" name="image_theight" value="<?php print (int)$_POST["image_theight"]?>" style="width: 40%;" />
    </td></tr>
    <tr><td class="deline" colspan="2"></td>

    <tr><td class="submit" colspan="2"><input type="submit" name="doPost" class="submit" value="<?php print $CNCAT["lang"]["do_save"]?>" /></td></tr>
</table>
</form>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
