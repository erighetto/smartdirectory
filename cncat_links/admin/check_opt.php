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


if (isset($_POST["doPost"])) {
    $config = array();

    $config["check_method"] = abs((int)$_POST["check_method"]);
    $config["check_timeout"] = abs((int)$_POST["check_timeout"]);
    $config["check_max_redirect"] = abs((int)$_POST["check_max_redirect"]);
    $config["check_link"] = !empty($_POST["check_link"]) ? 1 : 0;
    $config["check_back_link"] = !empty($_POST["check_back_link"]) ? 1 : 0;
    $config["check_cy"] = !empty($_POST["check_cy"]) ? 1 : 0;
    $config["check_pr"] = !empty($_POST["check_pr"]) ? 1 : 0;
    $config["check_favicon"] = !empty($_POST["check_favicon"]) ? 1 : 0;

    if ($config["check_method"] > 1) {
        $config["check_method"] = 1;
    }

    $config["check_user_agent"] = cn_substr($_POST["check_user_agent"], 0, 255);
    $config["check_robots_txt"] = !empty($_POST["check_robots_txt"]) ? 1 : 0;
    $config["check_bots_list"] = cn_substr($_POST["check_bots_list"], 0, 255);

    foreach ($config as $name => $value) {
        $query = "REPLACE `" . $CNCAT["config"]["db"]["prefix"] . "config` SET `name`='" . $name . "', `value`='" . mysql_escape_string($value) . "'";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=check_opt");
    exit;
} else {
    $query = "SELECT `name`, `value` FROM `" . $CNCAT["config"]["db"]["prefix"] . "config`";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    while ($row = mysql_fetch_assoc($res)) {
        $_POST[$row["name"]] = $row["value"];
    }
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["settings"]?> / <?php print $CNCAT["lang"]["settings_check"]?></h1>
<table class="form">
<form action="index.php?act=check_opt" method="post">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["settings_check"]?></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["check_method"]?></td>
        <td class="field">
            <select name="check_method">
                <option value="0" <?php print $_POST["check_method"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["method_sock"]?></option>
                <option value="1" <?php print $_POST["check_method"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["method_fget"]?></option>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["check_timeout"]?></td><td class="field"><input type="text" class="text" name="check_timeout" value="<?php print htmlspecialchars($_POST["check_timeout"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["check_maxredirect"]?></td><td class="field"><input type="text" class="text" name="check_max_redirect" value="<?php print htmlspecialchars($_POST["check_max_redirect"])?>" /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["check_work"]?></td><td class="field"><input type="checkbox" name="check_link" <?php print !empty($_POST["check_link"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["check_back_link"]?></td><td class="field"><input type="checkbox" name="check_back_link" <?php print !empty($_POST["check_back_link"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["check_pr"]?></td><td class="field"><input type="checkbox" name="check_pr" <?php print !empty($_POST["check_pr"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["check_cy"]?></td><td class="field"><input type="checkbox" name="check_cy" <?php print !empty($_POST["check_cy"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["check_favicon"]?></td><td class="field"><input type="checkbox" name="check_favicon" <?php print !empty($_POST["check_favicon"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    
    <tr><td class="name"><?php print $CNCAT["lang"]["user_agent_header"]?></td><td class="field">
        <input type="text" name="check_user_agent" class="text" value="<?php print htmlspecialchars(!empty($_POST["check_user_agent"]) ? $_POST["check_user_agent"] : "")?>" />
        <p style="background: #efefef; padding: 2px 5px;">Mozilla/5.0 (compatible; CNCat/4.2; +http://www.cn-software.com/en/cncat/robot/)</p>
    </td></tr>
    
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["check_robots_txt"]?></td><td class="field"><input type="checkbox" name="check_robots_txt" <?php print !empty($_POST["check_robots_txt"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name">
        <?php print $CNCAT["lang"]["robots_txt_bots_list"]?>
        <p style="background: #efefef; padding: 2px 5px;"><?php print $CNCAT["lang"]["check_bots_list_hint"]?></p>
    </td><td class="field">
        <input type="text" name="check_bots_list" class="text" value="<?php print htmlspecialchars(!empty($_POST["check_bots_list"]) ? $_POST["check_bots_list"] : "")?>" />
        <p style="background: #efefef; padding: 2px 5px;">Aport, Googlebot, Lycos, Mail.Ru, MSNBot, Slurp, StackRambler, WebAlta, Yandex</p>
    </td></tr>

    <tr><td class="deline" colspan="2"></td>
    <tr><td class="submit" colspan="2"><input type="submit" name="doPost" class="submit" value="<?php print $CNCAT["lang"]["do_save"]?>" /></td></tr>
</form>
</table>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
