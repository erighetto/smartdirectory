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
    $config["add_article_enable"] = !empty($_POST["add_article_enable"]) ? 1 : 0;

    $config["add_article_use_captcha"] = !empty($_POST["add_article_use_captcha"]) ? $_POST["add_article_use_captcha"] : "";
    $config["recaptcha_public_key"] = $_POST["recaptcha_public_key"];
    $config["recaptcha_private_key"] = $_POST["recaptcha_private_key"];
    $config["keycaptcha_private_key"] = $_POST["keycaptcha_private_key"];
    
    $config["add_article_use_link_url"] = !empty($_POST["add_article_use_link_url"]) ? 1 : 0;
    $config["add_article_use_back_link"] = !empty($_POST["add_article_use_back_link"]) ? 1 : 0;
    $config["add_article_use_author"] = !empty($_POST["add_article_use_author"]) ? 1 : 0;
    $config["add_article_use_descr_full"] = !empty($_POST["add_article_use_descr_full"]) ? 1 : 0;
    $config["add_article_use_meta_keywords"] = !empty($_POST["add_article_use_meta_keywords"]) ? 1 : 0;
    $config["add_article_use_meta_descr"] = !empty($_POST["add_article_use_meta_descr"]) ? 1 : 0;
    
    $config["add_article_short_wysiwyg"] = !empty($_POST["add_article_short_wysiwyg"]) ? 1 : 0;
    $config["add_article_show_dis_cat"] = !empty($_POST["add_article_show_dis_cat"]) ? 1 : 0;

    $config["add_article_message"] = cn_substr($_POST["add_article_message"], 0, 65535);
    $config["add_article_show_message"] = !empty($_POST["add_article_show_message"]) ? 1 : 0;
    $config["add_article_html_message"] = !empty($_POST["add_article_html_message"]) ? 1 : 0;

    $config["add_article_max_url"] = abs((int)$_POST["add_article_max_url"]);
    $config["add_article_max_backurl"] = abs((int)$_POST["add_article_max_backurl"]);
    $config["add_article_min_title"] = abs((int)$_POST["add_article_min_title"]);
    $config["add_article_max_title"] = abs((int)$_POST["add_article_max_title"]);
    $config["add_article_min_email"] = abs((int)$_POST["add_article_min_email"]);
    $config["add_article_max_email"] = abs((int)$_POST["add_article_max_email"]);
    $config["add_article_min_author"] = abs((int)$_POST["add_article_min_author"]);
    $config["add_article_max_author"] = abs((int)$_POST["add_article_max_author"]);
    $config["add_article_min_descr"] = abs((int)$_POST["add_article_min_descr"]);
    $config["add_article_max_descr"] = abs((int)$_POST["add_article_max_descr"]);
    $config["add_article_min_descr_full"] = abs((int)$_POST["add_article_min_descr_full"]);
    $config["add_article_max_descr_full"] = abs((int)$_POST["add_article_max_descr_full"]);
    $config["add_article_min_meta_keywords"] = abs((int)$_POST["add_article_min_meta_keywords"]);
    $config["add_article_max_meta_keywords"] = abs((int)$_POST["add_article_max_meta_keywords"]);
    $config["add_article_min_meta_descr"] = abs((int)$_POST["add_article_min_meta_descr"]);
    $config["add_article_max_meta_descr"] = abs((int)$_POST["add_article_max_meta_descr"]);
    $config["add_article_max_cats"] = abs((int)$_POST["add_article_max_cats"]);

    if ($config["add_article_max_url"] > 255) {
        $config["add_article_max_url"] = 255;
    }

    if ($config["add_article_max_backurl"] > 255) {
        $config["add_article_max_backurl"] = 255;
    }

    if ($config["add_article_max_title"] > 255) {
        $config["add_article_max_title"] = 255;
    }

    if ($config["add_article_max_email"] > 255) {
        $config["add_article_max_email"] = 255;
    }

    if ($config["add_article_max_author"] > 255) {
        $config["add_article_max_author"] = 255;
    }

    if ($config["add_article_max_descr"] > 65535) {
        $config["add_article_max_descr"] = 65535;
    }

    if ($config["add_article_max_descr_full"] > 65535) {
        $config["add_article_max_descr_full"] = 65535;
    }

    if ($config["add_article_max_meta_keywords"] > 65535) {
        $config["add_article_max_meta_keywords"] = 65535;
    }

    if ($config["add_article_max_meta_descr"] > 65535) {
        $config["add_article_max_meta_descr"] = 65535;
    }

    $config["add_article_check_exists_url"] = !empty($_POST["add_article_check_exists_url"]) ? 1 : 0;
    $config["add_article_ignore_www"] = !empty($_POST["add_article_ignore_www"]) ? 1 : 0;

    $config["add_article_back_link_host"] = !empty($_POST["add_article_back_link_host"]) ? 1 : 0;
    $config["add_article_check_link"] = !empty($_POST["add_article_check_link"]) ? 1 : 0;
    $config["add_article_auto_approve"] = abs((int)$_POST["add_article_auto_approve"]);
    $config["add_article_accept_to_add"] = abs((int)$_POST["add_article_accept_to_add"]);

    if ($config["add_article_accept_to_add"] > 2) {
        $config["add_article_accept_to_add"] = 2;
    }

    if ($config["add_article_auto_approve"] > 3) {
        $config["add_article_auto_approve"] = 3;
    }

    foreach ($config as $name => $value) {
        $query = "REPLACE `" . $CNCAT["config"]["db"]["prefix"] . "config` SET `name`='" . $name . "', `value`='" . mysql_escape_string($value) . "'";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=articles_opt");
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
<h1><?php print $CNCAT["lang"]["settings"]?> / <?php print $CNCAT["lang"]["articles"]?></h1>
<table class="form">
<form action="index.php?act=articles_opt" method="post">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["article_add"]?></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["enable_articles_add"]?></td><td class="field"><input type="checkbox" name="add_article_enable" <?php print !empty($_POST["add_article_enable"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["use_captcha"]?></td>
        <td class="field">
            <select name="add_article_use_captcha">
                <option value="" <?php print !$_POST["add_article_use_captcha"] ? "selected=\"selected\"" : ""?>><?php print "---"?> </option>
                <option value="default" <?php print $_POST["add_article_use_captcha"]=="default" ? "selected=\"selected\"" : ""?>><?php print "Common Captcha"?> </option>
                <option value="recaptcha" <?php print $_POST["add_article_use_captcha"]=="recaptcha" ? "selected=\"selected\"" : ""?>><?php print "reCaptcha"?> </option>
                <option value="keycaptcha" <?php print $_POST["add_article_use_captcha"]=="keycaptcha" ? "selected=\"selected\"" : ""?>><?php print "keyCaptcha"?> </option>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["recaptcha_public_key"]?></td><td class="field"><input type="text" class="text" name="recaptcha_public_key" value="<?php print htmlspecialchars($_POST["recaptcha_public_key"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["recaptcha_private_key"]?></td><td class="field"><input type="text" class="text" name="recaptcha_private_key" value="<?php print htmlspecialchars($_POST["recaptcha_private_key"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["keycaptcha_private_key"]?></td><td class="field"><input type="text" class="text" name="keycaptcha_private_key" value="<?php print htmlspecialchars($_POST["keycaptcha_private_key"])?>" /></td></tr>
    
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["message_above_form"]?></td>
        <td class="field">
            <label><input type="checkbox" name="add_article_show_message" <?php print !empty($_POST["add_article_show_message"]) ? "checked=\"checked\"" : ""?> /> <?php print $CNCAT["lang"]["display"]?></label>
            <label><input type="checkbox" name="add_article_html_message" <?php print !empty($_POST["add_article_html_message"]) ? "checked=\"checked\"" : ""?> /> <?php print $CNCAT["lang"]["html_code"]?></label>
        </td>
    </tr>
    <tr><td class="name"></td>
        <td class="field">
            <textarea name="add_article_message" style="height: 50px;"><?php print htmlspecialchars($_POST["add_article_message"])?></textarea>
        </td>
    </tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["link_url_article"], $CNCAT["lang"]["use_field_name"])?></td><td class="field"><input type="checkbox" name="add_article_use_link_url" <?php print !empty($_POST["add_article_use_link_url"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["back_link_url"], $CNCAT["lang"]["use_field_name"])?></td><td class="field"><input type="checkbox" name="add_article_use_back_link" <?php print !empty($_POST["add_article_use_back_link"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["author"], $CNCAT["lang"]["use_field_name"])?></td><td class="field"><input type="checkbox" name="add_article_use_author" <?php print !empty($_POST["add_article_use_author"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["meta_keywords"], $CNCAT["lang"]["use_field_name"])?></td><td class="field"><input type="checkbox" name="add_article_use_meta_keywords" <?php print !empty($_POST["add_article_use_meta_keywords"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["meta_descr"], $CNCAT["lang"]["use_field_name"])?></td><td class="field"><input type="checkbox" name="add_article_use_meta_descr" <?php print !empty($_POST["add_article_use_meta_descr"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["use_wysiwyg_abstract"]?></td><td class="field"><input type="checkbox" name="add_article_short_wysiwyg" <?php print !empty($_POST["add_article_short_wysiwyg"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["show_disable_cats"]?></td><td class="field"><input type="checkbox" name="add_article_show_dis_cat" <?php print !empty($_POST["add_article_show_dis_cat"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["link_url_article"], $CNCAT["lang"]["field_max_len"])?></td><td class="field"><input type="text" class="text" name="add_article_max_url" value="<?php print htmlspecialchars($_POST["add_article_max_url"])?>" /></td></tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["back_link_url"], $CNCAT["lang"]["field_max_len"])?></td><td class="field"><input type="text" class="text" name="add_article_max_backurl" value="<?php print htmlspecialchars($_POST["add_article_max_backurl"])?>" /></td></tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["site_title_article"], $CNCAT["lang"]["field_len"])?></td>
        <td class="field">
            <?php print $CNCAT["lang"]["from"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_min_title" value="<?php print (int)$_POST["add_article_min_title"]?>" />
            <?php print $CNCAT["lang"]["to"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_max_title" value="<?php print (int)$_POST["add_article_max_title"]?>" />
        </td>
    </tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["email"], $CNCAT["lang"]["field_len"])?></td>
        <td class="field">
            <?php print $CNCAT["lang"]["from"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_min_email" value="<?php print (int)$_POST["add_article_min_email"]?>" />
            <?php print $CNCAT["lang"]["to"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_max_email" value="<?php print (int)$_POST["add_article_max_email"]?>" />
        </td>
    </tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["author"], $CNCAT["lang"]["field_len"])?></td>
        <td class="field">
            <?php print $CNCAT["lang"]["from"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_min_author" value="<?php print (int)$_POST["add_article_min_author"]?>" />
            <?php print $CNCAT["lang"]["to"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_max_author" value="<?php print (int)$_POST["add_article_max_author"]?>" />
        </td>
    </tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["descr_article"], $CNCAT["lang"]["field_len"])?></td>
        <td class="field">
            <?php print $CNCAT["lang"]["from"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_min_descr" value="<?php print (int)$_POST["add_article_min_descr"]?>" />
            <?php print $CNCAT["lang"]["to"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_max_descr" value="<?php print (int)$_POST["add_article_max_descr"]?>" />
        </td>
    </tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["descr_full_article"], $CNCAT["lang"]["field_len"])?></td>
        <td class="field">
            <?php print $CNCAT["lang"]["from"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_min_descr_full" value="<?php print (int)$_POST["add_article_min_descr_full"]?>" />
            <?php print $CNCAT["lang"]["to"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_max_descr_full" value="<?php print (int)$_POST["add_article_max_descr_full"]?>" />
        </td>
    </tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["meta_keywords"], $CNCAT["lang"]["field_len"])?></td>
        <td class="field">
            <?php print $CNCAT["lang"]["from"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_min_meta_keywords" value="<?php print (int)$_POST["add_article_min_meta_keywords"]?>" />
            <?php print $CNCAT["lang"]["to"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_max_meta_keywords" value="<?php print (int)$_POST["add_article_max_meta_keywords"]?>" />
        </td>
    </tr>
    <tr><td class="name"><?php print cn_str_replace("%FIELD%", $CNCAT["lang"]["meta_descr"], $CNCAT["lang"]["field_len"])?></td>
        <td class="field">
            <?php print $CNCAT["lang"]["from"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_min_meta_descr" value="<?php print (int)$_POST["add_article_min_meta_descr"]?>" />
            <?php print $CNCAT["lang"]["to"]?>: <input type="text" class="text" style="width: 40%;" name="add_article_max_meta_descr" value="<?php print (int)$_POST["add_article_max_meta_descr"]?>" />
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["max_select_cats"]?></td><td class="field"><input type="text" class="text" name="add_article_max_cats" value="<?php print (int)$_POST["add_article_max_cats"]?>" /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["source_check"]?></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["check_exists_url"]?></td><td class="field"><input type="checkbox" name="add_article_check_exists_url" <?php print !empty($_POST["add_article_check_exists_url"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["ignore_www"]?></td><td class="field"><input type="checkbox" name="add_article_ignore_www" <?php print !empty($_POST["add_article_ignore_www"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["check_back_link_host"]?></td><td class="field"><input type="checkbox" name="add_article_back_link_host" <?php print !empty($_POST["add_article_back_link_host"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["check_link_work"]?></td><td class="field"><input type="checkbox" name="add_article_check_link" <?php print !empty($_POST["add_article_check_link"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["accept_to_add"]?></td>
        <td class="field">
            <select name="add_article_accept_to_add">
                <option value="0" <?php print $_POST["add_article_accept_to_add"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["any"]?></option>
                <option value="1" <?php print $_POST["add_article_accept_to_add"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["work"]?></option>
                <option value="2" <?php print $_POST["add_article_accept_to_add"] == 2 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["work_with_back"]?></option>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["auto_approve"]?></td>
        <td class="field">
            <select name="add_article_auto_approve"<?php print $CN_TYPE == "free" ? " disabled=\"disabled\" " : ""?>>
                <option value="0" <?php print $_POST["add_article_auto_approve"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["never"]?></option>
                <option value="1" <?php print $_POST["add_article_auto_approve"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["always"]?></option>
                <option value="2" <?php print $_POST["add_article_auto_approve"] == 2 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["work"]?></option>
                <option value="3" <?php print $_POST["add_article_auto_approve"] == 3 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["work_with_back"]?></option>
            </select>
        </td>
    </tr>
    <tr><td class="deline" colspan="2"></td>

    <tr><td class="submit" colspan="2"><input type="submit" name="doPost" class="submit" value="<?php print $CNCAT["lang"]["do_save"]?>" /></td></tr>
</table>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
