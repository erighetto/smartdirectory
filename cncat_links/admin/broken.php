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

$CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], "admin");

unset($_SESSION["global_sql_cond"]);
unset($_SESSION["global_referer"]);

$CNCAT["admin"]["act"] = "broken";

$_cat = (int)$_GET["cat"];
$_subcat = !empty($_GET["subcat"]);
$_page = (int)$_GET["page"];

if (!isset($_REQUEST["sort"])) {
    $_sort = 3;
} else {
    $_sort = (int)$_REQUEST["sort"];
}

$_dir = (int)$_GET["dir"];

$_is_find = false;
$_cond = array();
$_cond[] = "`link_broken_warning`=1";

if ($_cat) {
    $cats = array();

    if ($_subcat) {
        $cats = getCatChildList($_cat);
        $cats[] = $_cat;

        if (isModer()) {
            $cats = array_intersect($cats, getModerCats());
        }

        if ($cats) {
            $items_id = getItemsByCat(array("`cat_id` IN (" . join(",", $cats) . ")"));

            if ($items_id) {
                $_cond[] = "`item_id` IN (" . join(",", $items_id) . ")";
                $_is_find = true;
            }
        }
    } else {
        if (isAdmin() || isModerCat($_cat)) {
            $items_id = getItemsByCat(array("`cat_id`=" . $_cat));

            if ($items_id) {
                $_cond[] = "`item_id` IN (" . join(",", $items_id) . ")";
                $_is_find = true;
            }
        }
    }

} else {
    if (isModer()) {
        $items_id = getItemsByCat(array("`cat_id` IN (" . join(",", getModerCats()) . ")"));

        if ($items_id) {
            $_cond[] = "`item_id` IN (" . join(",", $items_id) . ")";
            $_is_find = true;
        }
    } else {
        $_is_find = true;
    }

    if ($_is_find) {
        $_SESSION["global_sql_cond"] = $_cond;
        $_SESSION["global_referer"] = $_SERVER["REQUEST_URI"];
    }
}

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
                "act" => "check",
                "sort" => $_GET["sort"],
                "dir" => $_GET["dir"]
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
?>
<h1><?php print $CNCAT["lang"]["links"]?> / <?php print $CNCAT["lang"]["links_broken"]?></h1>
<form action="index.php">
    <input type="hidden" name="act" value="broken" />
<?php
displayCats($_cat, $_subcat);
displaySort($_sort, $_dir);
?>
    <input type="hidden" name="page" value="<?php print $_page?>" />
</form>
<div class="deline"></div>
<?php
if ($_is_find) {
    $_items_count = getItemsCount($_cond);
} else {
    $_items_count = 0;
}

if ($_items_count) {
    print "<p>" . $CNCAT["lang"]["broken_count"] . " <strong>" . $_items_count . "</strong></p>";

?>
<?php
    displayPagebar(
        array(
            "act" => "links",
            "type" => $_type,
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
    $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
    $query = "SELECT c.action_comm,i." . join(", i.", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items` i
        LEFT JOIN `" . $CNCAT["config"]["db"]["prefix"] . "linkact_comm` c ON c.item_id = i.item_id
        WHERE " . join(" AND ", $_cond) . "
        " . getSqlSort($_sort, $_dir) . "
        " . getSqlPager($_page);

    print renderItems($query);
    print $CNCAT_ENGINE->tpl->renderTemplate("admin_table_items_bottom");
    displayPagebar(
        array(
            "act" => "links",
            "type" => $_type,
            "cat" => $_cat,
            "subcat" => $_subcat,
            "sort" => $_sort,
            "dir" => $_dir
        ),
        $_page, $_items_count
    );
?>
<?php
} else {
    print "<span class=\"not_found\">" . $CNCAT["lang"]["no_links"] . "</span>";
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
