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

$_mode = $_GET["mode"];

if ($_mode == "reset") {
    $_fields = array();

    if (!empty($_POST["jumps_to"])) {
        $_fields["link_jumps_to"] = 0;
        $_fields["link_jumps_to_neg"] = 0;
    }

    if (!empty($_POST["jumps_from"])) {
        $_fields["link_jumps_from"] = 0;
        $_fields["link_jumps_from_neg"] = 0;
    }

    if (!empty($_POST["rating_moder"])) {
        $_fields["item_rating_moder"] = 0;
        $_fields["item_rating_moder_neg"] = 0;
    }

    if ($_fields) {
        itemUpdate($_fields);
    }

    header("Location: index.php?act=counters");
    exit;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["counters"]?></h1>
<table class="form">
<form action="index.php?act=counters&mode=reset" method="post">
    <tr><td class="title"><?php print $CNCAT["lang"]["counters"]?></td></tr>
    <tr><td class="name"><input type="checkbox" name="jumps_from" id="jumps_from" /> <label for="jumps_from"><?php print $CNCAT["lang"]["counter_jumps_from"]?></label></td></tr>
    <tr><td class="name"><input type="checkbox" name="jumps_to" id="jumps_to" /> <label for="jumps_to"><?php print $CNCAT["lang"]["counter_jumps_to"]?></label></td></tr>
    <tr><td class="name"><input type="checkbox" name="rating_moder" id="rating_moder" /> <label for="rating_moder"><?php print $CNCAT["lang"]["moder_rating"]?></label></td></tr>
    <tr><td class="deline"></td></tr>
    <tr><td class="name"><input type="submit" class="submit" value="<?php print $CNCAT["lang"]["do_zero"]?>" /></td></tr>
</form>
</table>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
