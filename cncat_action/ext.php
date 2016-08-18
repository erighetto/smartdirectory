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

//init captcha script
if ($CNCAT["config"]["comments_use_captcha"]=="default" || 
    $CNCAT["config"]["comments_use_captcha"] == "recaptcha" ||
    $CNCAT["config"]["comments_use_captcha"] == "keycaptcha")
{
    require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "captcha/{$CNCAT["config"]["comments_use_captcha"]}.php";
}
elseif ($CNCAT["config"]["comments_use_captcha"])
{
    $CNCAT["config"]["comments_use_captcha"] = "default";
    require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "captcha/{$CNCAT["config"]["comments_use_captcha"]}.php";
}  

$CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], "extended");
prepareToRender();

$CNCAT["render_result"] = cn_render();

return;
/**
 * Prepare varibles for render
 * @global $CNCAT
 * @global $CNCAT_ENGINE 
 */
function prepareToRender() {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $dbPrefix = $CNCAT["config"]["db"]["prefix"];
    $CNCAT["page"]["search_form_url"] = $CNCAT_ENGINE->url->createCnUrl("search");
    $CNCAT["page"]["add_url"] = $CNCAT_ENGINE->url->createCnUrl("add");
    $CNCAT["page"]["add_article_url"] = $CNCAT_ENGINE->url->createCnUrl("add_article");
    $CNCAT["page"]["map_url"] = $CNCAT_ENGINE->url->createCnUrl("map");
    $item_id = (int)$_GET["id"];

    $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("ext", "ITEM");
    $fieldsSql = "i.item_id, i.item_type, i.item_status, i.item_meta_keywords, i.item_meta_descr, i.item_display_ext, i.item_rating_users, i.item_votes_count, i.item_com_count " . (is_array($fields) ? ", i." . join(", i.", $fields["int"]) : "");

    foreach ($CNCAT["config"]["extfields"]["items"][0] as $name => $field) {
        if ($field["active"] || $CNCAT["config"]["extfields"]["items"][1][$name]["active"]) {
            if ($field["type"] == 6) {
                $fieldsSql .= ", `" . $name . "_mime`";
            } else {
                $fieldsSql .= ", `" . $name . "`";
            }
        }
    }

    $query = "SELECT " . $fieldsSql . " FROM `" . $dbPrefix . "items` i WHERE i.item_id=" . $item_id;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    if (!($CNCAT["item"] = mysql_fetch_assoc($res))) {
        $CNCAT_ENGINE->misc->error404();
        exit;
    }

    if (!$CNCAT["item"]["item_display_ext"]) {
        $CNCAT["item"]["item_display_ext"] = $CNCAT["config"]["display_ext"];
    }

    if ($CNCAT["item"]["item_display_ext"] != 1) {
        $CNCAT_ENGINE->misc->error404();
        exit;
    }

    if (!$CNCAT["item"]["link_target"]) {
        $CNCAT["item"]["link_target"] = $CNCAT["config"]["link_target"];
    }

    if (($CNCAT["config"]["add_use_wysiwyg"] || $CNCAT["item"]["item_type"] == 1) && !empty($CNCAT["item"]["item_descr_full"])) {
        $CNCAT["item"]["item_descr_full"] = $CNCAT["system"]["str_sid"] . $CNCAT["item"]["item_descr_full"];
    }

    if (
        (
            ($CNCAT["config"]["add_short_wysiwyg"] && $CNCAT["item"]["item_type"] == 0)
            || ($CNCAT["config"]["add_article_short_wysiwyg"] && $CNCAT["item"]["item_type"] == 1)
        ) && !empty($CNCAT["item"]["item_descr"])
    ) {
        $CNCAT["item"]["item_descr"] = $CNCAT["system"]["str_sid"] . $CNCAT["item"]["item_descr"];
    }

    $query = "SELECT `cat_id` FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat` WHERE `item_id` = " . $item_id;
    $res = $CNCAT_ENGINE->db->query($query, "Item cats") or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $cats = array();

    while ($row = mysql_fetch_assoc($res)) {
        $cats[] = $row["cat_id"];
    }

    if ($CNCAT_ENGINE->misc->isAdmin()) {
        $CNCAT["item"]["_control_bar"] = true;
    } elseif ($CNCAT_ENGINE->misc->isModer()) {
        $CNCAT["item"]["_control_bar"] = count(array_diff($cats, $CNCAT_ENGINE->misc->getModerCats())) < count($cats);
    } else {
        $CNCAT["item"]["_control_bar"] = false;
    }

    $CNCAT["page"]["keywords"] = $CNCAT["item"]["item_meta_keywords"];
    $CNCAT["page"]["description"] = $CNCAT["item"]["item_meta_descr"];

    // CNCat 4.0 beta
    $CNCAT["meta"]["keywords"] = $CNCAT["page"]["keywords"];
    $CNCAT["meta"]["description"] = $CNCAT["page"]["description"];

    if ($CNCAT["item"]["item_status"] == 1) {
        $CNCAT['page']['title'] = $CNCAT["item"]["item_title"] . ' - ' . $CNCAT['page']['title'];
    }
        
    $CNCAT_ENGINE->loadBanners("ext" . $CNCAT["item"]["item_type"]);
}

/**
 * Render page
 * 
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @return $result 
 */
function cn_render() {
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    
    if ($CNCAT["item"]["item_type"] == 1) {
        $result = $CNCAT_ENGINE->tpl->renderTemplate("ext_article");
    } else {
        $result = $CNCAT_ENGINE->tpl->renderTemplate("ext");
    }
    
    $result = cn_str_replace("{DISPLAY CATEGORIES}", renderCats($CNCAT["item"]["item_id"]), $result);
    $result = cn_str_replace("{DISPLAY FILTERS}", renderFilters($CNCAT["item"]["item_id"]), $result);
    $result = cn_str_replace("{DISPLAY EXTFIELDS}", renderExtfields($CNCAT["item"]), $result);
    $result = cn_str_replace("{DISPLAY COMMENTS}", renderComments($CNCAT["item"]["item_id"]), $result);

    if ($CNCAT["item"]["_control_bar"]) {
        $rat_res = array();
        for ($j = 0; $j <= 10; $j++) {
            $CNCAT["admin"]["item_rating_value"] = $j;
            if ($j == (int)$CNCAT["item"]["item_rating_moder"]) {
                $rat_res[] = $CNCAT_ENGINE->tpl->renderTemplate("admin_rating_num_active");
            } else {
                $rat_res[] = $CNCAT_ENGINE->tpl->renderTemplate("admin_rating_num");
            }
        }
        $rat_res = $CNCAT_ENGINE->tpl->renderTemplate("admin_rating_top") . join($CNCAT_ENGINE->tpl->renderTemplate("admin_rating_delim"), $rat_res) . $CNCAT_ENGINE->tpl->renderTemplate("admin_rating_bottom");
        $result = cn_str_replace("{DISPLAY ADMIN_RATING}", $rat_res, $result);
    }

    return $result;
}

/**
 * Render categories
 * @global  $CNCAT
 * @global  $CNCAT_ENGINE
 * @param int $item_id
 * @return $result 
 */
function renderCats($item_id) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $_SERVER["QUERY_STRING"] = "";
    $result = "";
    $result .= $CNCAT_ENGINE->tpl->renderTemplate("ext_cats_top");

    $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("ext_cat", "CAT");
    $fieldsSql = "c.id, c.path_full " . (is_array($fields) ? ", c." . join(", c.", $fields["int"]) : "");

    $query = "SELECT " . $fieldsSql . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat` i,
        `" . $CNCAT["config"]["db"]["prefix"] . "cats` c
        WHERE i.item_id=" . $item_id . "
        AND c.id=i.cat_id
        ORDER BY i.priority";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    $num = 0;
    $count = mysql_num_rows($res);

    $CNCAT["page"]["item_type"] = -1;
    $CNCAT["page"]["sort_order"] = $CNCAT["config"]["default_sort_order"];

    while ($CNCAT["cat"] = mysql_fetch_assoc($res)) {
        $CNCAT["cat"]["_url"] = $CNCAT_ENGINE->url->createUrlCat($CNCAT["cat"]["id"], $CNCAT["cat"]["path_full"]);
        $result .= $CNCAT_ENGINE->tpl->renderTemplate("ext_cat");

        if ($num < ($count - 1)) {
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("ext_cat_delim");
        }

        $num++;
    }

    $result .= $CNCAT_ENGINE->tpl->renderTemplate("ext_cats_bottom");

    return $result;
}

/**
 * Renders filters
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param int $item_id
 * @return $result 
 */
function renderFilters($item_id) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $dbPrefix = $CNCAT["config"]["db"]["prefix"];

    $result = "";
    $values = array();

    $query = "SELECT v.filter_id, v.title FROM `" . $dbPrefix . "itemfilt` i, " . $dbPrefix . "filtvals v
        WHERE i.item_id=" . $item_id . "
        AND v.id=i.filtval_id
        ORDER BY v.sort_order";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    while ($row = mysql_fetch_assoc($res)) {
        $values[$row["filter_id"]][] = $row["title"];
    }

    if ($values) {
        $query = "SELECT `id`, `title` FROM `" . $dbPrefix . "filters` WHERE id IN(" . join(",", array_keys($values)) . ") ORDER BY `sort_order`";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        while ($row = mysql_fetch_assoc($res)) {
            $CNCAT["filter"]["title"] = $row["title"];
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("ext_filter_top");
            $count = count($values[$row["id"]]);

            foreach ($values[$row["id"]] as $num => $value) {
                $CNCAT["filtval"]["title"] = $value;
                $result .= cn_trim($CNCAT_ENGINE->tpl->renderTemplate("ext_filtval"));
    
                if ($num < ($count - 1)) {
                    $result .= $CNCAT_ENGINE->tpl->renderTemplate("ext_filtval_delim");
                }
            }

            $result .= $CNCAT_ENGINE->tpl->renderTemplate("ext_filter_bottom");
        }
    }

    return $result;
}

/**
 * Render ext.fields
 * 
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param array $item
 * @return $result
 */
function renderExtfields($item) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $result = "";

    foreach ($CNCAT["config"]["extfields"]["items"][$item["item_type"]] as $name => $field) {
        if (
            !$field["active"] ||
            !in_array(3, $field["display"])
        ) {
            continue;
        }

        $CNCAT["extfield"] = $field;
        $CNCAT["extfield"]["name"] = $name;

        if ($field["type"] == 6) {
            $CNCAT["extfield"]["value"] = $item[$name . "_mime"];
        } else {
            $CNCAT["extfield"]["value"] = $item[$name];
        }

        $result .= $CNCAT_ENGINE->tpl->renderTemplate("ext_ext_field");
    }

    return $result;
}

/**
 * Render Comments
 * 
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param int $item_id
 * @return $result 
 */
function renderComments($item_id) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $form_errors = array();
    $CNCAT["page"]["form_data"] = array(
        "code" => "",
        "author_name" => "",
        "author_email" => "",
        "text" => "",
        "vote" => 0
    );

    if (isset($_POST["doPost"])) {
        $form_data = array();
        $form_data["code"] = isset($_POST["code"]) ? $_POST["code"] : "";
        $form_data["author_name"] = isset($_POST["author_name"]) ? $_POST["author_name"] : "";
        $form_data["author_email"] = isset($_POST["author_email"]) ? $_POST["author_email"] : "";
        $form_data["text"] = isset($_POST["text"]) ? $_POST["text"] : "";
        $form_data["vote"] = isset($_POST["vote"]) ? intval($_POST["vote"]) : 0;
        
        $CNCAT["page"]["form_data"] = $form_data;
        $CNCAT["page"]["form_data"]["code"] = "";

        $captcha_valid = true;

        if ($CNCAT["config"]["comments_use_captcha"]) {
            if (call_user_func($CNCAT["config"]["comments_use_captcha"] . "Valid") != "DONE")
            {
                $form_errors[] = $CNCAT["lang"]["wrong_image_code"];
                $captcha_valid = false;
            }
            else {
                $_SESSION["captcha_accept"] = "DONE";
            }
        }

        if (
            $CNCAT["config"]["comments_add_enable"] && (
            ($CNCAT["item"]["item_type"] == 0 && $CNCAT["config"]["comments_links_enable"]) ||
            ($CNCAT["item"]["item_type"] == 1 && $CNCAT["config"]["comments_articles_enable"])
        )) {
            if ($CNCAT["config"]["comments_min_author_name"] == 1 && empty($form_data["author_name"])) {
                $form_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["name"], $CNCAT["lang"]["field_empty"]);
            } elseif (cn_strlen($form_data["author_name"]) < $CNCAT["config"]["comments_min_author_name"]) {
                $form_errors[] = cn_str_replace(
                    array("%FIELD%", "%LEN%"),
                    array($CNCAT["lang"]["name"], $CNCAT["config"]["comments_min_author_name"]),
                    $CNCAT["lang"]["field_to_short"]
                );
            } elseif (cn_strlen($form_data["author_name"]) > $CNCAT["config"]["comments_max_author_name"]) {
                $form_errors[] = cn_str_replace(
                    array("%FIELD%", "%COUNT%", "%LEN%"),
                    array($CNCAT["lang"]["name"], cn_strlen($form_data["author_name"]), $CNCAT["config"]["comments_max_author_name"]),
                    $CNCAT["lang"]["field_to_long"]
                );
            }
    
            if (empty($form_data["text"])) {
                $form_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["comment"], $CNCAT["lang"]["field_empty"]);
            } elseif (cn_strlen($form_data["text"]) < $CNCAT["config"]["comments_min_text"]) {
                $form_errors[] = cn_str_replace(
                    array("%FIELD%", "%LEN%"),
                    array($CNCAT["lang"]["comment"], $CNCAT["config"]["comments_min_text"]),
                    $CNCAT["lang"]["field_to_short"]
                );
            } elseif (cn_strlen($form_data["text"]) > $CNCAT["config"]["comments_max_text"]) {
                $form_errors[] = cn_str_replace(
                    array("%FIELD%", "%COUNT%", "%LEN%"),
                    array($CNCAT["lang"]["comment"], cn_strlen($form_data["text"]), $CNCAT["config"]["comments_max_text"]),
                    $CNCAT["lang"]["field_to_long"]
                );
            }
    
            if ($CNCAT["config"]["comments_min_author_email"] == 1 && empty($form_data["author_email"])) {
                $form_errors[] = cn_str_replace("%FIELD%", "E-mail", $CNCAT["lang"]["field_empty"]);
            } elseif (cn_strlen($form_data["author_email"]) < $CNCAT["config"]["comments_min_author_email"]) {
                $form_errors[] = cn_str_replace(
                    array("%FIELD%", "%LEN%"),
                    array("E-mail", $CNCAT["config"]["comments_min_author_email"]),
                    $CNCAT["lang"]["field_to_short"]
                );
            } elseif (cn_strlen($form_data["author_email"]) > $CNCAT["config"]["comments_max_author_email"]) {
                $form_errors[] = cn_str_replace(
                    array("%FIELD%", "%COUNT%", "%LEN%"),
                    array("E-mail", cn_strlen($form_data["email"]), $CNCAT["config"]["comments_max_author_email"]),
                    $CNCAT["lang"]["field_to_long"]
                );
            }
        }

        if ($CNCAT["config"]["rating_enable"]) {
            if ($form_data["vote"] < 0) {
                $form_data["vote"] = 0;
            } elseif ($form_data["vote"] > 10) {
                $form_data["vote"] = 10;
            }
        } else {
            $form_data["vote"] = 0;
        }

        if (
            $CNCAT["config"]["comments_add_enable"] && (
            ($CNCAT["item"]["item_type"] == 0 && $CNCAT["config"]["comments_links_enable"]) ||
            ($CNCAT["item"]["item_type"] == 1 && $CNCAT["config"]["comments_articles_enable"])
        )) {
            if (!$form_errors) {
                $query = "
                    INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "comments`
                    (`id`, `item_id`, `author_name`, `author_email`, `text`, `vote`, `date_insert`, `active`, `display`)
                    VALUES (
                        NULL,
                        " . $item_id . ",
                        '" . mysql_escape_string($form_data["author_name"]) . "',
                        '" . mysql_escape_string($form_data["author_email"]) . "',
                        '" . mysql_escape_string($form_data["text"]) . "',
                        " . $form_data["vote"] . ",
                        NOW(),
                        " . ($CNCAT["config"]["comments_auto_approve"] ? 1 : 0) . ",
                        " . ($CNCAT["config"]["comments_auto_approve"] ? 1 : 0) . "
                    )
                ";
                $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                $comment_id = mysql_insert_id();
        
                $_SESSION["comment_post_successful"] = 1;
    
                if ($CNCAT["config"]["comments_auto_approve"]) {
                    $query = "
                        UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items`
                        SET item_com_count = item_com_count + 1
                        WHERE item_id = " . $item_id . "
                    ";
                    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
                    voteForItem($item_id, $form_data["vote"]);
                }
                $title = $CNCAT["config"]["use_translit"]? $CNCAT["item"]["item_title_translite"]: $CNCAT["item"]["item_title"];  
                if (!$CNCAT["config"]["comments_auto_approve"]) {
                    header("Location: " . $CNCAT_ENGINE->url->createUrlExt($item_id, $title) . "#comments");
                    exit;
                } else {
                    header("Location: " . $CNCAT_ENGINE->url->createUrlExt($item_id, $title) . "#comment_" . $comment_id);
                    exit;
                }
            } elseif (empty($form_data["text"]) && $CNCAT["config"]["rating_without_com"] && $form_data["vote"] > 0) {
                if ($captcha_valid) {
                    voteForItem($item_id, $form_data["vote"]);
                    $title = $CNCAT["config"]["use_translit"]? $CNCAT["item"]["item_title_translite"]: $CNCAT["item"]["item_title"];
                    header("Location: " . $CNCAT_ENGINE->url->createUrlExt($item_id, $title));
                    exit;
                } else {
                    $form_errors = array($CNCAT["lang"]["wrong_image_code"]);
                }
            }
        } else {
            if ($captcha_valid) {
                voteForItem($item_id, $form_data["vote"]);
                $title = $CNCAT["config"]["use_translit"]? $CNCAT["item"]["item_title_translite"]: $CNCAT["item"]["item_title"];
                
                header("Location: " . $CNCAT_ENGINE->url->createUrlExt($item_id, $title));
                exit;
            } else {
                $form_errors = array($CNCAT["lang"]["wrong_image_code"]);
            }
        }
    }

    // generate new secret code
    
    if ($CNCAT["config"]["comments_use_captcha"]) {
        unset($_SESSION["captcha_accept"]);
        $CNCAT["page"]["captcha"] = call_user_func($CNCAT["config"]["comments_use_captcha"]."CaptchaHtml");   
    }

    $result = "";
    $result_form_errors = "";
    $CNCAT["comments"]["post_result"] = "";

    if ($_SESSION["comment_post_successful"]) {
        if (!$CNCAT["config"]["comments_auto_approve"]) {
            $CNCAT["comments"]["post_result"] = $CNCAT["lang"]["comment_add_successful"];
        }

        $_SESSION["comment_post_successful"] = 0;
    }

    if ($form_errors) {
        $result_form_errors = $CNCAT_ENGINE->tpl->renderTemplate("ext_comments_errors_top");
        
        foreach ($form_errors as $error) {
            $CNCAT["comments"]["form_error"] = $error;
            $result_form_errors .= $CNCAT_ENGINE->tpl->renderTemplate("ext_comments_error");
        }

        $result_form_errors .= $CNCAT_ENGINE->tpl->renderTemplate("ext_comments_errors_bottom");
    }

    if ($CNCAT["item"]["item_type"] == 1) {
        $result .= $CNCAT_ENGINE->tpl->renderTemplate("ext_article_comments_top");
    } else {
        $result .= $CNCAT_ENGINE->tpl->renderTemplate("ext_comments_top");
    }

    if (
        ($CNCAT["item"]["item_type"] == 0 && $CNCAT["config"]["comments_links_enable"]) ||
        ($CNCAT["item"]["item_type"] == 1 && $CNCAT["config"]["comments_articles_enable"])
    ) {
        $query = "
            SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "comments`
            WHERE item_id = " . $item_id . " AND active = 1 " . (!$CNCAT_ENGINE->misc->isAdmin() ? "AND display = 1" : "") . "
            ORDER BY id ASC
        ";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        while ($row = mysql_fetch_assoc($res)) {
            $CNCAT["comment"] = $row;
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("ext_comment");
        }
    }

    if ($CNCAT["item"]["item_type"] == 1) {
        $result .= cn_str_replace("{DISPLAY ERRORS}", $result_form_errors, $CNCAT_ENGINE->tpl->renderTemplate("ext_article_comments_bottom"));
    } else {
        $result .= cn_str_replace("{DISPLAY ERRORS}", $result_form_errors, $CNCAT_ENGINE->tpl->renderTemplate("ext_comments_bottom"));
    }

    if (
        $CNCAT["config"]["rating_enable"] ||
        ($CNCAT["item"]["item_type"] == 0 && $CNCAT["config"]["comments_links_enable"]) ||
        ($CNCAT["item"]["item_type"] == 1 && $CNCAT["config"]["comments_articles_enable"])
    ) {
        return $result;
    } else {
        return "";
    }
}

/**
 * Voting for a item
 * 
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @param int $item_id
 * @param int $vote
 * @return null; 
 */
function voteForItem($item_id, $vote) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    if (!$CNCAT["config"]["rating_enable"]) {
        return;
    }

    $inc = true;

    if ($CNCAT["config"]["rating_vote_limiter"]) {
        $inc = false;

        $query = "
            SELECT UNIX_TIMESTAMP(`time` + INTERVAL " . (int)$CNCAT["config"]["rating_vote_interval"] . " SECOND) AS `time`
            FROM `" . $CNCAT["config"]["db"]["prefix"] . "jumps`
            WHERE
                `item_id`=" . $item_id . " AND
                `ip`=INET_ATON('" . mysql_escape_string($_SERVER["REMOTE_ADDR"]) . "') AND
                `type`='vote'
        ";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());      

        if ($row = mysql_fetch_assoc($res)) {
            if ($row["time"] < time()) {
                $inc = true;
                $query = "
                    UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "jumps` SET `time`=NOW() WHERE
                        `ip`=INET_ATON('" . mysql_escape_string($_SERVER["REMOTE_ADDR"]) . "') AND
                        `type`='vote' AND
                        `item_id`=" . $item_id . "
                ";
                $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            }
        } else {
            $inc = true;
            $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "jumps`
                VALUES(
                    " . $item_id . ",
                    INET_ATON('" . mysql_escape_string($_SERVER["REMOTE_ADDR"]) . "'),
                    'vote',
                    NOW()
                )";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }

        $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "jumps` WHERE `time` < (NOW() - INTERVAL 1 DAY) AND `type`='vote'";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    if ($inc == true) {
        if ($vote > 0) {
            $item_rating = 
                ($CNCAT["item"]["item_rating_users"] * $CNCAT["item"]["item_votes_count"] + $vote) /
                ($CNCAT["item"]["item_votes_count"] + 1);

            $query = "
                UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items`
                SET
                    item_rating_users = " . $item_rating . ",
                    item_rating_users_neg = " . -$item_rating . ",
                    item_votes_count = item_votes_count + 1
                WHERE item_id = " . $item_id . "
            ";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }
    }
}
?>
