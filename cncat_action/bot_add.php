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

//Checking secret code
if (
    !$CNCAT["config"]["add_secret_access"] &&
    (!isset($_GET[$CNCAT["config"]["add_secret_param"]]) && !isset($_POST[$CNCAT["config"]["add_secret_param"]]))
) {
    $CNCAT_ENGINE->misc->error404();
    exit;
}

if (
    $_GET[$CNCAT["config"]["add_secret_param"]] != $CNCAT["config"]["add_secret_value"] &&
    $_POST[$CNCAT["config"]["add_secret_param"]] != $CNCAT["config"]["add_secret_value"]
) {
    $CNCAT_ENGINE->misc->error404();
    exit;
}

require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "check.php";
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "sync_all.php";

$CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], "add");

$_errors = array();

// check max fields length
$CNCAT["config"]["add_max_url"] = $CNCAT["config"]["add_max_url"] > 0 ? $CNCAT["config"]["add_max_url"] : 255;
$CNCAT["config"]["add_max_backurl"] = $CNCAT["config"]["add_max_backurl"] > 0 ? $CNCAT["config"]["add_max_backurl"]  : 255;
$CNCAT["config"]["add_max_title"] = $CNCAT["config"]["add_max_title"] > 0 ? $CNCAT["config"]["add_max_title"] : 255;
$CNCAT["config"]["add_max_email"] = $CNCAT["config"]["add_max_email"] > 0 ? $CNCAT["config"]["add_max_email"] : 255;
$CNCAT["config"]["add_max_descr"] = $CNCAT["config"]["add_max_descr"] > 0 ? $CNCAT["config"]["add_max_descr"] : 2048;
$CNCAT["config"]["add_max_descr_full"] = $CNCAT["config"]["add_max_descr_full"] > 0 ? $CNCAT["config"]["add_max_descr_full"] : 4096;
$CNCAT["config"]["add_max_meta_keywords"] = $CNCAT["config"]["add_max_meta_keywords"] > 0 ? $CNCAT["config"]["add_max_meta_keywords"] : 2048;
$CNCAT["config"]["add_max_meta_descr"] = $CNCAT["config"]["add_max_meta_descr"] > 0 ? $CNCAT["config"]["add_max_meta_descr"] : 2048;
$CNCAT["page"]["search_form_url"] = $CNCAT_ENGINE->url->createCnUrl("search");
$CNCAT["page"]["add_url"] = $CNCAT_ENGINE->url->createCnUrl("add");
$CNCAT["page"]["add_article_url"] = $CNCAT_ENGINE->url->createCnUrl("add_article");
$CNCAT["page"]["map_url"] = $CNCAT_ENGINE->url->createCnUrl("map");
        
// check back_link
$back_link_token = isset($_SESSION["back_link_token"]) ? $_SESSION["back_link_token"] : '';

if (!$back_link_token) {
    $back_link_token = $CNCAT_ENGINE->misc->createBackUrlRef();
    $_SESSION["back_link_token"] = $back_link_token;
}

if (isset($_POST['doPost'])) {
    $_item = array();
    
    $_item['link_url'] = $_POST['link_url'];
    $_item['link_back_link_url'] = $_POST['back_link'];
    $_item['item_title'] = $_POST['site_title'];
    $_item['item_title_translite'] = cn_translitEncode($_item['item_title']);
    $_item['item_author_email'] = $_POST['email'];
    $_item['item_descr'] = $_POST['descr'];
    $_item['item_descr_full'] = $_POST['descr_full'];
    $_item['item_meta_keywords'] = $_POST['meta_keywords'];
    $_item['item_meta_descr'] = $_POST['meta_descr'];
    
    $_errors = array();
    
    // link_url
    if (!empty($_item['link_url']) && !preg_match("#^(ht|f)tps?://#i", $_item['link_url'])) {
        $_POST["link_url"] = $_item['link_url'] = "http://" . $_item['link_url'];
    }

    if (empty($_item['link_url']) || (cn_strlen($_item['link_url']) < 11)) {
        $_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["link_url"], $CNCAT["lang"]["field_empty"]);
    } elseif (strlen($_item['link_url']) > $CNCAT["config"]["add_max_url"]) {
        $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["link_url"], $CNCAT["config"]["add_max_url"]), $CNCAT["lang"]["field_to_long"]);
    } elseif ($CNCAT["config"]["add_check_exists_url"] && checkExistingUrl($_item['link_url'])) {
        $_errors[] = cn_str_replace("%URL%", cn_str($_item['link_url']), $CNCAT["lang"]["url_already_exists"]);
    } else {
        $black_list = array();
        $white_list = array();

        $query = "SELECT `type`, `check_method`, `check_str` FROM `" . $CNCAT["config"]["db"]["prefix"] . "bwlist`";
        $res = $CNCAT_ENGINE->db->query($query, "Black/white list") or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        while ($row = mysql_fetch_assoc($res)) {
            if ($row["type"] == "black") {
                $black_list[] = array($row["check_str"], $row["check_method"]);
            } else {
                $white_list[] = $row["check_str"];
            }
        }

        foreach ($white_list as $link) {
            if (cn_strpos($_item['link_url'], $link) !== false) {
                $black_list = array();
            }
        }

        foreach ($black_list as $link) {
            if ($link[1] == "substr") {
                if (cn_strpos($_item['link_url'], $link[0]) !== false) {
                    $_errors[] = $CNCAT["lang"]["url_in_blacklist"];
                    break;
                }
            } else {
                if (@preg_match("/" . preg_quote($link[0], "/") . "/i" . $CN_STRINGS["preg"], $_item['link_url'])) {
                    $_errors[] = $CNCAT["lang"]["url_in_blacklist"];
                    break;
                }
            }
        }
    }

    // site title
    if (empty($_item['item_title'])) {
        $_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["site_title"], $CNCAT["lang"]["field_empty"]);
    } elseif (cn_strlen($_item['item_title']) > $CNCAT["config"]["add_max_title"]) {
        $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["site_title"], $CNCAT["config"]["add_max_title"]), $CNCAT["lang"]["field_to_long"]);
    }

    // backlink
    if ($CNCAT["config"]["add_use_back_link"]) {
        if (!empty($_item['link_back_link_url']) && !preg_match("#^http[s]?://#i", $_item['link_back_link_url'])) {
            $_POST["back_link"] = $_item['link_back_link_url'] = "http://" . $_item['link_back_link_url'];
        }

        if ($CNCAT["config"]["add_accept_to_add"] == 2) {
            if (empty($_item["link_back_link_url"]) || (cn_strlen($_item["link_back_link_url"]) < 11)) {
                $_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["back_link_url"], $CNCAT["lang"]["field_empty"]);
            }
        }

        if (!empty($_item["link_back_link_url"])) {
            if (!$_errors && $CNCAT["config"]["add_back_link_host"]) {
                $u1 = @parse_url($_item["link_url"]);
                $u2 = @parse_url($_item["link_back_link_url"]);

                if ($u1["host"] !== $u2["host"]) {
                    $_errors[] = $CNCAT["lang"]["must_equal_host"];
                }
            }
        }
    }

    // email
    if ($CNCAT["config"]["add_min_email"] == 1 && empty($_item['item_author_email'])) {
        $_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["email"], $CNCAT["lang"]["field_empty"]);
    } elseif (cn_strlen($_item['item_author_email']) < $CNCAT["config"]["add_min_email"]) {
        $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["email"], $CNCAT["config"]["add_min_email"]), $CNCAT["lang"]["field_to_short"]);
    } elseif (cn_strlen($_item['item_author_email']) > $CNCAT["config"]["add_max_email"]) {
        $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["email"], $CNCAT["config"]["add_max_email"]), $CNCAT["lang"]["field_to_long"]);
    //} elseif (!@preg_match("/^" . $CNCAT["config"]["add_mail_regexp"] . "$/i" . $CN_STRINGS["preg"], $email)) {
    } elseif ($CNCAT["config"]["add_min_email"] > 0 && !@preg_match("/^[^@\s]+@[^@\s]+\.[a-z0-9]+$/i" . $CN_STRINGS["preg"], $_item['item_author_email'])) {
        $_errors[] = $CNCAT["lang"]["invalid_email_format"];
    }

    // descr
    if ($CNCAT["config"]["add_min_descr"] == 1 && empty($_item['item_descr'])) {
        $_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["descr"], $CNCAT["lang"]["field_empty"]);
    } elseif (cn_strlen($_item['item_descr']) < $CNCAT["config"]["add_min_descr"]) {
        $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["descr"], $CNCAT["config"]["add_min_descr"]), $CNCAT["lang"]["field_to_short"]);
    } elseif (cn_strlen($_item['item_descr']) > $CNCAT["config"]["add_max_descr"]) {
        $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["descr"], $CNCAT["config"]["add_max_descr"]), $CNCAT["lang"]["field_to_long"]);
    }

    // descr full
    if ($CNCAT["config"]["add_use_descr_full"]) {
        if ($CNCAT["config"]["add_min_descr_full"] == 1 && empty($_item['item_descr_full'])) {
            $_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["descr_full"], $CNCAT["lang"]["field_empty"]);
        } elseif (cn_strlen($_item['item_descr_full']) < $CNCAT["config"]["add_min_descr_full"]) {
            $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["descr_full"], $CNCAT["config"]["add_min_descr_full"]), $CNCAT["lang"]["field_to_short"]);
        } elseif (cn_strlen($_item['item_descr_full']) > $CNCAT["config"]["add_max_descr_full"]) {
            $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["descr_full"], $CNCAT["config"]["add_max_descr_full"]), $CNCAT["lang"]["field_to_long"]);
        }
    }

    // meta keywords
    if ($CNCAT["config"]["add_use_meta_keywords"]) {
        if ($CNCAT["config"]["add_min_meta_keywords"] == 1 && empty($_item['item_meta_keywords'])) {
            $_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["meta_keywords"], $CNCAT["lang"]["field_empty"]);
        } elseif (cn_strlen($_item['item_meta_keywords']) < $CNCAT["config"]["add_min_meta_keywords"]) {
            $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["meta_keywords"], $CNCAT["config"]["add_min_meta_keywords"]), $CNCAT["lang"]["field_to_short"]);
        } elseif (cn_strlen($_item['item_meta_keywords']) > $CNCAT["config"]["add_max_meta_keywords"]) {
            $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["meta_keywords"], $CNCAT["config"]["add_max_meta_keywords"]), $CNCAT["lang"]["field_to_long"]);
        }
    }

    // meta descr
    if ($CNCAT["config"]["add_use_meta_descr"]) {
        if ($CNCAT["config"]["add_min_meta_descr"] == 1 && empty($_item['item_meta_descr'])) {
            $_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["meta_descr"], $CNCAT["lang"]["field_empty"]);
        } elseif (cn_strlen($_item['item_meta_descr']) < $CNCAT["config"]["add_min_meta_descr"]) {
            $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["meta_descr"], $CNCAT["config"]["add_min_meta_descr"]), $CNCAT["lang"]["field_to_short"]);
        } elseif (cn_strlen($_item['item_meta_descr']) > $CNCAT["config"]["add_max_meta_descr"]) {
            $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["meta_descr"], $CNCAT["config"]["add_max_meta_descr"]), $CNCAT["lang"]["field_to_long"]);
        }
    }

    // category
    $_cat = (int)$_POST["cat"];

    // check category existsing
    $query = "SELECT `id`, `disable_add` FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats` WHERE `id`=" . $_cat;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $cat = mysql_fetch_assoc($res);

    if (!$cat["id"]) {
        $_errors[] = $CNCAT["lang"]["must_select_cats"];
    } else {
        $query = "
            SELECT `id`, `child_id_list`, `disable_add`, `disable_child_add`
            FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats`
            WHERE `parent_id` != -1
        ";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $disabled_cats = array();

        while ($row = mysql_fetch_assoc($res)) {
            if (!empty($row["child_id_list"])) {
                if ($row["disable_child_add"]) {
                    $disabled_cats = array_merge($disabled_cats, explode(",", $row["child_id_list"]));
                }
            }
        }

        if ($cat["disable_add"] || in_array($cat["id"], $disabled_cats)) {
            $_errors[] = $CNCAT["lang"]["must_select_cats"];
        }
    }

    if (empty($_errors)) {
        // check work
        if ($CNCAT["config"]["add_check_link"] || $CNCAT["config"]["add_accept_to_add"] > 0) {
            $check_result = cn_checkLink($_item["link_url"], true, false, (bool)$CNCAT["config"]["check_pr"], (bool)$CNCAT["config"]["check_cy"], $CNCAT["config"]["check_timeout"], $CNCAT["config"]["check_max_redirect"]);
            $_item["link_chk_work_res"] = $check_result["work"] ? 1 : 0;
            $_item["link_chk_work_date"] = date("Y-m-d H:i:s");
            $_item["last_check"] = date("Y-m-d H:i:s");

            if ($CNCAT["config"]["check_pr"]) {
                $_item["link_rating_pr"] = $check_result["pr"];
                $_item["link_pr_date"] = date("Y-m-d H:i:s");
            }

            if ($CNCAT["config"]["check_cy"]) {
                $_item["link_rating_cy"] = $check_result["cy"];
                $_item["link_cy_date"] = date("Y-m-d H:i:s");
            }

            if (!$check_result["work"] && $CNCAT["config"]["add_accept_to_add"] > 0) {
                $_errors[] = $CNCAT["lang"]["check_link_error"];
            }

            if (isset($check_result["cy"]) && $CNCAT["config"]["link_add_min_cy"] > 0) {
                if ($check_result["cy"] < $CNCAT["config"]["link_add_min_cy"]) {
                    $_errors[] = cn_str_replace(array("%MIN_CY%", "%CY%"), array($CNCAT["config"]["link_add_min_cy"], $check_result["cy"]), $CNCAT["lang"]["add_link_accept_cy"]);
                }
            }

            if (isset($check_result["pr"]) && $CNCAT["config"]["link_add_min_pr"] > 0) {
                if ($check_result["pr"] < $CNCAT["config"]["link_add_min_pr"]) {
                    $_errors[] = cn_str_replace(array("%MIN_PR%", "%PR%"), array($CNCAT["config"]["link_add_min_pr"], $check_result["pr"]), $CNCAT["lang"]["add_link_accept_pr"]);
                }
            }
        }

        // check backlink
        if ($CNCAT["config"]["add_accept_to_add"] == 2) {
            if (!$CNCAT["config"]["add_use_back_link"] || empty($_item["link_back_link_url"])) {
                $_item["link_back_link_url"] = $_item["link_url"];
            }

            $check_result = cn_checkLink($_item["link_back_link_url"], true, true, false, false, $CNCAT["config"]["check_timeout"], $CNCAT["config"]["check_max_redirect"]);

            $_item["link_chk_back_res"] = $check_result["back"] ? 1 : 0;
            $_item["link_chk_back_date"] = date("Y-m-d H:i:s");

            if (!$check_result["back"]) {
                $_errors[] = $CNCAT["lang"]["check_back_link_error"];
            }
        }
    }

    if (empty($_errors)) {
        $_item["item_status"] = $CNCAT["config"]["add_secret_approve"] == 1 ? 1 : 0;
        $_item["item_insert_date"] = date('Y-m-d H:i:s');
        $_item["item_submit_date"] = date('Y-m-d H:i:s');

        $_item["item_submit_type"] = $_item["item_status"] == 1 ? 2 : 0;
        $_item["item_insert_type"] = 3;

        // BLOB fields
        $_item["item_image"] = "";
        $_item["link_favicon"] = "";

        $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "items`";
        $fields = array();

        foreach ($_item as $k => $v) {
            $fields[] = "`" . $k . "`=" . (is_numeric($v) ? $v : ("'" . mysql_escape_string($v) . "'"));
        }

        $query .= " SET " . implode(",", $fields);
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $item_id = mysql_insert_id();

        unset($_SESSION["back_link_token"]);

        $query = "
            SELECT item_title, item_descr, item_descr_full
            FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
            WHERE item_id = " . $item_id . "
        ";
        $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $row = mysql_fetch_assoc($result);

        if (
            empty($row["item_title"])
            || ($CNCAT["config"]["add_min_descr"] > 0 && empty($row["item_descr"]))
            || ($CNCAT["config"]["add_use_descr_full"] && $CNCAT["config"]["add_min_descr_full"] > 0 && empty($row["item_descr_full"]))
        ) {
            $query = "
                DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                WHERE item_id = " . $item_id . "
            ";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

            $CNCAT_ENGINE->displayError('Incorrect character set.');
        }

        insertCats(array($_cat), $item_id);
        cn_syncAll();
        // send mail notify to admin
        if ($CNCAT["config"]["mail_notify_admin"]) {
            $CNCAT_ENGINE->misc->sendMailAdmin($item_id, array($_cat));
        }

        // send mail notify to user
        if ($CNCAT["config"]["mail_notify_add"]) {
            $CNCAT_ENGINE->misc->sendMailAdd($item_id);
        }

        if ($CNCAT["config"]["mail_notify_approve"] && $item_status == 1) {
            $CNCAT_ENGINE->misc->sendMailApprove($item_id);
        }
        print $CNCAT_ENGINE->tpl->renderTemplate("top");
        print $CNCAT_ENGINE->tpl->renderTemplate("menu");

        print "<table id=\"add_table\"><td><td class=\"text\"><p><strong>" . $CNCAT["lang"]["thanks_for_add"] . "</strong></p><p><a href=\"" . $CNCAT["config"]["cncat_url"] . "\">" . $CNCAT["lang"]["return_to_main"] . "</a></p></tr></td></table>";

        print $CNCAT_ENGINE->tpl->renderTemplate("bottom");
        exit;
    }
}

print $CNCAT_ENGINE->tpl->renderTemplate("top");
print $CNCAT_ENGINE->tpl->renderTemplate("menu");
//init lang phrase
$CNCAT["lang"]["field_link_url"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_max_url"], $CNCAT["lang"]["field_link_url"]);
$CNCAT["lang"]["field_back_link"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_max_backurl"], $CNCAT["lang"]["field_back_link"]);
$CNCAT["lang"]["field_email"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_max_email"], $CNCAT["lang"]["field_email"]);
$CNCAT["lang"]["field_descr"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_max_descr"], $CNCAT["lang"]["field_descr"]);
$CNCAT["lang"]["field_descr_full"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_max_descr_full"], $CNCAT["lang"]["field_descr_full"]);
$CNCAT["lang"]["field_site_title"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_max_title"], $CNCAT["lang"]["field_site_title"]);
$CNCAT["lang"]["field_meta_keywords"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_max_meta_keywords"], $CNCAT["lang"]["field_meta_keywords"]);
$CNCAT["lang"]["field_meta_descr"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_max_meta_descr"], $CNCAT["lang"]["field_meta_descr"]);
?>
<form action="?" method="post">
<input type="hidden" name="<?php print cn_str($CNCAT["config"]["add_secret_param"])?>" value="<?php print cn_str($CNCAT["config"]["add_secret_value"])?>" />
<table id="add_table" align="center">
    <tr><td class="text" colspan="2">
<?php
if (!empty($_errors)) {
    print '<ul>';

    foreach ($_errors as $error) {
        print '<li style="color: red;">' . $error . '</li>';
    }

    print '</ul>';
}
?>
    </td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["field_link_url"]?>:</td>
        <td class="field"><input type="text" class="text" name="link_url" value="<?php print cn_str($_POST["link_url"])?>" /></td>
    </tr>
    <?php if ($CNCAT["config"]["add_use_back_link"]):?><tr><td class="name"><?php print $CNCAT["lang"]["field_back_link"]?>:</td><td class="field"><input type="text" class="text" name="back_link" value="<?php print cn_str($_POST["back_link"])?>" /></td></tr>
    <?php endif;?><tr><td class="name"><?php print $CNCAT["lang"]["field_site_title"]?>:</td><td class="field"><input type="text" class="text" name="site_title" value="<?php print cn_str($_POST["site_title"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["categories"]?>:</td><td class="field"><?php print renderCategories();?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["field_email"]?>:</td><td class="field"><input type="text" class="text" name="email" value="<?php print cn_str($_POST["email"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["field_descr"]?>:</td><td class="field"><textarea name="descr"><?php print cn_str($_POST["descr"])?></textarea></td></tr>
    <?php if ($CNCAT["config"]["add_use_descr_full"]):?><tr><td class="name"><?php print $CNCAT["lang"]["field_descr_full"]?>:</td><td class="field"><textarea name="descr_full"><?php print cn_str($_POST["descr_full"])?></textarea></td></tr>
    <?php endif;if ($CNCAT["config"]["add_use_meta_keywords"]):?><tr><td class="name"><?php print $CNCAT["lang"]["field_meta_keywords"]?>:</td><td class="field"><input type="text"class="text"  name="meta_keywords" value="<?php print cn_str($_POST["meta_keywords"])?>" /></td></tr>
    <?php endif;if ($CNCAT["config"]["add_use_meta_descr"]):?><tr><td class="name"><?php print $CNCAT["lang"]["field_meta_descr"]?>:</td><td class="field"><input type="text"class="text"  name="meta_descr" value="<?php print cn_str($_POST["meta_descr"])?>" /></td></tr>
    <?php endif;?><tr><td colspan="2" class="text">
        <?php print $CNCAT["lang"]["backlinks_list"]?>:<br />
        <?php print renderBacklinks(0, $back_link_token);?>
    </td></tr>
    <tr><td colspan="2" class="submit"><input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_submit"]?>" /></td></tr>
</table>
</form>
<?php
print $CNCAT_ENGINE->tpl->renderTemplate("bottom");

/**
 *  Check URL for existing in DB
 *  $url URL for checking
 *  @return boolean, true if exists 
 */ 
function checkExistingUrl($url) {
    GLOBAL $CNCAT_ENGINE, $CNCAT;

    $url = cn_substr ($url, -1)=="/" ? cn_substr ($url, 0, -1) : $url;

    $dbPrefix = $CNCAT["config"]["db"]["prefix"];

    // URL as is
    $query = "SELECT COUNT(*) AS count FROM " . $dbPrefix . "items WHERE (link_url='" . mysql_escape_string($url. '/') . "' OR link_url='" . mysql_escape_string($url) . "') AND item_status IN (0,1,2)";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $row = mysql_fetch_assoc($res);

    if ((int)$row["count"] > 0) {
        return true;
    }

    // URL +- "www."
    if ($CNCAT["config"]["add_ignore_www"]) {
        $url1 = cn_strtolower($url);

        if (cn_substr($url1, 0, 11) == "http://www.") { // has www
            $url = "http://" . cn_substr($url, 11);
        } elseif (cn_substr($url1, 0, 12) == "https://www.") { // has www
            $url = "https://" . cn_substr($url, 12);
        } elseif (cn_substr($url1, 0, 7) == "http://") { // hasn't www
            $url = "http://www." . cn_substr($url, 7);
        } elseif (cn_substr($url1, 0, 8) == "https://") { // hasn't www
            $url = "https://www." . cn_substr($url, 8);
        } else {
            return false;
        }

        $query = "SELECT COUNT(*) AS count FROM " . $dbPrefix . "items WHERE (link_url='" . mysql_escape_string($url. '/') . "' OR link_url='" . mysql_escape_string($url) . "') AND item_status IN (0,1,2)";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $row = mysql_fetch_assoc($res);

        if ((int)$row["count"] > 0) {
            return true;
        }
    }

    return false;
}

function insertCats($cats, $item_id) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $dbPrefix = $CNCAT["config"]["db"]["prefix"];

    $cat_values = array();

    foreach ($cats as $cat) {
        $cat_values[] = "(" . (int)$item_id . ", " . (int)$cat  . ")";
    }

    $query = "INSERT INTO `" . $dbPrefix . "itemcat` (`item_id`, `cat_id`) VALUES" . join(",", $cat_values);
    $CNCAT_ENGINE->db->query($query, null, false) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
}
/**
 *  Render categories tree
 *  @return render result 
 */ 
function renderCategories() {
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $result = "";
    $result .= "<select name=\"cat\">\n";

    $query = "
        SELECT
            `id`, `id_real`, `is_link`, `title`, `title_full`, `path_full`,
            `image_mime`, `sort_order`, `item_count`, `item_count_full`,
            `tree_level`, `child_id_list`, `disable_add`, `disable_child_add`
        FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats`
        WHERE `parent_id`!=-1
        ORDER BY `title_full`
    ";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $categories = array();
    $disabled_cats = array();

    while ($row = mysql_fetch_assoc($res)) {
        if (!empty($row["child_id_list"])) {
            if ($row["disable_child_add"]) {
                $disabled_cats = array_merge($disabled_cats, explode(",", $row["child_id_list"]));
            }
        }
        if(!$row["disable_add"])
            $categories[] = $row;
    }

    foreach ($categories as $row) {
        if (!in_array($row["id"], $disabled_cats))
        $result .=
            "<option value=\"{$row["id"]}\""
                . ($_POST["cat"] == $row["id"] ? " selected=\"selected\" " : "")
            . ">"
            . str_repeat("- ", $row["tree_level"]) . cn_str($row['title'])
            . "</option>\n";
    }

    $result .= "</select>";
    return $result;
}

/**
 *  Render backlinks
 *  @return render result 
 */ 
function renderBacklinks($item_id, $ref = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $result = "";
    $result .= "<table>";

    $query = "SELECT `user_code` FROM `" . $CNCAT["config"]["db"]["prefix"] . "backlinks` WHERE `disabled`=0 ORDER BY `sort_order`, `id`";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    while ($row = mysql_fetch_assoc($res)) {
        $row["user_code"] = cn_str_replace("%CATNAME%", $CNCAT["config"]["catalog_title"], $row["user_code"]);
        $row["user_code"] = cn_str_replace("%BACKURL%", cn_getBackUrl(empty($ref) ? "%SITEID%" : "%REF%"), $row["user_code"]);
        $row["user_code"] = cn_str_replace("%SITEID%", $item_id, $row["user_code"]);
        $row["user_code"] = cn_str_replace("%REF%", $ref, $row["user_code"]);

        $result .= "<tr><td><div style=\"overflow: scroll; width: 250px; height: 80px; border: 1px solid silver;\">" . cn_str($row["user_code"]) . "</div></td><td style=\"padding: 10px; vertical-align: middle; border: 1px solid silver;\">" . $row["user_code"] . "</td></tr>\n";
    }

    $result .= "</table>";
    return $result;
}
