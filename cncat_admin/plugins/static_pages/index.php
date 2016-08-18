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

if (!defined("ADMIN_INTERFACE")) {
    exit;
}

require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "auth.php";

// Check install
$query = "SHOW TABLES LIKE '" . $CNCAT["config"]["db"]["prefix"] . "pages'";
$result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

if (!mysql_num_rows($result)) {
    $install_queries = array(
        "CREATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "pages` (`id` int(11) NOT NULL auto_increment, `title` varchar(255) NOT NULL default '', `name` varchar(255) NOT NULL default '', `contents` text NOT NULL,`page_order` INT( 10 ) NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8",
        "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "config` (name, value) VALUES ('static_page_enable', 1)"
    );

    foreach ($install_queries as $query) {
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=static_pages");
    exit;
} 
elseif ( empty($CNCAT["config"]["static_page_enable"]) )
{
    $install_queries = array(
        "ALTER TABLE `" . $CNCAT["config"]["db"]["prefix"] . "pages` ADD `page_order` INT( 10 ) NOT NULL",
        "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "config` (name, value) VALUES ('static_page_enable', 1)"
    );

    foreach ($install_queries as $query) {
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=static_pages");
    exit;
}

// Load config
require_once @dirname(__FILE__) . "/config.default.php";
$config_file = dirname(__FILE__) . "/config.php";

if (file_exists($config_file)) {
    require_once $config_file;
}

$_mode = isset($_GET["mode"]) ? $_GET["mode"] : "";

if ($_mode == "add") {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $form_data = array(
            "title"    => isset($_POST["title"]) ? $_POST["title"] : "",
            "name"     => isset($_POST["name"]) ? $_POST["name"] : "",
            "page_order"    => isset($_POST["page_order"]) ? intval($_POST["page_order"]) : 0,
            "contents" => isset($_POST["contents"]) ? $_POST["contents"] : "",
        );

        if (empty($form_data["title"])) {
            $form_errors[] = $CNCAT["plugins"]["static_pages"]["lang"]["title_empty"];
        }

        if (empty($form_data["contents"])) {
            $form_errors[] = $CNCAT["plugins"]["static_pages"]["lang"]["content_empty"];
        }

        if (!$form_errors) {
            $query = "
                INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "pages`
                (`id`, `title`, `name`, `contents`, `page_order`)
                VALUES (
                    NULL,
                    '" . mysql_escape_string($form_data["title"]) . "',
                    '" . mysql_escape_string($form_data["name"]) . "',
                    '" . mysql_escape_string($form_data["contents"]) . "',
                    '" . mysql_escape_string($form_data["page_order"]) . "'
                )
            ";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

            header("Location: index.php?act=static_pages");
            exit;
        }
    }
} elseif ($_mode == "edit") {
    $page_id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

    $query = "
        SELECT `id`, `title`, `name`, `contents`, `page_order`
        FROM `" . $CNCAT["config"]["db"]["prefix"] . "pages`
        WHERE `id` = " . $page_id . "
    ";
    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $page = mysql_fetch_assoc($result);

    if ($page) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $form_data = array(
                "title"    => isset($_POST["title"]) ? $_POST["title"] : "",
                "name"     => isset($_POST["name"]) ? $_POST["name"] : "",
                "page_order"     => isset($_POST["page_order"]) ? intval($_POST["page_order"]) : 0,
                "contents" => isset($_POST["contents"]) ? $_POST["contents"] : ""
            );
    
            if (empty($form_data["title"])) {
                $form_errors[] = $CNCAT["plugins"]["static_pages"]["lang"]["empty_title"];
            }

            if (empty($form_data["contents"])) {
                $form_errors[] = $CNCAT["plugins"]["static_pages"]["lang"]["content_empty"];
            }

            if (!$form_errors) {
                $query = "
                    UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "pages` SET
                        `title` = '" . mysql_escape_string($form_data["title"]) . "',
                        `name` = '" . mysql_escape_string($form_data["name"]) . "',
                        `page_order` = '" . mysql_escape_string($form_data["page_order"]) . "',
                        `contents` = '" . mysql_escape_string($form_data["contents"]) . "'
                    WHERE `id` = " . $page_id . "
                ";
                $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
                header("Location: index.php?act=static_pages");
                exit;
            }
        } else {
            $form_data = array(
                "title"    => $page["title"],
                "name"     => $page["name"],
                "page_order"     => $page["page_order"],
                "contents" => $page["contents"]
            );
        }
    }
} elseif ($_mode == "delete") {
    $page_id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

    $query = "
        DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "pages` WHERE `id` = " . $page_id . "
    ";
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=static_pages");
    exit;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";

if ($_mode == "add") {
?>
    <h1><?php print $CNCAT["plugins"]["static_pages"]["lang"]["_title"]?> / <?php print $CNCAT["plugins"]["static_pages"]["lang"]["addition"]?></h1>
    <script type="text/javascript" src="<?php print $CNCAT["config"]["cncat_url"] . $CNCAT["system"]["dir_engine_scripts"]?>tinymce/tiny_mce.js">
    </script>
    <a href="?act=static_pages"><?php print $CNCAT["plugins"]["static_pages"]["lang"]["pagelist"]?></a>
    <div class="deline"></div>
<?php
    if ($form_errors) {
        print "<ul class=\"errors\">\n";
    
        foreach ($form_errors as $error) {
            print "<li>" . $error . "</li>\n";
        }
    
        print "</ul>\n";
    }
?>
    <table class="form">
    <form action="index.php?act=static_pages&mode=add" method="post">
        <tr><td colspan="2" class="title"><?php print $CNCAT["plugins"]["static_pages"]["lang"]["page"]?></td></tr>
        <tr>
            <td class="name"><?php print $CNCAT["plugins"]["static_pages"]["lang"]["title"]?><span class="required">*</span></td>
            <td class="field"><input type="text" class="text" name="title" value="<?php print htmlspecialchars($form_data["title"])?>" /></td>
        </tr>
        <tr>
            <td class="name"><?php print $CNCAT["plugins"]["static_pages"]["lang"]["name"]?></td>
            <td class="field"><input type="text" class="text" name="name" value="<?php print htmlspecialchars($form_data["name"])?>" /></td>
        </tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["sort"]?></td>
            <td class="field"><input type="text" class="text" name="page_order" value="100" /></td>
        </tr>
        <tr><td class="name" colspan="2"><?php print $CNCAT["plugins"]["static_pages"]["lang"]["contents"]?><span class="required">*</span></td></tr>
        <tr><td class="name" colspan="2">
            <?php print $CNCAT_ENGINE->render->renderTextEditor("contents", $form_data["contents"]);?>
        </td></tr>
        <tr><td class="deline" colspan="2"></td></tr>
        <tr><td class="submit" colspan="2">
            <input type="submit" class="submit" value="<?php print $CNCAT["plugins"]["static_pages"]["lang"]["do_add"]?>" />
        </td></tr>
    </form>
    </table>
    <br />
    <?php print $CNCAT["plugins"]["static_pages"]["lang"]["note"] ?>
<?php
} elseif ($_mode == "edit") {
?>
    <h1><?php print $CNCAT["plugins"]["static_pages"]["lang"]["_title"]?> / <?php print $CNCAT["plugins"]["static_pages"]["lang"]["editing"]?></h1>
    <a href="?act=static_pages"><?php print $CNCAT["plugins"]["static_pages"]["lang"]["pagelist"]?></a>
    <div class="deline"></div>
<?php
    if (!$page) {
        print "<span class=\"not_found\">" . $CNCAT["plugins"]["static_pages"]["lang"]["page_not_exists"] . "</span>";
    } else {
?>
    <script type="text/javascript" src="<?php print $CNCAT["config"]["cncat_url"] . $CNCAT["system"]["dir_engine_scripts"]?>tinymce/tiny_mce.js">
    </script>
<?php
    if ($form_errors) {
        print "<ul class=\"errors\">\n";
    
        foreach ($form_errors as $error) {
            print "<li>" . $error . "</li>\n";
        }
    
        print "</ul>\n";
    }
?>
    <table class="form">
    <form action="index.php?act=static_pages&mode=edit&id=<?php print $page["id"]?>" method="post">
        <tr><td colspan="2" class="title"><?php print $CNCAT["plugins"]["static_pages"]["lang"]["page"]?></td></tr>
        <tr>
            <td class="name"><?php print $CNCAT["plugins"]["static_pages"]["lang"]["title"]?><span class="required">*</span></td>
            <td class="field"><input type="text" class="text" name="title" value="<?php print htmlspecialchars($form_data["title"])?>" /></td>
        </tr>
        <tr>
            <td class="name"><?php print $CNCAT["plugins"]["static_pages"]["lang"]["name"]?></td>
            <td class="field"><input type="text" class="text" name="name" value="<?php print htmlspecialchars($form_data["name"])?>" /></td>
        </tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["sort"]?></td>
            <td class="field"><input type="text" class="text" name="page_order" value="<?php print htmlspecialchars($form_data["page_order"])?>" /></td>
        </tr>
        <tr><td class="name" colspan="2"><?php print $CNCAT["plugins"]["static_pages"]["lang"]["contents"]?><span class="required">*</span></td></tr>
        <tr><td class="name" colspan="2">
            <?php print $CNCAT_ENGINE->render->renderTextEditor("contents", $form_data["contents"]);?>
        </td></tr>
        <tr><td class="deline" colspan="2"></td></tr>
        <tr><td class="submit" colspan="2">
            <input type="submit" class="submit" value="<?php print $CNCAT["plugins"]["static_pages"]["lang"]["do_save"]?>" />
        </td></tr>
    </form>
    </table>
    <div class="deline"></div>
    <?php print $CNCAT["plugins"]["static_pages"]["lang"]["note"] ?>
<?php
    }
} else {
    $query = "SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "pages` ORDER BY id DESC";
    $result = mysql_query($query);
    $pages = array();

    while ($row = mysql_fetch_assoc($result)) {
        $pages[] = $row;
    }
?>
    <h1><?php print $CNCAT["plugins"]["static_pages"]["lang"]["_title"]?></h1>
    <a href="index.php?act=static_pages&mode=add"><?php print $CNCAT["plugins"]["static_pages"]["lang"]["do_add"]?></a>
    <div class="deline"></div>
    <div class="ok_box"><?php print $CNCAT["plugins"]["static_pages"]["lang"]["hint"]?></div>
<?php
    if ($pages) {
?>
    <table class="list">
    <tr><td class="title">
        <?php print $CNCAT["plugins"]["static_pages"]["lang"]["title"]?>
    </td><td class="title">
        <?php print $CNCAT["plugins"]["static_pages"]["lang"]["name"]?>
    </td><td class="title">
        URL
    </td><td class="title">
        <?php print $CNCAT["lang"]["sort"] ?>
    </td><td class="title"></td><td class="title"></td></tr>
    <?php foreach ($pages as $page) {?>
        <tr>
            <td class="item"><?php print htmlspecialchars($page["title"])?></td>
            <td class="item"><?php print htmlspecialchars($page["name"])?></td>
            <td class="item">
            <?php if ($page["name"]) {?>
                <a href="<?php print $CNCAT["config"]["cncat_url"]?>page.php?id=<?php print htmlspecialchars($page["name"])?>" target="_blank"><?php print $CNCAT["config"]["cncat_url"]?>page.php?id=<?php print htmlspecialchars($page["name"])?></a><br />
                <a href="<?php print $CNCAT["config"]["cncat_url"]?>page/<?php print htmlspecialchars($page["name"])?>"><?php print $CNCAT["config"]["cncat_url"]?>page/<?php print htmlspecialchars($page["name"])?></a>
            <?php } else {?>
                <a href="<?php print $CNCAT["config"]["cncat_url"]?>page.php?id=<?php print $page["id"]?>" target="_blank"><?php print $CNCAT["config"]["cncat_url"]?>page.php?id=<?php print $page["id"]?></a><br />
                <a href="<?php print $CNCAT["config"]["cncat_url"]?>page/<?php print $page["id"]?>"><?php print $CNCAT["config"]["cncat_url"]?>page/<?php print $page["id"]?></a>
            <?php }?>
            </td>
            <td class="item"><?php print $page["page_order"]?></td>
            <td class="item"><a href="index.php?act=static_pages&mode=edit&id=<?php print $page["id"]?>"><img src="<?php print $CNCAT["config"]["cncat_url"] . $CNCAT["system"]["dir_engine_images"]?>/edit.gif" alt="" /></a></td>
            <td class="item"><a href="index.php?act=static_pages&mode=delete&id=<?php print $page["id"]?>" onclick="return confirm('<?php print cn_str_replace("%NAME%", htmlspecialchars(cn_str_replace("'", "\'", htmlspecialchars($page["title"]))), $CNCAT["lang"]["really_delete"])?>');"><img src="<?php print $CNCAT["config"]["cncat_url"] . $CNCAT["system"]["dir_engine_images"]?>/delete.gif" alt="" /></a></td>
        </tr>
    <?php }?>
    </table>
<?php
    } else {
        print "<span class=\"not_found\">" . $CNCAT["plugins"]["static_pages"]["lang"]["no_pages"] . "</span>";
    }
?>
<?php
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
