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
header("Content-type: text/xml; charset=" . $CNCAT["lang"]["charset"]);
cn_render();

function prepareToRender() {
    global $CNCAT, $CNCAT_ENGINE;
}

function cn_render() {
    global $CNCAT, $CNCAT_ENGINE;

    // get cats
    $query = "
        SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats` ORDER BY id DESC
    ";

    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $cats = array();
    $hidden_cats_ids = array();

    while ($cat = mysql_fetch_assoc($result)) {
        if (!$cat["display"]) {
            $hidden_cats_ids[] = $cat["id"];

            foreach ((array)explode(",", $cat["child_id_list"]) as $cid) {
                $hidden_cats_ids[] = $cid;
            }
        }

        $cats[$cat["id"]] = $cat;
    }

    // get items
    $query = "
        SELECT item_id, item_title_translite FROM `" . $CNCAT["config"]["db"]["prefix"] . "items` WHERE item_status = 1 ORDER BY item_id DESC
    ";

    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $items = array();

    while ($item = mysql_fetch_assoc($result)) {
        $items[$item["item_id"]] = $item;
    }

    print "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"; //<?
    print "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

    foreach ($cats as $cat_id => $cat) {
        if (in_array($cat_id, $hidden_cats_ids)) {
            continue;
        }
    
        $CNCAT["page"]["cid"] = $cat["id"];
        $CNCAT["page"]["cat"] = $cat;
        $CNCAT["page"]["page_num"] = 0;
        $CNCAT["page"]["sort_order"] = intval($CNCAT["config"]["default_sort_order"]);
        $CNCAT["page"]["item_type"] = -1;

        if ($CNCAT["config"]["show_child_items"]) {
            $page_count = ceil($cat["item_count_full"] / $CNCAT["config"]["items_per_page"]);
        } else {
            $page_count = ceil($cat["item_count"] / $CNCAT["config"]["items_per_page"]);
        }

        if ($page_count == 0) {
            $page_count = 1;
        }

        for ($i = 0; $i < $page_count; $i++) {
            print "<url>\n";
            
            if ($i > 0) {
                print "\t<loc>" . htmlspecialchars($CNCAT_ENGINE->url->createUrlPageIndex($i)) . "</loc>\n";
            } else {
                print "\t<loc>" . htmlspecialchars($CNCAT_ENGINE->url->createUrlCat($cat["id"], $cat["path_full"])) . "</loc>\n";
            }
            
            print "</url>\n";
        }
    }

    foreach ($items as $item_id => $item) {
            print "<url>\n";
            $title = $CNCAT["config"]["use_translit"]? $item["item_title_translite"]: $item["item_title"];
            print "\t<loc>" . htmlspecialchars($CNCAT_ENGINE->url->createUrlExt($item_id, $title)) . "</loc>\n";
            print "</url>\n";
    }

    print "</urlset> ";
}
?>
