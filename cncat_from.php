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
$id = $_SERVER["QUERY_STRING"];
 
if (substr($id, 0, 1) == "r") {
    $query = "
        SELECT item_id
        FROM `" . $dbPrefix . "items`
        WHERE item_token = '" . mysql_escape_string(substr($id, 0, 15)) . "'
            AND item_status = 1
    ";
} else {
    $query = "
        SELECT item_id
        FROM `" . $dbPrefix . "items`
        WHERE item_id = " .intval($id) . "
            AND item_status = 1
    ";
}

$res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
$row = mysql_fetch_assoc($res);

if ($row) {
    $id = $row["item_id"];
} else {
    $id = 0;
}

if ($id) {
    $inc = false;

    if ($CNCAT["config"]["jumps_from_limiter"]) {
        $query = "
            SELECT UNIX_TIMESTAMP(`time` + INTERVAL " . (int)$CNCAT["config"]["jumps_from_interval"] . " SECOND) AS `time`
            FROM `" . $dbPrefix . "jumps` WHERE
            `item_id`=" . $id . "
            AND `ip`=INET_ATON('" . mysql_escape_string($_SERVER["REMOTE_ADDR"]) . "')
            AND `type`='from'
        ";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        if ($row = mysql_fetch_assoc($res)) {
            if ($row["time"] < time()) {
                $inc = true;
                $query = "
                    UPDATE `" . $dbPrefix . "jumps` SET `time`=NOW()
                    WHERE
                        `ip`=INET_ATON('" . mysql_escape_string($_SERVER["REMOTE_ADDR"]) . "')
                        AND `type`='from'
                        AND `item_id`=" . $id . "
                    ";
                $CNCAT_ENGINE->db->query($query, null, false) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            }
        } else {
            $inc = true;
            $query = "
                INSERT INTO `" . $dbPrefix . "jumps`
                VALUES(
                    " . $id . ",
                    INET_ATON('" . mysql_escape_string($_SERVER["REMOTE_ADDR"]) . "'),
                    'from',
                    NOW()
                )
            ";
            $CNCAT_ENGINE->db->query($query, null, false) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }

        $query = "DELETE FROM `" . $dbPrefix . "jumps` WHERE `time` < (NOW() - INTERVAL 1 DAY) AND `type`='from'";
        $CNCAT_ENGINE->db->query($query, null, false) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    if (!$CNCAT["config"]["jumps_from_limiter"] || $inc) {
        $query = "UPDATE `" . $dbPrefix . "items` SET `link_jumps_from`=`link_jumps_from`+1, `link_jumps_from_neg`=`link_jumps_from_neg`-1 WHERE `item_id`=" . $id;
        $CNCAT_ENGINE->db->query($query, null, false) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }
}

header("Location: " . $CNCAT["config"]["cncat_url"]);
exit;
?>
