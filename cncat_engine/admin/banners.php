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

if ($_mode == "add" && isset($_POST["doPost"])) {
    $code = cn_substr($_POST["bcode"], 0, 65000);
    $condition = cn_substr($_POST["bcondition"], 0, 255);
    $comment = cn_substr($_POST["bcomment1"], 0, 255);
    $page = abs((int)$_POST["bpage"]);
    $cat_id = abs((int)$_POST["cat_id"]);
    $pattern = cn_substr(trim($_POST["pattern"]), 0, 255);
    $position = cn_substr($_POST["position"], 0, 20);
    $sort_order = (int)$_POST["sort_order"];
    $enable_php = !empty($_POST["enable_php"]) ? 1 : 0;
    $disabled = !empty($_POST["disabled"]) ? 1 : 0;
    $item_type = (array)$_POST["item_type"];
    $child_cats = !empty($_POST["child_cats"]) ? 1 : 0;
    $on_cat_main = !empty($_POST["on_cat_main"]) ? 1 : 0;

    $errors = array();

    if (!$errors) {
        $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "banners`
            (`id`, `bcode`, `bcondition`, `bcomment`, `bpage`, `cat_id`, `child_cats`, `pattern`, `item_type`, `position`, `sort_order`, `enable_php`, `disabled`, `on_cat_main`)
            VALUES(
                0,
                '" . mysql_escape_string($code) . "',
                '" . mysql_escape_string($condition) . "',
                '" . mysql_escape_string($comment) . "',
                " . $page . ",
                " . $cat_id . ",
                " . $child_cats . ",
                '" . mysql_escape_string($pattern) . "',
                '" . mysql_escape_string(implode(",", $item_type)) . "',
                '" . mysql_escape_string($position) . "',
                " . $sort_order . ",
                " . $enable_php . ",
                " . $disabled . ",
                " . $on_cat_main . "
            )";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        header("Location: index.php?act=banners");
        exit;
    }
} elseif ($_mode == "edit" && isset($_POST["doPost"])) {
    $id = (int)$_GET["id"];
    $code = cn_substr($_POST["bcode"], 0, 65000);
    $condition = cn_substr($_POST["bcondition"], 0, 255);
    $comment = cn_substr($_POST["bcomment1"], 0, 255);
    $page = abs((int)$_POST["bpage"]);
    $cat_id = abs((int)$_POST["cat_id"]);
    $pattern = cn_substr(trim($_POST["pattern"]), 0, 255);
    $position = cn_substr($_POST["position"], 0, 20);
    $sort_order = (int)$_POST["sort_order"];
    $enable_php = !empty($_POST["enable_php"]) ? 1 : 0;
    $disabled = !empty($_POST["disabled"]) ? 1 : 0;
    $item_type = (array)$_POST["item_type"];
    $child_cats = !empty($_POST["child_cats"]) ? 1 : 0;
    $on_cat_main = !empty($_POST["on_cat_main"]) ? 1 : 0;

    $errors = array();

    if (!$errors) {
        $query = "
            UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "banners` SET
                `bcode`='" . mysql_escape_string($code) . "',
                `bcondition`='" . mysql_escape_string($condition) . "',
                `bcomment`='" . mysql_escape_string($comment) . "',
                `bpage`=" . $page . ",
                `cat_id`=" . $cat_id . ",
                `child_cats`=" . $child_cats . ",
                `pattern`='" . mysql_escape_string($pattern) . "',
                `item_type`='" . mysql_escape_string(implode(",", $item_type)) . "',
                `position`='" . mysql_escape_string($position) . "',
                `sort_order`=" . $sort_order . ",
                `enable_php`=" . $enable_php . ",
                `disabled`=" . $disabled . ",
                `on_cat_main`=" . $on_cat_main . "
            WHERE `id`=" . $id . "
        ";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        header("Location: index.php?act=banners");
        exit;
    }
} elseif ($_mode == "delete") {
    $id = (int)$_GET["id"];

    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "banners` WHERE `id`=" . $id;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=banners");
    exit;
} elseif ($_mode == "settings" && isset($_POST["doPost"])) {
    $query = "
        UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "config`
        SET `value`=" . (!empty($_POST["show_banners"]) ? 1 : 0) . "
        WHERE `name`='show_banners'
    ";
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=banners");
    exit;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";

if ($_mode == "add") {
    if (!isset($_POST["doPost"])) {
        $query = "SELECT MAX(sort_order) AS sort_order FROM `" . $CNCAT["config"]["db"]["prefix"] . "banners`";
        $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $row = mysql_fetch_assoc($result);

        $_POST["sort_order"] = $row["sort_order"] + 100;
    }
?>
<h1><?php print $CNCAT["lang"]["banners"]?> / <?php print $CNCAT["lang"]["addition"]?></h1>
<a href="index.php?act=banners"><?php print $CNCAT["lang"]["banner_list"]?></a>
<div class="deline"></div>
<table class="form">
<form action="index.php?act=banners&mode=add" method="post">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["banner"]?></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["banner_code"]?></td>
        <td class="field"><textarea name="bcode"><?php print htmlspecialchars($_POST["bcode"])?></textarea></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["comment"]?></td>
        <td class="field"><input name="bcomment1" class="text" value="<?php print htmlspecialchars($_POST["bcomment1"])?>" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["page"]?></td>
        <td class="field">
            <input type="radio" name="bpage" value="0" id="onall" checked="checked" /> <label for="onall"><?php print $CNCAT["lang"]["display_on_all"]?></label><br />
            <input type="radio" name="bpage" value="1" id="onmain" /> <label for="onmain"><?php print $CNCAT["lang"]["display_on_main"]?></label><br />
            <input type="radio" name="bpage" value="2" id="exceptmain" /> <label for="exceptmain"><?php print $CNCAT["lang"]["display_except_main"]?></label><br />
            <input type="radio" name="bpage" value="3" id="incat" /> <label for="incat"><?php print $CNCAT["lang"]["into_category"]?></label>
            <p style="margin-left: 20px;"><select name="cat_id">
<?php
    $query = "
        SELECT id, title, tree_level FROM " . $CNCAT["config"]["db"]["prefix"] . "cats
        WHERE parent_id > 0
        ORDER BY sort_order_global ASC
    ";
    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
    while ($row = mysql_fetch_assoc($result)) {
?>
                <option value="<?php print $row["id"]?>">&nbsp;<?php print str_repeat('-&nbsp;&nbsp;&nbsp;', $row["tree_level"])?><?php print htmlspecialchars($row["title"])?></option>
<?php
    }
?>
            </select><br />
            <input type="checkbox" name="child_cats" id="child_cats" /> <label for="child_cats"><?php print $CNCAT["lang"]["include_subcats"]?></label><br />
            <input type="checkbox" name="on_cat_main" id="on_cat_main"<?php print $_POST["on_cat_main"] == 1 ? " checked=\"checked\"" : ""?> /> <label for="on_cat_main"><?php print $CNCAT["lang"]["display_on_cat_main"]?></label>
            </p>
            <input type="radio" name="bpage" value="4" id="bypattern" /> <label for="bypattern"><?php print $CNCAT["lang"]["by_uri_pattern"]?></label>
            <p style="margin-left: 20px;"><input type="text" class="text" name="pattern" value="<?php print htmlspecialchars($_POST["pattern"])?>" /></p>
            <input type="radio" name="bpage" value="5" id="onsearch"<?php print $_POST["bpage"] == 5 ? " checked=\"checked\"" : ""?> /> <label for="onsearch"><?php print $CNCAT["lang"]["on_page_search"]?></label><br />
            <input type="radio" name="bpage" value="6" id="onmap"<?php print $_POST["bpage"] == 6 ? " checked=\"checked\"" : ""?> /> <label for="onmap"><?php print $CNCAT["lang"]["on_page_map"]?></label><br />
            <input type="radio" name="bpage" value="7" id="onadd"<?php print $_POST["bpage"] == 7 ? " checked=\"checked\"" : ""?> /> <label for="onadd"><?php print $CNCAT["lang"]["on_page_link_add"]?></label><br />
            <input type="radio" name="bpage" value="8" id="onaddarticle"<?php print $_POST["bpage"] == 8 ? " checked=\"checked\"" : ""?> /> <label for="onaddarticle"><?php print $CNCAT["lang"]["on_page_article_add"]?></label><br />
            <input type="radio" name="bpage" value="9" id="onext"<?php print $_POST["bpage"] == 9 ? " checked=\"checked\"" : ""?> /> <label for="onext"><?php print $CNCAT["lang"]["on_page_ext"]?></label><br />
            <p style="margin-left: 20px;">
                <input type="checkbox" name="item_type[]" value="0" id="type0" /> <label for="type0"><?php print $CNCAT["lang"]["links"]?></label>
                <input type="checkbox" name="item_type[]" value="1" id="type1" /> <label for="type1"><?php print $CNCAT["lang"]["articles"]?></label>
            </p>
        </td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["condition_on_php"]?></td>
        <td class="field"><input name="bcondition" class="text" value="<?php print htmlspecialchars($_POST["bcondition"])?>" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["position"]?></td>
        <td class="field">
            <select name="position">
                <option value="page_top"<?php print $_POST["position"] == "page_top" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["on_page_top"]?></option>
                <option value="page_left"<?php print $_POST["position"] == "page_left" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["on_page_left"]?></option>
                <option value="page_right"<?php print $_POST["position"] == "page_right" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["on_page_right"]?></option>
                <option value="page_bottom"<?php print $_POST["position"] == "page_bottom" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["on_page_bottom"]?></option>
                <option value="items_top"<?php print $_POST["position"] == "items_top" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["before_items"]?></option>
                <option value="items_middle"<?php print $_POST["position"] == "items_middle" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["among_items"]?></option>
                <option value="items_bottom"<?php print $_POST["position"] == "items_bottom" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["after_items"]?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["display_order"]?></td>
        <td class="field"><input type="text" class="text" name="sort_order" value="<?php print htmlspecialchars($_POST["sort_order"])?>" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["php_support"]?></td>
        <td class="field"><input type="checkbox" name="enable_php" <?php print !empty($_POST["enable_php"]) ? "checked=\"checked\"" : ""?> /></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["not_display"]?></td>
        <td class="field"><input type="checkbox" name="disabled" <?php print !empty($_POST["disabled"]) ? "checked=\"checked\"" : ""?> /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr><td class="submit" colspan="2"><input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_submit"]?>" /></td></tr>
</table>
<?php
} elseif ($_mode == "edit") {
?>
<h1><?php print $CNCAT["lang"]["banners"]?> / <?php print $CNCAT["lang"]["editing"]?></h1>
<a href="index.php?act=banners"><?php print $CNCAT["lang"]["banner_list"]?></a>
<div class="deline"></div>
<?php
    $id = (int)$_GET["id"];

    $query = "SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "banners` WHERE `id`=" . $id;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    if ($row = mysql_fetch_assoc($res)) {
        if (!isset($_POST["doPost"])) {
            $_POST = $row;
            $_POST["bcomment1"] = $_POST["bcomment"];
        }
?>
<table class="form">
<form action="index.php?act=banners&mode=edit&id=<?php print $row["id"]?>" method="post">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["back_link"]?></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["banner_code"]?></td>
        <td class="field"><textarea name="bcode"><?php print htmlspecialchars($_POST["bcode"])?></textarea></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["comment"]?></td>
        <td class="field"><input name="bcomment1" class="text" value="<?php print htmlspecialchars($_POST["bcomment1"])?>" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["page"]?></td>
        <td class="field">
            <input type="radio" name="bpage" value="0" id="onall"<?php print $_POST["bpage"] == 0 ? " checked=\"checked\"" : ""?> /> <label for="onall"><?php print $CNCAT["lang"]["display_on_all"]?></label><br />
            <input type="radio" name="bpage" value="1" id="onmain"<?php print $_POST["bpage"] == 1 ? " checked=\"checked\"" : ""?> /> <label for="onmain"><?php print $CNCAT["lang"]["display_on_main"]?></label><br />
            <input type="radio" name="bpage" value="2" id="exceptmain"<?php print $_POST["bpage"] == 2 ? " checked=\"checked\"" : ""?> /> <label for="exceptmain"><?php print $CNCAT["lang"]["display_except_main"]?></label><br />
            <input type="radio" name="bpage" value="3" id="incat"<?php print $_POST["bpage"] == 3 ? " checked=\"checked\"" : ""?> /> <label for="incat"><?php print $CNCAT["lang"]["into_category"]?></label>
            <p style="margin-left: 20px;"><select name="cat_id">
<?php
    $query = "
        SELECT id, title, tree_level FROM " . $CNCAT["config"]["db"]["prefix"] . "cats
        WHERE parent_id > 0
        ORDER BY sort_order_global ASC
    ";
    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
    while ($row = mysql_fetch_assoc($result)) {
?>
                <option value="<?php print $row["id"]?>"<?php print $_POST["cat_id"] == $row["id"] ? " selected=\"selected\"" : ""?>>&nbsp;<?php print str_repeat('-&nbsp;&nbsp;&nbsp;', $row["tree_level"])?><?php print htmlspecialchars($row["title"])?></option>
<?php
    }
?>
            </select><br />
            <input type="checkbox" name="child_cats" id="child_cats"<?php print $_POST["child_cats"] == 1 ? " checked=\"checked\"" : ""?> /> <label for="child_cats"><?php print $CNCAT["lang"]["include_subcats"]?></label><br />
            <input type="checkbox" name="on_cat_main" id="on_cat_main"<?php print $_POST["on_cat_main"] == 1 ? " checked=\"checked\"" : ""?> /> <label for="on_cat_main"><?php print $CNCAT["lang"]["display_on_cat_main"]?></label>
            </p>
            <input type="radio" name="bpage" value="4" id="bypattern"<?php print $_POST["page"] == 4 ? " checked=\"checked\"" : ""?> /> <label for="bypattern"><?php print $CNCAT["lang"]["by_uri_pattern"]?></label>
            <p style="margin-left: 20px;"><input type="text" class="text" name="pattern" value="<?php print htmlspecialchars($_POST["pattern"])?>" /></p>
            <input type="radio" name="page" value="5" id="onsearch"<?php print $_POST["bpage"] == 5 ? " checked=\"checked\"" : ""?> /> <label for="onsearch"><?php print $CNCAT["lang"]["on_page_search"]?></label><br />
            <input type="radio" name="bpage" value="6" id="onmap"<?php print $_POST["bpage"] == 6 ? " checked=\"checked\"" : ""?> /> <label for="onmap"><?php print $CNCAT["lang"]["on_page_map"]?></label><br />
            <input type="radio" name="bpage" value="7" id="onadd"<?php print $_POST["bpage"] == 7 ? " checked=\"checked\"" : ""?> /> <label for="onadd"><?php print $CNCAT["lang"]["on_page_link_add"]?></label><br />
            <input type="radio" name="bpage" value="8" id="onaddarticle"<?php print $_POST["bpage"] == 8 ? " checked=\"checked\"" : ""?> /> <label for="onaddarticle"><?php print $CNCAT["lang"]["on_page_article_add"]?></label><br />
            <input type="radio" name="bpage" value="9" id="onext"<?php print $_POST["bpage"] == 9 ? " checked=\"checked\"" : ""?> /> <label for="onext"><?php print $CNCAT["lang"]["on_page_ext"]?></label><br />
            <p style="margin-left: 20px;">
                <input type="checkbox" name="item_type[]" value="0" id="type0"<?php print in_array('0', explode(',', $_POST["item_type"])) ? " checked=\"checked\"" : ""?> /> <label for="type0"><?php print $CNCAT["lang"]["links"]?></label>
                <input type="checkbox" name="item_type[]" value="1" id="type1"<?php print in_array('1', explode(',', $_POST["item_type"])) ? " checked=\"checked\"" : ""?> /> <label for="type1"><?php print $CNCAT["lang"]["articles"]?></label>
            </p>
        </td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["condition_on_php"]?></td>
        <td class="field"><input name="bcondition" class="text" value="<?php print htmlspecialchars($_POST["bcondition"])?>" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["position"]?></td>
        <td class="field">
            <select name="position">
                <option value="page_top"<?php print $_POST["position"] == "page_top" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["on_page_top"]?></option>
                <option value="page_left"<?php print $_POST["position"] == "page_left" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["on_page_left"]?></option>
                <option value="page_right"<?php print $_POST["position"] == "page_right" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["on_page_right"]?></option>
                <option value="page_bottom"<?php print $_POST["position"] == "page_bottom" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["on_page_bottom"]?></option>
                <option value="items_top"<?php print $_POST["position"] == "items_top" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["before_items"]?></option>
                <option value="items_middle"<?php print $_POST["position"] == "items_middle" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["among_items"]?></option>
                <option value="items_bottom"<?php print $_POST["position"] == "items_bottom" ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["after_items"]?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["sort_order"]?></td>
        <td class="field"><input type="text" class="text" name="sort_order" value="<?php print htmlspecialchars($_POST["sort_order"])?>" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["php_support"]?></td>
        <td class="field"><input type="checkbox" name="enable_php" <?php print !empty($_POST["enable_php"]) ? "checked=\"checked\"" : ""?> /></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["not_display"]?></td>
        <td class="field"><input type="checkbox" name="disabled" <?php print !empty($_POST["disabled"]) ? "checked=\"checked\"" : ""?> /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr><td class="submit" colspan="2"><input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_save"]?>" /></td></tr>
</table>
<?php
    } else {
        print "<span class=\"not_found\">" . $CNCAT["lang"]["link_not_found"] . "</span>\n";
    }
} else {
?>
    <h1><?php print $CNCAT["lang"]["banners"]?></h1>
    <form action="index.php?act=banners&mode=settings" method="post">
        <p>
            <input type="checkbox" name="show_banners" id="show_banners" <?php print !empty($CNCAT["config"]["show_banners"]) ? "checked=\"checked\"" : ""?> /> <label for="show_banners"><?php print $CNCAT["lang"]["show_banners"]?></label>
            <input type="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_save"]?>" />
        </p>
    </form>
    <div class="deline"></div>
    <a href="index.php?act=banners&mode=add"><?php print $CNCAT["lang"]["banner_add"]?></a>
    <div class="deline"></div>
<?php
    $query = "SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "banners` ORDER BY sort_order ASC, id ASC";
    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $banners = array();
    
    while ($row = mysql_fetch_assoc($result)) {
        $banners[] = $row;
    }
    
    if ($banners) {
?>
    <table class="items">
        <tr>
            <td class="title"><?php print $CNCAT["lang"]["banner_code"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["comment"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["page"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["position"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["php_support"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["sort_order"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["display"]?></td>
            <td class="title" style="width: 16px;">&nbsp;</td>
            <td class="title" style="width: 16px;">&nbsp;</td>
        </tr>
<?php
        $pages = array(
            0 => $CNCAT["lang"]["display_on_all"],
            1 => $CNCAT["lang"]["display_on_main"],
            2 => $CNCAT["lang"]["display_except_main"],
            3 => $CNCAT["lang"]["into_category"],
            4 => $CNCAT["lang"]["by_uri_pattern"],
            5 => $CNCAT["lang"]["on_page_search"],
            6 => $CNCAT["lang"]["on_page_map"],
            7 => $CNCAT["lang"]["on_page_link_add"],
            8 => $CNCAT["lang"]["on_page_article_add"],
            9 => $CNCAT["lang"]["on_page_ext"]
        );
        $positions = array(
            'page_left' => $CNCAT["lang"]["on_page_left"],
            'page_right' => $CNCAT["lang"]["on_page_right"],
            'page_top' => $CNCAT["lang"]["on_page_top"],
            'page_bottom' => $CNCAT["lang"]["on_page_bottom"],
            'items_top' => $CNCAT["lang"]["before_items"],
            'items_bottom' => $CNCAT["lang"]["after_items"],
            'items_middle' => $CNCAT["lang"]["among_items"],
        );

        foreach ($banners as $banner) {
            $additional = '';

            if ($banner["bpage"] == 3) {
                $query = "SELECT title_full FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats` WHERE id=" . intval($banner['cat_id']);
                $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                $row = mysql_fetch_assoc($result);
    
                $additional = htmlspecialchars($row["title_full"]);
                
                if ($banner["child_cats"]) {
                    $additional .= "<br />" . $CNCAT["lang"]["include_subcats"];
                }
            }
            
            if ($banner["bpage"] == 4) {
                $additional = '<code>' . htmlspecialchars($banner["pattern"]) . '</code>';
            }
            
            if ($banner["bpage"] == 9) {
                $types = explode(",", $banner["item_type"]);

                $additional .= in_array(0, $types) ? $CNCAT["lang"]["links"] : "";
                $additional .= in_array(1, $types) ? (!empty($additional) ? ', ' : '') . $CNCAT["lang"]["articles"] : "";
            }
?>
    <tr <?php print $banner["disabled"] ? "class=\"disabled\"" : ""?>>
        <td class="item"><div style="width: 200px; height: 50px; overflow: scroll;"><?php print nl2br(htmlspecialchars($banner["bcode"]))?></div></td>
        <td class="item" style="white-space: normal;"><?php print htmlspecialchars($banner["bcomment"])?></td>
        <td class="item">
            <?php print htmlspecialchars($pages[$banner["bpage"]])?>
            <?php
            if (!empty($additional)) {
                print '<br />' . $additional;
            }
            ?>
        </td>
        <td class="item">
            <?php print htmlspecialchars($positions[$banner["position"]])?>
        </td>
        <td class="item"><?php print $banner["enable_php"] ? $CNCAT["lang"]["yes"] : $CNCAT["lang"]["no"]?></td>
        <td class="item"><?php print intval($banner["sort_order"])?></td>
        <td class="item"><?php print !$banner["disabled"] ? $CNCAT["lang"]["yes"] : $CNCAT["lang"]["no"]?></td>
        <td class="item"><a href="index.php?act=banners&mode=edit&id=<?php print $banner["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/edit.gif" alt="" /></a></td>
        <td class="item"><a href="index.php?act=banners&mode=delete&id=<?php print $banner["id"]?>" onclick="return confirm('<?php print cn_str_replace("%NAME%", htmlspecialchars(cn_str_replace("'", "\'", $CNCAT["lang"]["banner"])), $CNCAT["lang"]["really_delete"])?>');"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/delete.gif" alt="" /></a></td>
    </tr>
<?php
        }
    } else {
        print "<span class=\"not_found\">" . $CNCAT["lang"]["no_banners"] . "</span>";
    }
?>
</table>
<?php
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
