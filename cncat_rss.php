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
require_once dirname(__FILE__) . "/cncat_init.php";
$CNCAT_ENGINE = cncatCreateObject ("engine", "CNCatEngine");
$CNCAT_ENGINE->init();
// set default items count
if ($CNCAT["config"]["rss_items_count"] < 1) {
    $CNCAT["config"]["rss_items_count"] = 10;
}

$CNCAT["page"]["cid"] = isset($_GET["c"]) ? (int)$_GET["c"] : 0;

if ($CNCAT["config"]["rss_display"] > 0) {
    if ($CNCAT["config"]["rss_display"] == 1 && $CNCAT["page"]["cid"] != $CNCAT["root_cat_id"]) {
        exit;
    }

    if ($CNCAT["config"]["rss_display"] == 2 && $CNCAT["page"]["cid"] == $CNCAT["root_cat_id"]) {
        exit;
    }
} else {
    exit;
}

prepareToRender();

header("Content-type: text/xml; charset=" . $CNCAT["lang"]["charset"]);
cn_render();

function prepareToRender() {
    global $CNCAT, $CNCAT_ENGINE;

    $query = "
        SELECT `title_full`, `path_full`, `child_id_list`
        FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats`
        WHERE `id`=" . (int)$CNCAT["page"]["cid"] . "
    ";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $cat = mysql_fetch_assoc($res);
    
    $cats_id = array();
    $cats_id[] = $CNCAT["page"]["cid"];
    
    if ($CNCAT["config"]["rss_show_child_items"] || ($CNCAT["config"]["display"] != 2 && $_cid == $CNCAT["root_cat_id"])) {
        foreach ((array)explode(",", $cat["child_id_list"]) as $cid) {
            if ((int)$cid == 0) continue;
            $cats_id[] = $cid;
        }
    }
    
    $CNCAT["sql"]["itemcats"] = $cats_id;
    $CNCAT["page"]["cat"]["path_full"] = $cat["path_full"];

    // prepare title
    $CNCAT["config"]["rss_title"] = cn_str_replace(
        array("%TITLE%", "%CATNAME%"),
        array($cat["title_full"], $CNCAT["config"]["catalog_title"]),
        $CNCAT["config"]["rss_title"]
    );
    
    // prepare descr
    $CNCAT["config"]["rss_descr"] = cn_str_replace(
        array("%TITLE%", "%CATNAME%"),
        array($cat["title_full"], $CNCAT["config"]["catalog_title"]),
        $CNCAT["config"]["rss_descr"]
    );
}

function cn_render() {
    global $CNCAT, $CNCAT_ENGINE;
    $show_items = -1;
    
    if ($CNCAT["config"]["rss_show_items"] == 1) {
        $show_items = 0;
    } elseif ($CNCAT["config"]["rss_show_items"] == 2) {
        $show_items = 1;
    }

    $query = "
        SELECT DISTINCT `i`.`item_id`, `i`.`item_title`, `i`.`item_descr`, `i`.`item_submit_date`, `i`.`item_title_translite`
        FROM `" . $CNCAT["config"]["db"]["prefix"] . "items` `i`, `" . $CNCAT["config"]["db"]["prefix"] . "itemcat` `ic`
        WHERE `i`.`item_status` = 1 AND " . ($show_items > -1 ? " `i`.`item_type` = " . $show_items . " AND" : "") . " `ic`.`cat_id` IN (" . implode(",", $CNCAT["sql"]["itemcats"]) . ") AND `i`.`item_id` = `ic`.`item_id`
        ORDER BY `i`.`item_submit_date` DESC
        LIMIT 0, " . $CNCAT["config"]["rss_items_count"] . "
    ";

    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $items = array();
    
    while ($item = mysql_fetch_assoc($res)) {
        $items[$item["item_id"]]["title"] = $item["item_title"];
        $items[$item["item_id"]]["description"] = $item["item_descr"];
        $items[$item["item_id"]]["pubdate"] = date("r", strtotime($item["item_submit_date"]));
        $items[$item["item_id"]]["link"] ="";
    
        if ($CNCAT["config"]["rss_item_link"] == 0) {
             $title = $CNCAT["config"]["use_translit"]? $item["item_title_translite"]: $item["item_title"];
             $items[$item["item_id"]]["link"] .= $CNCAT_ENGINE->url->createUrlExt($item["item_id"], $title);
        } elseif($CNCAT["config"]["rss_item_link"] == 1) {
             $items[$item["item_id"]]["link"] .= $CNCAT_ENGINE->url->createUrlCat($CNCAT["page"]["cid"], $CNCAT["page"]["cat"]["path_full"]);
        } else {
            $items[$item["item_id"]]["link"] .= $CNCAT["config"]["cncat_url"];
        }
    }

    print "<?xml version=\"1.0\" encoding=\"" . $CNCAT["lang"]["charset"] . "\"?>\n"; //<?
    print "<rss version=\"2.0\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\n";
    print "<channel>\n";
    print "<title>" . htmlspecialchars($CNCAT["config"]["rss_title"]) . "</title>\n";
    print "<link>http://" . $CNCAT["config"]["cncat_url"] . "</link>\n";
    print "<description>" . htmlspecialchars($CNCAT["config"]["rss_descr"]) . "</description>\n";
    print "<generator>CNCat " . $CNCAT_PRODUCT_VERSION . "</generator>\n";
    print "<webMaster>" . $CNCAT["config"]["admin_email"] . "</webMaster>\n";
    
    foreach ($items as $item_id => $item) {
        print "<item>\n";
        print "\t<title>" . htmlspecialchars($item["title"]) . "</title>\n";
        print "\t<link>" . htmlspecialchars($item["link"]) . "</link>\n";
        print "\t<description>" . htmlspecialchars($item["description"]) . "</description>\n";
        print "\t<pubDate>" . $item["pubdate"] . "</pubDate>\n";
        print "</item>\n";
    }
    
    print "</channel>\n";
    print "</rss>";
}

?>
