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
 * Convert int size to string
 * @param int $size
 * @return string 
 */
function sizeToStr($size) {
    $ps = array('b', 'Kb', 'Mb');

    for ($i = 0; (($size / 1024) >= 1) && ($i <= 3); $i++) {
        $size /= 1024;
    }

    return round($size, 2) . ' ' . $ps[$i];
}

/**
 * Get mktime from string
 * @param string $str
 * @return int 
 */
function cn_strtotime($str) {
    return mktime(0, 0, 0, substr($str, 3, 2), substr($str, 0, 2), substr($str, 7, 4));  
}

/*******************************************************************************
 * Moderators
 ******************************************************************************/
/**
 * Insert record about a new moder
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $fields 
 */
function moderInsert($fields) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $values = array();

    foreach ($fields as $name => $value) {
        $values[] = "`" . $name . "`=" . (is_numeric($value) ? $value :  "'" . mysql_escape_string($value) . "'");
    }

    $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "moders` SET " . join(",", $values);
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
}

/**
 * Update moder's data in DB
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $fields
 * @param $where
 * @param $other 
 */
function moderUpdate($fields, $where = "", $other = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $values = array();

    foreach ($fields as $name => $value) {
        $values[] = "`" . $name . "`=" . (is_numeric($value) ? $value :  "'" . mysql_escape_string($value) . "'");
    }

    $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "moders` SET " . join(",", $values) . "
        " . ($where ? "WHERE " . $where : "") . " " . $other;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
}
/**
 * Delete moders from DB
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $where
 * @param $other 
 */
function moderDelete($where = "", $other = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "moders`
        " . ($where ? "WHERE " . $where : "") . " " . $other;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
}
/**
 * Count of moders
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $where
 * @param $other
 * @return int 
 */
function moderExists($where = "", $other = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $query = "SELECT COUNT(*) AS `count` FROM `" . $CNCAT["config"]["db"]["prefix"] . "moders`
        " . ($where ? "WHERE " . $where : "") . " " . $other;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $row = mysql_fetch_assoc($res);

    return $row["count"];
}

/**
 * Get moder's data from DB
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $fields
 * @param $where
 * @param $other
 * @return array
 */
function moderSelect($fields, $where = "", $other = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $query = "SELECT " . $fields . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "moders`
        " . ($where ? "WHERE " . $where : "") . " " . $other;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $result = array();

    while ($row = mysql_fetch_assoc($res)) {
        $result[] = $row;
    }

    return $result;
}

/*******************************************************************************
 * Categories
 ******************************************************************************/ 
/**
 * Insert new category
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param  $fields 
 */
function catInsert($fields) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $values = array();

    foreach ($fields as $name => $value) {
        $values[] = "`" . $name . "`=" . (is_numeric($value) ? $value :  "'" . mysql_escape_string($value) . "'");
    }

    $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "cats` SET " . join(",", $values);
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
}

/**
 * Update category
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $fields
 * @param $where
 * @param $other 
 */
function catUpdate($fields, $where = "", $other = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $values = array();

    foreach ($fields as $name => $value) {
        $values[] = "`" . $name . "`=" . (is_numeric($value) ? $value :  "'" . mysql_escape_string($value) . "'");
    }

    $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "cats` SET " . join(",", $values) . "
        " . ($where ? "WHERE " . $where : "") . " " . $other;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
}

/**
 * Delete category
 * 
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $where
 * @param $other 
 */
function catDelete($where = "", $other = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats`
        " . ($where ? "WHERE " . $where : "") . " " . $other;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
}

/**
 * Check category exists
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $where
 * @param $other
 * @return int 
 */
function catExists($where = "", $other = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $query = "SELECT COUNT(*) AS `count` FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats`
        " . ($where ? "WHERE " . $where : "") . " " . $other;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $row = mysql_fetch_assoc($res);

    return $row["count"];
}

/**
 * Select categories
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $fields
 * @param $where
 * @param $other
 * @return array 
 */
function catSelect($fields, $where = "", $other = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $query = "SELECT " . $fields . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats`
        " . ($where ? "WHERE " . $where : "") . " " . $other;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $result = array();

    while ($row = mysql_fetch_assoc($res)) {
        $result[] = $row;
    }

    return $result;
}
/**
 * Check for parent
 * @param $cat_id
 * @param $parent_id
 * @return boolean 
 */
function catIsChildFor($cat_id, $parent_id) {
    list($row) = catSelect("`child_id_list`", "`id`=" . $parent_id);

    if (!empty($row["child_id_list"])) {
        $cats = explode(",", $row["child_id_list"]);

        return in_array($cat_id, $cats);
    }

    return false;
}

/*******************************************************************************
 * Items
 ******************************************************************************/
/**
 * Add new utem to DB
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param type $fields 
 */
function itemInsert($fields) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $values = array();

    foreach ($fields as $name => $value) {
        $values[] = "`" . $name . "`=" . (is_numeric($value) ? $value :  "'" . mysql_escape_string($value) . "'");
    }

    $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "items`
        SET " . join(",", $values);
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
}
/**
 * Item update
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $fields
 * @param $where
 * @param $other 
 */
function itemUpdate($fields, $where = "", $other = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $values = array();

    foreach ($fields as $name => $value) {
        $values[] = "`" . $name . "`=" . (is_numeric($value) ? $value :  "'" . mysql_escape_string($value) . "'");
    }

    $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items` SET " . join(",", $values) . "
        " . ($where ? "WHERE " . $where : "") . " " . $other;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
}
/**
 * Update ext. fields of the item
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $item_type
 * @param $fields
 * @param $where
 * @param $other 
 */
function itemUpdateExt($item_type, $fields, $where = "", $other = "") {
    global $CNCAT, $CNCAT_ENGINE;

    $values = array();

    foreach ($fields as $name => $value) {
        if (isset($CNCAT["config"]["extfields"]["items"][$item_type][$name])) {
            if ($CNCAT["config"]["extfields"]["items"][$item_type][$name]["type"] == 1) {
                if (!is_numeric($value)) {
                    $values[] = "`" . $name . "` = NULL";
                } else {
                    $values[] = "`" . $name . "` = " . intval($value);
                }
            } elseif ($CNCAT["config"]["extfields"]["items"][$item_type][$name]["type"] == 2) {
                if (!is_numeric($value)) {
                    $values[] = "`" . $name . "` = NULL";
                } else {
                    $values[] = "`" . $name . "` = " . floatval($value);
                }
            } else {
                if (empty($value)) {
                    $values[] = "`" . $name . "` = NULL";
                } else {
                    $values[] = "`" . $name . "` = '" . mysql_escape_string($value) . "'";
                }
            }
        }
    }

    if ($values) {
        $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items` SET " . join(",", $values) . "
            " . ($where ? "WHERE " . $where : "") . " " . $other;
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }
}
/**
 * Delete  item from DB
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $where
 * @param $other 
 */
function itemDelete($where = "", $other = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
        " . ($where ? "WHERE " . $where : "") . " " . $other;
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
}

/**
 * Check item for existing
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $where
 * @param  $other
 * @return int 
 */
function itemExists($where = "", $other = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $query = "SELECT COUNT(*) AS `count` FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
        " . ($where ? "WHERE " . $where : "") . " " . $other;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $row = mysql_fetch_assoc($res);

    return (int)$row["count"];
}
/**
 * Same function itemExists($where = "", $other = "")
 * @param type $where
 * @param type $other
 * @return type 
 */
function itemCount($where = "", $other = "") {
    return itemExists($where, $other);
}
/**
 * Exec select with parameters
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $fields
 * @param $where
 * @param $other
 * @return array
 */
function itemSelect($fields, $where = "", $other = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $query = "SELECT " . $fields . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
        " . ($where ? "WHERE " . $where : "") . " " . $other;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $result = array();

    while ($row = mysql_fetch_assoc($res)) {
        $result[] = $row;
    }

    return $result;
}
/**
 * Update item's status
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $items_id
 * @param $status 
 */
function itemSetStatus($items_id, $status = 0) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $items_id = (array)$items_id;
    itemUpdate(
        array("item_status" => $status),
        "`item_id` IN (" . join(",", $items_id) . ")"
    );
}
/**
 * Set item's rating
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $items_id
 * @param $rating 
 */
function itemSetRating($items_id, $rating = 0) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    itemUpdate(
         array("item_rating_moder" => $rating, "item_rating_moder_neg" => -$rating),
        "`item_id` IN (" . join(",", (array)$items_id) . ")"
    );
}

/*******************************************************************************
 * Misc
 ******************************************************************************/ 
/**
 * Render Item for ACP
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $item
 * @return $result 
 */
function renderItem($item) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $result = "";
    $CNCAT["item"] = $item;

    if ($CNCAT["config"]["favicon_display"] == 1) {
        if ($CNCAT["item"]["link_favicon_mime"]) {
            $CNCAT["item"]["_favicon_url"] = $CNCAT["abs"] . "{$CNCAT["system"]["dir_prefix"]}image.php?favicon=" . $CNCAT["item"]["item_id"];
        }
    } elseif ($CNCAT["config"]["favicon_display"] == 2) {
        if ($CNCAT["item"]["link_favicon_url"]) {
            $CNCAT["item"]["_favicon_url"] = $CNCAT["item"]["link_favicon_url"];
        }
    }

    if ($CNCAT["config"]["favicon_display"] && $CNCAT["config"]["favicon_yandex"] && !$CNCAT["item"]["_favicon_url"]) {
        $urlp = @parse_url($CNCAT["item"]["link_url"]);
        $CNCAT["item"]["_favicon_url"] = "http://favicon.yandex.ru/favicon/" . $urlp["host"];
    }
    $title = $CNCAT["config"]["use_translit"]? $CNCAT["item"]["item_title_translite"]: $CNCAT["item"]["item_title"];
    $CNCAT["item"]["_ext_url"] = $CNCAT_ENGINE->url->createUrlExt($CNCAT["item"]["item_id"], $title);

    $res_item = $CNCAT_ENGINE->tpl->renderTemplate("admin_item");

    foreach ((array)$item["cats"] as $cat) {
        $CNCAT["cat"]["title_full"] = $cat["title_full"];
        $CNCAT["cat"]["_url"] = $CNCAT_ENGINE->url->createUrlCat($cat["id"], $cat["path_full"]);
        $cats[] = $CNCAT_ENGINE->tpl->renderTemplate("admin_item_cat");
    }

    $rating = "";

    for ($i = 0; $i <= 10; $i++) {
        $CNCAT["admin"]["item_rating_value"] = $i;
        $rating .= $CNCAT_ENGINE->tpl->renderTemplate("admin_item_rating");
    }

    $res_item = cn_str_replace("{DISPLAY RATING}", $rating, $res_item);

    if ($item["cats"]) {
        $result = cn_str_replace("{DISPLAY CATS}", join($CNCAT_ENGINE->tpl->renderTemplate("admin_item_cat_delim"), $cats), $res_item);
    } else {
        $result = cn_str_replace("{DISPLAY CATS}", $CNCAT["lang"]["not_assign"], $res_item);
    }

    return $result;
}
/**
 * Append categorie
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $items 
 */
function appendCats(&$items) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    if ($items) {
        $query = "SELECT c.id, c.title_full, c.path_full, i.item_id
            FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat` i, `" . $CNCAT["config"]["db"]["prefix"] . "cats` c
            WHERE c.id=i.cat_id AND i.item_id IN (" . join(",", array_keys($items)) . ")";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
    
        while ($row = mysql_fetch_assoc($res)) {
            $items[$row["item_id"]]["cats"][] = $row;
        }
    }
}
/**
 * Append check
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $items 
 */
function appendCheck(&$items) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    if ($items) {
        $query = "SELECT `item_id`, `check_flag` FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
            WHERE `moder_id`=" . (isModer() ? getModerId() : 0);
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
        $items_check = array();

        while ($row = mysql_fetch_assoc($res)) {
            if (!$row["check_flag"]) {
                $items_check[] = $row["item_id"];
            }
        }

        foreach ($items as $item_id => $item) {
            $items[$item_id]["item_check"] = in_array($item["item_id"], $items_check);
        }
    }
}
/**
 * Render items
 * 
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $query
 * @param $item_count
 * @return $result
 */
function renderItems($query, $item_count = 0) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $result = "";

    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
    $items = array();

    while ($row = mysql_fetch_assoc($res)) {
        $items[$row["item_id"]] = $row;
    }

    if ($items) {
        appendCats($items);
        appendCheck($items);
        $result .= $CNCAT_ENGINE->tpl->renderTemplate("admin_items_begin");
    
        foreach ($items as $item) {
            $result .= renderItem($item);
        }
    
        $result .= $CNCAT_ENGINE->tpl->renderTemplate("admin_items_end");
    }

    return $result;
}
/**
 * display pagination
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $params
 * @param $page
 * @param $items_count 
 */
function displayPagebar($params, &$page, $items_count) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $page_count = getPageCount($items_count);

    if ($page < 0) {
        $page = 0;
    } elseif ($page > ($page_count - 1)) {
        $page = $page_count - 1;
    }

    $urlp = array();

    foreach ($params as $name => $value) {
        $urlp[] = $name . "=" . $value;
    }

    $urlp[] = "page={PAGE}";

    if ($page_count > 1) {
        print '<center>' . $CNCAT_ENGINE->render->renderPageNavigation("index.php?" . join("&", $urlp), $page_count, $page) . '</center>';
    }
}
/**
 * Get item's count
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $where
 * @param $join
 * @return int 
 */
function getItemsCount($where = array(), $join = "AND") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $query = "SELECT COUNT(*) AS `count` FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
        " . ($where ? "WHERE " . join(" " . $join . " ", $where) : "");
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $row = mysql_fetch_assoc($res);

    return (int)$row["count"];
}
/**
 * Get an Items from the category
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $where
 * @param $join
 * @return array 
 */
function getItemsByCat($where = array(), $join = "AND") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $query = "SELECT `item_id` FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat` " . ($where ? "WHERE " . join(" " . $join . " ", $where) : "");
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $items = array();

    while ($row = mysql_fetch_assoc($res)) {
        $items[] = $row["item_id"];
    }

    return $items;
}
/**
 * Get list of child categories
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $id
 * @return array 
 */
function getCatChildList($id) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $query = "SELECT `child_id_list` FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats`
        WHERE `id`=" . $id;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $row = mysql_fetch_assoc($res);
    $cats = array();

    if ($row["child_id_list"]) {
        $cats = explode(",", $row["child_id_list"]);
    }

    return $cats;
}
/**
 * Get page count
 * @global $CNCAT
 * @param $items_count
 * @return int 
 */
function getPageCount($items_count) {
    GLOBAL $CNCAT;

    return ceil($items_count / $CNCAT["config"]["items_per_page_admin"]);
}
/**
 * Get limit for SQL query
 * @global $CNCAT
 * @param $page
 * @return string 
 */
function getSqlPager($page) {
    GLOBAL $CNCAT;

    return "LIMIT " . ($page * $CNCAT["config"]["items_per_page_admin"]) . ", " . $CNCAT["config"]["items_per_page_admin"];
}

/**
 * Get order for SQL query
 * @global $CNCAT
 * @param $sort_id
 * @param $sort_dir
 * @return string
 */
function getSqlSort($sort_id, $sort_dir) {
    GLOBAL $CNCAT;

    $itemorder = array (
        // Popularity
        0 => array ("link_jumps_from_neg DESC, link_jumps_to_neg DESC, item_title DESC", 
                    "link_jumps_from_neg, link_jumps_to_neg, item_title",
                    "popularity",
                    1,
                    0),
        // Title
        1 => array ("item_title", 
                    "item_title DESC", 
                    "title",
                    0,
                    0),
        // Moderator rating
        2 => array ("item_rating_moder_neg DESC, item_title DESC", 
                    "item_rating_moder_neg, item_title",                     
                    "rating_moder",
                    1,
                    0), 
        // Submit date
        3 => array ("item_submit_date, item_insert_date",
                    "item_submit_date DESC, item_insert_date DESC",                                          
                    "submit_date",
                    1,
                    0),
        // Google PR
        4 => array ("link_rating_pr_neg DESC, item_title DESC", 
                    "link_rating_pr_neg, item_title",                     
                    "rating_pr",
                    1,
                    0),
        // Yandex CY
        5 => array ("link_rating_cy_neg DESC, item_title DESC", 
                    "link_rating_cy_neg, item_title",                     
                    "rating_cy",
                    1,
                    0),
        // ID
        6 => array ("item_id ASC, item_title ASC", 
                    "item_id DESC, item_title DESC",
                    "",
                    1,
                    1)
    );

    if (!isset($itemorder[$sort_id])) {
        $sort_id = 3;
    }

    if ($sort_dir < 0) {
        $sort_dir = 0;
    } elseif ($sort_dir > 1) {
        $sort_dir = 1;
    }

    if ($sort_dir == 0) {
        $sort_dir = 1;
    } else {
        $sort_dir = 0;
    }

    return "ORDER BY " . $itemorder[$sort_id][$sort_dir];
}
/**
 * Render select element for sort order
 * @global $CNCAT
 * @param $sort_id
 * @param $sort_dir 
 */
function displaySort($sort_id, $sort_dir) {
    GLOBAL $CNCAT;

    $sort = $CNCAT["sql"]["itemorder"];
?>
<p>
    <?php print $CNCAT["lang"]["sort_by"]?>:
    <select name="sort">
<?php
    foreach ($sort as $sid => $sql) {
        print "<option value=\"" . $sid . "\" " . ($sid == $sort_id ? "selected=\"selected\"" : "") . ">" . $CNCAT["lang"]["sort_by_" . $sid] . "</option>";
    }
?>
    </select>
    <select name="dir">
        <option value="0" <?php print $sort_dir == 0 ? "selected=\"selected\"" : ""?> ><?php print $CNCAT["lang"]["sort_desc"]?></option>
        <option value="1" <?php print $sort_dir == 1 ? "selected=\"selected\"" : ""?> ><?php print $CNCAT["lang"]["sort_asc"]?></option>
    </select>
    <input type="submit" class="submit" value="<?php print $CNCAT["lang"]["do_sort"]?>" />
</p>
<?php
}
/**
 * render categories
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param $cat
 * @param $subcat
 * @param $cats_count 
 */
function displayCats($cat, $subcat, $cats_count = array()) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;
?>
<p>
    <select name="cat" onchange="this.form.submit()">
<?php
    if (isAdmin()) {
        $query = "SELECT `id`, `title`, `tree_level`
            FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats`
            WHERE `parent_id`!=-1 AND `is_link`=0 ORDER BY `sort_order_global`";
    } else {
        $query = "SELECT `id`, `title`, `tree_level`
            FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats`
            WHERE `id` IN (" . join(",", getModerCats()) . ") AND `is_link`=0 ORDER BY `sort_order_global`";
    }

    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $level = 0;

    print "<option" . ($cat == $row["id"] ? " selected=\"selected\"" : "") . " value=\"0\">" . $CNCAT["lang"]["all_cats"] . "</option>";

    while ($row = mysql_fetch_assoc($res)) {
        print "<option value=\"" . $row["id"] . "\" class=\"level" . $row["tree_level"] . "\"" . ($cat == $row["id"] ? " selected=\"selected\"" : "") . ">"; 
        print str_repeat("&nbsp;-&nbsp;&nbsp;&nbsp;", $row["tree_level"]);
        print htmlspecialchars($row["title"]) . ($cats_count ? " / " . (int)$cats_count[$row["id"]] : "");
        print "</option>\n";
    }
?>
    </select>
    <input type="checkbox" name="subcat" id="include_subcats" value="1" <?php print $subcat ? "checked=\"checked\"" : ""?> /> <label for="include_subcats"><?php print $CNCAT["lang"]["include_subcats"]?></label>
    <input type="submit" class="submit" value="<?php print $CNCAT["lang"]["do_select"]?>" />
</p>
<p>
    <input type="checkbox" name="t0" id="type_links" onclick="this.form.submit()" <?php print !empty($_GET["t0"]) ? "checked=\"checked\"" : "" ?> /> <label for="type_links"><?php print $CNCAT["lang"]["links"]?></label>
    <input type="checkbox" name="t1" id="type_articles" onclick="this.form.submit()" <?php print !empty($_GET["t1"]) ? "checked=\"checked\"" : "" ?> /> <label for="type_articles"><?php print $CNCAT["lang"]["articles"]?></label>
</p>
<?php
}
?>
