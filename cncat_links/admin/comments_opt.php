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

    $config["comments_links_enable"] = !empty($_POST["comments_links_enable"]) ? 1 : 0;
    $config["comments_articles_enable"] = !empty($_POST["comments_articles_enable"]) ? 1 : 0;
    $config["comments_add_enable"] = !empty($_POST["comments_add_enable"]) ? 1 : 0;
    $config["comments_auto_approve"] = !empty($_POST["comments_auto_approve"]) ? 1 : 0;
    
    $config["comments_use_captcha"] = !empty($_POST["comments_use_captcha"]) ? $_POST["comments_use_captcha"] : "";
    $config["recaptcha_public_key"] = $_POST["recaptcha_public_key"];
    $config["recaptcha_private_key"] = $_POST["recaptcha_private_key"];
    $config["keycaptcha_private_key"] = $_POST["keycaptcha_private_key"];
    
    $config["comments_min_author_name"] = abs((int)$_POST["comments_min_author_name"]);
    $config["comments_max_author_name"] = abs((int)$_POST["comments_max_author_name"]);
    $config["comments_min_author_email"] = abs((int)$_POST["comments_min_author_email"]);
    $config["comments_max_author_email"] = abs((int)$_POST["comments_max_author_email"]);
    $config["comments_min_text"] = abs((int)$_POST["comments_min_text"]);
    $config["comments_max_text"] = abs((int)$_POST["comments_max_text"]);
    $config["rating_enable"] = !empty($_POST["rating_enable"]) ? 1 : 0;
    $config["rating_without_com"] = !empty($_POST["rating_without_com"]) ? 1 : 0;

    if ($config["check_method"] > 1) {
        $config["check_method"] = 1;
    }

    foreach ($config as $name => $value) {
        $query = "REPLACE `" . $CNCAT["config"]["db"]["prefix"] . "config` SET `name`='" . $name . "', `value`='" . mysql_escape_string($value) . "'";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=comments_opt");
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
<h1><?php print $CNCAT["lang"]["settings"]?> / <?php print $CNCAT["lang"]["comments"]?></h1>
<table class="form">
<form action="index.php?act=comments_opt" method="post">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["comments"]?></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["comments_links_enable"]?></td><td class="field"><input type="checkbox" name="comments_links_enable" <?php print !empty($_POST["comments_links_enable"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["comments_articles_enable"]?></td><td class="field"><input type="checkbox" name="comments_articles_enable" <?php print !empty($_POST["comments_articles_enable"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["comments_post_enable"]?></td><td class="field"><input type="checkbox" name="comments_add_enable" <?php print !empty($_POST["comments_add_enable"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["comments_auto_approve"]?></td><td class="field"><input type="checkbox" name="comments_auto_approve" <?php print !empty($_POST["comments_auto_approve"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["use_captcha"]?></td>
        <td class="field">
            <select name="comments_use_captcha">
                <option value="" <?php print !$_POST["comments_use_captcha"] ? "selected=\"selected\"" : ""?>><?php print "---"?> </option>
                <option value="default" <?php print $_POST["comments_use_captcha"]=="default" ? "selected=\"selected\"" : ""?>><?php print "Common Captcha"?> </option>
                <option value="recaptcha" <?php print $_POST["comments_use_captcha"]=="recaptcha" ? "selected=\"selected\"" : ""?>><?php print "reCaptcha"?> </option>
                <option value="keycaptcha" <?php print $_POST["comments_use_captcha"]=="keycaptcha" ? "selected=\"selected\"" : ""?>><?php print "keyCaptcha"?> </option>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["recaptcha_public_key"]?></td><td class="field"><input type="text" class="text" name="recaptcha_public_key" value="<?php print htmlspecialchars($_POST["recaptcha_public_key"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["recaptcha_private_key"]?></td><td class="field"><input type="text" class="text" name="recaptcha_private_key" value="<?php print htmlspecialchars($_POST["recaptcha_private_key"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["keycaptcha_private_key"]?></td><td class="field"><input type="text" class="text" name="keycaptcha_private_key" value="<?php print htmlspecialchars($_POST["keycaptcha_private_key"])?>" /></td></tr>
    
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["author"], $CNCAT["lang"]["field_len"])?></td><td class="field">
        <?php print $CNCAT["lang"]["from"]?>: <input type="text" class="text" style="width: 40%;" name="comments_min_author_name" value="<?php print intval($_POST["comments_min_author_name"])?>" />
        <?php print $CNCAT["lang"]["to"]?>: <input type="text" class="text" style="width: 40%;" name="comments_max_author_name" value="<?php print intval($_POST["comments_max_author_name"])?>" />
    </td></tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["email"], $CNCAT["lang"]["field_len"])?></td><td class="field">
        <?php print $CNCAT["lang"]["from"]?>: <input type="text" class="text" style="width: 40%;" name="comments_min_author_email" value="<?php print intval($_POST["comments_min_author_email"])?>" />
        <?php print $CNCAT["lang"]["to"]?>: <input type="text" class="text" style="width: 40%;" name="comments_max_author_email" value="<?php print intval($_POST["comments_max_author_email"])?>" />
    </td></tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["comment"], $CNCAT["lang"]["field_len"])?></td><td class="field">
        <?php print $CNCAT["lang"]["from"]?>: <input type="text" class="text" style="width: 40%;" name="comments_min_text" value="<?php print intval($_POST["comments_min_text"])?>" />
        <?php print $CNCAT["lang"]["to"]?>: <input type="text" class="text" style="width: 40%;" name="comments_max_text" value="<?php print intval($_POST["comments_max_text"])?>" />
    </td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["rating_enable"]?></td><td class="field"><input type="checkbox" name="rating_enable" <?php print !empty($_POST["rating_enable"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["rating_vote_without_comment"]?></td><td class="field"><input type="checkbox" name="rating_without_com" <?php print !empty($_POST["rating_without_com"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="submit" colspan="2"><input type="submit" name="doPost" class="submit" value="<?php print $CNCAT["lang"]["do_save"]?>" /></td></tr>
</form>
</table>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
