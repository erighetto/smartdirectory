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

require_once dirname(__FILE__) . "/cncat_init.php";
$CNCAT_ENGINE = cncatCreateObject ("engine", "CNCatEngine");
$CNCAT_ENGINE->initDB();
$CNCAT_ENGINE->initConfig();
$dbPrefix = $CNCAT["config"]["db"]["prefix"];
$id = (int)$_SERVER["QUERY_STRING"];

$query = "SELECT `link_url` FROM `" . $dbPrefix . "items` WHERE `item_id`=" . $id;
$res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

if ($row = mysql_fetch_assoc($res)) {
    $url = $row["link_url"];
    $inc = false;

    if ($CNCAT["config"]["jumps_to_limiter"]) {
        $query = "SELECT UNIX_TIMESTAMP(`time` + INTERVAL " . (int)$CNCAT["config"]["jumps_from_interval"] . " SECOND) AS `time`
            FROM `" . $dbPrefix . "jumps` WHERE
            `item_id`=" . $id . "
            AND `ip`=INET_ATON('" . mysql_escape_string($_SERVER["REMOTE_ADDR"]) . "')
            AND `type`='to'";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        if ($row = mysql_fetch_assoc($res)) {
            if ($row["time"] < time()) {
                $inc = true;
                $query = "UPDATE `" . $dbPrefix . "jumps` SET `time`=NOW() WHERE
                    `ip`=INET_ATON('" . mysql_escape_string($_SERVER["REMOTE_ADDR"]) . "')
                    AND `type`='to'
                    AND `item_id`=" . $id;
                $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            }
        } else {
            $inc = true;
            $query = "INSERT INTO `" . $dbPrefix . "jumps`
                VALUES(
                    " . $id . ",
                    INET_ATON('" . mysql_escape_string($_SERVER["REMOTE_ADDR"]) . "'),
                    'to',
                    NOW()
                )";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }

        $query = "DELETE FROM `" . $dbPrefix . "jumps` WHERE `time` < (NOW() - INTERVAL 1 DAY) AND `type`='to'";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    if (!$CNCAT["config"]["jumps_to_limiter"] || $inc) {
        $query = "UPDATE `" . $dbPrefix . "items` SET `link_jumps_to`=`link_jumps_to`+1, `link_jumps_to_neg`=`link_jumps_to_neg`-1 WHERE `item_id`=" . $id;
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: " . $url);
    exit;
} else {
    die("Item not found!");
}
?>
