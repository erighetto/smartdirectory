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
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "sync_all.php";

$_mode = $_REQUEST["mode"];
$CNCAT["page"]["type"] = (int)$_REQUEST["type"];
$_page = (int)$_REQUEST["page"];
$_cat = (int)$_REQUEST["cat"];
$_subcat = (int)!empty($_REQUEST["subcat"]);
$_comment_action = $_REQUEST["comment"];
$_checkone_id = $_REQUEST["checkone_id"];
if ($_mode == "counters") {
    $hash = '';

    // Links
    if (isAdmin()) {
        $query = "SELECT `item_status`, COUNT(*) AS `count`
        FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
        FORCE INDEX (status)
        GROUP BY `item_status`";
    } else {
        $query = "SELECT `item_status`, COUNT(*) AS `count`
            FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat`
            FORCE INDEX (item_status)
            WHERE `cat_id` IN (" . join(",", getModerCats()) . ")
            GROUP BY `item_status`";
    }
    
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $counts = array(0, 0, 0, 4 => 0, 5 => 0);

    while ($row = mysql_fetch_assoc($res)) {
        $counts[$row["item_status"]] = $row["count"];
    }

    $i = 0;

    foreach ($counts as $type => $count) {
        if ($i++) {
            $hash .= ', ';
        }
        
        $hash .= 'links' . $type . ': ' . $count;
    }

    // Broken
    $cond = array("`link_broken_warning`=1");

    if (isAdmin()) {
        $broken_count = getItemsCount($cond);
    } else {
        if ($items_id = getItemsByCat(array("`cat_id` IN (" . join(",", getModerCats()) . ")"))) {
            $cond[] = "`item_id` IN (" . join(",", $items_id) . ")";
            $broken_count = getItemsCount($cond);
        } else {
            $broken_count = 0;
        }
    }
    
    $hash .= ', broken: ' . $broken_count;

    // Check
    $query = "SELECT COUNT(*) AS `count` FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
        WHERE `moder_id`=" . (isModer() ? getModerId() : 0) . " AND `check_flag`=0";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $row = mysql_fetch_assoc($res);
    $check_count = (int)$row["count"];

    $hash .= ', check: ' . $check_count;

    header('Content-Type: text/json');
    print '{' . $hash . '}';

    exit;
}

if (!isset($_REQUEST["sort"])) {
    $_sort = $CNCAT["config"]["default_sort_order_admin"];
} else {
    $_sort = (int)$_REQUEST["sort"];
}

if (!isset($_REQUEST["dir"])) {
    $_dir = $CNCAT["config"]["default_sort_dir_admin"];
} else {
    $_dir = (int)$_REQUEST["dir"];
}

if ($CNCAT["page"]["type"] < 0 || $CNCAT["page"]["type"] > 5) {
    $CNCAT["page"]["type"] = 0;
}

$_items_type = -1;

if (empty($_REQUEST["t0"]) || empty($_REQUEST["t1"])) {
    if(!empty($_REQUEST["t0"])) {
        $_items_type = 0;
    } elseif(!empty($_REQUEST["t1"])) {
        $_items_type = 1;
    }
}

if (isModer() && $CNCAT["page"]["type"] == 4) {
    accessDenied();
}

if ($CNCAT["page"]["type"] == 3) {
    header("Location: index.php?act=check&mode=report");
    exit;
}

$_items_id = array();

// execute action
if ((int)$_REQUEST["global"] > 0) {
    foreach (itemSelect("`item_id`", join(" AND ", (array)$_SESSION["global_sql_cond"])) as $item) {
        $_items_id[] = $item["item_id"];
    }

    $_referer = !empty($_SESSION["global_referer"]) ? $_SESSION["global_referer"] : "index.php?act=links";
} else {
    $_items_id = (array)$_REQUEST["id"];
    $_referer = !empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "index.php?act=links";
}

foreach ($_items_id as $num => $item_id) {
    $_items_id[$num] = (int)$item_id;
}

$_items_id = array_unique($_items_id);

if ($_mode == "delete") {
    if ($_items_id) {
        foreach ($_items_id as $item_id) {
            if (isset($_GET["black"])) {
                $query = "
                    SELECT link_url
                    FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                    WHERE item_id = " . $item_id . "
                ";
                $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

                if ($rows = itemSelect("`link_url`", "`item_id` = " . $item_id)) {
                    $link_url = $rows[0]["link_url"];

                    if (!empty($link_url)) {
                        $url = parse_url($link_url);

                        if (!empty($url["host"])) {
                            $query = "
                                INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "bwlist`
                                    (`id`, `type`, `check_method`, `check_str`)
                                VALUES (NULL, 'black', 'substr', '" . mysql_escape_string($url["host"]) . "')
                            ";
                            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                        }
                    }
                }
            }
            $CNCAT_ENGINE->misc->itemLog(0, $CNCAT["lang"]["log_item_status_2"], $item_id, $_comment_action);
        }

        itemSetStatus($_items_id, 2);

        if ($CNCAT["config"]["mail_notify_decline"]) {
            foreach ($_items_id as $id) {
                $CNCAT_ENGINE->misc->sendMailDecline($id);
            }
        }

        cn_syncAll();
    }
    
    if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        $_item_id = array_shift($_items_id);

        if ($_item_id) {
            $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
            $query = "
                SELECT " . join(", ", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                WHERE `item_id` =" . $_item_id . "
            ";
        
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
            $item = mysql_fetch_assoc($res);
            $items = array();
        
            if ($item) {
                $items[$item['item_id']] = $item;
                appendCats($items);
                appendCheck($items);
        
                print renderItem($items[$item['item_id']]);
            }
        }
        
        exit;
    } else {
        header("Location: " . $_referer);
        exit;
    }
} elseif ($_mode == "isolation") {
    
    if ($_items_id) {
        foreach ($_items_id as $item_id) {
            if (isset($_GET["black"])) {
                $query = "
                    SELECT link_url
                    FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                    WHERE item_id = " . $item_id . "
                ";
                $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

                if ($rows = itemSelect("`link_url`", "`item_id` = " . $item_id)) {
                    $link_url = $rows[0]["link_url"];

                    if (!empty($link_url)) {
                        $url = parse_url($link_url);

                        if (!empty($url["host"])) {
                            $query = "
                                INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "bwlist`
                                    (`id`, `type`, `check_method`, `check_str`)
                                VALUES (NULL, 'black', 'substr', '" . mysql_escape_string($url["host"]) . "')
                            ";
                            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                        }
                    }
                }
            }

            $CNCAT_ENGINE->misc->itemLog(0, $CNCAT["lang"]["log_item_status_5"], $item_id, $_comment_action);
        }

        itemSetStatus($_items_id, 5);

        if ($CNCAT["config"]["mail_notify_isolation"]) {
            foreach ($_items_id as $id) {
                $CNCAT_ENGINE->misc->sendMailIsolation($id);
            }
        }

        cn_syncAll();
    }

    if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        $_item_id = array_shift($_items_id);

        if ($_item_id) {
            $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
            $query = "
                SELECT " . join(", ", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                WHERE `item_id` =" . $_item_id . "
            ";

            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            $item = mysql_fetch_assoc($res);
            $items = array();

            if ($item) {
                $items[$item['item_id']] = $item;
                appendCats($items);
                appendCheck($items);

                print renderItem($items[$item['item_id']]);
            }
        }

        exit;
    } else {
        header("Location: " . $_referer);
        exit;
    }
} elseif ($_mode == "asnew") {
    if ($_items_id) {
        foreach ($_items_id as $item_id) {
            $CNCAT_ENGINE->misc->itemLog(0, $CNCAT["lang"]["log_item_status_0"], $item_id);
        }

        itemSetStatus($_items_id, 0);
        cn_syncAll();
    }

    if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        $_item_id = array_shift($_items_id);

        if ($_item_id) {
            $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
            $query = "
                SELECT " . join(", ", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                WHERE `item_id` =" . $_item_id . "
            ";
        
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
            $item = mysql_fetch_assoc($res);
            $items = array();
        
            if ($item) {
                $items[$item['item_id']] = $item;
                appendCats($items);
                appendCheck($items);
        
                print renderItem($items[$item['item_id']]);
            }
        }
        
        exit;
    } else {
        header("Location: " . $_referer);
        exit;
    }
} elseif ($_mode == "approve") {
    if ($_items_id) {
        //itemSetStatus($_items_id, 1);
        foreach ($_items_id as $item_id) {
            $CNCAT_ENGINE->misc->itemLog(0, $CNCAT["lang"]["log_item_status_1"], $item_id);
        }

        itemUpdate(array(
                "item_status" => "1",
                "item_submit_date" => date("Y-m-d H:i:s"), 
                "item_submit_type" => 1
            ),
            "`item_id` IN (" . join(",", $_items_id) . ")"
        );

        if ($CNCAT["config"]["mail_notify_approve"]) {
            foreach ($_items_id as $id) {
                $CNCAT_ENGINE->misc->sendMailApprove($id);
            }
        }

        cn_syncAll();
    }

    if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        $_item_id = array_shift($_items_id);

        if ($_item_id) {
            $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
            $query = "
                SELECT " . join(", ", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                WHERE `item_id` =" . $_item_id . "
            ";
        
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
            $item = mysql_fetch_assoc($res);
            $items = array();
        
            if ($item) {
                $items[$item['item_id']] = $item;
                appendCats($items);
                appendCheck($items);
        
                print renderItem($items[$item['item_id']]);
            }
        }
        
        exit;
    } else {
        header("Location: " . $_referer);
        exit;
    }
} elseif ($_mode == "rating") {
    if ($_items_id) {
        $_value = (int)$_REQUEST["value"];

        if ($value < 0) {
            $value = 0;
        } elseif ($value > 10) {
            $value = 10;
        }

        itemSetRating($_items_id, $_value);
    }

    if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        $CNCAT["admin"]["act"] = 'links';
        $_item_id = array_shift($_items_id);

        if ($_item_id) {
            $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
            $query = "
                SELECT " . join(", ", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                WHERE `item_id` =" . $_item_id . "
            ";
        
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
            $item = mysql_fetch_assoc($res);
            $items = array();
        
            if ($item) {
                $items[$item['item_id']] = $item;
                appendCats($items);
                appendCheck($items);
        
                print renderItem($items[$item['item_id']]);
            }
        }
        
        exit;
    } else {
        header("Location: " . $_referer);
        exit;
    }
} elseif ($_mode == "destroy") {
    if ($_items_id) {
        $query = "
            DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat`
            WHERE `item_id` IN (" . join(",", $_items_id) . ")
        ";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        $query = "
            DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemfilt`
            WHERE `item_id` IN (" . join(",", $_items_id) . ")
        ";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        $query = "
            DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
            WHERE `item_id` IN (" . join(",", $_items_id) . ")
            " . (isAdmin() ? "" : "AND `moder_id`=" . getModerId());
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        $query = "
            DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "actlog`
            WHERE `item_id` IN (" . join(",", $_items_id) . ")
        ";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        $query = "
            DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkact_comm`
            WHERE `item_id` IN (" . join(",", $_items_id) . ")
        ";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        
        itemDelete("`item_id` IN(" . join(",", $_items_id) . ")");
        cn_syncAll();
    }

    header("Location: " . $_referer);
    exit;
} elseif ($_mode == "checkadd") {
    if ($_items_id) {
        $query = "SELECT `item_id` FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
            WHERE `check_flag`=0 AND `item_id` IN (" . join(",", $_items_id) . ")
            " . (isAdmin() ? "" : "AND `moder_id`=" . getModerId());
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $items = array();

        while ($row = mysql_fetch_assoc($res)) {
            $items[] = $row["item_id"];
        }

        $values = array();

        foreach ($_items_id as $item_id) {
            if (!in_array($item_id, $items)) {
                $values[] = "(" . (isModer() ? getModerId() : 0) . ", " . $item_id . ", 0)";
            }
        }

        if ($values) {
            $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
                VALUES " . join(",", $values);
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }
    }

    if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        $CNCAT["admin"]["act"] = 'links';
        $_item_id = array_shift($_items_id);

        if ($_item_id) {
            $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
            $query = "
                SELECT " . join(", ", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                WHERE `item_id` =" . $_item_id . "
            ";
        
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
            $item = mysql_fetch_assoc($res);
            $items = array();
        
            if ($item) {
                $items[$item['item_id']] = $item;
                appendCats($items);
                appendCheck($items);
        
                print renderItem($items[$item['item_id']]);
            }
        }
        
        exit;
    } else {
        header("Location: " . $_referer);
        exit;
    }
} elseif ($_mode == "checkdel") {
    if ($_items_id) {
        $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
            WHERE `moder_id`=" . (isModer() ? getModerId() : 0) . " AND `item_id` IN (" . join(",", $_items_id) . ")";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        $_item_id = array_shift($_items_id);

        if ($_item_id) {
            $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
            $query = "
                SELECT " . join(", ", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                WHERE `item_id` =" . $_item_id . "
            ";
        
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
            $item = mysql_fetch_assoc($res);
            $items = array();
        
            if ($item) {
                $items[$item['item_id']] = $item;
                appendCats($items);
                appendCheck($items);
        
                print renderItem($items[$item['item_id']]);
            }
        }
        
        exit;
    } else {
        header("Location: " . $_referer);
        exit;
    }
} elseif ($_mode == "nobroken") {
    if ($_items_id) {
        $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items`
            SET `link_broken_warning`=0
            WHERE `item_id` IN (" . join(",", $_items_id) . ")";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        $_item_id = array_shift($_items_id);

        if ($_item_id) {
            $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
            $query = "
                SELECT " . join(", ", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                WHERE `item_id` =" . $_item_id . "
            ";
        
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
            $item = mysql_fetch_assoc($res);
            $items = array();
        
            if ($item) {
                $items[$item['item_id']] = $item;
                appendCats($items);
                appendCheck($items);
        
                print renderItem($items[$item['item_id']]);
            }
        }
        
        exit;
    } else {
        header("Location: " . $_referer);
        exit;
    }
}

unset($_SESSION["global_sql_cond"]);
unset($_SESSION["global_referer"]);

$_is_find = false;

// Create sql cond
$_cond = array();
$_cond[] = "`item_status`=" . $CNCAT["page"]["type"];

if ($_cat) {
    $cats = array();

    if ($_subcat) {
        $cats = getCatChildList($_cat);
        $cats[] = $_cat;

        if (isModer()) {
            $cats = array_intersect($cats, getModerCats());
        }

        if ($cats) {
            $itemcat = getItemsByCat(array("`cat_id` IN (" . join(",", $cats) . ")"));

            if ($itemcat) {
                $_cond[] = "`item_id` IN (" . join(",", $itemcat) . ")";
                $_is_find = true;
            }
        }
    } else {
        if (isAdmin() || isModerCat($_cat)) {
            $itemcat = getItemsByCat(array("`cat_id`=" . $_cat));

            if ($itemcat) {
                $_cond[] = "`item_id` IN (" . join(",", $itemcat) . ")";
                $_is_find = true;
            }
        }
    }

} else {
    if (isModer()) {
        $itemcat = getItemsByCat(array("`cat_id` IN (" . join(",", getModerCats()) . ")"));

        if ($itemcat) {
            $_cond[] = "`item_id` IN (" . join(",", $itemcat) . ")";
        }
    }

    $_is_find = true;
}

if ($_is_find) {
    $_is_find = false;
    
    if ($_items_type > -1) {
        $_cond[] = "`item_type` = " . $_items_type;
    }

    $_items_count = itemCount(join(" AND ", $_cond));

    if ($_items_count) {
        $_is_find = true;
    }

    if ($_is_find) {
        $_SESSION["global_sql_cond"] = $_cond;
        $_SESSION["global_referer"] = $_SERVER["REQUEST_URI"];
    }
}

$CNCAT["admin"]["act"] = 'links';

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
    $query = "SELECT " . join(", ", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
        WHERE " . join(" AND ", $_cond) . "
        " . getSqlSort($_sort, $_dir) . "
        LIMIT 1 OFFSET " . (($_page + 1) * $CNCAT["config"]["items_per_page_admin"] - 1);

    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
    $items = array(mysql_fetch_assoc($res));

    if (!empty($items[0])) {
        appendCats($items);
        appendCheck($items);

        $items_str = renderItem($items[0]);
        $items_str = '"' . str_replace(
            array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
            array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'),
            $items_str) .
        '"';

        ob_start();
        displayPagebar(
            array(
                "act" => "links",
                "type" => $CNCAT["page"]["type"],
                "cat" => $_cat,
                "subcat" => $_subcat,
                "sort" => $_sort,
                "dir" => $_dir
            ),
            $_page, $_items_count
        );
        $pages_str = ob_get_clean();
        $pages_str = '"' . str_replace(
            array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
            array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'),
            $pages_str) .
        '"';

        print '[' . $items_str . ', ' . $pages_str . ']';
    } else {
        print '[]';
    }

    exit;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";

$types = array(
    $CNCAT["lang"]["links_0"],
    $CNCAT["lang"]["links_1"],
    $CNCAT["lang"]["links_2"],
    4 => $CNCAT["lang"]["links_4"],
    5 => $CNCAT["lang"]["do_isolation"]
);
print "<h1>" . $CNCAT["lang"]["links"] . " / " . $types[$CNCAT["page"]["type"]] . "</h1>";

$cats_count = array();

if (isAdmin()) {
    $query = "SELECT `cat_id`, COUNT(*) AS `count`
        FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat`
        WHERE `item_status`=" . $CNCAT["page"]["type"] . "
        GROUP BY `cat_id`";
} else {
    $query = "SELECT `cat_id`, COUNT(*) AS `count`
        FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat`
        WHERE `cat_id` IN (" . join(",", getModerCats()) . ") AND `item_status`=" . $CNCAT["page"]["type"] . "
        GROUP BY `cat_id`
    ";
}

$res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

while ($row = mysql_fetch_assoc($res)) {
    $cats_count[$row["cat_id"]] = $row["count"];
}
?>
<form action="index.php" method="get">
    <input type="hidden" name="act" value="links" />
    <input type="hidden" name="type" value="<?php print $CNCAT["page"]["type"]?>" />
    <p><?php displayCats($_cat, $_subcat, $cats_count)?></p>
    <p><?php displaySort($_sort, $_dir)?></p>
    <input type="hidden" name="page" value="<?php print $_page?>" />
</form>
<div class="deline"></div>
<?php
if ($_is_find) {
?>
<?php
    displayPagebar(
        array(
            "act" => "links",
            "type" => $CNCAT["page"]["type"],
            "cat" => $_cat,
            "subcat" => $_subcat,
            "sort" => $_sort,
            "dir" => $_dir
        ),
        $_page, $_items_count
    );
?>
<form action="index.php" method="post">
<?php
    if ($_checkone_id > 0) $_cond[] = "item_id = $_checkone_id";
    $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
    $query = "SELECT c.action_comm,i." . join(", i.", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items` i
        LEFT JOIN `" . $CNCAT["config"]["db"]["prefix"] . "linkact_comm` c ON c.item_id = i.item_id
        WHERE " . join(" AND i.", $_cond) . "
        " . getSqlSort($_sort, $_dir) . "
        " . getSqlPager($_page);
    print renderItems($query);
    print $CNCAT_ENGINE->tpl->renderTemplate("admin_table_items_bottom");
    displayPagebar(
        array(
            "act" => "links",
            "type" => $CNCAT["page"]["type"],
            "cat" => $_cat,
            "subcat" => $_subcat,
            "sort" => $_sort,
            "dir" => $_dir
        ),
        $_page, $_items_count
    );
} else {
    print "<span class=\"not_found\">" . $CNCAT["lang"]["no_links"] . "</span>";
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
