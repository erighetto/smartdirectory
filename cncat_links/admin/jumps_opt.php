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

    $config["jumps_to_limiter"] = !empty($_POST["jumps_to_limiter"]) ? 1 : 0;
    $config["jumps_to_interval"] = (int)$_POST["jumps_to_interval"];
    $config["jumps_from_limiter"] = !empty($_POST["jumps_from_limiter"]) ? 1 : 0;
    $config["jumps_from_interval"] = (int)$_POST["jumps_from_interval"];
    $config["rating_vote_limiter"] = !empty($_POST["rating_vote_limiter"]) ? 1 : 0;
    $config["rating_vote_interval"] = (int)$_POST["rating_vote_interval"];

    foreach ($config as $name => $value) {
        $query = "REPLACE `" . $CNCAT["config"]["db"]["prefix"] . "config` SET `name`='" . $name . "', `value`='" . mysql_escape_string($value) . "'";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=jumps_opt");
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
<h1><?php print $CNCAT["lang"]["settings"]?> / <?php print $CNCAT["lang"]["settings_jumps_security"]?></h1>
<form action="index.php?act=jumps_opt" method="post">
<table class="form">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["settings_jumps_security"]?></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["use_jumps_to_limiter"]?></td><td class="field"><input type="checkbox" name="jumps_to_limiter" <?php print !empty($_POST["jumps_to_limiter"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["jumps_timeout"]?></td><td class="field"><input type="text" class="text" name="jumps_to_interval" value="<?php print htmlspecialchars($_POST["jumps_to_interval"])?>" /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["use_jumps_from_limiter"]?></td><td class="field"><input type="checkbox" name="jumps_from_limiter" <?php print !empty($_POST["jumps_from_limiter"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["jumps_timeout"]?></td><td class="field"><input type="text" class="text" name="jumps_from_interval" value="<?php print htmlspecialchars($_POST["jumps_from_interval"])?>" /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["use_vote_limiter"]?></td><td class="field"><input type="checkbox" name="rating_vote_limiter" <?php print !empty($_POST["rating_vote_limiter"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["vote_limiter_time"]?></td><td class="field"><input type="text" class="text" name="rating_vote_interval" value="<?php print htmlspecialchars($_POST["rating_vote_interval"])?>" /></td></tr>

    <tr><td class="deline" colspan="2"></td>
    <tr><td class="submit" colspan="2"><input type="submit" name="doPost" class="submit" value="<?php print $CNCAT["lang"]["do_save"]?>" /></td></tr>
</table>
</form>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
