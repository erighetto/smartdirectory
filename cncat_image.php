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

if (!mysql_connect(
        $CNCAT["config"]["db"]["host"],
        $CNCAT["config"]["db"]["user"],
        $CNCAT["config"]["db"]["password"]
    )) {
    // image not found   
    exit;
}

if (!mysql_select_db($CNCAT["config"]["db"]["name"])) {
    // image not found
    exit;
}

$table = "";
    $id = $_GET["cat"];

if (isset($_GET["cat"])) {
    $table = "`" . $CNCAT["config"]["db"]["prefix"] . "cats`";
    $fields = "`image`, `image_mime` AS `mime`";
    $field_id = "`id`";
} elseif (isset($_GET["item"])) {
    $id    = intval($_GET["item"]);
    $field = isset($_GET["field"]) ? $_GET["field"] : null;

    if (in_array($field, array('ext_image1', 'ext_image2', 'ext_image3'), true)) {
        if (isset($_GET["thumb"])) {
            $fields = "`" . $field . "_thumb` AS `image`, `" . $field . "_mime` AS `mime`";
        } else {
            $fields = "`" . $field . "` AS `image`, `" . $field . "_mime` AS `mime`";
        }
    } else {
        $fields = "`item_image` AS `image`, `item_image_mime` AS `mime`";
    }

    $table = "`" . $CNCAT["config"]["db"]["prefix"] . "items`";
    $field_id = "`item_id`";
} elseif (isset($_GET["favicon"])) {
    $id = $_GET["favicon"];
    $table = "`" . $CNCAT["config"]["db"]["prefix"] . "items`";
    $fields = "`link_favicon` AS `image`, `link_favicon_mime` AS `mime`";
    $field_id = "`item_id`";
} elseif (isset($_GET["image"])) {
    $id = $_GET["image"];
    $table = "`" . $CNCAT["config"]["db"]["prefix"] . "images`";
    $fields = "`img_data` AS `image`, `img_mime` AS `mime`";
    $field_id = "`img_id`";
} elseif (isset($_GET["thumb"])) {
    $id = $_GET["thumb"];
    $table = "`" . $CNCAT["config"]["db"]["prefix"] . "images`";
    $fields = "`thumb_data` AS `image`, `img_mime` AS `mime`";
    $field_id = "`img_id`";
} else {
    // image not found
    exit;
}

if (!($res = mysql_query("SELECT " . $fields . " FROM " . $table . " WHERE " . $field_id . "=" . intval($id)))) {
    // image not found
    exit;
}

if (!($row = mysql_fetch_assoc($res))) {
    // image not found
    exit;
}

header("Content-type: " . $row["mime"]);
print $row["image"];
