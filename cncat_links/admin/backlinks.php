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
    $user_code = cn_substr($_POST["user_code"], 0, 1024);
    $check_method = $_POST["check_method"];
    $check_code = cn_substr($_POST["check_code"], 0, 1024);
    $check_url = cn_substr($_POST["check_url"], 0, 1024);
    $check_preg = cn_substr($_POST["check_preg"], 0, 1024);
    $sort_order = (int)$_POST["sort_order"];
    $disabled = !empty($_POST["disabled"]) ? 1 : 0;

    if ($check_method != "code" && $check_method != "url" && $check_method != "preg") {
        $check_method = "code";
    }

    $errors = array();

    if (!$errors) {
        $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "backlinks`
            VALUES(
                0,
                '" . mysql_escape_string($user_code) . "',
                '" . mysql_escape_string($check_code) . "',
                '" . mysql_escape_string($check_url) . "',
                '" . mysql_escape_string($check_preg) . "',
                '" . mysql_escape_string($check_method) . "',
                " . $sort_order . ",
                " . $disabled . "
            )";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        header("Location: index.php?act=backlinks");
        exit;
    }
} elseif ($_mode == "edit" && isset($_POST["doPost"])) {
    $id = (int)$_GET["id"];
    $user_code = cn_substr($_POST["user_code"], 0, 1024);
    $check_method = $_POST["check_method"];
    $check_code = cn_substr($_POST["check_code"], 0, 1024);
    $check_url = cn_substr($_POST["check_url"], 0, 1024);
    $check_preg = cn_substr($_POST["check_preg"], 0, 1024);
    $sort_order = (int)$_POST["sort_order"];
    $disabled = !empty($_POST["disabled"]) ? 1 : 0;

    if ($check_method != "code" && $check_method != "url" && $check_method != "preg") {
        $check_method = "code";
    }

    $errors = array();

    if (!$errors) {
        $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "backlinks` SET
            `user_code`='" . mysql_escape_string($user_code) . "',
            `check_code`='" . mysql_escape_string($check_code) . "',
            `check_url`='" . mysql_escape_string($check_url) . "',
            `check_preg`='" . mysql_escape_string($check_preg) . "',
            `check_method`='" . mysql_escape_string($check_method) . "',
            `sort_order`=" . $sort_order . ",
            `disabled`=" . $disabled . "
            WHERE `id`=" . $id;
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        header("Location: index.php?act=backlinks");
        exit;
    }
} elseif ($_mode == "delete") {
    $id = (int)$_GET["id"];

    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "backlinks` WHERE `id`=" . $id;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=backlinks");
    exit;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";

if ($_mode == "add") {
?>
<h1><?php print $CNCAT["lang"]["backlinks"]?> / <?php print $CNCAT["lang"]["addition"]?></h1>
<a href="index.php?act=backlinks"><?php print $CNCAT["lang"]["back_list"]?></a>
<div class="deline"></div>
<div class="ok_box"><?php print $CNCAT["lang"]["backlink_code_hint"]?></div>
<table class="form">
<form action="index.php?act=backlinks&mode=add" method="post">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["back_link"]?></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["back_html_code"]?></td>
        <td class="field"><textarea name="user_code"><?php print htmlspecialchars($_POST["user_code"])?></textarea></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name">&nbsp;</td>
        <td class="field"><input type="radio" name="check_method" value="code" <?php print $_POST["check_method"] == "code" ? "checked=\"checked\"" : ""?> /> <?php print $CNCAT["lang"]["back_html"]?></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["back_check_html"]?></td>
        <td class="field">
        <textarea name="check_code"><?php htmlspecialchars(print $_POST["check_code"])?></textarea></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name">&nbsp;</td>
        <td class="field"><input type="radio" name="check_method" value="url" <?php print $_POST["check_method"] == "url" ? "checked=\"checked\"" : ""?> /> <?php print $CNCAT["lang"]["back_url"]?></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["back_check_url"]?></td>
        <td class="field"><input type="text" class="text" name="check_url" value="<?php htmlspecialchars(print $_POST["check_url"])?>" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name">&nbsp;</td>
        <td class="field"><input type="radio" name="check_method" value="preg" <?php print $_POST["check_method"] == "preg" ? "checked=\"checked\"" : ""?> /> <?php print $CNCAT["lang"]["back_regexp"]?></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["back_check_regexp"]?></td>
        <td class="field"><input type="text" class="text" name="check_preg" value="<?php print htmlspecialchars($_POST["check_preg"])?>" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["display_order"]?></td>
        <td class="field"><input type="text" class="text" name="sort_order" value="<?php print htmlspecialchars($_POST["sort_order"])?>" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["back_not_display"]?></td>
        <td class="field"><input type="checkbox" name="disabled" <?php print !empty($_POST["disabled"]) ? "checked=\"checked\"" : ""?> /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr><td class="submit" colspan="2"><input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_submit"]?>" /></td></tr>
</table>
<?php
} elseif ($_mode == "edit") {
?>
<h1><?php print $CNCAT["lang"]["backlinks"]?> / <?php print $CNCAT["lang"]["editing"]?></h1>
<a href="index.php?act=backlinks"><?php print $CNCAT["lang"]["links_list"]?></a>
<div class="deline"></div>
<?php
    $id = (int)$_GET["id"];

    $query = "SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "backlinks` WHERE `id`=" . $id;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    if ($row = mysql_fetch_assoc($res)) {
        if (!isset($_POST["doPost"])) {
            $_POST = $row;
        }
?>
<p><?php print $CNCAT["lang"]["backlink_code_hint"]?></p>
<table class="form">
<form action="index.php?act=backlinks&mode=edit&id=<?php print $row["id"]?>" method="post">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["back_link"]?></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["back_html_code"]?></td>
        <td class="field"><textarea name="user_code"><?php print htmlspecialchars($_POST["user_code"])?></textarea></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name">&nbsp;</td>
        <td class="field"><input type="radio" name="check_method" value="code" <?php print $_POST["check_method"] == "code" ? "checked=\"checked\"" : ""?> /> <?php print $CNCAT["lang"]["back_html"]?></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["back_check_html"]?></td>
        <td class="field"><textarea name="check_code"><?php htmlspecialchars(print $_POST["check_code"])?></textarea></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name">&nbsp;</td>
        <td class="field"><input type="radio" name="check_method" value="url" <?php print $_POST["check_method"] == "url" ? "checked=\"checked\"" : ""?> /> <?php print $CNCAT["lang"]["back_url"]?></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["back_check_url"]?></td>
        <td class="field"><input type="text" class="text" name="check_url" value="<?php htmlspecialchars(print $_POST["check_url"])?>" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name">&nbsp;</td>
        <td class="field"><input type="radio" name="check_method" value="preg" <?php print $_POST["check_method"] == "preg" ? "checked=\"checked\"" : ""?> /> <?php print $CNCAT["lang"]["back_regexp"]?></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["back_check_regexp"]?></td>
        <td class="field"><input type="text" class="text" name="check_preg" value="<?php print htmlspecialchars($_POST["check_preg"])?>" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["sort_order"]?></td>
        <td class="field"><input type="text" class="text" name="sort_order" value="<?php print htmlspecialchars($_POST["sort_order"])?>" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["back_not_display"]?></td>
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
<h1><?php print $CNCAT["lang"]["backlinks"]?></h1>
<a href="index.php?act=backlinks&mode=add"><?php print $CNCAT["lang"]["back_add"]?></a>
<div class="deline"></div>
<?php
$query = "SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "backlinks`";
$res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
$links = array();

while ($row = mysql_fetch_assoc($res)) {
    $links[] = $row;
}

if ($links) {
?>
<table class="items">
    <tr>
        <td class="title"><?php print $CNCAT["lang"]["back_html_code"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["back_check_string"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["check_method"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["sort"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["display"]?></td>
        <td class="title" style="width: 16px;">&nbsp;</td>
        <td class="title" style="width: 16px;">&nbsp;</td>
    </tr>
<?php
    foreach ($links as $link) {
?>
    <tr>
        <td class="item"><div style="width: 200px; height: 50px; overflow: scroll;"><?php print htmlspecialchars($link["user_code"])?></div></td>
        <td class="item"><div style="width: 200px; height: 50px; overflow: scroll;"><?php print cn_str($link["check_method"] == "code" ? $link["check_code"] : ($link["check_method"] == "url" ? $link["check_url"] : $link["check_preg"]))?></div></td>
        <td class="item"><?php print $link["check_method"] == "code" ? $CNCAT["lang"]["back_html"] : ($link["check_method"] == "url" ? $CNCAT["lang"]["back_url"] : $CNCAT["lang"]["back_regexp"])?></td>
        <td class="item"><?php print htmlspecialchars($link["sort_order"])?></td>
        <td class="item"><?php print !$link["disabled"] ? $CNCAT["lang"]["yes"] : $CNCAT["lang"]["no"]?></td>
        <td class="item"><a href="index.php?act=backlinks&mode=edit&id=<?php print $link["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/edit.gif" alt="" /></a></td>
        <td class="item"><a href="index.php?act=backlinks&mode=delete&id=<?php print $link["id"]?>" onclick="return confirm('<?php print cn_str_replace("%NAME%", htmlspecialchars(cn_str_replace("'", "\'", $link["user_code"])), $CNCAT["lang"]["really_delete"])?>');"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/delete.gif" alt="" /></a></td>
    </tr>
<?php
    }
} else {
    print "<span class=\"not_found\">" . $CNCAT["lang"]["no_back"] . "</span>";
}
?>
</table>
<?php
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
