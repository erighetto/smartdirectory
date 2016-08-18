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

if ($_mode == "clearlog") {
    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "checklog`";
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=cron");
    exit;
}

if ($_mode == "logs") {
    $query = "
        SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "checklog`
        ORDER BY id DESC
    ";
    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $logs = array();

    while ($row = mysql_fetch_assoc($result)) {
        $logs[] = $row;
    }

    include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
    <h1><?php print $CNCAT["lang"]["settings"]?> / <?php print $CNCAT["lang"]["check_report"]?></h1>
    <a href="index.php?act=cron&mode=cron"><?php print $CNCAT["lang"]["check_by_schedule"]?></a>,
    <a href="index.php?act=cron&mode=clearlog" onclick="return confirm('<?php print $CNCAT["lang"]["really_clean_report"]?>');"><?php print $CNCAT["lang"]["clean_report"]?></a>
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
                <div style="width: 600px; height: 100px; font: normal 12px Courier; white-space: nowrap; overflow: scroll; border: 1px solid silver; padding: 5px;"><?php print nl2br(htmlspecialchars($log["text"]))?></div>
            </td>
        </tr>
    <?php }?>
    </table>
<?php
    } else {
        print "<span class=\"not_found\">" . $CNCAT["lang"]["no_report"] . "</span>";
    }

    include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
} else {
    if (isset($_POST["doPost"])) {
        $config = array();

        if ($_mode == "gen") {
            $config["cron_item_type"] = (!empty($_POST["item_type"]) && is_array($_POST["item_type"]) ? implode(",", $_POST["item_type"]) : "");
            $config["cron_item_status"] = (!empty($_POST["item_status"]) && is_array($_POST["item_status"]) ? implode(",", $_POST["item_status"]) : "");
            $config["cron_check_period"] = abs((int)$_POST["cron_check_period"]);
            $config["cron_check_limit"] = abs((int)$_POST["cron_check_limit"]);
        } else {
            $config["cron_password"] = cn_substr($_POST["cron_password"], 0, 255);   
        }

        foreach ($config as $name => $value) {
            $query = "REPLACE `" . $CNCAT["config"]["db"]["prefix"] . "config` SET `name`='" . $name . "', `value`='" . mysql_escape_string($value) . "'";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }

        header("Location: index.php?act=cron");
        exit;
    }

    $query = "";

    foreach (explode(",", $CNCAT["config"]["cron_item_type"]) as $type) {
        $query .= "&type[]=" . $type;
    }

    foreach (explode(",", $CNCAT["config"]["cron_item_status"]) as $status) {
        $query .= "&status[]=" . $status;
    }

    $query .= "&expire=" . intval($CNCAT["config"]["cron_check_period"]);
    $query .= "&limit=" . intval($CNCAT["config"]["cron_check_limit"]);
    $query .= "&passw=" . urlencode($CNCAT["config"]["cron_password"]);
    $query = substr($query, 1);
    
    $commands = array(
        "php"  => 'php -f "' . $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_prefix"] . 'cron.php" "' . $query . '"',
        "wget" => 'wget "http://' . $_SERVER['HTTP_HOST'] . $CNCAT["abs"] . $CNCAT["system"]["dir_prefix"] . 'cron.php?' . $query . '"',
    );

    include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
    <h1><?php print $CNCAT["lang"]["settings"]?> / <?php print $CNCAT["lang"]["check_by_schedule"]?></h1>
    <a href="index.php?act=cron&mode=logs"><?php print $CNCAT["lang"]["check_report"]?></a>
    <div class="deline"></div>
    <form action="index.php?act=cron&mode=save" method="post">
        <p>
            <?php print $CNCAT["lang"]["cron_access_key"]?>:<br />
            <input type="text" class="text" name="cron_password" size="30" value="<?php print htmlspecialchars($CNCAT["config"]["cron_password"])?>" />
        </p>
        <input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_save"]?>" />
    </form>
    <div class="deline"></div>

    <div class="error_box">
        <?php print $CNCAT["lang"]["cron_warning"]?>
    </div>
    <p><strong><?php print $CNCAT["lang"]["check_options"]?>:</strong></p>
    <form action="index.php?act=cron&mode=gen" method="post">
        <p>
            <input type="checkbox" name="item_type[]" value="0" id="type0" <?php print in_array("0", explode(",", $CNCAT["config"]["cron_item_type"]), true) ? "checked=\"checked\"" : "";?> /> <label for="type0"><?php print $CNCAT["lang"]["links"]?></label>
            <input type="checkbox" name="item_type[]" value="1" id="type1" <?php print in_array("1", explode(",", $CNCAT["config"]["cron_item_type"]), true) ? "checked=\"checked\"" : "";?> /> <label for="type1"><?php print $CNCAT["lang"]["articles"]?></label>
        </p>
        <p>
            <input type="checkbox" name="item_status[]" value="0" id="links_0" <?php print in_array("0", explode(",", $CNCAT["config"]["cron_item_status"]), true) ? "checked=\"checked\"" : "";?> /> <label for="links_0"><?php print $CNCAT["lang"]["links_0"]?></label>
            <input type="checkbox" name="item_status[]" value="1" id="links_1" <?php print in_array("1", explode(",", $CNCAT["config"]["cron_item_status"]), true) ? "checked=\"checked\"" : "";?> /> <label for="links_1"><?php print $CNCAT["lang"]["links_1"]?></label>
            <input type="checkbox" name="item_status[]" value="2" id="links_2" <?php print in_array("2", explode(",", $CNCAT["config"]["cron_item_status"]), true) ? "checked=\"checked\"" : "";?> /> <label for="links_2"><?php print $CNCAT["lang"]["links_2"]?></label>
        </p>
        <p>
            <?php print $CNCAT["lang"]["cron_link_update"]?>:<br />
            <input type="text" class="text" name="cron_check_period" size="30" value="<?php print intval($CNCAT["config"]["cron_check_period"])?>" />
        </p>
        <p>
            <?php print $CNCAT["lang"]["cron_update_limit"]?>:<br />
            <input type="text" class="text" name="cron_check_limit" size="30" value="<?php print intval($CNCAT["config"]["cron_check_limit"])?>" />
        </p>
        <input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_generate"]?>" />
    </form>
    <div class="deline"></div>

    <p>
        <?php print $CNCAT["lang"]["cron_command_php"]?>:<br />
        <input type="text" class="text" value="<?php print htmlspecialchars($commands['php'])?>" size="100" readonly="readonly" />
    </p>
    <p>
        <?php print $CNCAT["lang"]["cron_command_wget"]?>:<br />
        <input type="text" class="text" value="<?php print htmlspecialchars($commands['wget'])?>" size="100" readonly="readonly" />
    </p>
<?php
    include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
}
?>
