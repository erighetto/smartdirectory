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

define("ADMIN_INTERFACE", true);
$ADMIN_INTERFACE = ADMIN_INTERFACE; 
require_once dirname(dirname(__FILE__)) . "/cncat_init.php";
// Initializing engine
if ($CNCAT["system"]["debug"])
    	$CNCAT["system"]["debug_time_engine_init_start"] = cncatGetMicrotime();
$CNCAT_ENGINE = cncatCreateObject ("engine", "CNCatEngine");
$CNCAT_ENGINE->init();
if ($CNCAT["system"]["debug"])
	$CNCAT["system"]["debug_time_engine_init_stop"] = cncatGetMicrotime();
session_start();

$CNCAT['page']['title'] = $CNCAT['config']['catalog_title'];
header("Content-type: text/html; charset=" . $CNCAT["lang"]["charset"]);
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_engine_common"] . "cnimage.php";

if (!$CNCAT_ENGINE->misc->isAdmin() && (!$_SESSION["moder_imgbr_allow"] && $CNCAT_ENGINE->misc->isModer())) {
    exit;
}

// Initialize config
$CNCAT["config"]["image_twidth"] = !empty($CNCAT["config"]["image_twidth"]) ? $CNCAT["config"]["image_twidth"] : 150;
$CNCAT["config"]["image_theight"] = !empty($CNCAT["config"]["image_theight"]) ? $CNCAT["config"]["image_theight"] : 150;
$CNCAT["config"]["image_width"] = !empty($CNCAT["config"]["image_width"]) ? $CNCAT["config"]["image_width"] : 0;
$CNCAT["config"]["image_height"] = !empty($CNCAT["config"]["image_height"]) ? $CNCAT["config"]["image_height"] : 0;

$_act = !empty($_GET["act"]) ? $_GET["act"] : "";
$_mode = !empty($_GET["mode"]) ? $_GET["mode"] : "";

/*******************************************************************************
 * Categories
 *******************************************************************************/ 
if ($_act == "cats") {
    if ($_mode == "add" && isset($_POST["doPost"])) {
        $cat_title = !empty($_POST["title"]) ? trim($_POST["title"]) : "";

        if (!empty($cat_title)) {
            $cat_title = substr($cat_title, 0, 100);
            $query = "
                INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "img_cats`
                (`cat_id`, `cat_title`)
                VALUES (NULL, '" . mysql_escape_string($cat_title) . "')
            ";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }

        header("Location: " . $CNCAT["abs"] . $CNCAT["system"]["dir_admin"] ."image_browser.php?act=cats");
        exit;
    } elseif ($_mode == "edit" && isset($_POST["doPost"])) {
        $cat_id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
        $cat_title = !empty($_POST["title"]) ? trim($_POST["title"]) : "";

        if ($cat_id) {
            $cat_title = substr($cat_title, 0, 100);

            $query = "
                UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "img_cats`
                SET `cat_title` = '" . mysql_escape_string($cat_title) . "'
                WHERE `cat_id` = " . $cat_id . "
            ";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }

        header("Location: " . $CNCAT["abs"] . $CNCAT["system"]["dir_admin"] . "image_browser.php?act=cats");
        exit;
    } elseif ($_mode == "delete") {
        $cat_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

        if ($cat_id) {
            $query = "
                UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "images`
                SET `img_cid` = 0
                WHERE `img_cid` = " . $cat_id . "
            ";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            $query = "
                DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "img_cats` WHERE `cat_id` = " . $cat_id . "
            ";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }

        header("Location: " . $CNCAT["abs"] . $CNCAT["system"]["dir_admin"] . "image_browser.php?act=cats");
        exit;
    }
/*******************************************************************************
 * Image upload
 *******************************************************************************/ 
} elseif ($_act == "upload" && isset($_POST["doPost"])) {
    $cat_id = !empty($_POST["cid"]) ? (int)$_POST["cid"] : 0;

    if ($cat_id < 0) {
        $cat_id = 0;
    }

    $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";

    if (!$_FILES["file"]["error"]) {
        $title = cn_substr($title, 0, 100);

            if ($result = cn_image_proccess($_FILES["file"]["tmp_name"])) {
                $query = "
                    INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "images`
                    (`img_id`, `img_cid`, `img_title`, `img_mime`, `img_data`, `thumb_data`)
                    VALUES (
                        NULL,
                        " . $cat_id . ",
                        '" . mysql_escape_string($title) . "',
                        '" . mysql_escape_string($result["image_mime"]) . "',
                        '" . mysql_escape_string($result["image_data"]) . "',
                        '" . mysql_escape_string($result["thumb_data"]) . "'
                    )
                ";
                $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            }
    }

    if (!empty($_SERVER["HTTP_REFERER"])) {
        header("Location: " . $_SERVER["HTTP_REFERER"]);
    } else {
        header("Location: " . $CNCAT["abs"] . $CNCAT["system"]["dir_admin"] . "image_browser.php?cat_id=" . $cat_id);
    }
    exit;
/*******************************************************************************
 * Image delete
 *******************************************************************************/ 
} elseif ($_act == "delete") {
    $img_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
    $cat_id = -1;

    if ($img_id) {
        $query = "
            SELECT img_cid
            FROM `" . $CNCAT["config"]["db"]["prefix"] . "images`
            WHERE img_id = " . $img_id . "
        ";
        $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        if ($row = mysql_fetch_assoc($result)) {
            $cat_id = $row["img_cid"];

            $query = "
                DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "images` WHERE img_id = " . $img_id . "
            ";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }
    }

    if (!empty($_SERVER["HTTP_REFERER"])) {
        header("Location: " . $_SERVER["HTTP_REFERER"]);
    } else {
        header("Location: " . $CNCAT["abs"] . $CNCAT["system"]["dir_admin"] . "image_browser.php?cat_id=" . $cat_id);
    }
    exit;
/*******************************************************************************
 * Image edit
 *******************************************************************************/ 
} elseif ($_act == "edit" && isset($_POST["doPost"])) {
    $img_id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
    $cat_id = isset($_POST["cat_id"]) ? (int)$_POST["cat_id"] : 0;
    $img_title = isset($_POST["title"]) ? cn_substr($_POST["title"], 0, 100) : "";
    $prev_cat_id = -1;

    if ($img_id) {
        $query = "
            SELECT img_cid
            FROM `" . $CNCAT["config"]["db"]["prefix"] . "images`
            WHERE img_id = " . $img_id . "
        ";
        $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        if ($row = mysql_fetch_assoc($result)) {
            $prev_cat_id = $row["img_cid"];

            if (!$_FILES["file"]["error"]) {
                    if ($result = cn_image_proccess($_FILES["file"]["tmp_name"])) {
                        $query = "
                            UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "images`
                            SET
                                img_cid = " . $cat_id . ",
                                img_title = '" . mysql_escape_string($img_title) . "',
                                img_mime = '" . mysql_escape_string($result["image_mime"]) . "',
                                img_data = '" . mysql_escape_string($result["image_data"]) . "',
                                thumb_data = '" . mysql_escape_string($result["thumb_data"]) . "'
                            WHERE img_id = " . $img_id . "
                        ";
                        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                    }
            } else {
                $query = "
                    UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "images`
                    SET
                        img_cid = " . $cat_id . ",
                        img_title = '" . mysql_escape_string($img_title) . "'
                    WHERE img_id = " . $img_id . "
                ";
                $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            }
        }
    }

    header("Location: " . $CNCAT["abs"] . $CNCAT["system"]["dir_admin"] . "image_browser.php?cat_id=" . $prev_cat_id);
    exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/2002/REC-xhtml1-20020801/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php print $CNCAT["lang"]["images"]?></title>
    <style type="text/css">
    * {
        margin: 0;
        padding: 0;
    }
    body {
        background: white;
        padding: 0;
        margin: 0;
    }
    div, input, select, a, td {
        font-family:tahoma; font-size:11px; color:#38464b;
    }
    a img {
        border: 0;
    }
    h3, p, form {
        margin: 5px 0;
    }
    input {
        padding: 1px 2px;
    }
    option {
        padding: 1px 2px;
    }
    h3 {
        font-size: 14px;
    }
    a {
        color:#3176b1;
    }
    #imglist {
        width: 100%;
        height: 500px;
        overflow: scroll;
        padding-bottom: 5px;
        border-top: 1px solid silver;
    }
    #imglist .image {
        float: left;
        height: <?php print $CNCAT["config"]["image_theight"] + 40?>px;
        border: 1px solid silver;
        margin: 5px 0 0 1px;
    }
    #imglist .image .clr {
        padding: 5px;
        border-bottom: 1px solid silver;
        background: #f4f4f4;
    }
    #imglist .image .lnk {
        padding: 5px;
    }
    #imgview {
        width: 100%;
        height: 438px;
        overflow: scroll;
        border-top: 1px solid silver;
        border-bottom: 1px solid silver;
    }
    #imgview img {
        margin: 10px;
    }
    .content {
        padding: 10px;
    }
    table {
        border-collapse: collapse;
    }
    td.item {
        padding: 4px;
        vertical-align: middle;
        border:1px solid #c2cdd1;
        white-space: nowrap;
    }
    td.title {
        background:#e8f0f4;border:1px solid #c2cdd1;padding:4px;
        color:black;font-weight:bold;
        white-space: nowrap;
    }
    input.text {
        color:#38464b;
        padding:2px 5px 2px 5px;
    }
    input.submit {
        padding:2px 5px 2px 5px;
    }
    </style>
</head>
<body>
<?php
/*******************************************************************************
 * Categories
 *******************************************************************************/ 
if ($_act == "cats") {
?>
    <div class="content">
    <p><a href="?"><?php print $CNCAT["lang"]["image_list"]?></a></p>
<?php
    $query = "
        SELECT cat_id, cat_title
        FROM `" . $CNCAT["config"]["db"]["prefix"] . "img_cats`
        ORDER BY cat_title ASC
    ";
    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $cats = array();

    while ($row = mysql_fetch_assoc($result)) {
        $cats[$row["cat_id"]] = $row;
    }
?>
    <table class="items">
<?php
    if ($cats) {
?>
        <tr><td class="title"><?php print $CNCAT["lang"]["title"]?></td><td class="title"></td><td class="title"></td></tr>
<?php
        foreach ($cats as $cat) {
?>
        <tr>
            <td class="item"><?php print $cat["cat_title"]?></td>
            <td class="item"><a href="?act=cats&mode=edit&id=<?php print $cat["cat_id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit.gif" alt="" /></a></td>
            <td class="item"><a href="?act=cats&mode=delete&id=<?php print $cat["cat_id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete.gif" alt="" /></a></td>
        </tr>
<?php
        }
    } else {
?>
        <em><?php print $CNCAT["lang"]["no_cats"]?></em>
<?php
    }
  
    if ($_mode == "edit") {
        $cat_id = isset($_GET["id"]) ? (int)$_GET["id"] : "";
        $cat_title = isset($cats[$_GET["id"]]) ? $cats[$_GET["id"]]["cat_title"] : "";
    }
?>
    </table>
    <?php if ($_mode == "edit") {?>
        <form action="?act=cats&mode=edit" method="post">
            <input type="hidden" name="id" value="<?php print $cat_id?>" />
            <input type="text" class="text" name="title" value="<?php print $cat_title?>" />
            <input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_save"]?>" />
        </form>
    <?php } else {?>
        <form action="?act=cats&mode=add" method="post">
            <input type="text" class="text" name="title" value="" />
            <input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_submit"]?>" />
        </form>
    <?php }?>
    </div>
<?php
/*******************************************************************************
 * Image edit
 *******************************************************************************/ 
} elseif ($_act == "edit") {
    $img_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
    $img_filename = $CNCAT["abs"] . "{$CNCAT["system"]["dir_prefix"]}image.php?image=" . $img_id;

    $query = "
        SELECT i.img_id, i.img_title, i.img_cid
        FROM `" . $CNCAT["config"]["db"]["prefix"] . "images` i
        WHERE i.img_id = " . $img_id . "
    ";

    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $img = mysql_fetch_assoc($result);
?>
    <div class="content">
        <p><a href="?"><?php print $CNCAT["lang"]["image_list"]?></a></p>
        <h3><?php print $img["img_title"]?></h3>
    </div>
    <div id="imgview">
        <img src="<?php print $img_filename?>?<?php print rand()?>" /></div>
    </div>
    <div class="content">
<?php
    $query = "
        SELECT cat_id, cat_title
        FROM `" . $CNCAT["config"]["db"]["prefix"] . "img_cats`
        ORDER BY cat_title ASC
    ";
    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $cats = array();

    while ($row = mysql_fetch_assoc($result)) {
        $cats[] = $row;
    }
?>
        <script type="text/javascript">
        s = <?php if (empty($img['img_title'])) {?>true<?php } else {?>false<?php }?>;
        </script>
        <form action="?act=edit&mode=save" method="post" enctype="multipart/form-data" onsubmit="if(s){this.title.value=''};return true;">
            <input type="hidden" name="id" value="<?php print $img_id?>" />
        <?php if (empty($img['img_title'])) {?>
            <input type="text" name="title" class="text" size="30" value="<?php print $CNCAT["lang"]["title"]?>..." onfocus="if(s){this.value='';this.style.color='black'};s=false" style="color: silver;" />
        <?php } else {?>
            <input type="text" name="title" class="text" size="30" value="<?php print htmlspecialchars($img["img_title"])?>" />
        <?php }?>
            <select name="cat_id">
                <option value="0"<?php print $img["img_cid"] == 0 ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["without_category"]?></option>
<?php
    foreach ($cats as $cat) {
?>
            <option value="<?php print $cat["cat_id"]?>"<?php print $img["img_cid"] == $cat["cat_id"] ? " selected=\"selected\"" : ""?>>
                <?php print htmlspecialchars($cat["cat_title"])?></option>
<?php
    }
?>
            </select>
            <input type="file" name="file" />
            <input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_save"]?>" />
        </form>
    </div>
<?php
/*******************************************************************************
 * Images list
 ******************************************************************************/
} else {
    $cat_id = isset($_GET["cat_id"]) ? (int)$_GET["cat_id"] : -1;

    $query = "
        SELECT cat_id, cat_title
        FROM `" . $CNCAT["config"]["db"]["prefix"] . "img_cats`
        ORDER BY cat_title ASC
    ";
    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $cats = array();

    while ($row = mysql_fetch_assoc($result)) {
        $cats[] = $row;
    }
?>
    <div class="content">
    <form action="?" method="get">
        <select name="cat_id" onchange="location.href='?cat_id='+this.options[this.selectedIndex].value">
            <option value="-1"<?php print $cat_id == -1 ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["all_cats"]?></option>
            <option value="0"<?php print $cat_id == 0 ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["without_category"]?></option>
<?php
    foreach ($cats as $cat) {
?>
        <option value="<?php print $cat["cat_id"]?>"<?php print $cat_id == $cat["cat_id"] ? " selected=\"selected\"" : ""?>>
            <?php print htmlspecialchars($cat["cat_title"])?></option>
<?php
    }
?>
        </select>
        <a href="?act=cats"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit.gif" alt="" /></a>
    </form>

    <script type="text/javascript">
    s = true;
    </script>
    <form action="?act=upload" method="post" enctype="multipart/form-data" onsubmit="if(s){this.title.value=''};return true;">
        <input type="hidden" name="cid" value="<?php print $cat_id?>" />
        <input type="text" class="text" name="title" size="30" value="<?php print $CNCAT["lang"]["title"]?>..." onfocus="if(s){this.value='';this.style.color='black'};s=false" style="color: silver;" />
        <input type="file" name="file" />
        <input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_upload"]?>" />
    </form>
    </div>
    <div id="imglist">
<?php
    if ($cat_id > 0) {
        $query = "
            SELECT img_id, img_title FROM `" . $CNCAT["config"]["db"]["prefix"] . "images`
            WHERE img_cid = " . $cat_id . "
            ORDER BY img_id DESC
        ";
    } else {
        $query = "
            SELECT img_id, img_title FROM `" . $CNCAT["config"]["db"]["prefix"] . "images`
            " . ($cat_id == 0 ? "WHERE img_cid = 0" : "") . "
            ORDER BY img_id DESC
        ";
    }

    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
    while ($row = mysql_fetch_assoc($result)) {
        $img_url = $CNCAT["abs"] . "{$CNCAT["system"]["dir_prefix"]}image.php?image=" . $row["img_id"];
        $img_turl = $CNCAT["abs"] . "{$CNCAT["system"]["dir_prefix"]}image.php?thumb=" . $row["img_id"];
?>
        <div class="image">
            <div class="clr">
                <a href="?act=edit&id=<?php print $row["img_id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit.gif" alt="" /></a>
                <a href="?act=delete&id=<?php print $row["img_id"]?>" onclick="return confirm('<?php print cn_str_replace("%NAME%", $CNCAT["lang"]["image"] . " " .$row["img_id"], $CNCAT["lang"]["really_delete"])?>');"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete.gif" alt="" /></a>
                <strong><?php print $row["img_title"]?></strong>
            </div>
            <div class="lnk">
                <a href="#" onclick="window.opener.mySetImage('<?php print $img_url?>');window.close();"><img src="<?php print $img_turl?>" /></a>
            </div>
        </div>
<?php
    }
?>
    </div>
<?php
}
?>
</body>
</html>