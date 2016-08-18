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
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "auth.php";

if (!isAdmin()) {
    accessDenied();
}

if (isset($_POST["doPost"])) {
    foreach ($_POST["mail"] as $name => $mail) {
        if ($name != "admin" && $name != "add" && $name != "approve" && $name != "decline" && $name != "isolation") {
            continue;
        }

        $mail["from"] = cn_substr($mail["from"], 0, 255);
        $mail["reply_to"] = cn_substr($mail["reply_to"], 0, 255);
        $mail["subject"] = cn_substr($mail["subject"], 0, 255);
        $mail["body"] = cn_substr($mail["body"], 0, 65000);

        $query = "
            REPLACE INTO `" . $CNCAT["config"]["db"]["prefix"] . "mail`
            (`name`, `from`, `reply_to`, `subject`, `body`)
            VALUES (
                '" . mysql_escape_string($name) . "',
                '" . mysql_escape_string($mail["from"]) . "',
                '" . mysql_escape_string($mail["reply_to"]) . "',
                '" . mysql_escape_string($mail["subject"]) . "',
                '" . mysql_escape_string($mail["body"]) . "'
            )
        ";

        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }
    
    $config = array();

    $config["mail_notify_admin"] = !empty($_POST["mail_notify_admin"]) ? 1 : 0;
    $config["mail_notify_add"] = !empty($_POST["mail_notify_add"]) ? 1 : 0;
    $config["mail_notify_approve"] = !empty($_POST["mail_notify_approve"]) ? 1 : 0;
    $config["mail_notify_decline"] = !empty($_POST["mail_notify_decline"]) ? 1 : 0;
    $config["mail_notify_isolation"] = !empty($_POST["mail_notify_isolation"]) ? 1 : 0;

    foreach ($config as $name => $value) {
        $query = "REPLACE `" . $CNCAT["config"]["db"]["prefix"] . "config` SET `name`='" . $name . "', `value`='" . mysql_escape_string($value) . "'";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=notify_opt");
    exit;
} else {
    $query = "
        SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "mail`
    ";
    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    while ($row = mysql_fetch_assoc($result)) {
        $_POST["mail"][$row["name"]] = $row;
    }

    $query = "SELECT `name`, `value` FROM `" . $CNCAT["config"]["db"]["prefix"] . "config`";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    while ($row = mysql_fetch_assoc($res)) {
        $_POST[$row["name"]] = $row["value"];
    }
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["settings"]?> / <?php print $CNCAT["lang"]["settings_mail_notify"]?></h1>

<form action="index.php?act=notify_opt" method="post">
<table class="form">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["settings_mail_notify"]?></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_notify_admin"]?></td><td class="field"><input type="checkbox" name="mail_notify_admin" <?php print !empty($_POST["mail_notify_admin"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_notify_add"]?></td><td class="field"><input type="checkbox" name="mail_notify_add" <?php print !empty($_POST["mail_notify_add"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_notify_approve"]?></td><td class="field"><input type="checkbox" name="mail_notify_approve" <?php print !empty($_POST["mail_notify_approve"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_notify_decline"]?></td><td class="field"><input type="checkbox" name="mail_notify_decline" <?php print !empty($_POST["mail_notify_decline"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_notify_isolation"]?></td><td class="field"><input type="checkbox" name="mail_notify_isolation" <?php print !empty($_POST["mail_notify_isolation"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="submit" colspan="2"><input type="submit" name="doPost" class="submit" value="<?php print $CNCAT["lang"]["do_save"]?>" /></td></tr>
    <tr><td class="deline" colspan="2"></td>
</table>

<script type="text/javascript">
function showTips(lnk) {
    var tips = document.getElementById('tips');

    if (tips.style.display != 'block') {
        lnk.innerHTML = '&#9650;';
        tips.style.display = 'block';
    } else {
        lnk.innerHTML = '&#9660;';
        tips.style.display = 'none';
    }
}
</script>
<div class="ok_box">
    <div style="float: right;"><a href="#" onclick="showTips(this); return false;" style="text-decoration: none; padding: 1px 5px;">&#9660;</a></div>
    <div><strong><?php print $CNCAT["lang"]["fields_list"]?>:</strong></div>
    <div id="tips" style="display: none;"><?php print $CNCAT["lang"]["mail_notify_tips"]?></div>
</div>

<table class="form">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["admin_notify"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_from"]?></td><td class="field"><input type="text" class="text" name="mail[admin][from]" value="<?php print htmlspecialchars($_POST["mail"]["admin"]["from"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_subject"]?></td><td class="field">
        <input type="text" class="text" name="mail[admin][subject]" value="<?php print htmlspecialchars($_POST["mail"]["admin"]["subject"])?>" />
    </td></tr>
    <tr><td class="name" colspan="2"><?php print $CNCAT["lang"]["mail_body"]?></td></tr>
    <tr><td class="name" colspan="2">
        <textarea name="mail[admin][body]" style="height: 100px;"><?php print htmlspecialchars($_POST["mail"]["admin"]["body"])?></textarea>
    </td></tr>

    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["add_notify"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_from"]?></td><td class="field"><input type="text" class="text" name="mail[add][from]" value="<?php print htmlspecialchars($_POST["mail"]["add"]["from"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_reply_to"]?></td><td class="field"><input type="text" class="text" name="mail[add][reply_to]" value="<?php print htmlspecialchars($_POST["mail"]["add"]["reply_to"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_subject"]?></td><td class="field">
        <input type="text" class="text" name="mail[add][subject]" value="<?php print htmlspecialchars($_POST["mail"]["add"]["subject"])?>" />
    </td></tr>
    <tr><td class="name" colspan="2"><?php print $CNCAT["lang"]["mail_body"]?></td></tr>
    <tr><td class="name" colspan="2">
        <textarea name="mail[add][body]" style="height: 100px;"><?php print htmlspecialchars($_POST["mail"]["add"]["body"])?></textarea>
    </td></tr>

    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["approve_notify"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_from"]?></td><td class="field"><input type="text" class="text" name="mail[approve][from]" value="<?php print htmlspecialchars($_POST["mail"]["approve"]["from"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_reply_to"]?></td><td class="field"><input type="text" class="text" name="mail[approve][reply_to]" value="<?php print htmlspecialchars($_POST["mail"]["approve"]["reply_to"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_subject"]?></td><td class="field">
        <input type="text" class="text" name="mail[approve][subject]" value="<?php print htmlspecialchars($_POST["mail"]["approve"]["subject"])?>" />
    </td></tr>
    <tr><td class="name" colspan="2"><?php print $CNCAT["lang"]["mail_body"]?></td></tr>
    <tr><td class="name" colspan="2">
        <textarea name="mail[approve][body]" style="height: 100px;"><?php print htmlspecialchars($_POST["mail"]["approve"]["body"])?></textarea>
    </td></tr>

    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["decline_notify"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_from"]?></td><td class="field"><input type="text" class="text" name="mail[decline][from]" value="<?php print htmlspecialchars($_POST["mail"]["decline"]["from"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_reply_to"]?></td><td class="field"><input type="text" class="text" name="mail[decline][reply_to]" value="<?php print htmlspecialchars($_POST["mail"]["decline"]["reply_to"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_subject"]?></td><td class="field">
        <input type="text" class="text" name="mail[decline][subject]" value="<?php print htmlspecialchars($_POST["mail"]["decline"]["subject"])?>" />
    </td></tr>
    <tr><td class="name" colspan="2"><?php print $CNCAT["lang"]["mail_body"]?></td></tr>
    <tr><td class="name" colspan="2">
        <textarea name="mail[decline][body]" style="height: 100px;"><?php print htmlspecialchars($_POST["mail"]["decline"]["body"])?></textarea>
    </td></tr>
    
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["isolation_notify"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_from"]?></td><td class="field"><input type="text" class="text" name="mail[isolation][from]" value="<?php print htmlspecialchars($_POST["mail"]["isolation"]["from"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_reply_to"]?></td><td class="field"><input type="text" class="text" name="mail[isolation][reply_to]" value="<?php print htmlspecialchars($_POST["mail"]["isolation"]["reply_to"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["mail_subject"]?></td><td class="field">
        <input type="text" class="text" name="mail[isolation][subject]" value="<?php print htmlspecialchars($_POST["mail"]["isolation"]["subject"])?>" />
    </td></tr>
    <tr><td class="name" colspan="2"><?php print $CNCAT["lang"]["mail_body"]?></td></tr>
    <tr><td class="name" colspan="2">
        <textarea name="mail[isolation][body]" style="height: 100px;"><?php print htmlspecialchars($_POST["mail"]["isolation"]["body"])?></textarea>
    </td></tr>
    
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="submit" colspan="2"><input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_save"]?>" /></td></tr>
</table>
</form>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
