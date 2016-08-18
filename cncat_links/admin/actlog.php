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

if (!isAdmin()) {
    accessDenied();
}

$_mode = isset($_GET["mode"]) ? $_GET["mode"] : "";
$_item_id = isset($_GET["item_id"]) ? intval($_GET["item_id"]) : 0;
$_compact = !empty($_GET["compact"]);

if ($_mode == "delete") {
    $query = "
        DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "actlog`
        WHERE item_id = " . $_item_id . "
    ";
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=actlog&item_id=" . $item_id . "&compact=" . ($_compact ? 1 : 0));
    exit;
}

$query = "
    SELECT a.*, m.id AS user_id, m.login AS user_login FROM `" . $CNCAT["config"]["db"]["prefix"] . "actlog` a
    LEFT JOIN `" . $CNCAT["config"]["db"]["prefix"] . "moders` m
        ON (a.user_id = m.id)
    WHERE item_id = " . $_item_id . "
    ORDER BY date DESC
";
$result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
$logs = array();

while ($row = mysql_fetch_assoc($result)) {
    $logs[] = $row;
}

if ($_compact) {
?>
<html>
    <head>
        <title><?php print $CNCAT["lang"]["links"]?> / Лог действий</title>
        <style type="text/css">
            body {
                background: white;
                padding: 5px;
                margin: 5px;
            }
            body, p {
                font-family:tahoma; font-size:11px; color:#38464b;
            }
            p {
                margin: 8px 10px;
            }
            a {
                color:#3176b1;
            }
            h1 {
                color:#3176b1;font-size:12pt;
            }
            a img {
                border: 0;
            }
            .title {
                background:#e8f0f4;border:1px solid #c2cdd1;padding:4px;
                color:black;font-weight:bold;
                white-space: nowrap;
            }
            .deline {
                border-bottom: 1px dashed #c2cdd1;
                margin: 10px 0;
            }
        </style>
    </head>
<body>
    <a href="index.php?act=actlog&mode=delete&item_id=<?php print $_item_id?>&compact=1" onclick="return confirm('<?php print $CNCAT["lang"]["really_clean_report"]?>');"><?php print $CNCAT["lang"]["clean_report"]?></a>
    <div class="deline"></div>
<?php
    if ($logs) {
        foreach ($logs as $log) {
?>
    <p><strong><?php print $log["date"]?></strong>, <?php print $CNCAT["lang"]["user"]?>: <?php print htmlspecialchars($log["user_id"] ? $log["user_login"] : $CNCAT["config"]["alogin"])?></p>
    <p><?php print nl2br(htmlspecialchars($log["comment"]))?></p>
    <div class="deline"></div>
<?php
        }
    } else {
        print "<strong>" . $CNCAT["lang"]["no_report"] . "</strong>";
    }
?>
</body>
</html>
<?php
    exit;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
    <h1><?php print $CNCAT["lang"]["action_log"]?></h1>
    <a href="index.php?act=actlog&mode=delete&item_id=<?php print $_item_id?>" onclick="return confirm('<?php print $CNCAT["lang"]["really_clean_report"]?>');"><?php print $CNCAT["lang"]["clean_report"]?></a>
    <div class="deline"></div>
    <p><input type="button" class="submit" value="<?php print $CNCAT["lang"]["do_refresh"]?>" onclick="location.href=location.href;" /></p>
<?php
if ($logs) {
?>
    <table class="items">
    <tr><td class="title"><?php print $CNCAT["lang"]["date"]?></td><td class="title"><?php print $CNCAT["lang"]["log"]?></td></tr>
    <?php foreach ($logs as $log) {?>
        <tr>
            <td class="item"><?php print $log["date"]?></td>
            <td class="item">
                <div style="width: 600px; height: 80px; font: normal 12px Courier; white-space: nowrap; overflow: scroll; border: 1px solid silver; padding: 5px;"><?php print nl2br(htmlspecialchars($log["comment"]))?></div>
            </td>
        </tr>
    <?php }?>
    </table>
<?php
} else {
    print "<span class=\"not_found\">" . $CNCAT["lang"]["no_report"] . "</span>";
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
