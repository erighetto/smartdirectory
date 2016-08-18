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

$_mode = !empty($_GET["mode"]) ? $_GET["mode"] : "";

if (!empty($_POST["action"]) || !empty($_GET["action"])) {
    if (!empty($_GET["action"])) {
        $id = intval($_GET["id"]);
        $_POST["action"] = array($id => $_GET["action"]);
    }

    if (is_array($_POST["action"])) {
        foreach ($_POST["action"] as $id => $action) {
            $id = intval($id);

            $query = "
                SELECT active, item_id, vote FROM `" . $CNCAT["config"]["db"]["prefix"] . "comments`
                WHERE id = " . $id . "
            ";
            $result = $CNCAT_ENGINE->db->query($query);
            $comment = mysql_fetch_assoc($result);

            $query = "
                SELECT item_votes_count, item_rating_users FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                WHERE item_id = " . intval($comment["item_id"]) . "
            ";
            $result = $CNCAT_ENGINE->db->query($query);
            $item = mysql_fetch_assoc($result);

            if ($comment && $item) {
                $item_id = $comment["item_id"];
            
                if ($action == "approve") {
                    $query = "
                        UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "comments`
                        SET active = 1, display = 1
                        WHERE id = " . $id . "
                    ";
                    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
                    $query = "
                        UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items`
                        SET item_com_count = item_com_count + 1
                        WHERE item_id = " . $item_id . "
                    ";
                    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                    
                    if ($CNCAT["config"]["rating_enable"]) {
                        if ($comment["vote"] > 0) {
                            $item_rating = 
                                ($item["item_rating_users"] * $item["item_votes_count"] + $comment["vote"]) /
                                ($item["item_votes_count"] + 1);

                            $query = "
                                UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items`
                                SET
                                    item_rating_users = " . $item_rating . ",
                                    item_votes_count = item_votes_count + 1
                                WHERE item_id = " . $item_id . "
                            ";
                            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                        }
                    }
                } elseif ($action == "delete") {
                    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "comments` WHERE id = " . intval($id);
                    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

                    if ($comment["active"]) {
                        $query = "
                            UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items`
                            SET item_com_count = item_com_count - 1
                            WHERE item_id = " . $item_id . "
                        ";
                        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                    }
                    
                    if ($CNCAT["config"]["rating_enable"]) {
                        if ($comment["vote"] > 0) {
                            if ($item["item_votes_count"] > 1) {
                                $item_rating = 
                                    ($item["item_rating_users"] * $item["item_votes_count"] - $comment["vote"]) /
                                    ($item["item_votes_count"] - 1);
                            } else {
                                $item_rating = 0;
                            }

                            if ($item_rating < 0) {
                                $item_rating = 0;
                            }

                            $query = "
                                UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items`
                                SET
                                    item_rating_users = " . $item_rating . ",
                                    item_votes_count = " . ($item["item_votes_count"] ? "item_votes_count - 1" : 0) . "
                                WHERE item_id = " . $item_id . "
                            ";
                            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                        }
                    }
                } elseif ($action == "show") {
                    $query = "
                        UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "comments`
                        SET display = 1
                        WHERE id = " . $id . "
                    ";
                    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        
                    $query = "
                        UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items`
                        SET item_com_count = item_com_count + 1
                        WHERE item_id = " . $item_id . "
                    ";
                    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                } elseif ($action == "hide") {
                    $query = "
                        UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "comments`
                        SET display = 0
                        WHERE id = " . $id . "
                    ";
                    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        
                    $query = "
                        UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items`
                        SET item_com_count = item_com_count - 1
                        WHERE item_id = " . $item_id . "
                    ";
                    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                }
            }
        }
    }

    header("Location: " . (!empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "index.php?act=comments"));
    exit;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";

if ($_mode == "list") {
    $_page = intval($_GET["page"]);

    $query = "
        SELECT COUNT(*) AS `count` FROM `" . $CNCAT["config"]["db"]["prefix"] . "comments`
        WHERE active = 1
    ";
    $result = $CNCAT_ENGINE->db->query($query);
    $row = mysql_fetch_assoc($result);
    $_items_count = $row["count"];
    $_pages_count = ceil($_items_count / 20);

    if ($_page < 0) {
        $_page = 0;
    } elseif ($_page > $_pages_count) {
        $_page = $_pages_count - 1;
    }

    $query = "
        SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "comments`
        INNER JOIN `" . $CNCAT["config"]["db"]["prefix"] . "items`
            USING (item_id)
        WHERE active = 1
        ORDER BY id DESC
        LIMIT " . ($_page * 20) . ", 20
    ";
    $result = $CNCAT_ENGINE->db->query($query);
    $comments = array();

    while ($comment = mysql_fetch_assoc($result)) {
        $comments[] = $comment;
    }

?>
<h1><?php print $CNCAT["lang"]["settings"]?> / <?php print $CNCAT["lang"]["approved_comments"]?></h1>
<a href="index.php?act=comments"><?php print $CNCAT["lang"]["new_comments"]?></a>
<div class="deline"></div>
<?php
    if ($_pages_count > 1) {
        print '<p>' . $CNCAT_ENGINE->render->renderPageNavigation("index.php?act=comments&mode=list&page={PAGE}", $_pages_count, $_page) . '</p>';
    }

    if ($comments) {
?>
<table class="items">
    <tr><td class="title"><?php print $CNCAT["lang"]["comment"]?></td><td class="title"><?php print $CNCAT["lang"]["rating"]?></td><td class="title"><?php print $CNCAT["lang"]["date"]?></td><td class="title"><?php print $CNCAT["lang"]["author"]?></td><td class="title"><?php print $CNCAT["lang"]["email"]?></td><td class="title"><?php print $CNCAT["lang"]["item_title"]?></td><td class="title"></td></tr>
<?php
    foreach ($comments as $comment) {
?>
    <tr>
        <td class="item">
            <?php print nl2br(htmlspecialchars($comment["text"]))?>
        </td><td class="item">
            <?php print $comment["vote"]?>
        </td><td class="item">
            <?php print $comment["date_insert"]?>
        </td><td class="item">
            <?php print $comment["author_name"]?>
        </td><td class="item">
            <?php print $comment["author_email"]?>
        </td><td class="item">
            <a href="<?php 
            $title = $CNCAT["config"]["use_translit"]? $comment["item_title_translite"]: $comment["item_title"]; 
            print $CNCAT_ENGINE->url->createUrlExt($comment["item_id"], $title)?>" target="_blank"><?php print htmlspecialchars($comment["item_title"])?></a>
        </td><td class="item">
            <a href="index.php?act=comments&action=delete&id=<?php print $comment["id"]?>" onclick="return confirm('<?php print cn_str_replace("%NAME%", $CNCAT["lang"]["comment"], $CNCAT["lang"]["really_delete"])?>');"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/delete.gif" alt="" /></a>
        </td>
    </tr>
<?php
    }
?>
</table>
<?php
    } else {
        print '<span class="not_found">' . $CNCAT["lang"]["no_comments"] . '</span>';
    }

    if ($_pages_count > 1) {
        print '<p>' . $CNCAT_ENGINE->render->renderPageNavigation("index.php?act=comments&mode=list&page={PAGE}", $_pages_count, $_page) . '</p>';
    }
} else {
    $query = "
        SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "comments`
        INNER JOIN `" . $CNCAT["config"]["db"]["prefix"] . "items`
            USING (item_id)
        WHERE active = 0
        ORDER BY id DESC
    ";
    $result = $CNCAT_ENGINE->db->query($query);
    $comments = array();
    
    while ($comment = mysql_fetch_assoc($result)) {
        $comments[] = $comment;
    }

?>
<h1><?php print $CNCAT["lang"]["settings"]?> / <?php print $CNCAT["lang"]["new_comments"]?></h1>
<a href="index.php?act=comments&mode=list"><?php print $CNCAT["lang"]["approved_comments"]?></a>
<div class="deline"></div>
<?php  
    if ($comments) {
?>
    <form action="" method="post">
    <p><input type="submit" class="submit" value="<?php print $CNCAT["lang"]["do_exec"]?>" /></p>
    <table class="items">
    <tr><td class="title" colspan="3"><?php print $CNCAT["lang"]["action"]?></td><td class="title"><?php print $CNCAT["lang"]["comment"]?></td><td class="title"><?php print $CNCAT["lang"]["rating"]?></td><td class="title"><?php print $CNCAT["lang"]["item_title"]?></td><td class="title"><?php print $CNCAT["lang"]["author"]?></td><td class="title">E-mail</td><td class="title"><?php print $CNCAT["lang"]["item_title"]?></td></tr>
    <tr>
        <td class="item" style="width: 60px;"><?php print $CNCAT["lang"]["do_approve"]?></td>
        <td class="item" style="width: 45px;"><?php print $CNCAT["lang"]["do_ignore_s"]?></td>
        <td class="item" style="width: 45px;"><?php print $CNCAT["lang"]["do_delete"]?></td>
        <td class="item" colspan="6"></td>
    </tr>
<?php
        foreach ($comments as $comment) {
?>
    <tr>
        <td class="item" style="text-align: center;" onclick="this.firstChild.checked=1;"><input type="radio" name="action[<?php print $comment["id"]?>]" value="approve" /></td>
        <td class="item" style="text-align: center;" onclick="this.firstChild.checked=1;"><input type="radio" name="action[<?php print $comment["id"]?>]" value="skip" checked="checked" /></td>
        <td class="item" style="text-align: center;" onclick="this.firstChild.checked=1;"><input type="radio" name="action[<?php print $comment["id"]?>]" value="delete" /></td>
        <td class="item">
            <?php print nl2br(htmlspecialchars($comment["text"]))?>
        </td><td class="item">
            <?php print $comment["vote"]?>
        </td><td class="item">
            <?php print $comment["date_insert"]?>
        </td><td class="item">
            <?php print htmlspecialchars($comment["author_name"])?>
        </td><td class="item">
            <?php print htmlspecialchars($comment["author_email"])?>
        </td><td class="item">
            <a href="<?php print $CNCAT_ENGINE->url->createUrlExt($comment["item_id"], $comment["item_title_translite"])?>" target="_blank"><?php print htmlspecialchars($comment["item_title"])?></a>
        </td>
    </tr>
<?php
    }
?>
    </table>
    <p><input type="submit" class="submit" value="<?php print $CNCAT["lang"]["do_exec"]?>" /></p>
    </form>
<?php
    } else {
        print '<span class="not_found">' . $CNCAT["lang"]["no_comments"] . '</span>';
    }
}
?>

<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
