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

$_mode = $_GET["mode"];

unset($_SESSION["global_sql_cond"]);
unset($_SESSION["global_referer"]);

$CNCAT["admin"]["act"] = "links";

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    print "[]";
    exit;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["links"]?> / <?php print $CNCAT["lang"]["links_dups"]?></h1>
<?php
if ($_mode == "view" && !empty($_GET["url"])) {
    $CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], "admin");
    $url = $_GET["url"];

    $_cond = array();
    $_cond[] = "`item_status`=1";
    $_cond[] = "`link_url`='" . mysql_escape_string($url) . "'";

    $_SESSION["global_sql_cond"] = $_cond;
    $_SESSION["global_referer"] = $_SERVER["REQUEST_URI"];

    $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
    $query = "SELECT c.action_comm,i." . join(", i.", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items` i
        LEFT JOIN `" . $CNCAT["config"]["db"]["prefix"] . "linkact_comm` c ON c.item_id = i.item_id
        WHERE " . join(" AND ", $_cond) . "
        ORDER BY `item_id` DESC";
?>
<form action="index.php" method="post">
<?php
    print renderItems($query);
    print $CNCAT_ENGINE->tpl->renderTemplate("admin_table_items_bottom");
} else {
    if (isModer()) {
        $items_id = getItemsByCat(array("`cat_id` IN(" . join(",", getModerCats()) . ")"));
    }

    $query = "
        SELECT link_url, COUNT(*) AS `count` FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
        WHERE `item_status`=1
        " . (isModer() ? "AND `item_id` IN (" . join(",", $items_id) . ")" : "") . "
        GROUP BY `link_url`
        HAVING `count` > 1
        ORDER BY `count` DESC
    ";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $is_found = false;

    while ($row = mysql_fetch_assoc($res)) {
        $is_found = true;
        
        if (empty($row["link_url"])) {
            continue;
        }

        print "[" . $row["count"] . "] <a href=\"index.php?act=dups&mode=view&url=" . urlencode($row["link_url"])  . "\">" . htmlspecialchars($row["link_url"]) . "</a><br />\n";
    }

    if (!$is_found) {
        print "<span class=\"not_found\">" . $CNCAT["lang"]["no_links"] . "</span>";
    }
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
