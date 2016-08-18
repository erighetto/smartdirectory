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

@set_time_limit(3600);

$charset = "utf-8";

/**
* Synchronize item counts for categories and subcategories    
*/
function cn_syncCats()
{
    GLOBAL $CNCAT, $CNCAT_ENGINE;

	$cats=Array();

    // Get all categories
    $query = "select id as cid, id_full, sort_order, parent_id, child_id_list, title, path FROM ".$CNCAT["config"]["db"]["prefix"]."cats c;";
	$res = $CNCAT_ENGINE->db->query($query, "Sync - item count - get all categories") or $CNCAT_ENGINE->displayErrorDB(mysql_error());
	while ($row = mysql_fetch_assoc($res))
    {   		
		$cats[$row["cid"]]["id"] = $row["cid"];
		$cats[$row["cid"]]["id_full"] = $row["cid"];
		$cats[$row["cid"]]["item_count"] = 0;
		$cats[$row["cid"]]["link_count"] = 0;
		$cats[$row["cid"]]["article_count"] = 0;
		$cats[$row["cid"]]["item_count_full"] = 0;
		$cats[$row["cid"]]["title"] = $row["title"];
		$cats[$row["cid"]]["title_full"] = $row["title"];
		$cats[$row["cid"]]["path"] = $row["path"];
		$cats[$row["cid"]]["path_full"] = $row["path"];
		$cats[$row["cid"]]["parent_id"] = $row["parent_id"];
        
		$cats[$row["cid"]]["child_id_list"] = "";
		$cats[$row["cid"]]["tree_level"] = 0;
		$cats[$row["cid"]]["sort_order_global"] = 0;
		$cats[$row["cid"]]["sort_order"] = $row["sort_order"];
		
        $cats[$row["cid"]]["_items"] = array();
        $cats[$row["cid"]]["_links"] = array();
        $cats[$row["cid"]]["_articles"] = array();
        $cats[$row["cid"]]["_sort"] = str_repeat("0", 10 - strlen((string)$row["sort_order"])) . $row["sort_order"].$row["title"];
        
	}

    // Get item count for each category itself
    	$query = "
        SELECT ic.item_id, ic.cat_id, ic.item_type
        FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat` ic
        WHERE ic.item_status = 1 AND ic.cat_id != 0
    ";
    $res = $CNCAT_ENGINE->db->query($query, "Sync - item count - item counts for each category") or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    while ($row = mysql_fetch_assoc($res)) {
		$cats[$row["cat_id"]]["item_count"]++;
        	if ($row["item_type"] == 0) {
            $cats[$row["cat_id"]]["_links"][] = $row["item_id"];
            $cats[$row["cat_id"]]["link_count"]++;
		} elseif ($row["item_type"] == 1) {
            $cats[$row["cat_id"]]["_articles"][] = $row["item_id"];
            $cats[$row["cat_id"]]["article_count"]++;
		}
	}

    $sort = array();
    // Calculating count with parents    
    foreach ($cats as $id1 => $cat)
    {
        $i=0; // To prevent loops
        $id = $id1;
		while ($id!=$CNCAT["root_cat_id"] && $id>0 && $i++<100)
        {
            $id = $cats[$id]["parent_id"];
            // Add ID to the parent directory's child list
            if (!empty ($cats[$id]["child_id_list"]))
                $cats[$id]["child_id_list"] .= ",";
            $cats[$id]["child_id_list"] .= $id1;
            
            // Update full title, path and id
            $cats[$id1]["id_full"] = $id."/".$cats[$id1]["id_full"];
            
            if ($id!=$CNCAT["root_cat_id"])
            {
                $zeros = str_repeat("0", 10 - strlen((string)$cats[$id]["sort_order"]));
                $cats[$id1]["_sort"] = $zeros . $cats[$id]["sort_order"] . $cats[$id]["title"] . $cats[$id1]["_sort"];
                $cats[$id1]["title_full"] = $cats[$id]["title"].$CNCAT["config"]["cat_title_delim"].$cats[$id1]["title_full"];
                $cats[$id1]["path_full"] = !empty($cats[$id]["path_full"]) ? ($cats[$id]["path"]."/".$cats[$id1]["path_full"]) : "";
            }
            
            // Increase tree level for current directory
            $cats[$id1]["tree_level"]++;
		};
	}

	foreach ($cats as $id => $cat) {
	    if (empty($cat["path"]) && $cat["parent_id"] != -1) {
            $cats[$id]["path_full"] = "";
            $child = explode(",", $cat["child_id_list"]);

            if ($child) {
                foreach ($child as $cid) {
                    if (isset($cats[$cid])) {
                        $cats[$cid]["path_full"] = "";
                    }
                }
            }
        }
    }

    foreach ($cats as $id => $cat) {
        $links = $cat["_links"];
        $articles = $cat["_articles"];

        foreach (explode(",", $cat["child_id_list"]) as $cat_id) {
            $links = array_merge((array)$links, (array)$cats[$cat_id]["_links"]);
            $articles = array_merge((array)$articles, (array)$cats[$cat_id]["_articles"]);
        }

        $links = array_unique($links);
        $articles = array_unique($articles);

        $cats[$id]["link_count_full"] = count($links);
        $cats[$id]["article_count_full"] = count($articles);
        $cats[$id]["item_count_full"] = $cats[$id]["link_count_full"] + $cats[$id]["article_count_full"];
    }
    
    function sortGlobal($a, $b) {
      return strcmp($a["_sort"], $b["_sort"]);
    }

	uasort($cats, "sortGlobal");
    // Updating counters
    $i = 0;
	foreach ($cats as $id => $cat)
    {
        if ($id>0)
        {
            $query = "UPDATE ".$CNCAT["config"]["db"]["prefix"]."cats 
                                SET id_full='".mysql_escape_string($cat["id_full"])."', 
                                    title_full='".mysql_escape_string($cat["title_full"])."', 
                                    path_full='".mysql_escape_string($cat["path_full"])."', 
                                    item_count='".$cat["item_count"]."',
                                    link_count='".$cat["link_count"]."',
                                    link_count_full='".$cat["link_count_full"]."',
                                    article_count='".$cat["article_count"]."',
                                    article_count_full='".$cat["article_count_full"]."',
                                    item_count_full='".$cat["item_count_full"]."',
                                    tree_level='".$cat["tree_level"]."',
                                    child_id_list='".mysql_escape_string($cat["child_id_list"])."',
                                    sort_order_global=".$i."                                
                                WHERE id='".$id."'";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            $i++;
        }
    }
    $query = "SELECT c1.id, c2.path_full, c2.item_count, c2.item_count_full FROM ".$CNCAT["config"]["db"]["prefix"]."cats c1 LEFT JOIN ".$CNCAT["config"]["db"]["prefix"]."cats c2 ON(c2.id=c1.id_real) WHERE c1.is_link=1" ;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    while ($row = mysql_fetch_assoc($res)) {
        $query = "UPDATE ".$CNCAT["config"]["db"]["prefix"]."cats
            SET
                `path_full`='" . $row["path_full"] . "',
                `item_count` ='" . $row["item_count"] . "',
                `link_count`='".$cat["link_count"]."',
                `article_count`='".$cat["article_count"]."',
                `item_count_full` ='" . $row["item_count_full"] . "'
            WHERE `id`=" . $row["id"];
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }
}

/**
 * Recount and sync for Items
 * @global $CNCAT
 * @global $CNCAT_ENGINE 
 */
function cn_syncItems()
{
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $query = "UPDATE ".$CNCAT["config"]["db"]["prefix"]."items set 
                link_jumps_to_neg=-link_jumps_to, 
                link_jumps_from_neg=-link_jumps_from,
                item_rating_moder_neg=-item_rating_moder,
                item_rating_users_neg=-item_rating_users,
                link_rating_pr_neg=-link_rating_pr,
                link_rating_cy_neg=-link_rating_cy,
                item_favour_neg=-item_favour                
                ;";
	$res = $CNCAT_ENGINE->db->query($query, "Fill negative counters and ratings", false) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    $query = "UPDATE ".$CNCAT["config"]["db"]["prefix"]."itemcat lc, ".$CNCAT["config"]["db"]["prefix"]."items l set 
                lc.item_status=l.item_status, lc.item_type=l.item_type where lc.item_id=l.item_id";

	$res = $CNCAT_ENGINE->db->query($query, "Copy item status to Item-Cat", false) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
    
}    
/**
 * Sync Moder's permission
 * @global $CNCAT
 * @global $CNCAT_ENGINE 
 */
function cn_syncModers() {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $query = "TRUNCATE TABLE " . $CNCAT["config"]["db"]["prefix"] . "modercat";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    $query = "SELECT id, cats, cats_child FROM " . $CNCAT["config"]["db"]["prefix"] . "moders";
    $r1 = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    while ($moder = mysql_fetch_assoc($r1)) {
        $cats = array();

        if (!empty($moder["cats"])) {
            $cats = explode(",", $moder["cats"]);
        }

        if (!empty($moder["cats_child"])) {
            $query = "SELECT child_id_list FROM ".$CNCAT["config"]["db"]["prefix"]."cats
                WHERE id IN (" . $moder["cats_child"] . ")";
            $r2 = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

            while ($cat = mysql_fetch_assoc($r2)) {
                if ($cat["child_id_list"]) {
                    $cats = array_merge($cats, explode(",", $cat["child_id_list"]));
                }
            }
        }        

        if ($cats) {
            $cats = array_unique($cats);
            $values = array();

            foreach ($cats as $cat) {
                $values[] = "(" . $moder["id"] . "," . $cat . ")";
            }

            $query = "INSERT INTO " . $CNCAT["config"]["db"]["prefix"] . "modercat
                VALUES " . join(",", $values);
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }
    }
}
/**
 * Run sync for all data
 */
function cn_syncAll() {
    cn_syncItems();
    cn_syncCats();
    cn_syncModers();
}
?>
