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
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "auth.php";

if (!isAdmin()) {
    accessDenied();
}

require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "sync_all.php";

$_mode = $_GET["mode"];

if ($_mode == "add" && isset($_POST["doPost"])) {
/*******************************************************************************
 * ADD BEGIN
 ******************************************************************************/
    $_moder = array();

    $_moder["login"] = cn_substr(cn_trim($_POST["login"]), 0, 255);;
    $_moder["pass"] = cn_substr($_POST["pass"], 0, 255);
    $_moder["pass_confirm"] = cn_substr($_POST["pass_confirm"], 0, 255);
    $_moder["email"] = cn_substr(cn_trim($_POST["email"]), 0, 255);
    $_moder["cat_edit"] = !empty($_POST["cat_edit"]) ? 1 : 0;
    $_moder["imgbr_allow"] = !empty($_POST["imgbr_allow"]) ? 1 : 0;
    $_moder["cats"] = (array)$_POST["cats"];
    $_moder["cats_child"] = (array)$_POST["cats_child"];

    $_errors = array();

    // check login
    if (empty($_moder["login"])) {
        $_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["login"], $CNCAT["lang"]["field_empty"]);
    } elseif (cn_strlen($_moder["login"]) > 255) {
        $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["login"], 255), $CNCAT["lang"]["field_to_long"]);
    } elseif(!preg_match("#[a-z0-9_\-]#i", $_moder["login"])) {
        $_errors[] = $CNCAT["lang"]["moder_login_invalid"];
    } elseif ($_moder["login"] == $CNCAT["config"]["alogin"]) {
        $_errors[] = $CNCAT["lang"]["moder_login_as_admin"];
    } elseif (moderExists("`login`='" . mysql_escape_string($_moder["login"]) . "'")) {
        $_errors[] = $CNCAT["lang"]["moder_login_exists"];
    }

    // check pass
    if (empty($_moder["pass"])) {
        $_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["password"], $CNCAT["lang"]["field_empty"]);
    } elseif (cn_strlen($_moder["pass"]) > 255) {
        $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["password"], 255), $CNCAT["lang"]["field_to_long"]);
    } elseif ($_moder["pass"] != $_moder["pass_confirm"]) {
        $_errors[] = $CNCAT["lang"]["password_confirm_invalid"];
    } else {
        $_moder["pass"] = md5($_moder["pass"]);
        unset($_moder["pass_confirm"]);
    }

    // check email
    if (cn_strlen($_moder["email"]) > 255) {
        $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["email"], 255), $CNCAT["lang"]["field_to_long"]);
    }

    // check cats
    foreach ($_moder["cats"] as $num => $cat) {
        $_moder["cats"][$num] = (int)$cat;
    }

    foreach ($_moder["cats_child"] as $num => $cat) {
        $_moder["cats_child"][$num] = (int)$cat;
    }

    if (!$_moder["cats"] && !$_moder["cats_child"]) {
        $_errors[] = $CNCAT["lang"]["must_be_select_cat"];
    } else {
        $_moder["cats"] = join(",", $_moder["cats"]);
        $_moder["cats_child"] = join(",", $_moder["cats_child"]);
    }

    // if no errors
    if (!$_errors) {
        moderInsert($_moder);

        cn_syncModers();

        header("Location: index.php?act=moders");
        exit;
    }
/*******************************************************************************
 * ADD END
 ******************************************************************************/
} elseif ($_mode == "edit" && isset($_POST["doPost"])) {
/*******************************************************************************
 * EDIT BEGIN
 ******************************************************************************/
    $_moder_id = (int)$_GET["id"];
    $_moder = array();

    $_moder["login"] = cn_trim($_POST["login"]);
    $_moder["pass"] = $_POST["pass"];
    $_moder["email"] = cn_trim($_POST["email"]);
    $_moder["cat_edit"] = !empty($_POST["cat_edit"]) ? 1 : 0;
    $_moder["imgbr_allow"] = !empty($_POST["imgbr_allow"]) ? 1 : 0;
    $_moder["cats"] = (array)$_POST["cats"];
    $_moder["cats_child"] = (array)$_POST["cats_child"];

    $_errors = array();

    if (moderExists("`id`=" . $_moder_id)) {
        // check login
        if (empty($_moder["login"])) {
            $_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["login"], $CNCAT["lang"]["field_empty"]);
        } elseif (cn_strlen($_moder["login"]) > 255) {
            $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["login"], 255), $CNCAT["lang"]["field_to_long"]);
        } elseif(!preg_match("#[a-z0-9_\-]#i", $_moder["login"])) {
            $_errors[] = $CNCAT["lang"]["moder_login_invalid"];
        } elseif ($_moder["login"] == $CNCAT["config"]["alogin"]) {
            $_errors[] = $CNCAT["lang"]["moder_login_as_admin"];
        } elseif (moderExists("`login`='" . mysql_escape_string($_moder["login"]) . "' AND `id`!=" . $_moder_id)) {
            $_errors[] = $CNCAT["lang"]["moder_login_exists"];
        }
        // check pass
        if (cn_strlen($_moder["pass"]) > 255) {
            $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["password"], 255), $CNCAT["lang"]["field_to_long"]);
        }
        // check email
        if (cn_strlen($_moder["email"]) > 255) {
            $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["email"], 255), $CNCAT["lang"]["field_to_long"]);
        }
        // check cats
        foreach ($_moder["cats"] as $num => $cat) {
            $_moder["cats"][$num] = (int)$cat;
        }
        foreach ($_moder["cats_child"] as $num => $cat) {
            $_moder["cats_child"][$num] = (int)$cat;
        }

        if (!empty($_moder["pass"])) {
            $_moder["pass"] = md5($_moder["pass"]);
        } else {
            unset($_moder['pass']);
        }

        if (!$_moder["cats"] && !$_moder["cats_child"]) {
            $_errors[] = $CNCAT["lang"]["must_be_select_cat"];
        } else {
            $_moder["cats"] = join(",", $_moder["cats"]);
            $_moder["cats_child"] = join(",", $_moder["cats_child"]);
        }

        // if no errors
        if (!$_errors) {
            moderUpdate($_moder, "`id`=" . $_moder_id);

            cn_syncModers();

            header("Location: index.php?act=moders");
            exit;
        }
    }
/*******************************************************************************
 * EDIT END
 ******************************************************************************/
} elseif ($_mode == "delete") {
/*******************************************************************************
 * DELETE BEGIN
 ******************************************************************************/
    $_moder_id = (int)$_GET["id"];
    moderDelete("`id`=" . $_moder_id);

    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "modercat` WHERE `mid`=" . $_moder_id;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=moders");
    exit;
/*******************************************************************************
 * DELETE END
 ******************************************************************************/
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";

if ($_mode == "add") {
/*******************************************************************************
 * ADD BEGIN
 ******************************************************************************/
?>
<h1><?php print $CNCAT["lang"]["moders"]?> / <?php print $CNCAT["lang"]["addition"]?></h1>
<a href="index.php?act=moders"><?php print $CNCAT["lang"]["moders_list"]?></a>
<div class="deline"></div>
<?php
if ($_errors) {
    print "<ul class=\"errors\">\n";

    foreach ($_errors as $error) {
        print "<li>" . $error . "</li>\n";
    }

    print "</ul>\n";
}
?>
<table class="form">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["moder"]?></td></tr>
<form action="index.php?act=moders&mode=add" method="post">
    <tr><td class="name"><?php print $CNCAT["lang"]["login"]?></td><td class="field"><input type="text" name="login" value="<?php print htmlspecialchars($_POST["login"])?>" class="text" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["password"]?></td><td class="field"><input type="password" name="pass" class="text" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["password_confirm"]?></td><td class="field"><input type="password" name="pass_confirm" class="text" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["email"]?></td><td class="field"><input type="text" name="email" value="<?php print htmlspecialchars($_POST["email"])?>" class="text" /></td></tr>
    <tr><td colspan="2" class="deline"></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["can_cat_edit"]?></td><td class="field"><input type="checkbox" name="cat_edit" <?php print $_POST["cat_edit"] ? "checked=\"checked\"" : ""?>/></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["moder_imgbr_allow"]?></td><td class="field"><input type="checkbox" name="imgbr_allow" <?php print $_POST["imgbr_allow"] ? "checked=\"checked\"" : ""?>/></td></tr>
    <tr><td colspan="2" class="deline"></td></tr>
    <tr><td class="name" colspan="2"><?php print $CNCAT["lang"]["moder_cats"]?></td></tr>
    <tr><td class="name" colspan="2">
<?php
    print "<div class=\"over_box\" style=\"height: 300px;\">\n";

    // get and print categories tree
    $level = 0;

    foreach (catSelect(
            "`id`, `title`, `tree_level`",
            "`parent_id`!=-1",
            "ORDER BY `title_full`, `id`"
        ) as $cat
    ) {
        while ($cat["tree_level"] > $level) {
             print "<ul>\n";
            $level++;
        }   

        while ($cat["tree_level"] < $level) {
            print "</ul>\n";
            $level--;
        }

        print "<li><input type=\"checkbox\" name=\"cats[]\" value=\"" . $cat["id"] . "\" ";
        print in_array($cat["id"], (array)$_POST["cats"]) ? "checked=\"checked\"" : "";
        print " />";
        // with child
        print "<input type=\"checkbox\" name=\"cats_child[]\" value=\"" . $cat["id"] . "\" ";
        print in_array($cat["id"], (array)$_POST["cats_child"]) ? "checked=\"checked\"" : "";
        print " />";

        print htmlspecialchars($cat["title"]) . "</li>\n";
    }

    print "</div>\n";
?>
    </td></tr>
    <tr><td colspan="2" class="submit"><input type="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_submit"]?>" class="submit" /></td></tr>
</form>
</table>
<?php
/*******************************************************************************
 * ADD END
 ******************************************************************************/
} elseif ($_mode == "edit") {
/*******************************************************************************
 * EDIT BEGIN
 ******************************************************************************/
    $_moder_id = (int)$_GET["id"];

    if (moderExists("`id`=" . $_moder_id)) {
        if (!isset($_POST["doPost"])) {
            list($_moder) = moderSelect("`login`, `email`, `cat_edit`,`imgbr_allow`, `cats`, `cats_child`", "`id`=" . $_moder_id);
            $_POST = $_moder;
            $_POST["cats"] = explode(",", $_moder["cats"]);
            $_POST["cats_child"] = explode(",", $_moder["cats_child"]);
        }
?>
<h1><?php print $CNCAT["lang"]["moders"]?> / <?php print $CNCAT["lang"]["editing"]?></h1>
<a href="index.php?act=moders"><?php print $CNCAT["lang"]["moders_list"]?></a>
<div class="deline"></div>
<?php
if ($errors) {
    print "<ul>\n";

    foreach ($errors as $error) {
        print "<li style=\"color: red;\">" . $error . "</li>\n";
    }

    print "</ul>\n";
}
?>
<table class="form">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["moder"]?> &laquo;<?php print $_moder["login"]?>&raquo;</td></tr>
<form action="index.php?act=moders&mode=edit&id=<?php print $_moder_id?>" method="post">
    <tr><td class="name"><?php print $CNCAT["lang"]["login"]?></td><td class="field"><input type="text" name="login" value="<?php print htmlspecialchars($_POST["login"])?>" class="text" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["password"]?></td><td class="field"><input type="password" name="pass" class="text" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["email"]?></td><td class="field"><input type="text" name="email" value="<?php print htmlspecialchars($_POST["email"])?>" class="text" /></td></tr>
    <tr><td colspan="2" class="deline"></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["can_cat_edit"]?></td><td class="field"><input type="checkbox" name="cat_edit" <?php print $_POST["cat_edit"] ? "checked=\"checked\"" : ""?>/></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["moder_imgbr_allow"]?></td><td class="field"><input type="checkbox" name="imgbr_allow" <?php print $_POST["imgbr_allow"] ? "checked=\"checked\"" : ""?>/></td></tr>
    <tr><td colspan="2" class="deline"></td></tr>
    <tr><td class="name" colspan="2"><?php print $CNCAT["lang"]["moder_cats"]?></td></tr>
    <tr><td class="name" colspan="2">
<?php
    print "<div class=\"over_box\" style=\"height: 300px;\">\n";

    // get and print categories tree
    $level = 0;

    foreach (catSelect(
            "`id`, `title`, `tree_level`",
            "`parent_id`!=-1",
            "ORDER BY `title_full`, `id`"
        ) as $cat
    ) {
        while ($cat["tree_level"] > $level) {
             print "<ul>\n";
            $level++;
        }   

        while ($cat["tree_level"] < $level) {
            print "</ul>\n";
            $level--;
        }

        print "<li><input type=\"checkbox\" name=\"cats[]\" value=\"" . $cat["id"] . "\" ";
        print in_array($cat["id"], (array)$_POST["cats"]) ? "checked=\"checked\"" : "";
        print " />";
        // with child
        print "<input type=\"checkbox\" name=\"cats_child[]\" value=\"" . $cat["id"] . "\" ";
        print in_array($cat["id"], (array)$_POST["cats_child"]) ? "checked=\"checked\"" : "";
        print " />";

        print htmlspecialchars($cat["title"]) . "</li>\n";
    }

    print "</div>\n";
?>
    </td></tr>
    <tr><td colspan="2" class="submit"><input type="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_save"]?>" class="submit" /></td></tr>
</form>
</table>
<?php
    }
/*******************************************************************************
 * EDIT END
 ******************************************************************************/
} else {
/*******************************************************************************
 * INDEX BEGIN
 ******************************************************************************/
?>
<h1><?php print $CNCAT["lang"]["moders"]?></h1>
<a href="index.php?act=moders&mode=add"><?php print $CNCAT["lang"]["moder_add"]?></a>
<div class="deline"></div>
<table class="list">
<?php
$query = "SELECT id, login, email, cat_edit, imgbr_allow
    FROM `" . $CNCAT["config"]["db"]["prefix"] . "moders`
    ORDER BY login";
$res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
$_moders = array();

while ($moder = mysql_fetch_assoc($res)) {
    if (!isset($_moders[$moder["id"]])) {
        $_moders[$moder["id"]] = array();

        $_moders[$moder["id"]]["login"] = $moder["login"];
        $_moders[$moder["id"]]["email"] = $moder["email"];
        $_moders[$moder["id"]]["cat_edit"] = $moder["cat_edit"];
        $_moders[$moder["id"]]["imgbr_allow"] = $moder["imgbr_allow"];
    }
}

if ($_moders) {
?>
    <tr>
        <td class="title"><?php print $CNCAT["lang"]["login"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["email"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["can_cat_edit"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["moder_imgbr_allow"]?></td>
        <td class="title" style="width: 16px;">&nbsp;</td>
        <td class="title" style="width: 16px;">&nbsp;</td>
    </tr>
<?php
} else {
    print "<span class=\"not_found\">" . $CNCAT["lang"]["no_moders"] . "</span>";
}

foreach ($_moders as $moder_id => $moder) {
?>
    <tr>
        <td class="item"><?php print $moder["login"]?></td>
        <td class="item"><?php print $moder["email"]?></td>
        <td class="item">
        <?php if ($moder["cat_edit"]) {?><?php print $CNCAT["lang"]["yes"]?><?php } else {?><?php print $CNCAT["lang"]["no"]?><?php }?>
        </td>
        <td class="item">
        <?php if ($moder["imgbr_allow"]) {?><?php print $CNCAT["lang"]["yes"]?><?php } else {?><?php print $CNCAT["lang"]["no"]?><?php }?>
        </td>
        <td class="item"><a href="index.php?act=moders&mode=edit&id=<?php print $moder_id?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit.gif" alt="" /></a></td>
        <td class="item"><a href="index.php?act=moders&mode=delete&id=<?php print $moder_id?>" onclick="return confirm('<?php print cn_str_replace("%NAME%", htmlspecialchars(cn_str_replace("'", "\'", $moder["login"])), $CNCAT["lang"]["really_delete"])?>');"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete.gif" alt="" /></a></td>
    </tr>
<?php
}
?>
</table>
<?php
}
/*******************************************************************************
 * INDEX END
 ******************************************************************************/
 
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
