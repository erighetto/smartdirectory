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
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "sync_all.php";
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_engine_common"] . "cnimage.php";

if (file_exists($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_config"] . "validator.php")) {
    ob_start();
    require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_config"] . "validator.php";
    ob_end_clean();
}

$_mode = $_GET["mode"];
$_count = (int)$_POST["count"];

if ($_count < 1) {
    $_count = 1;
} elseif ($_count > 10) {
    $_count = 10;
}

if ($_mode == "forms" && isset($_POST["doPost"])) {
    $_errors = array();
    $_item = array();
    $_is_edit = isset($_GET['edit']);
    $_item_id = (int)$_GET['id'];

    for ($i = 1; $i <= $_count; $i++) {
        $_item[$i] = array();
        $_item[$i]["item_type"] = 1;

        // Title
        $_item[$i]["item_title"] = cn_trim($_POST["item_title"][$i]);
        $_item[$i]["item_title_translite"] = cn_translitEncode($_item[$i]["item_title"]);

        if (empty($_item[$i]["item_title"])) {
            //$_errors[$i][] = cn_str_replace("%FIELD%", $CNCAT["lang"]["site_title"], $CNCAT["lang"]["field_empty"]);
        } elseif (cn_strlen($_item[$i]["item_title"]) > 255) {
            $_errors[$i][] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["site_title_article"], 255), $CNCAT["lang"]["field_to_long"]);
        }

        // URL
        $_item[$i]["link_url"] = cn_trim($_POST["link_url"][$i]);

        if (!empty($_item[$i]["link_url"]) && !preg_match("#^(ht|f)tps?://#i", $_item[$i]["link_url"])) {
            $_item[$i]["link_url"] = "http://" . $_item[$i]["link_url"];
        }

        if (empty($_item[$i]["link_url"])) {
            //$_errors[$i][] = cn_str_replace("%FIELD%", $CNCAT["lang"]["link_url"], $CNCAT["lang"]["field_empty"]);
        } elseif (cn_strlen($_item[$i]["link_url"]) > 255) {
            $_errors[$i][] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["link_url_article"], 255), $CNCAT["lang"]["field_to_long"]);
        }

        // check title & url
        if (empty($_item[$i]["item_title"])) {
            $_errors[$i][] = cn_str_replace("%FIELD%", $CNCAT["lang"]["site_title_article"], $CNCAT["lang"]["field_empty"]);
        }

        // Backlink
        $_item[$i]["link_back_link_url"] = cn_trim($_POST["link_back_link_url"][$i]);

        if (!empty($_item[$i]["link_back_link_url"]) && !preg_match("#^http[s]?://#i", $_item[$i]["link_back_link_url"])) {
            $_item[$i]["link_back_link_url"] = "http://" . $_item[$i]["link_back_link_url"];
        }

        if (cn_strlen($_item[$i]["link_back_link_url"]) > 255) {
            $_errors[$i][] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["back_link_url"], 255), $CNCAT["lang"]["field_to_long"]);
        }

        // Categories
        $_cats[$i] = (array)$_POST["cats"][$i];

        if (!empty($_item[$i]["item_title"]) || !empty($_item[$i]["link_url"])) {
            if (empty($_cats[$i])) {
                $_errors[$i][] = $CNCAT["lang"]["must_be_select_cat"];
            }
        }

        // Email
        $_item[$i]["item_author_email"] = cn_trim($_POST["item_author_email"][$i]);

        if (cn_strlen($_item[$i]["item_author_email"]) > 255) {
            $_errors[$i][] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["email"], 255), $CNCAT["lang"]["field_to_long"]);
        }

        // Author
        $_item[$i]["item_author_name"] = cn_trim($_POST["item_author_name"][$i]);

        if (cn_strlen($_item[$i]["item_author_name"]) > 255) {
            $_errors[$i][] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["author"], 255), $CNCAT["lang"]["field_to_long"]);
        }

        // Description
        $_item[$i]["item_descr"] = cn_trim($_POST["item_descr"][$i]);

        if (cn_strlen($_item[$i]["item_descr"]) > 65000) {
            $_errors[$i][] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["descr_article"], 65000), $CNCAT["lang"]["field_to_long"]);
        }

        // Full description
        $_item[$i]["item_descr_full"] = cn_trim($_POST["item_descr_full"][$i]);

        if (cn_strlen($_item[$i]["item_descr_full"]) > 65000) {
            $_errors[$i][] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["descr_full_article"], 65000), $CNCAT["lang"]["field_to_long"]);
        }

        // Meta keywords
        $_item[$i]["item_meta_keywords"] = cn_trim($_POST["item_meta_keywords"][$i]);

        if (cn_strlen($_item[$i]["item_meta_keywords"]) > 65000) {
            $_errors[$i][] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["meta_keywords"], 65000), $CNCAT["lang"]["field_to_long"]);
        }

        // Meta description
        $_item[$i]["item_meta_descr"] = cn_trim($_POST["item_meta_descr"][$i]);

        if (cn_strlen($_item[$i]["item_meta_descr"]) > 65000) {
            $_errors[$i][] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["meta_descr"], 65000), $CNCAT["lang"]["field_to_long"]);
        }

        // Jumps from
        $_item[$i]["link_jumps_from"] = (int)$_POST["link_jumps_from"][$i];

        if ($_item[$i]["link_jumps_from"] < 0) {
            $_item[$i]["link_jumps_from"] = 0;
        }

        // Jumps to
        $_item[$i]["link_jumps_to"] = (int)$_POST["link_jumps_to"][$i];

        if ($_item[$i]["link_jumps_to"] < 0) {
            $_item[$i]["link_jumps_to"] = 0;
        }
        
        // PR
        $_item[$i]["link_rating_pr"] = (int)$_POST["link_rating_pr"][$i];

        if ($_item[$i]["link_rating_pr"] < 0) {
            $_item[$i]["link_rating_pr"] = 0;
        } elseif ($_item[$i]["link_rating_pr"] > 10) {
            $_item[$i]["link_rating_pr"] = 10;
        }
        
        // CY
        $_item[$i]["link_rating_cy"] = (int)$_POST["link_rating_cy"][$i];

        if ($_item[$i]["link_rating_cy"] < 0) {
            $_item[$i]["link_rating_cy"] = 0;
        }

        // Moder rating
        $_item[$i]["item_rating_moder"] = (int)$_POST["item_rating_moder"][$i];

        if ($_item[$i]["item_rating_moder"] < 0) {
            $_item[$i]["item_rating_moder"] = 0;
        } elseif ($_item[$i]["item_rating_moder"] > 10) {
            $_item[$i]["item_rating_moder"] = 10;
        }

        // Ext fields
        $_item_ext[$i] = array();
        
        foreach ($CNCAT["config"]["extfields"]["items"][1] as $name => $item) {
            if (!$item["active"] || ($item["type"] != 6 && !isset($_POST[$name]))) {
                continue;
            }

            if (function_exists("cn_extFieldValidate")) {
                $error = "";
                cn_extFieldValidate($name, $_POST[$name][$i], $error);
    
                if (!empty($error)) {
                    $_errors[$i][] = $error;
                    continue;
                }
            }

            switch ($item["type"]) {
                case 1: $_item_ext[$i][$name] = $_POST[$name][$i]; break;
                case 2: $_item_ext[$i][$name] = $_POST[$name][$i]; break;
                case 3: $_item_ext[$i][$name] = substr($_POST[$name][$i], 0, 255); break;
                case 4:
                    if (preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)$#", $_POST[$name][$i], $m)) {
                        $_item_ext[$i][$name] = $m[3] . "-" . $m[2] . "-" . $m[1] . " 00:00:00";
                    } else {
                        $_item_ext[$i][$name] = '';
                    }
                break;
                case 5: $_item_ext[$i][$name] = substr($_POST[$name][$i], 0, 16777215); break;
                case 6: {
                    if (!empty($_POST[$name . "_delete"][$i])) {
                        $_item[$i][$name] = "";
                        $_item[$i][$name . "_mime"] = "";
                        $_item[$i][$name . "_thumb"] = "";
                    }

                    if (!$_FILES[$name]["error"][$i] && $_FILES[$name]["size"][$i] > 0) {
                        if ($result = cn_image_proccess($_FILES[$name]["tmp_name"][$i])) {
                            $_item[$i][$name] = $result["image_data"];
                            $_item[$i][$name . "_mime"] = $result["image_mime"];
                            $_item[$i][$name . "_thumb"] = $result["thumb_data"];
                        }
                    }
                }
                break;
            }
        }

        // Filters
        $_fils[$i] = (array)$_POST["fils"][$i];

        // Item status
        $_item[$i]["item_status"] = (int)$_POST["item_status"][$i];

        if ($_is_edit) {
            $items = itemSelect("`item_status`", "`item_id` = " . $_item_id);
            
            if ($items[0]["item_status"] != 1 && $_item[$i]["item_status"] == 1) {
                $_item[$i]["item_submit_date"] = date("Y-m-d H:i:s");
            }
        } else {
            if ($_item[$i]["item_status"] == 1) {
                $_item[$i]["item_submit_date"] = date("Y-m-d H:i:s");
            }
        }

        if ($_item[$i]["item_status"] < 0 || $_item[$i]["item_status"] > 2) {
            $_item[$i]["item_status"] = 0;
        }

        // Link target
        $_item[$i]["link_target"] = (int)$_POST["link_target"][$i];

        if ($_item[$i]["link_target"] < 0 || $_item[$i]["link_target"] > 3) {
            $_item[$i]["link_target"] = 0;
        }

        // Item display ext
        $_item[$i]["item_display_ext"] = (int)$_POST["item_display_ext"][$i];

        if ($_item[$i]["item_display_ext"] < 0 || $_item[$i]["item_display_ext"] > 2) {
            $_item[$i]["item_display_ext"] = 0;
        }

        // Item favour
        $_item[$i]["item_favour"] = (int)$_POST["item_favour"][$i];

        if ($_item[$i]["item_favour"] < 0) {
            $_item[$i]["item_favour"] = 0;
        } elseif ($_item[$i]["item_favour"] > 10) {
            $_item[$i]["item_favour"] = 10;
        }

        // Mail notify
        $_mail_notify = !empty($_POST["mail_notify"]);

        // Submit type
        if ($_item[$i]["item_status"] == 1) {
            $_item[$i]["item_submit_type"] = 1;
        }
    }

    if (!$_errors) {
        for ($i = 1; $i <= $_count; $i++) {
            if (empty($_item[$i]["item_title"])) {
                continue;
            }

            if ($_is_edit) {
                $item = itemSelect("`item_status`", "`item_id`=" . $_item_id);

                $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat`
                WHERE `item_id`=" . $_item_id;
                $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

                $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemfilt`
                WHERE `item_id`=" . $_item_id;
                $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

                itemUpdate($_item[$i], "`item_id`=" . $_item_id);
                itemUpdateExt(1, $_item_ext[$i], "`item_id`=" . $_item_id);
            } else {
                $_item[$i]["item_image"] = "";
                $_item[$i]["link_favicon"] = "";
                $_item[$i]["item_insert_date"] = date("Y-m-d H:i:s");
                if ($_item[$i]["item_status"] == 1) $_item[$i]["item_submit_date"] = date("Y-m-d H:i:s");

                itemInsert($_item[$i]);
                $_item_id = mysql_insert_id();
                itemUpdateExt(1, $_item_ext[$i], "`item_id`=" . $_item_id);
            }

            // categories
            $values = array();

            foreach ($_cats[$i] as $cat_id) {
                $values[] = "(" . (int)$_item_id . ", " . (int)$cat_id . ", 0, 0, 1)";
            }

            $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "itemcat`
                VALUES " . join(",", $values);
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

            // filters
            if ($_fils[$i]) {
                $values = array();

                foreach ($_fils[$i] as $fil_id) {
                    $values[] = "(" . (int)$_item_id . ", " . (int)$fil_id . ")";
                }

                $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "itemfilt`
                    VALUES " . join(",", $values);
                $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            }

            //
            if ($_mail_notify) {
                if ($_is_edit) {
                    if ($item["item_status"] != $_item[$i]["item_status"]) {
                        if ($_item[$i]["item_status"] == 1) {
                            $CNCAT_ENGINE->misc->sendMailApprove($_item_id);
                        } elseif ($_item[$i]["item_status"] == 2) {
                            $CNCAT_ENGINE->misc->sendMailDecline($_item_id);
                        }
                    }
                } else {
                    $CNCAT_ENGINE->misc->sendMailAdmin($_item_id);
                    $CNCAT_ENGINE->misc->sendMailAdd($_item_id);

                    if ($_item[$i]["item_status"] == 1) {
                        $CNCAT_ENGINE->misc->sendMailApprove($_item_id);
                    } elseif ($_item[$i]["item_status"] == 2) {
                        $CNCAT_ENGINE->misc->sendMailDecline($_item_id);
                    }
                }
            }
        }

        cn_syncAll();

        if ($_is_edit) {
            header("Location: " . (!empty($_POST["referer"]) ? $_POST["referer"] : "index.php?act=articles_add"));
        } else {
            header("Location: index.php?act=articles_add");
        }

        exit;
    }
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["links_and_articles"]?> / <?php print isset($_GET['edit']) ? $CNCAT["lang"]["article_edit"] : $CNCAT["lang"]["article_add"]?></h1>
<?php
if ($_mode == "forms") {
    $_item_id = (int)$_GET["id"];
    $_is_edit = isset($_GET["edit"]);

    if ($_is_edit) {
        // if editing not exists link
        if (!itemExists("`item_id`=" . $_item_id)) {
            print "<span class=\"not_found\">" . $CNCAT["lang"]["link_not_found"] . "</span>";
            include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
            exit;
        }

        $query = "SELECT `cat_id` FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat`
            WHERE `item_id`=" . $_item_id;
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $_item_cats = array();

        while ($row = mysql_fetch_array($res)) {
            $_item_cats[] = $row['cat_id'];
        }
    }

    if (!isset($_POST["doPost"])) {
        if (!isset($_GET["edit"])) {
            $cats = (array)$_POST["cats"];
            $fils = (array)$_POST["fils"];
            $status = (int)$_POST["item_status"];
            $_POST["cats"] = array();
            $_POST["fils"] = array();
            $_POST["status"] = array();

            for ($link_num = 1; $link_num <= $_count; $link_num++) {
                $_POST["cats"][$link_num] = $cats;
                $_POST["fils"][$link_num] = $fils;
                $_POST["item_status"][$link_num] = $status;
            }
        } else {
            list($item) = itemSelect("*", "`item_id`=" . $_item_id);

            foreach ((array)$item as $k => $v) {
                $_POST[$k][1] = $v;
            }

            $_POST["cats"][1] = $_item_cats;

            $query = "SELECT `filtval_id` FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemfilt`
                WHERE `item_id`=" . $_item_id;
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            $_POST['fils'] = array();

            while ($row = mysql_fetch_array($res)) {
                $_POST['fils'][1][] = $row['filtval_id'];
            }

            $_POST["referer"] = $_SERVER["HTTP_REFERER"];
        }
    }

    if ($_errors) {
        foreach ($_errors as $num => $errors) {
            print "<strong>" . $CNCAT["lang"]["link"] . " " . $num . ":</strong>";
            print "<ul>\n";

            foreach ($errors as $error) {
                print "<li style=\"color: red;\">" . $error . "</li>\n";
            }

            print "</ul>\n";
        }
    }

    $cats_count = 0;

    print "<script type=\"text/javascript\" src=\"" .
        $CNCAT["abs"] . $CNCAT["system"]["dir_engine_scripts"] .
        "tinymce/tiny_mce.js\"></script>";
    print $CNCAT_ENGINE->tpl->renderTemplate("js_calendar");
?>
<script type="text/javascript">
function favourUp(id) {
    var favour = document.getElementById('favour_' + id);

    if (favour.value < 10) {
        favour.value = parseInt(favour.value) + 1;
    }
}

function favourDown(id) {
    var favour = document.getElementById('favour_' + id);

    if (favour.value > 0) {
        favour.value = parseInt(favour.value) - 1;
    }
}

function favourReset(id) {
    var favour = document.getElementById('favour_' + id);
    favour.value = 0;
}
</script>
<table class="form">
<form action="index.php?act=articles_add&mode=forms<?php print isset($_REQUEST["edit"]) ? "&edit=1&id=" . $_REQUEST["id"] : ""?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="count" value="<?php print $_count?>" />
    <input type="hidden" name="referer" value="<?php print $_POST["referer"]?>" />
<?php
    for ($link_num = 1; $link_num <= $_count; $link_num++) {
?>
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["article"]?> <?php print $link_num?></td></tr>
    <tr><td class="name" colspan="2"><?php print $CNCAT["lang"]["site_title_article"]?></td></tr>
    <tr><td class="name" colspan="2"><input type="text" name="item_title[<?php print $link_num?>]" value="<?php print htmlspecialchars($_POST['item_title'][$link_num])?>" class="text" /></td></tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr><td colspan="2" class="name"><?php print $CNCAT["lang"]["descr_article"]?></td></tr>
    <tr><td colspan="2" class="name">
<?php
if ($CNCAT["config"]["add_article_short_wysiwyg"]) {
    print $CNCAT_ENGINE->render->renderTextEditor("item_descr[" . $link_num . "]", $_POST['item_descr'][$link_num]);
} else {
?>
    <textarea name="item_descr[<?php print $link_num?>]" style="width: 100%;"><?php print htmlspecialchars($_POST['item_descr'][$link_num])?></textarea>
<?php
}
?>
    </td></tr>
    <tr><td colspan="2" class="name"><?php print $CNCAT["lang"]["descr_full_article"]?></td></tr>
    <tr><td colspan="2" class="name">
<?php
print $CNCAT_ENGINE->render->renderTextEditor("item_descr_full[" . $link_num . "]", $_POST['item_descr_full'][$link_num]);
?>
    </td></tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr><td class="name" colspan="2"><?php print $CNCAT["lang"]["categories"]?></td></tr>
    <tr>
        <td class="name" colspan="2">
<?php
    $cats = renderCats("cats[" . $link_num . "][]", $_POST["cats"][$link_num], $cats_count, $_item_cats);

    if ($cats_count) {
        print "<div class=\"over_box\" style=\"height: 300px;\">\n";
        print $cats;
        print "</div>\n";
    } else {
        print "<span class=\"not_found\">" . $CNCAT["lang"]["no_cats"] . ".</span>";
    }
?>
        </td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["link_url_article"]?></td>
        <td class="field"><input type="text" name="link_url[<?php print $link_num?>]" value="<?php print htmlspecialchars($_POST['link_url'][$link_num])?>" class="text" /></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["back_link_url"]?></td>
        <td class="field"><input type="text" name="link_back_link_url[<?php print $link_num?>]" value="<?php print htmlspecialchars($_POST['link_back_link_url'][$link_num])?>" class="text" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["email"]?></td>
        <td class="field"><input type="text" name="item_author_email[<?php print $link_num?>]" value="<?php print htmlspecialchars($_POST['item_author_email'][$link_num])?>" class="text" /></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["author"]?></td>
        <td class="field"><input type="text" name="item_author_name[<?php print $link_num?>]" value="<?php print htmlspecialchars($_POST['item_author_name'][$link_num])?>" class="text" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["meta_keywords"]?></td>
        <td class="field"><input type="text" name="item_meta_keywords[<?php print $link_num?>]" value="<?php print htmlspecialchars($_POST['item_meta_keywords'][$link_num])?>" class="text" /></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["meta_descr"]?></td>
        <td class="field"><input type="text" name="item_meta_descr[<?php print $link_num?>]" value="<?php print htmlspecialchars($_POST['item_meta_descr'][$link_num])?>" class="text" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["jumps_from"]?> / <?php print $CNCAT["lang"]["jumps_to"]?></td>
        <td class="field">
            <input type="text" name="link_jumps_from[<?php print $link_num?>]" value="<?php print (int)$_POST["link_jumps_from"][$link_num]?>" class="text" style="width: 100px;" />
            <input type="text" name="link_jumps_to[<?php print $link_num?>]" value="<?php print (int)$_POST["link_jumps_to"][$link_num]?>" class="text" style="width: 100px;" />
        </td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["pr"]?> / <?php print $CNCAT["lang"]["cy"]?></td>
        <td class="field">
            <input type="text" name="link_rating_pr[<?php print $link_num?>]" value="<?php print (int)$_POST["link_rating_pr"][$link_num]?>" class="text" style="width: 100px;" />
            <input type="text" name="link_rating_cy[<?php print $link_num?>]" value="<?php print (int)$_POST["link_rating_cy"][$link_num]?>" class="text" style="width: 100px;" />
        </td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["moder_rating"]?></td>
        <td class="field">
            <select name="item_rating_moder[<?php print $link_num?>]">
            <?php
            for ($i = 0; $i <= 10; $i++) {
                print '<option value="' . $i . '"' . ($_POST["item_rating_moder"][$link_num] == $i ? " selected=\"selected\"" : "") . '>' . $i . '</option>';
            }
            ?>
            </select>
        </td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <?php print renderFilters("fils[" . $link_num . "]", $_POST["fils"][$link_num])?>
    <?php print renderExtfields($link_num)?>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["link_target_type"]?></td>
        <td class="field">
            <select name="link_target[<?php print $link_num?>]">
                <option value="0" <?php print $_POST["link_target"][$link_num] == 0 ? "selected=\"selected\"" : "";?> ><?php print $CNCAT["lang"]["deafult_as_in_settings"]?></option>
                <option value="1" <?php print $_POST["link_target"][$link_num] == 1 ? "selected=\"selected\"" : "";?> ><?php print $CNCAT["lang"]["target_direct"]?></option>
                <option value="2" <?php print $_POST["link_target"][$link_num] == 2 ? "selected=\"selected\"" : "";?> ><?php print $CNCAT["lang"]["target_redirect"]?></option>
                <option value="3" <?php print $_POST["link_target"][$link_num] == 3 ? "selected=\"selected\"" : "";?> ><?php print $CNCAT["lang"]["target_js_redirect"]?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["article_favour_level"]?></td>
        <td class="field">
            <input type="text" name="item_favour[<?php print $link_num?>]" id="favour_<?php print $link_num?>" value="<?php print (int)$_POST['item_favour'][$link_num]?>" class="text" style="width: 40px;" readonly="readonly" />
            <input type="button" value="+" onclick="favourUp(<?php print $link_num?>)" class="submit" />
            <input type="button" value="0" onclick="favourReset(<?php print $link_num?>)" class="submit" />
            <input type="button" value="-" onclick="favourDown(<?php print $link_num?>)" class="submit" />
        </td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["status"]?></td>
        <td class="field">
            <label><input type="radio" name="item_status[<?php print $link_num?>]" value="0" <?php print $_POST["item_status"][$link_num] == 0 ? "checked=\"checked\"" : "";?> /> <?php print $CNCAT["lang"]["links_0"]?></label>
            <label><input type="radio" name="item_status[<?php print $link_num?>]" value="1" <?php print $_POST["item_status"][$link_num] == 1 ? "checked=\"checked\"" : "";?> /> <?php print $CNCAT["lang"]["links_1"]?></label>
            <label><input type="radio" name="item_status[<?php print $link_num?>]" value="2" <?php print $_POST["item_status"][$link_num] == 2 ? "checked=\"checked\"" : "";?> /> <?php print $CNCAT["lang"]["links_2"]?></label>
        </td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["send_mail_notify"]?></td>
        <td class="field">
            <input type="checkbox" name="mail_notify" <?php print !empty($_POST["mail_notify"][$link_num]) ? "checked=\"checked\"" : "";?> />
        </td>
    </tr>
<?php
    }
?>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="submit" colspan="2">
            <?php if ($_is_edit) {?>
                <input type="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_save"]?>" class="submit" <?php print !$cats_count ? "disabled=\"disabled\"" : ""?> />
            <?php } else {?>
                <input type="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_submit"]?>" class="submit" <?php print !$cats_count ? "disabled=\"disabled\"" : ""?> />
            <?php }?>
            <input type="button" value="<?php print $CNCAT["lang"]["do_cancel"]?>" class="submit" onclick="location.href='<?php print ($_is_edit && !empty($_POST["referer"])) ? htmlspecialchars($_POST["referer"]) : "index.php?act=articles_add"?>'" />
        </td>
    </tr>
</form>
</table>
<?php
} else {
    $cats_count = 0;
    $cats = renderCats("cats[]", array(), $cats_count);

    if (!$cats_count) {
?>
<div class="error_box">
    <?php print $CNCAT["lang"]["before_add_create_cat"]?>
</div>
<?php
    }
?>
<table class="form">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["options"]?></td></tr>
<form action="index.php?act=articles_add&mode=forms" method="post">
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["articles_count"]?></td>
        <td class="field"><input type="text" class="text" name="count" value="1" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr><td class="name" colspan="2"><?php print $CNCAT["lang"]["categories"]?></td></tr>
    <tr><td class="name" colspan="2">
<?php
    if ($cats_count) {
        print "<div class=\"over_box\" style=\"height: 300px;\">\n";
        print $cats;
        print "</div>\n";
    } else {
        print "<span class=\"not_found\">" . $CNCAT["lang"]["no_cats"] . "</span>";
    }
?>
    </td></tr>
    <tr><td class="deline" colspan="2"></td></tr>
<?php
    print renderFilters("fils", array());
?>
    <tr>
        <td class="name" colspan="2">
            <input type="radio" name="item_status" id="status0" value="0" /> <label for="status0"><?php print $CNCAT["lang"]["links_0"]?></label>
            <input type="radio" name="item_status" id="status1" value="1" checked="checked" /> <label for="status1"><?php print $CNCAT["lang"]["links_1"]?></label>
        </td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td colspan="2" class="submit">
            <input type="submit" value="<?php print $CNCAT["lang"]["do_continue"]?>" class="submit" <?php print !$cats_count ? "disabled=\"disabled\"" : ""?> />
        </td>
    </tr>
</form>
</table>
<?php
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";

function renderCats($field_name, $sel_cats = array(), &$count, $item_cats = array()) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    static $cats = false;
    $result = "";

    if ($cats === false) {
        $cats = array();

        $query = "SELECT `id`, `title`, `tree_level`
            FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats` WHERE `parent_id`!=-1 AND `is_link`=0
            " . (isModer() ? "AND `id` IN (" . join(",", getModerCats()) . ")": "") . "
             ORDER BY `title_full`";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
        while ($row = mysql_fetch_assoc($res)) {
            $cats[] = $row;
        }

        $count = count($cats);
    }

    $level = 0;

    foreach ($cats as $cat) {
        while ($cat["tree_level"] > $lastLevel) {
            $result .= "<ul>\n";
            $lastLevel++;
        }   

        while ($cat["tree_level"] < $lastLevel) {
            $result .= "</ul>\n";
            $lastLevel--;
        }

        $result .= "<li><input type=\"checkbox\" name=\"" . $field_name . "\" id=\"cat" . $cat["id"] . "\" value=\"" . $cat["id"] . "\" ";
        $result .= in_array($cat["id"], (array)$sel_cats) ? "checked=\"checked\"" : "";
        $result .= " /><label for=\"cat" . $cat["id"] . "\">\n";
        if (in_array($cat["id"], (array)$item_cats)) {
            $result .= "<span style=\"background: #eee; padding: 2px;\">";
        }
        $result .= htmlspecialchars($cat["title"]);
        if (in_array($cat["id"], (array)$item_cats)) {
            $result .= "</span>";
        }
        $result .= "</label></li>\n";
    }

    return $result;
}

function renderFilters($field_name, $sel_fils = array()) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    static $filters = false;
    $result = "";

    if ($filters === false) {
        $filters = array();

        $query = "SELECT
                    f.id `fid`, f.title `ftitle`, f.sort_order `forder`, f.required,
                    v.id `vid`, v.title `vtitle`, v.sort_order `vorder`, v.filter_id
            FROM `" . $CNCAT["config"]["db"]["prefix"] . "filters` f
            LEFT JOIN `" . $CNCAT["config"]["db"]["prefix"] . "filtvals` v
            ON (v.filter_id=f.id)";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $filters = array();
        
        while ($row = mysql_fetch_assoc($res)) {
            if (!isset($filters[$row["fid"]])) {
                $filters[$row["fid"]] = array(
                    'title' => $row["ftitle"],
                    'sort_order' => $row["forder"],
                    'required' => $row["required"],
                    'values' => array()
                );
            }
        
            if ($row["vid"]) {
                $filters[$row["fid"]]["values"][$row["vid"]] = array(
                    'title' => $row["vtitle"],
                    'sort_order' => $row["vorder"]
                );
            }
        }
    }

    foreach ($filters as $filter_id => $filter) {
        $result .= "<tr><td class=\"name\">" . htmlspecialchars($filter["title"]) . "</td><td class=\"field\">";
        
        foreach ($filter["values"] as $value_id => $value) {
            $result .= "<input type=\"checkbox\" name=\"" . $field_name . "[]\" id=\"filtval" . $value_id . "\" value=\"" . $value_id . "\" " . (in_array($value_id, (array)$sel_fils) ? "checked=\"checked\"" : "") . " /> <label for=\"filtval" . $value_id . "\">" . htmlspecialchars($value["title"]) . "</label><br />";
        }

        $result .= "</td></tr>";
        $result .= "<tr><td class=\"deline\" colspan=\"2\"></td></tr>";
    }

    return $result;
}

function renderExtfields($num) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $result = "";

    foreach ($CNCAT["config"]["extfields"]["items"][1] as $name => $field) {
        if (!$field["active"]) {
            continue;
        }

        $result .= "<tr><td class=\"name\">" . htmlspecialchars($field["title"]) . "</td><td class=\"field\">";

        switch ($field["type"]) {
            case 1: case 2: case 3: $result .= "<input type=\"text\" name=\"" . $name  . "[" . $num . "]\" class=\"text\" value=\"" . htmlspecialchars($_POST[$name][$num]) . "\" />"; break;
            case 4:
                // convert db datetime to cncat datetime
                // 2007-11-01 09:50:0 => 09:50 01.11.2007
                if (preg_match("/^(\d+)-(\d+)-(\d+)/", $_POST[$name][$num], $m)) {
                    $_POST[$name][$num] = $m[3] . '.' . $m[2] . '.' . $m[1];
                }

                $result .= "<div id=\"" . $name . "_" . $num . "_calendar\" class=\"calendar\"></div>";
                $result .= "<input type=\"text\" class=\"text\" style=\"width: 70%;\" name=\"" . $name  . "[" . $num . "]\" id=\"" . $name  . "_" . $num . "\" size=\"18\" value=\"" . htmlspecialchars($_POST[$name][$num]) . "\" readonly=\"readonly\" />";
                $result .= ' <a href="" onclick="ShowCalendarE(\'' . $name . '_' . $num . '_calendar\', \'' . $name . '_' . $num . '\', 0); return false;"><img src="' . $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"] . 'calendar.gif" alt="" style="vertical-align: middle;" /></a>';
                $result .= ' <a href="" onclick="document.getElementById(\'' . $name . '_' . $num . '\').value=\'\'; return false;"><img src="' . $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"] . 'delete.gif" alt="" style="vertical-align: middle;" /></a>';
                break;
            case 5: $result .= "<textarea name=\"" . $name  . "[" . $num . "]\">" . htmlspecialchars($_POST[$name][$num]) . "</textarea>"; break;
            case 6: {
                $item_id = isset($_POST["item_id"][$num]) ? $_POST["item_id"][$num] : 0;

                if ($item_id && !empty($_POST[$name . "_mime"][$num])) {
                    $result .= "<img src=\"" . $CNCAT["abs"] . "{$CNCAT["system"]["dir_prefix"]}image.php?item=" . $item_id . "&thumb=1&field=" . $name . "\" alt=\"\" />";
                    $result .= "<br /><input type=\"checkbox\" name=\"" . $name . "_delete\" id=\"" . $name . "_delete\" /> <label for=\"" . $name . "_delete\">" . $CNCAT["lang"]["do_delete"] . "</label><br />";
                }

                $result .= "<input type=\"file\" name=\"" . $name  . "[" . $num . "]\" />"; break;
            }
        }

        $result .= "</td></tr>";
        $result .= "<tr><td class=\"deline\" colspan=\"2\"></td></tr>";
    }

    return $result;
}
?>
