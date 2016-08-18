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
require_once dirname(__FILE__) . "/cncat_init.php";
$CNCAT_ENGINE = cncatCreateObject ("engine", "CNCatEngine");
$CNCAT_ENGINE->initDB();
$CNCAT_ENGINE->initConfig();
$CNCAT_ENGINE->initMisc();
if (PHP_SAPI == 'cli') {
    if (isset($argv[1])) {
        parse_str($argv[1], $_GET);
    }
}

$items_limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
$expire_days = isset($_GET['expire']) ? intval($_GET['expire']) : 30;
$item_types = isset($_GET['type']) ? (array)$_GET['type'] : array();
$item_status = isset($_GET['status']) ? (array)$_GET['status'] : array();
$password = isset($_GET['passw']) ? $_GET['passw'] : array();

require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "check.php";

if (
    empty($CNCAT["config"]["cron_password"])
    || $password != $CNCAT["config"]["cron_password"]
) {
    exit;
}

if ($expire_days <= 0) {
    $expire_days = 1;
}

foreach ($item_types as $k => $type) {
    $item_types[$k] = intval($type);

    if ($item_types[$k] != 0 && $item_types[$k] != 1) {
        unset($item_types[$k]);
    }
}

foreach ($item_status as $k => $status) {
    $item_status[$k] = intval($status);

    if ($item_status[$k] != 0 && $item_status[$k] != 1 && $item_status[$k] != 2) {
        unset($item_status[$k]);
    }
}

if (!$item_types || !$item_status) {
    exit;
}

$query = "
    SELECT `item_id`, `item_title`, `link_url`, `link_back_link_url`
    FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
    WHERE 
        link_chk_work_date < (NOW() - INTERVAL " . $expire_days . " DAY) AND
        item_type IN(" . implode(',', $item_types) . ") AND
        item_status IN(" . implode(',', $item_status) . ")
    ORDER BY `link_chk_work_date` ASC
    " . ($items_limit > 0 ? "LIMIT 0, " . $items_limit : "") . "
";
$result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
$check_log_id = !empty($_GET["log_id"]) ? intval($_GET["log_id"]) : 0;

set_time_limit(0);
$max_exec_time = intval(ini_get('max_execution_time'));

if ($max_exec_time > 5) {
    $max_exec_time -= 5;
}

$exec_time = 0;

while ($row = mysql_fetch_assoc($result)) {
    if (empty($row["link_url"])) {
        continue;
    }

    $time_start = cncatGetMicrotime();
    $check_log = "";
    $check_log .= date("d-m-Y H:i:s") . " " . $row["item_id"] . " " . $row["link_url"];

    $check_result = cn_checkLink(
        $row["link_url"],
        $CNCAT["config"]["check_link"],
        $CNCAT["config"]["check_back_link"],
        $CNCAT["config"]["check_pr"],
        $CNCAT["config"]["check_cy"],
        $CNCAT["config"]["check_max_timeout"],
        $CNCAT["config"]["check_max_redirect"]
    );

    if ($CNCAT["config"]["check_back_link"] && !empty($row["link_back_link_url"])) {
        $check2 = cn_checkLink(
            $row["link_back_link_url"],
            1,
            1,
            0,
            0,
            $CNCAT["config"]["check_max_timeout"],
            $CNCAT["config"]["check_max_redirect"]
        );
        $check_result["back"] = $check2["back"];
    }

    $fields = array();

    $fields[] = "`last_check`=NOW()";
    if ($CNCAT["config"]["check_link"]) {
        $fields[] = "`link_chk_work_res`=" . intval($check_result["work"]);
        $fields[] = "`link_chk_work_date`=NOW()";
        $check_log .= " work=" . intval($check_result["work"]);
    }

    if ($CNCAT["config"]["check_back_link"]) {
        $fields[] = "`link_chk_back_res`=" . intval($check_result["back"]);
        $fields[] = "`link_chk_back_date`=NOW()";
        
        $check_log .= " back=" . intval($check_result["work"]);
    }

    if ($CNCAT["config"]["check_pr"]) {
        $fields[] = "`link_rating_pr`=" . intval($check_result["pr"]);
        $fields[] = "`link_rating_pr_neg`=" . -intval($check_result["pr"]);
        $fields[] = "`link_pr_date`=NOW()";
        
        $check_log .= " pr=" . intval($check_result["pr"]);
    }

    if ($CNCAT["config"]["check_cy"]) {
        $fields[] = "`link_rating_cy`=" . intval($check_result["cy"]);
        $fields[] = "`link_rating_cy_neg`=" . -intval($check_result["cy"]);
        $fields[] = "`link_cy_date`=NOW()";
        
        $check_log .= " cy=" . intval($check_result["cy"]);
    }

    if ($CNCAT["config"]["check_favicon"]) {
        if (!empty($check["favicon"]["data"])) {
            $fields[] = "`link_favicon`='" . mysql_escape_string($check_result["favicon"]["data"]) . "'";
            $fields[] = "`link_favicon_mime`='" . mysql_escape_string($check_result["favicon"]["mime"]) . "'";
        } else {
            $fields[] = "`link_favicon`=''";
            $fields[] = "`link_favicon_mime`=''";
        }

        $fields[] = "`link_favicon_url`='" . mysql_escape_string($check_result["favicon"]["url"]) . "'";
    }

    if ($fields) {
        $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items` SET
            " . join(",", $fields) . "
            WHERE `item_id`=" . $row["item_id"] . "
        ";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    if (!$check_log_id) {
        $query = "
            INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "checklog`
            VALUES (NULL, NOW(), '" . mysql_escape_string($check_log) . "\r\n')
        ";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $check_log_id = mysql_insert_id();
        
        $_SERVER["REQUEST_URI"] .= "&log_id=" . $check_log_id;
    } else {
        $query = "
            UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "checklog` SET
                `text` = CONCAT(`text`, '" . mysql_escape_string($check_log . "\r\n") . "')
            WHERE id = " . $check_log_id . "
        ";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    $exec_time += cncatGetMicrotime() - $time_start;

    if (PHP_SAPI != 'cli') {
        if ($max_exec_time != 0 && $exec_time >= $max_exec_time) {
            header("Location: " . $_SERVER["REQUEST_URI"]);
        }
    }
}
?>
