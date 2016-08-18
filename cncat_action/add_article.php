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

require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "check.php";
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "sync_all.php";
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_engine_common"] . "cnimage.php";

if ($CNCAT["config"]["add_article_use_captcha"]=="default" || 
    $CNCAT["config"]["add_article_use_captcha"] == "recaptcha" ||
    $CNCAT["config"]["add_article_use_captcha"] == "keycaptcha")
{
    require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "captcha/{$CNCAT["config"]["add_article_use_captcha"]}.php";
}
elseif ($CNCAT["config"]["add_article_use_captcha"])
{
    $CNCAT["config"]["add_article_use_captcha"] = "default";
    require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "captcha/{$CNCAT["config"]["add_article_use_captcha"]}.php";
}

$CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], "add");
$CNCAT_ENGINE->loadBanners("add_article");

prepareToRender();
$CNCAT["render_result"] = cn_render();

return;

/**
*   Renders whole page
*   @return render result
*/
function cn_render() {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $CNCAT["page"]["search_form_url"] = $CNCAT_ENGINE->url->createCnUrl("search");
    $CNCAT["page"]["add_url"] = $CNCAT_ENGINE->url->createCnUrl("add");
    $CNCAT["page"]["add_article_url"] = $CNCAT_ENGINE->url->createCnUrl("add_article");
    $CNCAT["page"]["map_url"] = $CNCAT_ENGINE->url->createCnUrl("map");
    
    $result = $CNCAT_ENGINE->tpl->renderTemplate("add");

    if ($CNCAT["config"]["add_article_enable"]) {
        $result = cn_str_replace("{DISPLAY FORM}", renderForm(), $result);
    } else {
        $result = cn_str_replace("{DISPLAY FORM}", "<tr><td class=\"text\">" . $CNCAT["lang"]["admin_disable_article_add"] . "</td></tr>", $result);
    }

    return $result;
}

/**
*   Prepare varibles for render
*/
function prepareToRender() {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $CNCAT["lang"]["menu_add_item"] = $CNCAT["lang"]["menu_add_article"];
    $CNCAT["page"]["title"] = $CNCAT["lang"]["menu_add_article"] . " / " . $CNCAT["page"]["title"];

    $CNCAT["lang"]["field_site_title"] = $CNCAT["lang"]["field_site_title_article"];
    $CNCAT["lang"]["field_link_url"] = $CNCAT["lang"]["field_link_url_article"];
    $CNCAT["lang"]["field_descr"] = $CNCAT["lang"]["field_descr_article"];
    $CNCAT["lang"]["field_descr_full"] = $CNCAT["lang"]["field_descr_full_article"];

    $CNCAT["lang"]["thanks_for_add"] = $CNCAT["lang"]["thanks_for_add_article"];
    $CNCAT["lang"]["ext_after_submit"] = $CNCAT["lang"]["ext_after_submit_article"];
    
    $CNCAT["config"]["add_use_back_link"] = $CNCAT["config"]["add_article_use_back_link"];
    $CNCAT["config"]["add_use_author"] = $CNCAT["config"]["add_article_use_author"];
    $CNCAT["config"]["add_use_meta_descr"] = $CNCAT["config"]["add_article_use_meta_descr"];
    $CNCAT["config"]["add_use_meta_keywords"] = $CNCAT["config"]["add_article_use_meta_keywords"];
    $CNCAT["config"]["add_use_captcha"] = $CNCAT["config"]["add_article_use_captcha"];
    
    $CNCAT["config"]["add_use_descr_full"] = 1;
    $CNCAT["config"]["add_max_cats"] = $CNCAT["config"]["add_article_max_cats"];
    

    // check max fields length
    $CNCAT["config"]["add_article_max_url"] = $CNCAT["config"]["add_article_max_url"] > 0 ? $CNCAT["config"]["add_article_max_url"] : 255;
    $CNCAT["config"]["add_article_max_backurl"] = $CNCAT["config"]["add_article_max_backurl"] > 0 ? $CNCAT["config"]["add_article_max_backurl"]  : 255;
    $CNCAT["config"]["add_article_max_title"] = $CNCAT["config"]["add_article_max_title"] > 0 ? $CNCAT["config"]["add_article_max_title"] : 255;
    $CNCAT["config"]["add_article_max_email"] = $CNCAT["config"]["add_article_max_email"] > 0 ? $CNCAT["config"]["add_article_max_email"] : 255;
    $CNCAT["config"]["add_article_max_descr"] = $CNCAT["config"]["add_article_max_descr"] > 0 ? $CNCAT["config"]["add_article_max_descr"] : 2048;
    $CNCAT["config"]["add_article_max_descr_full"] = $CNCAT["config"]["add_article_max_descr_full"] > 0 ? $CNCAT["config"]["add_article_max_descr_full"] : 4096;
    $CNCAT["config"]["add_article_max_meta_keywords"] = $CNCAT["config"]["add_article_max_meta_keywords"] > 0 ? $CNCAT["config"]["add_article_max_meta_keywords"] : 2048;
    $CNCAT["config"]["add_article_max_meta_descr"] = $CNCAT["config"]["add_article_max_meta_descr"] > 0 ? $CNCAT["config"]["add_article_max_meta_descr"] : 2048;
}

/**
 *  Render add form
 *  @return render result 
 */ 
function renderForm() {
    GLOBAL $CNCAT_ENGINE, $CNCAT;

    $dbPrefix = $CNCAT["config"]["db"]["prefix"];

    // if don't isset submit command
    if (!isset($_GET["submit"]) && !isset($_POST["next"])) {
        resetForm();
    }

    // set wizard mode
    $wizard = false;

    // if don't isset step, set first step
    if (!isset($_SESSION["step"]) || $CNCAT["config"]["add_use_captcha"] && $_SESSION["captcha_accept"] != "DONE") {
        $_SESSION["step"] = 1;
    } else {
        $_SESSION["step"] = (int)$_SESSION["step"];
    }
    
    // step is a link to _SESSION[step]
    $step =& $_SESSION["step"];

    // if don't isset item_id, set item_id = 0
    if (!isset($_SESSION["item_id"])) {
        $_SESSION["item_id"] = 0;
    } else {
        $_SESSION["item_id"] = (int)$_SESSION["item_id"];
    }

    // item_id is a link to _SESSION[item_id]
    $item_id =& $_SESSION["item_id"];    

    // module array
    $CNCAT["add"] = array();

    // check step
    if ($step < 1 || $step > 4 || !$item_id) {
        $step = 1;
    }

    // final step
    if ($step == 4) {
        if (!$item_id) {
            resetForm();
        }

        if ($CNCAT["config"]["display_ext"]) {
            $query = "
                    SELECT item_title, item_title_translite
                    FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                    WHERE item_id = " . $item_id . "
                ";
            $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            $row = mysql_fetch_assoc($result);
            $title = $CNCAT["config"]["use_translit"]? $row['item_title_translite']: $row["item_title"]; 
            $CNCAT["lang"]["ext_after_submit"] = cn_str_replace(
                "%URL%", 
                $CNCAT_ENGINE->url->createUrlExt($item_id, $title), 
                $CNCAT["lang"]["ext_after_submit"]
            );
        }
    
        $result = $CNCAT_ENGINE->tpl->renderTemplate("final");

        if (!$wizard) {
            $query = "SELECT COUNT(*) AS `count` FROM `" . $dbPrefix . "backlinks` WHERE `disabled`=0";
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            $row = mysql_fetch_assoc($res);

            if (!$row['count']) {
                $result = cn_str_replace("{DISPLAY BACKLINKS}", "", $result);
            } else {
                $result = cn_str_replace("{DISPLAY BACKLINKS}", renderBacklinks($item_id), $result);
            }
        }

        resetForm(false);
        return $result;
    }

    // check back_link
    $check_back_link = false;
    $back_link_token = isset($_SESSION["back_link_token"]) ? $_SESSION["back_link_token"] : '';

    if (!$back_link_token) {
        $back_link_token = $CNCAT_ENGINE->misc->createBackUrlRef();
        $_SESSION["back_link_token"] = $back_link_token;
    }

    // if form submit
    if (isset($_POST["next"])) {
        $_item = array();
        $_item["item_token"] = $back_link_token;

        // SITE URL
        if (!$wizard || $step == 1) {
            $link_url = "";

            if ($CNCAT["config"]["add_article_use_link_url"]) {
                $link_url = trim($_POST["link_url"]);
    
                if (!empty($link_url) && !preg_match("#^(ht|f)tps?://#i", $link_url)) {
                    $link_url = "http://" . $link_url;
                }
    
                if (empty($link_url) || (cn_strlen($link_url) < 11)) {
                    //$errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["link_url_article"], $CNCAT["lang"]["field_empty"]);
                } elseif (strlen($link_url) > $CNCAT["config"]["add_article_max_url"]) {
                    $errors[] = cn_str_replace(array("%FIELD%", "%LEN%", "%COUNT%"), array($CNCAT["lang"]["link_url_article"], $CNCAT["config"]["add_article_max_url"], cn_strlen($link_url)), $CNCAT["lang"]["field_to_long"]);
                } elseif ($CNCAT["config"]["add_article_check_exists_url"] && checkExistingUrl($link_url)) {
                    $errors[] = cn_str_replace("%URL%", htmlspecialchars($link_url), $CNCAT["lang"]["url_already_exists"]);
                } else {
                    $black_list = array();
                    $white_list = array();
    
                    $query = "SELECT `type`, `check_method`, `check_str` FROM `" . $dbPrefix . "bwlist`";
                    $res = $CNCAT_ENGINE->db->query($query, "Black/white list") or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
                    while ($row = mysql_fetch_assoc($res)) {
                        if ($row["type"] == "black") {
                            $black_list[] = array($row["check_str"], $row["check_method"]);
                        } else {
                            $white_list[] = $row["check_str"];
                        }
                    }
    
                    foreach ($white_list as $link) {
                        if (strpos($link_url, $link) !== false) {
                            $black_list = array();
                        }
                    }
    
                    foreach ($black_list as $link) {
                        if ($link[1] == "substr") {
                            if (strpos($link_url, $link[0]) !== false) {
                                $errors[] = $CNCAT["lang"]["url_in_blacklist"];
                                break;
                            }
                        } else {
                            if (@preg_match($link[0] . $CN_STRINGS["preg"], $link_url)) {
                                $errors[] = $CNCAT["lang"]["url_in_blacklist"];
                                break;
                            }
                        }
                    }
                }
            }

            $CNCAT["add"]["link_url"] = $link_url;
        }

        // BACK LINK
        if (!$wizard || $step == 2) {
            $back_link = "";

            if ($CNCAT["config"]["add_article_use_back_link"]) {
                $back_link = trim($_POST["back_link"]);
            
                if (!empty($back_link) && !preg_match("#^http[s]?://#i", $back_link)) {
                    $back_link = "http://" . $back_link;
                }

                if ((empty($back_link) || cn_strlen($back_link) < 11) && $CNCAT["config"]["add_article_accept_to_add"] == 2) {
                    $errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["back_link_url"], $CNCAT["lang"]["field_empty"]);
                } elseif (cn_strlen($back_link) > $CNCAT["config"]["add_article_max_backurl"]) {
                    $errors[] = cn_str_replace(array("%FIELD%", "%LEN%", "%COUNT%"), array($CNCAT["lang"]["back_link"], $CNCAT["config"]["add_article_max_backurl"], cn_strlen($back_link)), $CNCAT["lang"]["field_to_long"]);
                } else {
                    if (!empty($back_link) && $CNCAT["config"]["add_article_back_link_host"]) {
                        $u1 = @parse_url($CNCAT["add"]["link_url"]);
                        $u2 = @parse_url($back_link);

                        if ($u1["host"] !== $u2["host"]) {
                            $errors[] = $CNCAT["lang"]["must_equal_host"];
                        }
                    }
                }
            }

            $CNCAT["add"]["back_link"] = $back_link;
        }

        // TITLE
        if (!$wizard || ($step == 1 || $step == 3)) {
            $title = trim($_POST["site_title"]);

            if ($CNCAT["config"]["add_article_min_title"] <= 1 && empty($title)) {
                $errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["site_title_article"], $CNCAT["lang"]["field_empty"]);
            } elseif (cn_strlen($title) < $CNCAT["config"]["add_article_min_title"]) {
                $errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["site_title_article"], $CNCAT["config"]["add_article_min_title"]), $CNCAT["lang"]["field_to_short"]);
            } elseif (cn_strlen($title) > $CNCAT["config"]["add_article_max_title"]) {
                $errors[] = cn_str_replace(array("%FIELD%", "%LEN%", "%COUNT%"), array($CNCAT["lang"]["site_title_article"], $CNCAT["config"]["add_article_max_title"], cn_strlen($title)), $CNCAT["lang"]["field_to_long"]);
            }

            $CNCAT["add"]["site_title"] = $title;
        }

        // CATS
        if (!$wizard || ($step == 3)) {
            $cats = (array)$_POST["cats"];

            // check cats exists
            if ($cats && count($cats) < $CNCAT["config"]["add_article_max_cats"]) {
                foreach ($cats as $num => $cat) {
                    if ((int)$cat == 0) {
                        unset($cats[$num]);
                    } else {
                        $cats[$num] = (int)$cats[$num];
                    }
                }

                $cats = array_unique($cats);

                $query = "SELECT `id`, `child_id_list`, `disable_add`, `disable_child_add` FROM `" . $dbPrefix . "cats` WHERE `id` IN(" . join(",", $cats) . ") AND `is_link`=0";
                $res = $CNCAT_ENGINE->db->query($query, "Cats exists") or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                $cats_id = array();
                $disabled_cats = array();

                while ($row = mysql_fetch_assoc($res)) {
                    if (!empty($row["child_id_list"])) {
                        if ($row["disable_child_add"]) {
                            $disabled_cats = array_merge($disabled_cats, explode(",", $row["child_id_list"]));
                        }
                    }

                    if ($row["disable_add"]) {
                        continue;
                    }

                    $cats_id[] = (int)$row["id"];
                }

                foreach ($cats as $num => $id) {
                    if (!in_array($id, $cats_id) || in_array($id, $disabled_cats)) {
                        unset($cats[$num]);
                    }
                }
            }

            if (!$cats) {
                $errors[] = $CNCAT["lang"]["must_select_cats"];
            } elseif (count($cats) > $CNCAT["config"]["add_article_max_cats"]) {
                $errors[] = cn_str_replace("%LEN%", $CNCAT["config"]["add_article_max_cats"], $CNCAT["lang"]["cats_to_long"]);
            }
        }

        // EMAIL
        if (!$wizard || ($step == 3)) {
            $email = trim($_POST["email"]);

            if ($CNCAT["config"]["add_article_min_email"] == 1 && empty($email)) {
                $errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["email"], $CNCAT["lang"]["field_empty"]);
            } elseif (cn_strlen($email) < $CNCAT["config"]["add_article_min_email"]) {
                $errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["email"], $CNCAT["config"]["add_article_min_email"]), $CNCAT["lang"]["field_to_short"]);
            } elseif (cn_strlen($email) > $CNCAT["config"]["add_article_max_email"]) {
                $errors[] = cn_str_replace(array("%FIELD%", "%LEN%", "%COUNT%"), array($CNCAT["lang"]["email"], $CNCAT["config"]["add_article_max_email"], cn_strlen($email)), $CNCAT["lang"]["field_to_long"]);
            } elseif ($CNCAT["config"]["add_article_min_email"] > 0 && !@preg_match("/^[^@\s]+@[^@\s]+\.[a-z0-9]+$/i" . $CN_STRINGS["preg"], $email)) {
                $errors[] = $CNCAT["lang"]["invalid_email_format"];
            }

            $CNCAT["add"]["email"] = $email;
        }

        // Author
        if (!$wizard || ($step == 3)) {
            $author = "";

            if ($CNCAT["config"]["add_article_use_author"]) {
                $author = trim($_POST["author"]);
    
                if ($CNCAT["config"]["add_article_min_author"] == 1 && empty($author)) {
                    $errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["author"], $CNCAT["lang"]["field_empty"]);
                } elseif (cn_strlen($author) < $CNCAT["config"]["add_article_min_author"]) {
                    $errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["author"], $CNCAT["config"]["add_article_min_author"]), $CNCAT["lang"]["field_to_short"]);
                } elseif (cn_strlen($author) > $CNCAT["config"]["add_article_max_author"]) {
                    $errors[] = cn_str_replace(array("%FIELD%", "%LEN%", "%COUNT%"), array($CNCAT["lang"]["author"], $CNCAT["config"]["add_article_max_author"], cn_strlen($author)), $CNCAT["lang"]["field_to_long"]);
                }
            }

            $CNCAT["add"]["author"] = $author;
        }

        // DESCRIPTION
        if (!$wizard || ($step == 3)) {
            $descr = trim($_POST["descr"]);

            if ($CNCAT["config"]["add_article_min_descr"] == 1 && empty($descr)) {
                $errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["descr_article"], $CNCAT["lang"]["field_empty"]);
            } elseif (cn_strlen($descr) < $CNCAT["config"]["add_article_min_descr"]) {
                $errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["descr_article"], $CNCAT["config"]["add_article_min_descr"]), $CNCAT["lang"]["field_to_short"]);
            } elseif (cn_strlen($descr) > $CNCAT["config"]["add_article_max_descr"]) {
                $errors[] = cn_str_replace(array("%FIELD%", "%LEN%", "%COUNT%"), array($CNCAT["lang"]["descr_article"], $CNCAT["config"]["add_article_max_descr"], cn_strlen($descr)), $CNCAT["lang"]["field_to_long"]);
            }

            $CNCAT["add"]["descr"] = $descr;
        }

        // FULL DESCRIPTION
        if (!$wizard || ($step == 3)) {
            $descr_full = "";

                $descr_full = trim($_POST["descr_full"]);

                if ($CNCAT["config"]["add_article_min_descr_full"] == 1 && empty($descr_full)) {
                    $errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["descr_full_article"], $CNCAT["lang"]["field_empty"]);
                } elseif (cn_strlen($descr_full) < $CNCAT["config"]["add_article_min_descr_full"]) {
                    $errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["descr_full_article"], $CNCAT["config"]["add_article_min_descr_full"]), $CNCAT["lang"]["field_to_short"]);
                } elseif (cn_strlen($descr_full) > $CNCAT["config"]["add_article_max_descr_full"]) {
                    $errors[] = cn_str_replace(array("%FIELD%", "%LEN%", "%COUNT%"), array($CNCAT["lang"]["descr_full_article"], $CNCAT["config"]["add_article_max_descr_full"], cn_strlen($descr_full)), $CNCAT["lang"]["field_to_long"]);
                }
            $CNCAT["add"]["descr_full"] = $descr_full;
        }

        // META KEYWORDS
        if (!$wizard || ($step == 3)) {
            $meta_keywords = "";

            if ($CNCAT["config"]["add_article_use_meta_keywords"]) {
                $meta_keywords = trim($_POST["meta_keywords"]);

                if ($CNCAT["config"]["add_article_min_meta_keywords"] == 1 && empty($meta_keywords)) {
                    $errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["meta_keywords"], $CNCAT["lang"]["field_empty"]);
                } elseif (cn_strlen($meta_keywords) < $CNCAT["config"]["add_article_min_meta_keywords"]) {
                    $errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["meta_keywords"], $CNCAT["config"]["add_article_min_meta_keywords"]), $CNCAT["lang"]["field_to_short"]);
                } elseif (cn_strlen($meta_keywords) > $CNCAT["config"]["add_article_max_meta_keywords"]) {
                    $errors[] = cn_str_replace(array("%FIELD%", "%LEN%", "%COUNT%"), array($CNCAT["lang"]["meta_keywords"], $CNCAT["config"]["add_article_max_meta_keywords"], cn_strlen($meta_keywords)), $CNCAT["lang"]["field_to_long"]);
                }
            }

            $CNCAT["add"]["meta_keywords"] = $meta_keywords;
        }

        // META DESCRIPTION
        if (!$wizard || ($step == 3)) {
            $meta_descr = "";

            if ($CNCAT["config"]["add_article_use_meta_descr"]) {
                $meta_descr = trim($_POST["meta_descr"]);

                if ($CNCAT["config"]["add_article_min_meta_descr"] == 1 && empty($meta_descr)) {
                    $errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["meta_descr"], $CNCAT["lang"]["field_empty"]);
                } elseif (cn_strlen($meta_descr) < $CNCAT["config"]["add_article_min_meta_descr"]) {
                    $errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["meta_descr"], $CNCAT["config"]["add_article_min_meta_descr"]), $CNCAT["lang"]["field_to_short"]);
                } elseif (cn_strlen($meta_descr) > $CNCAT["config"]["add_article_max_meta_descr"]) {
                    $errors[] = cn_str_replace(array("%FIELD%", "%LEN%", "%COUNT%"), array($CNCAT["lang"]["meta_descr"], $CNCAT["config"]["add_article_max_meta_descr"], cn_strlen($meta_descr)), $CNCAT["lang"]["field_to_long"]);
                }
            }

            $CNCAT["add"]["meta_descr"] = $meta_descr;
        }

        // FILTERS
        if ((!$wizard || $step == 3) && $CNCAT["config"]["use_filters"]) {
            $filters = (array)$_POST["filters"];

            foreach ($filters as $id => $filter) {
                if ((int)$id == 0) {
                    unset($filters[$id]);
                } else {
                    $filters[$id] = (array)$filters[$id];

                    foreach ($filter as $k => $v) {
                        if ((int)$v == 0) {
                            unset($filters[$id][$k]);
                        } else {
                            $filters[$id][$k] = (int)$filters[$id][$k];
                        }
                    }
                }
            }

            // check filters exists
            if ($filters) {
                $filters_id = array();

                $query = "SELECT `id` FROM `" . $dbPrefix . "filters` WHERE `id` IN(" . join(",", array_keys($filters)) . ")";
                $res = $CNCAT_ENGINE->db->query($query, "Filters exists") or $CNCAT_ENGINE->displayErrorDB(mysql_error());

                 while ($row = mysql_fetch_assoc($res)) {
                    $filters_id[] = (int)$row["id"];
                }

                foreach ($filters as $id => $filter) {
                    if (!in_array($id, (array)$filters_id)) {
                        unset($filters[$id]);
                    }
                }

                // check filters values exists
                if ($filters) {
                    $filters_value = array();

                    $query = "SELECT `id`, `filter_id` FROM `" . $dbPrefix . "filtvals` WHERE `filter_id` IN(" . join(",", array_keys($filters)) . ")";
                    $res = $CNCAT_ENGINE->db->query($query, "Filters values exists") or $CNCAT_ENGINE->displayErrorDB(mysql_error());

                    while ($row = mysql_fetch_assoc($res)) {
                        $filters_value[(int)$row["filter_id"]][] = (int)$row["id"];
                    }

                    foreach ($filters as $fil_id => $filter) {
                        foreach ($filter as $num => $id) {
                            if (!in_array($id, (array)$filters_value[$fil_id])) {
                                unset($filters[$fil_id][$num]);
                            }
                        }
                    }
                }
            }

            $query = "SELECT `id`, `required`, `title` FROM `" . $dbPrefix . "filters`";
            $res = $CNCAT_ENGINE->db->query($query, "Filters required") or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            $required = array();

            while ($row = mysql_fetch_assoc($res)) {
                $required[$row["id"]] = array((bool)$row["required"], $row["title"]);
            }

            foreach ($required as $id => $req) {
                if ($req[0] && !$filters[$id]) {
                    $errors[] = cn_str_replace("%FILTER%", htmlspecialchars($req[1]), $CNCAT["lang"]["must_select_filval"]);
                } 
            }
        }

        // EXTENSION FIELDS
        $ext_fields = array();

        foreach ($CNCAT["config"]["extfields"]["items"][1] as $name => $item) {
            if (!$item["active"] || ($item["type"] != 6 && !isset($_POST[$name]))) {
                continue;
            }

            $CNCAT["add"][$name] = $_POST[$name];

            if (function_exists("cn_extFieldValidate")) {
                $error = "";
                cn_extFieldValidate($name, $_POST[$name], $error);
    
                if (!empty($error)) {
                    $errors[] = $error;
                    continue;
                }
            }
            
            if ($item["required"]) {
                if (
                    ($item["type"] != 6 && empty($_POST[$name]))
                    || (($item["type"] == 1 || $item["type"] == 2) && !is_numeric($_POST[$name]))
                ) {
                    $errors[] = cn_str_replace("%FIELD%", $item["title"], $CNCAT["lang"]["field_empty"]);

                    continue;
                }
            }

            switch ($item["type"]) {
                case 1: $ext_fields["`" . $name . "`"] = is_numeric($_POST[$name]) ? intval($_POST[$name]) : 'NULL'; break;
                case 2: $ext_fields["`" . $name . "`"] = is_numeric($_POST[$name]) ? floatval($_POST[$name]) : 'NULL'; break;
                case 3: $ext_fields["`" . $name . "`"] = "'" . mysql_escape_string(substr($_POST[$name], 0, 255)) . "'"; break;
                case 4:
                    if (preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)$#", $_POST[$name], $m)) {
                        $ext_fields["`" . $name . "`"] = "'" . $m[3] . "-" . $m[2] . "-" . $m[1] . " 00:00:00'";
                    } else {
                        $ext_fields["`" . $name . "`"] = 'NULL';
                    }
                break;
                case 5: $ext_fields["`" . $name . "`"] = "'" . mysql_escape_string(substr($_POST[$name], 0, 16777215)) . "'"; break;
                case 6: {
                    if (!$_FILES[$name]["error"] && isset($_FILES[$name]["tmp_name"])) {
                        if ($result = cn_image_proccess($_FILES[$name]["tmp_name"])) {
                            $ext_fields["`" . $name . "`"] = "'" . mysql_escape_string($result["image_data"]) . "'";
                            $ext_fields["`" . $name . "_mime`"] = "'" . $result["image_mime"] . "'";
                            $ext_fields["`" . $name . "_thumb`"] = "'" . mysql_escape_string($result["thumb_data"]) . "'";
                        }
                    }
                }
                break;
            }
        }

        // CAPTCHA
        if (!$wizard || ($step == 1)) {
            if ($CNCAT["config"]["add_article_use_captcha"]) {
                if (call_user_func($CNCAT["config"]["add_article_use_captcha"] . "Valid") != "DONE") {
                    $errors[] = $CNCAT["lang"]["wrong_image_code"];
                }
                else {
                    $_SESSION["captcha_accept"] = "DONE";
                }
            }
        }
        
        // check link_url
        $check_link_url = false;
        $check_result = array();

        if (
            !$errors
            && (
                $CNCAT["config"]["add_article_check_link"]
                || $CNCAT["config"]["add_article_accept_to_add"] > 0
            )
        ) {
            $check_result = cn_checkLink($link_url, true, false, (bool)$CNCAT["config"]["check_pr"], (bool)$CNCAT["config"]["check_cy"], $CNCAT["config"]["check_timeout"], $CNCAT["config"]["check_max_redirect"]);
            $check_link_url = (bool)$check_result["work"];

            if ($item_id) {
                $query = "UPDATE `" . $dbPrefix . "items` SET
                    `link_chk_work_res`=" . (int)$check_link_url . ",
                    `link_chk_work_date`=NOW(), `last_check`=NOW()";
                $query .= $CNCAT["config"]["check_pr"] ? ", `link_rating_pr`=" . $check_result["pr"] . ", `link_rating_pr_neg`=" . -$check_result["pr"] . ", `link_pr_date`=NOW()" : "";
                $query .= $CNCAT["config"]["check_cy"] ? ", `link_rating_cy`=" . $check_result["cy"] . ", `link_rating_cy_neg`=" . -$check_result["cy"] . ", `link_cy_date`=NOW()" : "";
                $query .= "WHERE `item_id`=" . $item_id;
                $CNCAT_ENGINE->db->query($query, null, false) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            } else {
                $_item["link_chk_work_res"] = $check_link_url ? 1 : 0;
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
            }

            if (!$check_link_url && $CNCAT["config"]["add_article_accept_to_add"] > 0) {
                $errors[] = $CNCAT["lang"]["check_link_error"];
            }
        }

        if (isset($check_result["cy"]) && $CNCAT["config"]["article_add_min_cy"] > 0) {
            if ($check_result["cy"] < $CNCAT["config"]["article_add_min_cy"]) {
                $errors[] = cn_str_replace(array("%MIN_CY%", "%CY%"), array($CNCAT["config"]["article_add_min_cy"], $check_result["cy"]), $CNCAT["lang"]["add_link_accept_cy"]);
            }
        }
        
        if (isset($check_result["pr"]) && $CNCAT["config"]["article_add_min_pr"] > 0) {
            if ($check_result["pr"] < $CNCAT["config"]["article_add_min_pr"]) {
                $errors[] = cn_str_replace(array("%MIN_PR%", "%PR%"), array($CNCAT["config"]["link_add_min_pr"], $check_result["pr"]), $CNCAT["lang"]["add_link_accept_pr"]);
            }
        }

        // check back_link
        if (
            !$errors
            && (
                $CNCAT["config"]["check_back_link"] ||
                $CNCAT["config"]["add_article_accept_to_add"] == 2
            )
        ) {
            if (!$CNCAT["config"]["add_article_use_back_link"] || empty($back_link)) {
                $back_link = $link_url;
            }

            $check_result = cn_checkLink($back_link, true, true, false, false, $CNCAT["config"]["check_timeout"], $CNCAT["config"]["check_max_redirect"]);
            $check_back_link = (bool)$check_result["back"];

            if ($item_id) {
                $query = "UPDATE `" . $dbPrefix . "items` SET
                    `link_chk_back_res`=" . ($check_back_link ? 1 : 0) . ",
                    `link_chk_back_date`=NOW()
                    WHERE `item_id`=" . $item_id;
                $CNCAT_ENGINE->db->query($query, null, false) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            } else {
                $_item["link_chk_back_res"] = $check_back_link;
                $_item["link_chk_back_date"] = date("Y-m-d H:i:s");
            }

            if (!$check_back_link && $CNCAT["config"]["add_article_accept_to_add"] == 2) {
                $errors[] = $CNCAT["lang"]["check_back_link_error"];
            }
        }

        // if no errors
        if (!$errors) {  
            $descr_full = $CNCAT_ENGINE->misc->cleanHtml($descr_full);
          
            if ($CNCAT["config"]["add_article_short_wysiwyg"]) {
                $descr = $CNCAT_ENGINE->misc->cleanHtml($descr);
            }

            //if (!$wizard) {
                $item_status = 0;
                $item_insert_type = 1;
                $item_submit_type = 0;

                if ($CNCAT["config"]["add_article_auto_approve"] == 1) {
                    $item_status = 1;
                } elseif ($CNCAT["config"]["add_article_auto_approve"] == 2) {
                    if ($check_link_url) {
                        $item_status = 1;
                    }
                }

                if ($item_status) {
                    $item_submit_type = 2;
                }

                $query = "INSERT INTO `" . $dbPrefix . "items`
                    (`item_id`, `item_status`, `item_title`, `item_title_translite`, `item_descr`, `item_descr_full`, `item_meta_keywords`, `item_meta_descr`, `item_insert_date`, `item_insert_type`, `item_author_email`, `item_author_name`, `link_url`, `link_back_link_url`, `item_image`, `link_favicon`";
                $query .= $item_status == 1 ? ", `item_submit_date`, `item_submit_type`" : "";
                foreach (array_keys($_item) as $k) {
                    $query .= ", `" . $k . "`";
                }

                $query .= $ext_fields ? ", " . join(", ", array_keys($ext_fields)) : "";
                $query .= ", `item_type`)
                    VALUES(
                        0,
                        " . $item_status . ",
                        '" . mysql_escape_string($title) . "',
                        '" . mysql_escape_string(cn_translitEncode($title)) . "',
                        '" . mysql_escape_string($descr) . "',
                        '" . mysql_escape_string($descr_full) . "',
                        '" . mysql_escape_string($meta_keywords) . "',
                        '" . mysql_escape_string($meta_descr) . "',
                        NOW(),
                        " . $item_insert_type . ",
                        '" . mysql_escape_string($email) . "',
                        '" . mysql_escape_string($author) . "',
                        '" . mysql_escape_string($link_url) . "',
                        '" . mysql_escape_string($back_link) . "',
                        '',
                        ''";
                $query .= $item_status == 1 ? (", NOW(), " . $item_submit_type) : "";
                foreach ($_item as $v) {
                    $query .= ", " . (is_numeric($v) ? $v : "'" . mysql_escape_string($v) . "'");
                }

                $query .= $ext_fields ? ", " . join(", ", array_values($ext_fields)) : "";
                $query .= ", 1)";
        
                $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                $item_id = mysql_insert_id();

                $_SESSION["back_link_token"] = "";

                // check bad encoding
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
                    || ($CNCAT["config"]["add_min_descr_full"] > 0 && empty($row["item_descr_full"]))
                ) {
                    $query = "
                        DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
                        WHERE item_id = " . $item_id . "
                    ";
                    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                    $CNCAT_ENGINE->displayError('Incorrect character set.');
                }

                insertFilters($filters, $item_id);
                insertCats($cats, $item_id);
                cn_syncAll();

                // send mail notify to admin
                if ($CNCAT["config"]["mail_notify_admin"]) {
                    $CNCAT_ENGINE->misc->sendMailAdmin($item_id, $cats);
                }

                // send mail notify to user
                if ($CNCAT["config"]["mail_notify_add"]) {
                    $CNCAT_ENGINE->misc->sendMailAdd($item_id);
                }

                if ($CNCAT["config"]["mail_notify_approve"] && $item_status == 1) {
                    $CNCAT_ENGINE->misc->sendMailApprove($item_id);
                }

                $step = 4;
                $CNCAT_ENGINE->url->redirect("add_article", "submit");
            //}
        } else {
            $err = renderErrors($errors);
        }
    }

    $CNCAT["config"]["add_message"] = $CNCAT["config"]["add_html_message"] ? $CNCAT["config"]["add_message"] : nl2br(cn_str($CNCAT["config"]["add_message"]));

    // RENDER
    $CNCAT["lang"]["field_link_url"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_article_max_url"], $CNCAT["lang"]["field_link_url"]);
    $CNCAT["lang"]["field_back_link"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_article_max_backurl"], $CNCAT["lang"]["field_back_link"]);
    $CNCAT["lang"]["field_email"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_article_max_email"], $CNCAT["lang"]["field_email"]);
    $CNCAT["lang"]["field_descr"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_article_max_descr"], $CNCAT["lang"]["field_descr"]);
    $CNCAT["lang"]["field_descr_full"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_article_max_descr_full"], $CNCAT["lang"]["field_descr_full"]);
    $CNCAT["lang"]["field_site_title"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_article_max_title"], $CNCAT["lang"]["field_site_title"]);
    $CNCAT["lang"]["field_meta_keywords"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_article_max_meta_keywords"], $CNCAT["lang"]["field_meta_keywords"]);
    $CNCAT["lang"]["field_meta_descr"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_article_max_meta_descr"], $CNCAT["lang"]["field_meta_descr"]);
    $CNCAT["lang"]["field_cats"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_article_max_cats"], $CNCAT["lang"]["field_cats"]);
    $CNCAT["lang"]["field_author"] = cn_str_replace("%LEN%", $CNCAT["config"]["add_article_max_author"], $CNCAT["lang"]["field_author"]);
    // generate new secret code
    if ($CNCAT["config"]["add_use_captcha"] && (!$wizard || $step == 1)) {
        $CNCAT["page"]["captcha"] = call_user_func($CNCAT["config"]["add_use_captcha"]."CaptchaHtml");    
    }
    $result = $CNCAT_ENGINE->tpl->renderTemplate("add_article_form");
    $result = cn_str_replace("{DISPLAY ERRORS}", $err, $result);
    
    if (!$wizard || ($step == 3)) {
        $result = cn_str_replace("{DISPLAY FILTERS}", renderFilters(), $result);
        $result = cn_str_replace("{DISPLAY CATEGORIES}", renderCategories(), $result);
        $result = cn_str_replace("{DISPLAY EXTFIELDS}", renderExtfields(), $result);
        $result = cn_str_replace("{DISPLAY EDITOR}", $CNCAT_ENGINE->render->renderTextEditor("descr_full", $descr_full), $result);

        if ($CNCAT["config"]["add_article_short_wysiwyg"]) {
            $result = cn_str_replace("{DISPLAY SHORT_EDITOR}", $CNCAT_ENGINE->render->renderTextEditor("descr", $descr), $result);
        } else {
            $result = cn_str_replace("{DISPLAY SHORT_EDITOR}", "<textarea name=\"descr\" cols=\"1\" rows=\"1\">" . htmlspecialchars($_POST["descr"]) . "</textarea>", $result);
        }
    }

    $result = cn_str_replace("{DISPLAY BACKLINKS}", renderBacklinks(0, $back_link_token), $result);

    return $result;
}

/**
 *  Render Errors
 *  @return render result 
 */ 
function renderErrors($errors) {
    GLOBAL $CNCAT_ENGINE, $CNCAT;
    $result = "";
    $result .= $CNCAT_ENGINE->tpl->renderTemplate("add_errors_top");

    foreach ($errors as $error) {
        if (is_array($error)) {
        } else {
            $CNCAT["error"]["text"] = $error;
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("add_error");
        }
    }

    $result .= $CNCAT_ENGINE->tpl->renderTemplate("add_errors_bottom");

    return $result;
}

/**
 *  Render Filters form
 *  @return render result 
 */ 
function renderFilters() {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $dbPrefix = $CNCAT["config"]["db"]["prefix"];

    $result = "";
    $query = "SELECT `id`, `filter_id`, `title` FROM `" . $dbPrefix . "filtvals` ORDER BY `sort_order`, `id`";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB (mysql_error());
    $values = array();

    while ($row = mysql_fetch_assoc($res)) {
        $values[$row["filter_id"]][$row["id"]] = $row["title"]; 
    }

    $query = "SELECT `id`, `title` FROM `" . $dbPrefix . "filters` ORDER BY `sort_order`, `id`";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    while ($row = mysql_fetch_assoc($res)) {
        $CNCAT["filter"]["title"] = $row["title"];
        $CNCAT["filter"]["id"] = $row["id"];

        $result .= $CNCAT_ENGINE->tpl->renderTemplate("add_filter_top");

        foreach ((array)$values[$row["id"]] as $value => $title) {
            $CNCAT["filtval"]["title"] = $title;
            $CNCAT["filtval"]["id"] = $value;
            $CNCAT["filtval"]["_checked"] = in_array($value, (array)$_POST["filters"][$row["id"]]) ? true : false;

            $result .= $CNCAT_ENGINE->tpl->renderTemplate("add_filtval");
        }

        $result .= $CNCAT_ENGINE->tpl->renderTemplate("add_filter_bottom");
    }

    return $result;
}

/**
 *  Render backlinks
 *  @return render result 
 */ 
function renderBacklinks($item_id, $ref = "") {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $dbPrefix = $CNCAT["config"]["db"]["prefix"];

    $result = "";
    $query = "SELECT `user_code` FROM `" . $dbPrefix . "backlinks` WHERE `disabled`=0 ORDER BY `sort_order`, `id`";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    $result .= $CNCAT_ENGINE->tpl->renderTemplate("add_backlinks_top");
    $CNCAT["backlink"]["user_code"] = "";

    while ($row = mysql_fetch_assoc($res)) {
        $CNCAT["backlink"]["user_code"] = cn_str_replace("%CATNAME%", $CNCAT["config"]["catalog_title"], $row["user_code"]);
        $CNCAT["backlink"]["user_code"] = cn_str_replace("%BACKURL%", cn_getBackUrl(empty($ref) ? "%SITEID%" : "%REF%"), $CNCAT["backlink"]["user_code"]);
        $CNCAT["backlink"]["user_code"] = cn_str_replace("%SITEID%", $item_id, $CNCAT["backlink"]["user_code"]);
        $CNCAT["backlink"]["user_code"] = cn_str_replace("%REF%", $ref, $CNCAT["backlink"]["user_code"]);
        $result .= $CNCAT_ENGINE->tpl->renderTemplate("add_backlink");
    }

    $result .= $CNCAT_ENGINE->tpl->renderTemplate("add_backlinks_bottom");

    return $result;
}

/**
 *  Render categories tree
 *  @return render result 
 */ 
function renderCategories() {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("add_cat", "CAT");
    $fieldsSql = "c.id, c.id_full, c.parent_id, c.path, c.path_full, c.sort_order, c.tree_level, c.child_id_list, c.parent_id, c.disable_add, c.disable_child_add ".(is_array ($fields) ? ", c.".join(", c.", $fields["int"]) : "");

    $catOrder = $CNCAT["config"]["cat_sort"]==1 ? "c.title_full" : "c.sort_order_global";//"c.sort_order_global";

    $dbPrefix = $CNCAT["config"]["db"]["prefix"];

    // Getting categories                                                 
    $query = "SELECT ".$fieldsSql." FROM ".$dbPrefix."cats c WHERE c.is_link=0 ORDER BY ".$catOrder;
    $res = $CNCAT_ENGINE->db->query($query, "Categories") or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    // No render if no categories
    $catCount = mysql_num_rows($res);
    if ($catCount==0) 
        return "";

    $cats = array();
    $disabled_cats = array();

    while ($cat = mysql_fetch_assoc($res)) {
        if (!empty($cat["child_id_list"])) {
            if ($cat["disable_child_add"]) {
                $disabled_cats = array_merge($disabled_cats, explode(",", $cat["child_id_list"]));
            }
        }

        if ($cat["disable_add"]) {
            $disabled_cats[] = $cat["id"];
        }

        $cats[$cat["id_full"]] = $cat;
    }

    foreach ($cats as $id_full => $cat) {
        $cats[$id_full]["disable"] = 0;
        $cat["_show"] = 1;

        if (in_array($cat["_show"], $disabled_cats)) {
            $cat["_show"] = 0;
        }

        if (!in_array($cat["id"], $disabled_cats)) {
            $id_path = explode("/", $id_full);
            $id_path_str = "";

            foreach ($id_path as $id) {
                $id_path_str .= (!empty($id_path_str) ? "/" : "") . $id;
                $cats[$id_path_str]["_show"] = 1;
            }
        } else {
            $cats[$id_full]["disable"] = 1;
        }
    }

    $lastLevel = 0;
    foreach ($cats as $cat) {
        $CNCAT["cat"] = $cat;

        if ($CNCAT["cat"]["id"]==$CNCAT["root_cat_id"])
            continue;
        $CNCAT_ENGINE->render->prepareCategoryToDisplay($CNCAT["cat"]);
        $level = $CNCAT["cat"]["tree_level"];
        while ($level>$lastLevel)
        {
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("add_cat_next_level");
            $lastLevel++;
        }   

        while ($level<$lastLevel)
        {
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("add_cat_prev_level");
            $lastLevel--;
        }   

        if (!$cat["_show"] && !$CNCAT["config"]["add_show_dis_cat"]) {
            continue;
        }

        $CNCAT["cat"]["disable_add"] = $cat["disable"];
        $CNCAT["cat"]["_checked"] = in_array((int)$CNCAT["cat"]["id"], (array)$_POST["cats"]) ? true : false;
        $result .= $CNCAT_ENGINE->tpl->renderTemplate("add_cat");

    }
    while (0<$CNCAT["cat"]["tree_level"])
    {   
        $CNCAT[cat][tree_level]--;
        $result .= $CNCAT_ENGINE->tpl->renderTemplate ("add_cat_prev_level");
    }
    return $result;
}

/**
 *  Render extension fields
 *  @return render result 
 */ 
function renderExtfields() {
    GLOBAL $CNCAT_ENGINE, $CNCAT;

    $result = "";
    $CNCAT["add"]["datetime"] = date("H:i d.m.Y");

    foreach ($CNCAT["config"]["extfields"]["items"][1] as $name => $field) {
        if (
            !$field["active"] ||
            !in_array(1, $field["display"])
        ) {
            continue;
        }

        if ($CNCAT_ENGINE->tpl->templateExists("add_extfield")) {
            $CNCAT["extfield"] = $field;
            $CNCAT["extfield"]["name"] = $name;
            $CNCAT["extfield"]["value"] = isset($_POST[$name]) ? $_POST[$name] : "";
            
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("add_extfield");
        }
    }

    return $result;
}

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

/**
 *  Init Add form 
 */
function resetForm($redirect = true) {
    GLOBAL  $CNCAT_ENGINE;
    unset($_SESSION["secret_number"]);
    unset($_SESSION["step"]);
    unset($_SESSION["item_id"]);
    unset($_SESSION["captcha_accept"]);
    
    foreach ($_POST as $k => $v) {
        unset($_POST[$k]);
    }

    if ($redirect) {
        $CNCAT_ENGINE->url->redirect("add_article", "submit");
    }
}

/**
 * Insert filters value
 * $filters custom filters
 * $item_id element ID
 */ 
function insertFilters($filters, $item_id) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $dbPrefix = $CNCAT["config"]["db"]["prefix"];

    if ($filters && $CNCAT["config"]["use_filters"]) {
        $fil_values = array();
    
        foreach ($filters as $id => $filter) {
            foreach ($filter as $value) {
                $fil_values[] = "(" . $item_id . ", " . $value . ")";
            }
        }

        $query = "INSERT INTO `" . $dbPrefix . "itemfilt` (`item_id`, `filtval_id`) VALUES" . join(",", $fil_values);
        $CNCAT_ENGINE->db->query($query, null, false) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }
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
?>
