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

error_reporting(0);

ob_start();
require_once dirname(__FILE__) . "/cncat_init.php";
ob_end_clean();

function cn_text_cut($text, $length) {
    if (mb_strlen($text) > $length) {
        $text = rtrim(mb_substr($text, 0, $length));
        $text = mb_substr($text, 0, mb_strrpos($text, ' '));
        $text .= '...';
    }

    return $text;
}

if (
    !mysql_connect(
        $CNCAT["config"]["db"]["host"],
        $CNCAT["config"]["db"]["user"],
        $CNCAT["config"]["db"]["password"]
    )
) {
    exit;
}

if (!mysql_select_db($CNCAT["config"]["db"]["name"])) {
    exit;
}

mysql_query("SET NAMES 'utf8'") or exit;

$type   = isset($_GET["type"]) ? intval($_GET["type"]) : -1;
$limit  = isset($_GET["limit"]) ? intval($_GET["limit"]) : 5;
$offset = isset($_GET["offset"]) ? intval($_GET["offset"]) : 0;
$length = isset($_GET["length"]) ? intval($_GET["length"]) : 150;
$sort   = isset($_GET["sort"]) ? $_GET["sort"] : "";

if ($type != 0 && $type != 1) {
    $type = -1;
}

if ($limit > 100) {
    $limit = 100;
} elseif ($limit < 1) {
    $limit = 1;
}

if ($offset > 1000) {
    $offset = 1000;
} elseif ($offset < 0) {
    $offset = 0;
}

if ($length > 1000) {
    $length = 1000;
} elseif ($length < 0) {
    $length = 0;
}

$sort_field = "item_submit_date";

if ($sort == "rating_moder") {
    $sort_field = "item_rating_moder";
} elseif ($sort == "rating_users") {
    $sort_field = "item_rating_users";
}

$query = "
    SELECT item_id, item_title, item_descr, link_url
    FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
    WHERE item_status = 1
";

if ($type != -1) {
    $query .= " AND item_type = " . $type;
}

$query .= " ORDER BY `" . $sort_field . "` DESC";
$query .= " LIMIT " . $offset . ", " . $limit;

$result = mysql_query($query) or exit;
?>
<ul class="cncat_items">
<?php
while ($row = mysql_fetch_assoc($result)) {
?>
    <li class="cncat_item">
        <a href="<?php print htmlspecialchars($row['link_url'])?>" target="_blank" class="cncat_link">
            <?php print htmlspecialchars($row['item_title'])?>
        </a>
<?php
    if ($length > 0) {
?>
        <p class="cncat_descr">
            <?php print htmlspecialchars(cn_text_cut(strip_tags($row['item_descr']), $length))?>
        </p>
<?php
    }
?>
    </li>
<?php
}
?>
</ul>
