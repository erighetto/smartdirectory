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

/**
 * Check moderator categorie permission
 * 
 * @global $CNCAT_ENGINE
 * @global $CNCAT
 * @staticvar string $cats
 * @staticvar string $add
 * @param $act
 * @param $cat_id
 * @return boolean 
 */
function catCanDo($act, $cat_id) {
    GLOBAL $CNCAT_ENGINE, $CNCAT;

    if (isAdmin()) {
        return true;
    } elseif (isModer()) {
        static $cats = null;
        static $add = null;
    
        if ($cats === null) {
            $query = "SELECT cid FROM `" . $CNCAT["config"]["db"]["prefix"] . "modercat` WHERE `mid`=" . getModerId();
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

            $add = array();
            $cats = array();
            $child = array();

            while ($row = mysql_fetch_assoc($res)) {
                $cats[] = $row["cid"];
                $add[] = $row["cid"];
            }

            if ($cats) {
                $query = "SELECT `tree_level`, `child_id_list` FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats` WHERE `id` IN(" . join(",", $cats) . ") ORDER BY `tree_level` ASC";
                $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

                $cats = array();
                $tree_level = 0;

                while ($row = mysql_fetch_assoc($res)) {
                    if (!$tree_level) {
                        $tree_level = $row["tree_level"];
                    }

                    $cats = array_merge($cats, (array)explode(",", $row["child_id_list"]));
                }
            }

            $cats = array_unique($cats);
        }

        if (in_array($cat_id, $cats) || ($act == "add" && in_array($cat_id, $add))) {
            return true;
        }
    }

    return false;
}

/**
 * Getting Moderator categories access permission
 * @global $CNCAT_ENGINE
 * @global $CNCAT
 * @staticvar boolean $cats
 * @return array 
 */
function getModerCats() {
    GLOBAL $CNCAT_ENGINE, $CNCAT;

    static $cats = false;

    if ($cats === false) {
        $query = "SELECT `cid` FROM `" . $CNCAT["config"]["db"]["prefix"] . "modercat`
            WHERE mid=" . getModerId();
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $cats = array();

        while ($row = mysql_fetch_assoc($res)) {
            $cats[] = $row["cid"];
        }
    }

    return $cats;
}
/**
 * Check Moderator for access to items edition
 * @global $CNCAT_ENGINE
 * @global $CNCAT
 * @param $item_id
 * @return boolean 
 */
function isModerItem($item_id) {
    GLOBAL $CNCAT_ENGINE, $CNCAT;

    $query = "SELECT COUNT(*) AS `count` FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat`
        WHERE `item_id`=" . $item_id . " AND `cat_id` IN (" . join(",", getModerCats()) . ")";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $row = mysql_fetch_assoc($res);

    if ($row["count"]) {
        return true;
    }

    return false;
}
/**
 * Check Moderator for editing in categorie
 * @param type $cat_id
 * @return type 
 */
function isModerCat($cat_id) {
    return in_array($cat_id, getModerCats());
}

if ($_SESSION["isadmin"] != md5("cncat4admin") && (int)$_SESSION["ismoder"] <= 0) {
    $login_error = false;

    if (isset($_POST["doPost"])) {
        $login_error = true;

        $login = cn_trim($_POST["login"]);
        $pass = $_POST["pass"];

        if ($login == $CNCAT["config"]["alogin"] && md5($pass) == $CNCAT["config"]["apassword"]) {
            $_SESSION["isadmin"] = md5("cncat4admin");
            $_SESSION["user_login"] = $CNCAT["config"]["alogin"];

            header("Location: " . $_SERVER["REQUEST_URI"]);
            exit;
        } else {
            $query = "SELECT `id`, `login`, `imgbr_allow` FROM `" . $CNCAT["config"]["db"]["prefix"] . "moders`
                WHERE `login`='" . mysql_escape_string($login) . "' AND `pass`='" . md5($pass) . "'";
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

            if (mysql_num_rows($res) == 1) {
                if ($row = mysql_fetch_assoc($res)) {
                    $_SESSION["ismoder"] = (int)$row["id"];
                    $_SESSION["user_login"] = $row["login"];
                    $_SESSION["moder_imgbr_allow"] = $row["imgbr_allow"];
                    
                    header("Location: " . $_SERVER["REQUEST_URI"]);
                    exit;
                }
            }
        }
    } else {
        if (!empty($CNCAT["config"]["demo"]["alogin"])) {
            $_POST["login"] = $CNCAT["config"]["demo"]["alogin"];
        }
    }
?>
<html>
<head>
    <title>CNCat :: <?php print $CNCAT["lang"]["administration"]?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php print $CNCAT["lang"]["charset"]?>">
    <style type="text/css" media="all">
        body {
            background: white;
            padding: 0;
            margin: 0;
        }
        table {
            border: 0;
            border-collapse: collapse;
        }
        select,input,body,td {
            font-size: 11px;
            font-family: tahoma;
            color: #38464b;
            padding: 0;
        }
        input {
            height: 24px;
            padding: 3px 0 0 5px;
        }
        a {
            color:#3176b1;
        }
        #authform {
            margin: 0 auto;
            width: 335px;
        }
        #authform .box {
            border-right: 1px solid #9bc871;
            border-bottom: 1px solid #9bc871;
        }
        input.text {
            width: 100%;
        }
        input.submit {
            width: 100px;
        }
        .field {
            padding: 5px 25px 5px 5px;
            width: 100%;
        }
        .name {
            padding-right: 20px;
        }
        .button {
            text-align: right;
            padding: 5px 25px 15px 0;
        }
        .title {
            height: 49px;
            background: url('<?php print $CNCAT['abs'] . $CNCAT['system']['dir_engine_images']?>cncatlogo.gif');
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .error {
            color: red;
            padding-bottom: 8px;
        }
        .copyright {
            vertical-align: bottom;
            padding: 10px;
        }
        .copyright table {
            width: auto;
        }
    </style>
</head>
<body>
<table width="100%" height="100%" align="right">
    <tr><td style="valign: middle;">
        <table id="authform" align="center">
            <tr><td class="title">
                <img src="<?php print $CNCAT['abs'] . $CNCAT['system']['dir_engine_images']?>lock.gif" align="right" style="margin: 14px 5px 0 0;" />
                <div style="margin: 22px 0 0 80px;">CNCat <?php print $CNCAT_PRODUCT_VERSION?></div>
            </td></tr>
            <tr><td>
            <form action="?" method="post">
            <table class="box" width="100%">
            <tr><td style="height: 35px;"></td></tr>
            <?php if ($login_error) {?>
                <tr><td class="error" colspan="2"><?php print $CNCAT["lang"]["invalid_auth_data"]?></td></tr>
            <?php }?>
            <tr>
                <td class="name"><?php print $CNCAT["lang"]["login"]?>:</td>
                <td class="field"><input type="text" name="login" value="<?php print htmlspecialchars($_POST["login"])?>" class="text" /></td>
            </tr>
            <tr>
                <td class="name"><?php print $CNCAT["lang"]["password"]?>:</td>
                <td class="field"><input type="password" name="pass" class="text" <?php print !empty($CNCAT["config"]["demo"]["apassword"]) ? "value=\"" . htmlspecialchars($CNCAT["config"]["demo"]["apassword"]) . "\"" : ""?> /></td>
            </tr>
            <tr>
                <td colspan="2" class="button">
                    <input type="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_enter"]?>" class="submit" />
                </td>
            </tr>
            </table>
            </form>
            <a href="<?php print $CNCAT["config"]["cncat_url"]?>"><?php print $CNCAT["lang"]["return_to_catalogue"]?></a>
            </td></tr>
        </table>
    </td></tr>
    <tr><td class="copyright">
        <center><?php print $CNCAT_COPYRIGHT?></center>
    </td></tr>
</table>
</body>
</html>
<?php
    exit;
}
/**
 * Is moder?
 * @return int
 */
function isModer() {
    return ((int)$_SESSION["ismoder"] > 0);
}
/**
 * Is Admin?
 * @return boolean
 */
function isAdmin() {
    return ($_SESSION["isadmin"] == md5("cncat4admin"));
}
/**
 * Return moder session ID
 * @return int
 */
function getModerId() {
    return (int)$_SESSION["ismoder"];
}

/**
 * Render permission denied page
 * @global $CNCAT_ENGINE
 * @global $CNCAT 
 */
function accessDenied() {
    GLOBAL $CNCAT_ENGINE, $CNCAT;

    include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
    print "<h1>Access denied.</h1><div class=\"error_box\">You don't have permissions to access.</div>";
    include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
    exit;
}

$CN_TYPE = "owned";

$CNCAT["admin"]["ajax_replace"] = false;
?>
