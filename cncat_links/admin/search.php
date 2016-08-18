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

require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_product"] . "search.php";
$CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], "admin");

unset($_SESSION["global_sql_cond"]);
unset($_SESSION["global_referer"]);

$_do_search = isset($_GET["doSearch"]);
$_page = (int)$_GET["page"];

if (!isset($_REQUEST["sort"])) {
    $_sort = 3;
} else {
    $_sort = (int)$_REQUEST["sort"];
}

$_dir = (int)$_GET["dir"];
if (!get_magic_quotes_gpc())
  $_GET["q"] = addslashes($_GET["q"]);
// search query
$_query = cn_substr(cn_trim(urldecode($_GET["q"])), 0, 255);

if (cn_strlen($_query) < 4) {
    $_query = "";
}

$_search_cond = createSearchCondition($_query);

// category
$_cat = (int)$_GET["cat"];
$_subcat = !empty($_GET["subcat"]);

// items status
$_types = (array)$_GET["types"];

foreach ($_types as $num => $type) {
    $_types[$num] = (int)$type;

    if ( ( $type < 0 || $type > 2 ) && $type != 5 ) {
        unset($_types[$num]);
    }
}

$_types = array_unique($_types);

// link check result
//$_check = (int)$_GET["check"];
// 0 - любые, 1 - рабочие, 2 - нерабочие, 3 - рабочие с обратной.

//if ($_check < 0 || $_check > 3) {
//    $_check = 0;
//}

$_work = isset($_GET["work"]) ? intval($_GET["work"]) : 0;

if ($_work < 0 || $_work > 2) {
    $_work = 0;
}

$_back = isset($_GET["back"]) ? intval($_GET["back"]) : 0;

if ($_back < 0 || $_back > 2) {
    $_back = 0;
}

// insert and submit type
$_insert_type = isset($_GET["insert_type"]) ? intval($_GET["insert_type"]) : 0;

if ($_insert_type < 0 || $_insert_type > 3) {
    $_insert_type = 0;
}

$_submit_type = isset($_GET["submit_type"]) ? intval($_GET["submit_type"]) : 0;

if ($_submit_type < 0 || $_submit_type > 3) {
    $_submit_type = 0;
}
$_items_type = -1;

if (empty($_REQUEST["t0"]) || empty($_REQUEST["t1"])) {
    if(!empty($_REQUEST["t0"])) {
        $_items_type = 0;
    } elseif(!empty($_REQUEST["t1"])) {
        $_items_type = 1;
    }
}

// filters
$_fils = (array)$_GET["fils"];

foreach ($_fils as $fnum => $values) {
    $_fils[$fnum] = array();

    foreach ($values as $vid) {
        $_fils[$fnum][] = (int)$vid;
    }
}

// begin and end date
$_use_date_begin = !empty($_GET["use_date_begin"]);
$_use_date_end = !empty($_GET["use_date_end"]);

$_date_begin = cn_strtotime($_GET["date_begin"]);
$_date_end = cn_strtotime($_GET["date_end"]);

//begin and end last check date
$_use_date_check_begin = !empty($_GET["use_date_check_begin"]);
$_use_date_check_end = !empty($_GET["use_date_check_end"]);

$_date_check_begin = cn_strtotime($_GET["date_check_begin"]);
$_date_check_end = cn_strtotime($_GET["date_check_end"]);

if ($_do_search) {
    $_is_find = false;
    $_cond = array();

    if (!empty($_types)) {
        $_cond[] = "`item_status` IN(" . join(",", $_types) . ")";
    } else {
        $_cond[] = "`item_status` IN(0,1,2,5)";
    }

    // get items by categories
    $cond = $_cond;

    if ($_cat) {
        if ($_subcat) {
            $cats = getCatChildList($_cat);
            $cats[] = $_cat;

            if (isModer()) {
                $cats = array_intersect($cats, getModerCats());
            }

            if ($cats) {
                $cond[] = "`cat_id` IN (" . join(",", $cats) . ")";

                if ($_items_id = getItemsByCat($cond)) { 
                    $_is_find = true;
                }
            }
        } else {
            $cond[] = "`cat_id`=" . $_cat;

            if (isAdmin() || isModerCat($_cat)) {
                if ($_items_id = getItemsByCat($cond)) {
                    $_is_find = true;
                }
            }
        }
    } else {
        if (isModer()) {
            $cond[] = "`cat_id` IN (" . join(",", getModerCats()) . ")";
            if ($_items_id = getItemsByCat($cond)) { 
                $_is_find = true;
            }
        } else {
            $_is_find = true;
        }
    }

    // get items by filters
    if ($_is_find) {
        $_is_find = false;

        if ($_fils) {
            $join = array();
            $where = array();

            foreach ($_fils as $filter_id => $values) {
                $values = (array)$values;

                $join[] = "`" . $CNCAT["config"]["db"]["prefix"] . "itemfilt` f" . $filter_id . " ON ic.item_id=f" . $filter_id . ".item_id";
                $where[] = "f" . $filter_id . ".filtval_id IN(" . join(",", $values) . ")";
            }

            $query = "SELECT DISTINCT i.item_id FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat` ic
                LEFT JOIN " . join(" LEFT JOIN ", $join) . "
                LEFT JOIN `" . $CNCAT["config"]["db"]["prefix"] . "items` i ON ic.item_id=i.item_id
                WHERE " . join(" AND ", $where) . ($_items_id ? (" AND i.item_id IN (" . join(",", $_items_id) . ")") : "");
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
            $_items_id = array();

            while ($row = mysql_fetch_assoc($res)) {
                $_items_id[] = $row["item_id"];
            }

            if ($_items_id) {
                $_cond[] = "`item_id` IN (" . join(",", $_items_id) .")";
                $_is_find = true;
            }
        } else {
            if ($_items_id) {
                $_cond[] = "`item_id` IN (" . join(",", $_items_id) .")";
            }

            $_is_find = true;
        }
    }
    
    $with_check_date = 0;
    // other conds
    if ($_is_find) {
        $_is_find = false;

        if ($_work == 1) {
            $_cond[] = "`link_chk_work_res`=1";
        } elseif ($_work == 2) {
            $_cond[] = "`link_chk_work_res`=0";
        }
        if ($_items_type > -1) {
            $_cond[] = "`item_type` = " . $_items_type;
        }
        if ($_back == 1) {
            $_cond[] = "`link_chk_back_res`=1";
        } elseif ($_back == 2) {
            $_cond[] = "`link_chk_back_res`=0";
        }

        if ($_use_date_begin) {
            $_cond[] = "UNIX_TIMESTAMP(`item_insert_date`)>" . $_date_begin;
        }

        if ($_use_date_end) {
            $_cond[] = "UNIX_TIMESTAMP(`item_insert_date`)<" . $_date_end;
        }
        //add creteria for last check
        if ($_use_date_check_begin) {
            $with_check_date = 1;
            $_cond[] = "UNIX_TIMESTAMP(`last_check`)>" . $_date_check_begin;
        }
        if ($_use_date_check_end) {
            $with_check_date = 1;
            $_cond[] = "UNIX_TIMESTAMP(`last_check`)<" . $_date_check_end;
        }

        if ($_insert_type != 0) {
            $_cond[] = "`item_insert_type`=" . $_insert_type;
        }

        if ($_submit_type != 0) {
            $_cond[] = "`item_submit_type`=" . $_submit_type;
        }

        if (!empty($_query)) {
            $_cond[] = $_search_cond["where"];
        }

        // get find items count
        $_items_count = getItemsCount($_cond);
        if ($_items_count) {
            $_is_find = true;
        }
    }

    if ($_is_find) {
        $_SESSION["global_sql_cond"] = $_cond;
        $_SESSION["global_referer"] = $_SERVER["REQUEST_URI"];
    }
}

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");

    if ($_search_cond["fields"]) {
        $fields["int"][] = $_search_cond["fields"];
    }

    $query = "SELECT " . join(",", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
        " . ($_cond ? "WHERE " . join(" AND ", $_cond) : "") . "
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
        $_url = array();
        $_url["act"] = "search";
        $_url["doSearch"] = 1;

        if (!empty($_GET["cat"])) {
            $_url["cat"] = $_GET["cat"];
        }

        if (!empty($_GET["subcat"])) {
            $_url["subcat"] = $_GET["subcat"];
        }
        if (!empty($_GET["types"])) {
            $_url["types[]"] = join("&types[]=", (array)$_GET["types"]);
        }

        if (!empty($_GET["insert_type"])) {
            $_url["insert_type"] = $_GET["insert_type"];
        }
        if (!empty($_GET["submit_type"])) {
            $_url["submit_type"] = $_GET["submit_type"];
        }
        if (!empty($_GET["work"])) {
            $_url["work"] = $_GET["work"];
        }
        if (!empty($_GET["back"])) {
            $_url["back"] = $_GET["back"];
        }
        if (is_array($_GET["fils"])) {
            foreach($_GET["fils"] as $id => $values)
              $_url["fils[$id][]"] = join("&fils[$id][]=", $values);
        }

        if (!empty($_GET["use_date_begin"])) {
            $_url["use_date_begin"] = $_GET["use_date_begin"];
        }

        if (!empty($_GET["use_date_end"])) {
            $_url["use_date_end"] = $_GET["use_date_end"];
        }

        if (!empty($_GET["date_begin"])) {
            $_url["date_begin"] = $_GET["date_begin"];
        }

        if (!empty($_GET["date_end"])) {
            $_url["date_end"] = $_GET["date_end"];
        }

        if (!empty($_GET["q"])) {
            $_url["q"] = $_GET["q"];
        }

        if (!empty($_GET["sort"])) {
            $_url["sort"] = $_GET["sort"];
        }

        if (!empty($_GET["dir"])) {
            $_url["dir"] = $_GET["dir"];
        }

        displayPagebar($_url, $_page, $_items_count);
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
?>
<h1><?php print $CNCAT["lang"]["links"]?> / <?php print $CNCAT["lang"]["search"]?></h1>
<?php           
print $CNCAT_ENGINE->tpl->renderTemplate("js_calendar");
?>
<form action="index.php" method="get" style="display: block;">
    <input type="hidden" name="act" value="search" />
    <input type="hidden" name="doSearch" value="1" />
    <p><?php print displayCats($_cat, $_subcat)?></p>
    <div class="deline"></div>
    <p>
        <input type="checkbox" name="types[]" value="0" <?php print in_array(0, $_types) ? "checked=\"checked\"" : ""?> id="status0" /> <label for="status0"><?php print $CNCAT["lang"]["links_0"]?></label>
        <input type="checkbox" name="types[]" value="1" <?php print in_array(1, $_types) ? "checked=\"checked\"" : ""?> id="status1" /> <label for="status1"><?php print $CNCAT["lang"]["links_1"]?></label>
        <input type="checkbox" name="types[]" value="2" <?php print in_array(2, $_types) ? "checked=\"checked\"" : ""?> id="status2" /> <label for="status2"><?php print $CNCAT["lang"]["links_2"]?></label>
        <input type="checkbox" name="types[]" value="5" <?php print in_array(5, $_types) ? "checked=\"checked\"" : ""?> id="status5" /> <label for="status5"><?php print $CNCAT["lang"]["do_isolation"]?></label>
    </p>
    <div class="deline"></div>
    <p>
        <?php print $CNCAT["lang"]["insert_by"]?>: <select name="insert_type">
            <option value="0" <?php print $_insert_type == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["any"]?></option>
            <option value="1" <?php print $_insert_type == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["by_user"]?></option>
            <option value="2" <?php print $_insert_type == 2 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["by_admin"]?></option>
            <option value="3" <?php print $_insert_type == 3 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["by_robot"]?></option>
        </select>
        <?php print $CNCAT["lang"]["submit_by"]?>: <select name="submit_type">
            <option value="0" <?php print $_submit_type == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["any"]?></option>
            <option value="1" <?php print $_submit_type == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["by_admin"]?></option>
            <option value="2" <?php print $_submit_type == 2 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["at_insert"]?></option>
            <option value="3" <?php print $_submit_type == 3 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["at_check"]?></option>
        </select>
    </p>
    <div class="deline"></div>
    <p>
        <?php print $CNCAT["lang"]["capacity"]?>: <select name="work">
            <option value="0" <?php print $_work == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["any"]?></option>
            <option value="1" <?php print $_work == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["work"]?></option>
            <option value="2" <?php print $_work == 2 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["not_work"]?></option>
        </select>
        <?php print $CNCAT["lang"]["back_link"]?>: <select name="back">
            <option value="0" <?php print $_back == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["any"]?></option>
            <option value="1" <?php print $_back == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["with_backlink"]?></option>
            <option value="2" <?php print $_back == 2 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["without_backlink"]?></option>
        </select>
    </p>
    <div class="deline"></div>
<?php
$query = "SELECT
            f.id `fid`, f.title `ftitle`, f.sort_order `forder`, f.required,
            v.id `vid`, v.title `vtitle`, v.sort_order `vorder`, v.filter_id
    FROM `" . $CNCAT["config"]["db"]["prefix"] . "filters` f
    LEFT JOIN `" . $CNCAT["config"]["db"]["prefix"] . "filtvals` v
    ON (v.filter_id=f.id)";
$res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
$filters = array();

while ($row = mysql_fetch_assoc($res)) {
    if (!isset($filters[$row["fid"]])) {
        $filters[$row["fid"]] = array(
            'title' => $row["ftitle"],
            'sort_order' => $row["forder"],
            'required' => $row["required"],
            'values' => array()
        );
    }

    if ($row["vid"]) {
        $filters[$row["fid"]]["values"][$row["vid"]] = array(
            'title' => $row["vtitle"],
            'sort_order' => $row["vorder"]
        );
    }
}

if ($filters) {
    print "<table>\n";

    foreach ($filters as $filter_id => $filter) {
        print "<tr><td>" . htmlspecialchars($filter["title"]) . ":</td><td>";

        foreach ($filter["values"] as $value_id => $value) {
            print "<label><input type=\"checkbox\" name=\"fils[" . $filter_id . "][]\" value=\"" . $value_id . "\" " . (in_array($value_id, (array)$_fils[$filter_id]) ? "checked=\"checked\"" : "") . " />" . htmlspecialchars($value["title"]) . "</label>&nbsp;";
        }

        print "</td></tr>";
    }

    print "</table>\n";
    print "<div class=\"deline\"></div>\n";
}
?>
    <table width="480">
        <tr>
            <td colspan="2">
                <?php print $CNCAT["lang"]["add_filter"]?>
                <br />
            </td>
        </tr>
        <tr><td>
            <div id="cal1" class="calendar"></div>
            <input type="checkbox" name="use_date_begin" id="use_date_begin" <?php print !empty($_GET["use_date_begin"]) ? "checked=\"checked\"" : ""?> /> <label for="use_date_begin"><?php print $CNCAT["lang"]["date_begin"]?>:</label>
            <input type="text" name="date_begin" id="date_begin" size="18" value="<?php print isset($_GET['date_begin']) ? htmlspecialchars($_GET['date_begin']) : date('d.m.Y', strtotime('-1 Month'))?>" readonly="readonly" />
            <img src="<?php print $CNCAT['abs'] . $CNCAT['system']['dir_engine_images']?>calendar.gif" style="cursor: pointer; vertical-align: middle;" onclick="ShowCalendarE('cal1', 'date_begin', 0);" />
        </td><td>
            <div id="cal2" class="calendar"></div>
            <input type="checkbox" name="use_date_end" id="use_date_end" <?php print !empty($_GET["use_date_end"]) ? "checked=\"checked\"" : ""?> /> <label for="use_date_end"><?php print $CNCAT["lang"]["date_end"]?>:</label>
            <input type="text" name="date_end" id="date_end" size="18" value="<?php print isset($_GET['date_end']) ? htmlspecialchars($_GET['date_end']) : date('d.m.Y')?>" readonly="readonly" />
            <img src="<?php print $CNCAT['abs'] . $CNCAT['system']['dir_engine_images']?>calendar.gif" style="cursor: pointer; vertical-align: middle;" onclick="ShowCalendarE('cal2', 'date_end', 0);" />
        </td></tr>
    </table>

    <div class="deline"></div>

    <table width="480">
        <tr>
            <td colspan="2">
                <?php print $CNCAT["lang"]["check_filter"]?>
                <br />
            </td>
        </tr>
        <tr><td>
            <div id="cal3" class="calendar"></div>
            <input type="checkbox" name="use_date_check_begin" id="use_date_check_begin" <?php print !empty($_GET["use_date_check_begin"]) ? "checked=\"checked\"" : ""?> /> <label for="use_date_check_begin"><?php print $CNCAT["lang"]["date_begin"]?>:</label>
            <input type="text" name="date_check_begin" id="date_check_begin" size="18" value="<?php print isset($_GET['date_check_begin']) ? htmlspecialchars($_GET['date_check_begin']) : date('d.m.Y', strtotime('-1 Month'))?>" readonly="readonly" />
            <img src="<?php print $CNCAT['abs'] . $CNCAT['system']['dir_engine_images']?>calendar.gif" style="cursor: pointer; vertical-align: middle;" onclick="ShowCalendarE('cal3', 'date_check_begin', 0);" />
        </td><td>
            <div id="cal4" class="calendar"></div>
            <input type="checkbox" name="use_date_check_end" id="use_date_check_end" <?php print !empty($_GET["use_date_check_end"]) ? "checked=\"checked\"" : ""?> /> <label for="use_date_check_end"><?php print $CNCAT["lang"]["date_end"]?>:</label>
            <input type="text" name="date_check_end" id="date_check_end" size="18" value="<?php print isset($_GET['date_check_end']) ? htmlspecialchars($_GET['date_check_end']) : date('d.m.Y')?>" readonly="readonly" />
            <img src="<?php print $CNCAT['abs'] . $CNCAT['system']['dir_engine_images']?>calendar.gif" style="cursor: pointer; vertical-align: middle;" onclick="ShowCalendarE('cal4', 'date_check_end', 0);" />
        </td></tr>
    </table>

    <div class="deline"></div>
    <p><?php print $CNCAT["lang"]["search_query"]?>: <input type="text" size="40" name="q" value="<?php print htmlspecialchars($_GET["q"])?>" /></p>
    <div class="deline"></div>
    <p>
        <input type="submit" class="submit" value="<?php print $CNCAT["lang"]["do_search"]?>" />
        <input type="button" class="submit" value="<?php print $CNCAT["lang"]["do_reset"]?>" onclick="location.href='index.php?act=search'" />
    </p>
    <div class="deline"></div>
    <p><?php displaySort($_sort, $_dir)?></p>
    <div class="deline"></div>
</form>
<?php
if ($_do_search) {
?>
<?php
    if ($_is_find) {
        print "<p>" . $CNCAT["lang"]["links_find_count"] . ": <strong>" . $_items_count . "</strong></p>";
?>
<?php
        $_url = array();
        $_url["act"] = "search";
        $_url["doSearch"] = 1;

        if (!empty($_GET["cat"])) {
            $_url["cat"] = $_GET["cat"];
        }
        if (empty($_REQUEST["t0"]) || empty($_REQUEST["t1"])) {
            if(!empty($_REQUEST["t0"])) {
                $_url["t0"] = "on";
            } elseif(!empty($_REQUEST["t1"])) {
                $_url["t1"] = "on";
            }
        }
        if (!empty($_GET["subcat"])) {
            $_url["subcat"] = $_GET["subcat"];
        }

        if (!empty($_GET["types"])) {
            $_url["types[]"] = join("&types[]=", (array)$_GET["types"]);
        }

        if (!empty($_GET["insert_type"])) {
            $_url["insert_type"] = $_GET["insert_type"];
        }
        if (!empty($_GET["submit_type"])) {
            $_url["submit_type"] = $_GET["submit_type"];
        }
        if (!empty($_GET["work"])) {
            $_url["work"] = $_GET["work"];
        }
        if (!empty($_GET["back"])) {
            $_url["back"] = $_GET["back"];
        }

        if (is_array($_GET["fils"])) {
            foreach($_GET["fils"] as $id => $values)
              $_url["fils[$id][]"] = join("&fils[$id][]=", $values);
        }

        if (!empty($_GET["use_date_begin"])) {
            $_url["use_date_begin"] = $_GET["use_date_begin"];
        }

        if (!empty($_GET["use_date_end"])) {
            $_url["use_date_end"] = $_GET["use_date_end"];
        }

        if (!empty($_GET["date_begin"])) {
            $_url["date_begin"] = $_GET["date_begin"];
        }

        if (!empty($_GET["date_end"])) {
            $_url["date_end"] = $_GET["date_end"];
        }

        if (!empty($_GET["q"])) {
            $_url["q"] = $_GET["q"];
        }

        if (!empty($_GET["sort"])) {
            $_url["sort"] = $_GET["sort"];
        }

        if (!empty($_GET["dir"])) {
            $_url["dir"] = $_GET["dir"];
        }
        if (!empty($_GET["use_date_begin"])) {
            $_url["use_date_check_begin"] = $_GET["use_date_check_begin"];
        }

        if (!empty($_GET["use_date_end"])) {
            $_url["use_date_check_end"] = $_GET["use_date_check_end"];
        }

        if (!empty($_GET["date_begin"])) {
            $_url["date_check_begin"] = $_GET["date_check_begin"];
        }

        if (!empty($_GET["date_end"])) {
            $_url["date_check_end"] = $_GET["date_check_end"];
        }
        displayPagebar($_url, $_page, $_items_count);
?>
<form action="index.php" method="post">
<?php
        $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM"); 
        $fields = "i." . join(",i.", $fields['int']);
        $con = array();
        if ($_search_cond["fields"]) {
            $con[] = $_search_cond["fields"];
        }
        $query = "SELECT c.action_comm," . $fields . ','.join(",", $con) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items` i
        LEFT JOIN `" . $CNCAT["config"]["db"]["prefix"] . "linkact_comm` c ON c.item_id = i.item_id
        WHERE " . join(" AND ", $_cond) . "
        " . getSqlSort($_sort, $_dir) . "
        " . getSqlPager($_page);

        print renderItems($query);
        print $CNCAT_ENGINE->tpl->renderTemplate("admin_table_items_bottom");
        displayPagebar($_url, $_page, $_items_count);
?>
<?php
    } else {
        print "<span class=\"not_found\">" . $CNCAT["lang"]["links_not_found"] . "</span>";
    }
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
