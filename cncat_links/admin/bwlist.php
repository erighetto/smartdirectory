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

if ($_mode == "addblack" && isset($_POST["doPost"])) {
    $check_str = substr($_POST["check_str"], 0, 80);
    $check_method = $_POST["check_method"];

    if ($check_method != "regexp" && $check_method != "substr") {
        $check_method = "substr";
    }

    if (!empty($check_str)) {
        $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "bwlist` VALUES (0, 'black', '" . mysql_escape_string($check_method) . "', '" . mysql_escape_string($check_str) . "')";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=bwlist");
    exit;
} elseif ($_mode == "addwhite" && isset($_POST["doPost"])) {
    $check_str = substr($_POST["check_str"], 0, 80);

    if (!empty($check_str)) {
        $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "bwlist` VALUES (0, 'white', 'substr', '" . mysql_escape_string($check_str) . "')";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=bwlist");
    exit;
} elseif ($_mode == "editblack" && isset($_POST["doPost"])) {
    $id = (int)$_GET["id"];
    $check_str = substr($_POST["check_str"], 0, 80);
    $check_method = $_POST["check_method"];

    if ($check_method != "regexp" && $check_method != "substr") {
        $check_method = "substr";
    }

    if (!empty($check_str)) {
        $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "bwlist` SET `check_method`='" . mysql_escape_string($check_method) . "', `check_str`='" . mysql_escape_string($check_str) . "' WHERE `id`=" . $id;
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=bwlist");
    exit;
} elseif ($_mode == "editwhite" && isset($_POST["doPost"])) {
    $id = (int)$_GET["id"];
    $check_str = substr($_POST["check_str"], 0, 80);

    if (!empty($check_str)) {
        $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "bwlist` SET `check_str`='" . mysql_escape_string($check_str) . "' WHERE `id`=" . $id;
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=bwlist");
    exit;
} elseif ($_mode == "delete") {
    $id = (int)$_GET["id"];

    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "bwlist` WHERE `id`=" . $id;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=bwlist");
    exit;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["bwlist"]?></h1>
<?php
$query = "SELECT `id`, `check_method`, `check_str` FROM `" . $CNCAT["config"]["db"]["prefix"] . "bwlist` WHERE `type`='black'";
$res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
$blacks = array();

while ($row = mysql_fetch_assoc($res)) {
    $blacks[] = $row;
}

if ($blacks) {
?>
<table class="items">
    <tr>
        <td class="title"><?php print $CNCAT["lang"]["expression"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["check_method"]?></td>
        <td class="title" style="width: 16px;">&nbsp;</td>
        <td class="title" style="width: 16px;">&nbsp;</td>
    </tr>
<?php
    foreach ($blacks as $black) {
?>
    <tr>
        <td class="item"><code><?php print htmlspecialchars($black["check_str"])?></code></td>
        <td class="item"><?php print $black["check_method"] == "substr" ? $CNCAT["lang"]["substr"] : $CNCAT["lang"]["regexp"]?></td>
        <td class="item"><a href="index.php?act=bwlist&mode=editblack&id=<?php print $black["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/edit.gif" alt="" /></a></td>
        <td class="item"><a href="index.php?act=bwlist&mode=delete&id=<?php print $black["id"]?>" onclick="return confirm('<?php print cn_str_replace("%NAME%", htmlspecialchars(cn_str_replace("'", "\'", $black["check_str"])), $CNCAT["lang"]["really_delete"])?>');"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete.gif" alt="" /></a></td>
    </tr>
<?php
    }
?>
</table>
<?php
} else {
    print "<span class=\"not_found\">" . $CNCAT["lang"]["blackl_empty"] . "</span>";
}
?>
<p><?php print $CNCAT["lang"]["blackl_regexp_hint"]?></p>
<?php
$black = array();

if ($_mode == "editblack" && isset($_GET["id"])) {
    $id = (int)$_GET["id"];

    $query = "SELECT `id`, `check_method`, `check_str` FROM `" . $CNCAT["config"]["db"]["prefix"] . "bwlist` WHERE `type`='black' AND `id`=" . $id;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $black = mysql_fetch_assoc($res);
}

if ($black) {
?>
<form action="index.php?act=bwlist&mode=editblack&id=<?php print $black["id"]?>" method="post">
<p>
    <input type="text" class="text" style="width: 200px;" name="check_str" value="<?php print htmlspecialchars($black["check_str"])?>" />
    <select name="check_method">
        <option value="substr" <?php print $black["check_method"] == "substr" ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["substr"]?></option>
        <option value="regexp" <?php print $black["check_method"] == "regexp" ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["regexp"]?></option>
    </select>
    <input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_save"]?>" />
    <input type="button" class="submit" onclick="location.href='index.php?act=bwlist'" value="<?php print $CNCAT["lang"]["do_cancel"]?>" />
</p>
</form>
<?php
} else {
?>
<form action="index.php?act=bwlist&mode=addblack" method="post">
<p>
    <input type="text" class="text" style="width: 200px;" name="check_str" />
    <select name="check_method">
        <option value="substr"><?php print $CNCAT["lang"]["substr"]?></option>
        <option value="regexp"><?php print $CNCAT["lang"]["regexp"]?></option>
    </select>
    <input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_submit"]?>" />
</p>
</form>
<?php
}
?>
<div class="deline"></div>
<?php
$query = "SELECT `id`, `check_str` FROM `" . $CNCAT["config"]["db"]["prefix"] . "bwlist` WHERE `type`='white'";
$res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
$whites = array();

while ($row = mysql_fetch_assoc($res)) {
    $whites[] = $row;
}

if ($whites) {
?>
<table class="items">
    <tr>
        <td class="title"><?php print $CNCAT["lang"]["whitel"]?></td>
        <td class="title" style="width: 16px;">&nbsp;</td>
        <td class="title" style="width: 16px;">&nbsp;</td>
    </tr>
<?php
    foreach ($whites as $white) {
?>
    <tr>
        <td class="item"><?php print htmlspecialchars($white["check_str"])?></td>
        <td class="item"><a href="index.php?act=bwlist&mode=editwhite&id=<?php print $white["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/edit.gif" alt="" /></a></td>
        <td class="item"><a href="index.php?act=bwlist&mode=delete&id=<?php print $white["id"]?>" onclick="return confirm('<?php print cn_str_replace("%NAME%", htmlspecialchars(cn_str_replace("'", "\'", $white["check_str"])), $CNCAT["lang"]["really_delete"])?>');"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/delete.gif" alt="" /></a></td>
    </tr>
<?php
    }
?>
</table>
<?php
} else {
    print "<span class=\"not_found\">" . $CNCAT["lang"]["whitel_empty"] . "</span>";
}

$white = array();

if ($_mode == "editwhite" && isset($_GET["id"])) {
    $id = (int)$_GET["id"];

    $query = "SELECT `id`, `check_str` FROM `" . $CNCAT["config"]["db"]["prefix"] . "bwlist` WHERE `type`='white' AND `id`=" . $id;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $white = mysql_fetch_assoc($res);
}

if ($white) {
?>
<form action="index.php?act=bwlist&mode=editwhite&id=<?php print $white["id"]?>" method="post">
<p>
    <input type="text" class="text" style="width: 200px;" name="check_str" value="<?php print htmlspecialchars($white["check_str"])?>" />
    <input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_save"]?>" />
    <input type="button" class="submit" onclick="location.href='index.php?act=bwlist'" value="<?php print $CNCAT["lang"]["do_cancel"]?>" />
</p>
</form>
<?php
} else {
?>
<form action="index.php?act=bwlist&mode=addwhite" method="post">
<p>
    <input type="text" class="text" style="width: 200px;" name="check_str" />
    <input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_submit"]?>" />
</p>
</form>
<?php
}
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
