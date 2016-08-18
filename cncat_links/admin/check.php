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

require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "sync_all.php";
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "check.php";
$CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], "admin");

unset($_SESSION["global_sql_cond"]);
unset($_SESSION["global_referer"]);

$_mode = $_GET["mode"];

if ($_mode == "checkone") {
/*******************************************************************************
 * CHECK ONE LINK BEGIN
 ******************************************************************************/
    session_write_close();
    $CNCAT["admin"]["ajax_replace"] = true;
 
    $_item_id = (int)$_GET["id"];

    $query = "SELECT `item_id`, `item_title`, `link_url`, `link_back_link_url` FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
        WHERE `item_id` =" . $_item_id;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    if ($row = mysql_fetch_assoc($res)) {
        $CNCAT["system"]["log"]["item_id"] = $row["item_id"];

        $check = cn_checkLink(
            $row["link_url"],
            $CNCAT["config"]["check_link"],
            $CNCAT["config"]["check_back_link"],
            $CNCAT["config"]["check_pr"],
            $CNCAT["config"]["check_cy"],
            $CNCAT["config"]["check_max_timeout"],
            $CNCAT["config"]["check_max_redirect"]
        );

        if ($CNCAT["config"]["check_back_link"] && !empty($row["link_back_link_url"])) {
            $check2 = cn_checkLink(
                $row["link_back_link_url"],
                1,
                1,
                0,
                0,
                $CNCAT["config"]["check_max_timeout"],
                $CNCAT["config"]["check_max_redirect"]
            );

            $check["back"] = $check2["back"];
        }

        $fields = array();
        $fields[] = "`last_check`=NOW()";
        if ($CNCAT["config"]["check_link"]) {
            $fields[] = "`link_chk_work_res`=" . $check["work"];
            $fields[] = "`link_chk_work_date`=NOW()";
        }

        if ($CNCAT["config"]["check_back_link"]) {
            $fields[] = "`link_chk_back_res`=" . $check["back"];
            $fields[] = "`link_chk_back_date`=NOW()";
        }

        if ($CNCAT["config"]["check_pr"]) {
            $fields[] = "`link_rating_pr`=" . $check["pr"];
            $fields[] = "`link_rating_pr_neg`=" . -$check["pr"];
            $fields[] = "`link_pr_date`=NOW()";
        }

        if ($CNCAT["config"]["check_cy"]) {
            $fields[] = "`link_rating_cy`=" . $check["cy"];
            $fields[] = "`link_rating_cy_neg`=" . -$check["cy"];
            $fields[] = "`link_cy_date`=NOW()";
        }

        if ($CNCAT["config"]["check_favicon"]) {
            if (!empty($check["favicon"]["data"])) {
                $fields[] = "`link_favicon`='" . mysql_escape_string($check["favicon"]["data"]) . "'";
                $fields[] = "`link_favicon_mime`='" . mysql_escape_string($check["favicon"]["mime"]) . "'";
            } else {
                $fields[] = "`link_favicon`=''";
                $fields[] = "`link_favicon_mime`=''";
            }

            $fields[] = "`link_favicon_url`='" . mysql_escape_string($check["favicon"]["url"]) . "'";
        }

        if ($fields) {
            $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items` SET
                " . join(",", $fields) . "
                WHERE `item_id`=" . $_item_id;
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }

        $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
        $query = "
            SELECT " . join(", ", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
            WHERE `item_id` =" . $_item_id . "
        ";
    
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
        $item = mysql_fetch_assoc($res);
        $items = array();

        if ($item) {
            $items[$item['item_id']] = $item;
            appendCats($items);
            appendCheck($items);
            
            print renderItem($items[$item['item_id']]);
                }
    }
    
    exit;

/*******************************************************************************
 * CHECK ONE LINK END
 ******************************************************************************/
} elseif ($_mode == "check") {
/*******************************************************************************
 * CHECK LIST BEGIN
 ******************************************************************************/
    $time_limit = (int)$_GET["tl"];
    $item_limit = (int)$_GET["il"];

    if ($time_limit < 15) {
        $time_limit = 15;
    }

    if ($item_limit < 1) {
        $item_limit = 1;
    }

    $check_link = !empty($_GET["link"]);
    $check_back = !empty($_GET["back"]);
    $check_cy = !empty($_GET["cy"]);
    $check_pr = !empty($_GET["pr"]);

    if ($check_link || $check_back || $check_cy || $check_pr) {
    //
    if ($check_back && !$check_link) {
        $check_link = true;
    }

    $approve = (int)$_GET["apr"];

    if ($approve < 0) {
        $approve = 0;
    } elseif ($approve > 2) {
        $approve = 2;
    }

    $_url = "index.php?act=check&mode=check&link=" . $_GET["link"] . "&back=" . $_GET["back"] . "&pr=" . $_GET["cy"] . "&cy=" . $_GET["pr"] . "&il=" . $_GET["il"] . "&tl=" . $_GET["tl"] . "&apr=" . $_GET["apr"] . "&rand=" . md5(time());

    $query = "SELECT item_id FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
        WHERE `moder_id`=" . (isAdmin() ? 0 : getModerId()) . " AND `check_flag`=0
        ORDER BY `item_id` DESC
        LIMIT " . (isset($_GET["skip"]) ? 1 : 0) . ", " . $item_limit;
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $items_id = array();

    while ($row = mysql_fetch_assoc($res)) {
        $items_id[] = $row["item_id"];
    }

    $items_count = 0;
    $chk_count = 0;
    $chk_need_count = 0;

    if ($items_id) {
        $items_count = count($items_id);

        $query = "SELECT COUNT(*) AS `count` FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
            WHERE `moder_id`=" . (isAdmin() ? 0 : getModerId()) . " AND `check_flag`=0";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $row = mysql_fetch_assoc($res);
        $chk_need_count = $row["count"];

        $query = "SELECT COUNT(*) AS `count` FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
            WHERE `moder_id`=" . (isAdmin() ? 0 : getModerId()) . " AND `check_flag`=1";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $row = mysql_fetch_assoc($res);
        $chk_count = $row["count"];
    }

    if ($items_count) {
        @set_time_limit($time_limit);
        $max_exec_time = ini_get("max_execution_time");

        if ($max_exec_time > $time_limit) {
            $max_exec_time = $time_limit;
        }

        $max_exec_time -= 5;
?>
<html>
    <head>
        <title><?php print $CNCAT["lang"]["links"]?> / <?php print $CNCAT["lang"]["links_check"]?></title>
        <style type="text/css">
            body {
                background: white;
                padding: 5px;
                margin: 5px;
            }
            body, p {
                font-family:tahoma; font-size:11px; color:#38464b;
            }
            p {
                margin: 8px 10px;
            }
            a {
                color:#3176b1;
            }
            h1 {
                color:#3176b1;font-size:12pt;
            }
            a img {
                border: 0;
            }
            .title {
                background:#e8f0f4;border:1px solid #c2cdd1;padding:4px;
                color:black;font-weight:bold;
                white-space: nowrap;
            }
            .deline {
                border-bottom: 1px dashed #c2cdd1;
                margin: 10px 0;
            }
        </style>
    </head>
<body>
    <script type="text/javascript">
        function windowRefresh() {
            document.location.href = '<?php print $_url?>';
        }

        window.setTimeout('windowRefresh()', <?php print $max_exec_time * 1000?>);
    </script> 
    <h1><?php print $CNCAT["lang"]["links_check"]?></h1>
    <p><a href="index.php?act=check&mode=report"><?php print $CNCAT["lang"]["stop_check"]?></a></p>
    <p>
        <?php print $CNCAT["lang"]["already_check"]?>: <strong id="chk_count"><?php print $chk_count?></strong><br />
        <?php print $CNCAT["lang"]["need_check"]?>: <strong id="chk_need_count"><?php print $chk_need_count?></strong>
    </p>
    <?php print $CNCAT_ENGINE->render->renderProgressBar(0, ($chk_need_count + $chk_count), $chk_count)?>
    <div class="title"><?php print $CNCAT["lang"]["check_result"]?></div>
<?php
        flush();
        ob_flush();

        $query = "SELECT `item_id`, `item_status`, `item_title`, `link_url`, `link_back_link_url` FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
            WHERE `item_id` IN (" . join(",", $items_id) . ")";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $chk_counter = 0;

        while ($row = mysql_fetch_assoc($res)) {
            $chk_counter++;
?>
    <div style="padding: 5px 10px;"><strong><?php print $row["item_title"]?></strong> (ID: <strong><?php print $row["item_id"]?></strong>)</div>
    <div style="padding: 0 10px;">
    <a href="<?php print $row["link_url"]?>"><?php print htmlspecialchars($row["link_url"])?></a>
<?php
            flush();
            ob_flush();

            $CNCAT["system"]["log"]["item_id"] = $row["item_id"];
            $check = cn_checkLink($row["link_url"], $check_link, $check_back, $check_pr, $check_cy, $CNCAT["config"]["check_max_timeout"], $CNCAT["config"]["check_max_redirect"]);

            if ($check_back && !empty($row["link_back_link_url"])) {
                $check2 = cn_checkLink(
                    $row["link_back_link_url"],
                    1,
                    1,
                    0,
                    0,
                    $CNCAT["config"]["check_max_timeout"],
                    $CNCAT["config"]["check_max_redirect"]
                );
        
                $check["back"] = $check2["back"];
            }
?>
    <?php print $check_cy || $check_pr ? "[" : ""?>
    <?php if ($check_cy) {?>
        CY: <strong><?php print $check["cy"] == -1 ? "?" : $check["cy"]?></strong>
    <?php }?>
    <?php if ($check_pr) {?>
        PR: <strong><?php print $check["pr"] == -1 ? "?" : $check["pr"]?></strong>
    <?php }?>
    <?php print $check_cy || $check_pr ? "]" : ""?>
    </div>
    <div style="padding: 5px 20px;">
        <?php if ($check_link) {?>
        <?php print $CNCAT["lang"]["link_work"]?>: <strong><span style="color:<?php print $check["work"] ? "green;\">" . $CNCAT["lang"]["yes"] : "red;\">" . $CNCAT["lang"]["no"]?></span></strong><br />
        <?php }?>
        <?php if ($check_back) {?>
        <?php print $CNCAT["lang"]["link_back_exists"]?>: <strong><span style="color:<?php print $check["back"] ? "green;\">" . $CNCAT["lang"]["yes"] : "red;\">" . $CNCAT["lang"]["no"]?></span></strong>
        <?php }?>
    </div>
    <div class="deline"></div>
    <script type="text/javascript">
    document.getElementById('progress_per').innerHTML = '<strong><?php print floor((100 / ($chk_need_count + $chk_count)) * ($chk_count + $chk_counter))?>%</strong>';
    document.getElementById('chk_count').innerHTML = '<?php print $chk_count + $chk_counter?>';
    document.getElementById('chk_need_count').innerHTML = '<?php print $chk_need_count - $chk_counter?>';
    document.getElementById('progress_bar').style.width = '<?php print floor((100 / ($chk_need_count + $chk_count)) * ($chk_count + $chk_counter))?>%';
    </script>
<?php
            flush();
            $fields = array();
            $fields[] = "`last_check`=NOW()";
            if ($check_link) {
                $fields[] = "`link_chk_work_res`=" . $check["work"];
                $fields[] = "`link_chk_work_date`=NOW()";
            }

            if ($check_back) {
                $fields[] = "`link_chk_back_res`=" . $check["back"];
                $fields[] = "`link_chk_back_date`=NOW()";
            }

            if ($check_pr) {
                $fields[] = "`link_rating_pr`=" . $check["pr"];
                $fields[] = "`link_rating_pr_neg`=" . -$check["pr"];
                $fields[] = "`link_pr_date`=NOW()";
            }

            if ($check_cy) {
                $fields[] = "`link_rating_cy`=" . $check["cy"];
                $fields[] = "`link_rating_cy_neg`=" . -$check["cy"];
                $fields[] = "`link_cy_date`=NOW()";
            }

            if ($CNCAT["config"]["check_favicon"]) {
                if (!empty($check["favicon"]["data"])) {
                    $fields[] = "`link_favicon`='" . mysql_escape_string($check["favicon"]["data"]) . "'";
                    $fields[] = "`link_favicon_mime`='" . mysql_escape_string($check["favicon"]["mime"]) . "'";
                } else {
                    $fields[] = "`link_favicon`=''";
                    $fields[] = "`link_favicon_mime`=''";
                }

                $fields[] = "`link_favicon_url`='" . mysql_escape_string($check["favicon"]["url"]) . "'";
            }

            if ($approve == 1) {
                if ($check["work"]) {
                    $fields[] = "`item_status`=1";
                    $fields[] = "`item_submit_type`=3";

                    if ($row['item_status'] != 1) {
                        $CNCAT_ENGINE->misc->sendMailApprove($row['item_id']);
                    }
                }
            } elseif ($approve == 2) {
                if ($check["work"] && $check["back"]) {
                    $fields[] = "`item_status`=1";
                    $fields[] = "`item_submit_type`=3";

                    if ($row['item_status'] != 1) {
                        $CNCAT_ENGINE->misc->sendMailApprove($row['item_id']);
                    }
                }
            }

            if ($fields) {
                $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items` SET
                    " . join(",", $fields) . "
                    WHERE `item_id`=" . $row["item_id"];
                $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error()); 
    
                $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
                    SET `check_flag`=1
                    WHERE `item_id`=" . $row["item_id"];
                $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error()); 
            }
        }
        flush();
        ob_flush();
?>
    <script type="text/javascript">
        windowRefresh();
    </script>
</body>
</html>
<?php
        exit;
    } else {
        cn_syncAll();
        header("Location: index.php?act=check&mode=report");
        exit;
    }
    //
    }
/*******************************************************************************
 * CHECK LIST END
 ******************************************************************************/
} elseif ($_mode == "sendmail") {
/*******************************************************************************
 * CHECK LIST BEGIN
 ******************************************************************************/
    $mail_subject = isset($_POST['mail_subject']) ? $_POST['mail_subject'] : false;
    $mail_body = isset($_POST['mail_body']) ? $_POST['mail_body'] : false;

    if ($mail_subject === false || $mail_body === false) {
        $mail_subject = !empty($_SESSION['mail_subject']) ? $_SESSION['mail_subject'] : '';
        $mail_body = !empty($_SESSION['mail_body']) ? $_SESSION['mail_body'] : '';
    } else {
        $_SESSION['mail_subject'] = $mail_subject;
        $_SESSION['mail_body'] = $mail_body;

        $time_limit = intval($_POST["tl"]);
        $item_limit = intval($_POST["il"]);

        header("Location: index.php?act=check&mode=sendmail&tl=" .$time_limit . "&il=" . $item_limit . "&rand=" . md5(time()));
        exit;
    }

    $time_limit = intval($_GET["tl"]);
    $item_limit = intval($_GET["il"]);

    if ($time_limit < 15) {
        $time_limit = 15;
    }

    if ($item_limit < 1) {
        $item_limit = 1;
    }

    if ($check_back && !$check_link) {
        $check_link = true;
    }

    $_url = "index.php?act=check&mode=sendmail&tl=" . $time_limit . "&il=" . $item_limit . "&rand=" . md5(time());

    $query = "
        SELECT item_id FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
        WHERE `moder_id`=0 AND `check_flag`=0
        ORDER BY `item_id` DESC
        LIMIT 0, " . $item_limit . "
    ";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $items_id = array();

    while ($row = mysql_fetch_assoc($res)) {
        $items_id[] = $row["item_id"];
    }

    $items_count = 0;
    $chk_count = 0;
    $chk_need_count = 0;

    if ($items_id) {
        $items_count = count($items_id);

        $query = "
            SELECT COUNT(*) AS `count` FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
            WHERE `moder_id`=0 AND `check_flag`=0
        ";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $row = mysql_fetch_assoc($res);
        $chk_need_count = $row["count"];

        $query = "
            SELECT COUNT(*) AS `count` FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
            WHERE `moder_id`=0 AND `check_flag`=1
        ";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $row = mysql_fetch_assoc($res);
        $chk_count = $row["count"];
    }

    if ($items_count) {
        @set_time_limit($time_limit);
        $max_exec_time = ini_get("max_execution_time");

        if ($max_exec_time > $time_limit) {
            $max_exec_time = $time_limit;
        }

        $max_exec_time -= 5;
?>
<html>
    <head>
        <title><?php print $CNCAT["lang"]["delivery_proccess"]?></title>
        <style type="text/css">
            body {
                background: white;
                padding: 5px;
                margin: 5px;
            }
            body, p {
                font-family:tahoma; font-size:11px; color:#38464b;
            }
            p {
                margin: 8px 10px;
            }
            a {
                color:#3176b1;
            }
            h1 {
                color:#3176b1;font-size:12pt;
            }
            a img {
                border: 0;
            }
            .title {
                background:#e8f0f4;border:1px solid #c2cdd1;padding:4px;
                color:black;font-weight:bold;
                white-space: nowrap;
            }
            .deline {
                border-bottom: 1px dashed #c2cdd1;
                margin: 10px 0;
            }
        </style>
    </head>
<body>
    <script type="text/javascript">
    function windowRefresh() {
        document.location.href = '<?php print $_url?>';
    }

    window.setTimeout('windowRefresh()', <?php print $max_exec_time * 1000?>);
    </script> 
    <h1><?php print $CNCAT["lang"]["delivery_proccess"]?></h1>
    <p><a href="index.php?act=check&mode=report"><?php print $CNCAT["lang"]["stop_check"]?></a></p>
    <p>
        <?php print $CNCAT["lang"]["already_send"]?>: <strong id="chk_count"><?php print $chk_count?></strong><br />
        <?php print $CNCAT["lang"]["need_send"]?>: <strong id="chk_need_count"><?php print $chk_need_count?></strong>
    </p>
    <?php print $CNCAT_ENGINE->render->renderProgressBar(0, ($chk_need_count + $chk_count), $chk_count)?>
    <div class="title"><?php print $CNCAT["lang"]["mail_send_result"]?></div>
<?php
        flush();
        ob_flush();

        $query = "
            SELECT *
            FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
            WHERE `item_id` IN (" . join(",", $items_id) . ")
        ";
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $chk_counter = 0;

        while ($row = mysql_fetch_assoc($res)) {
            $chk_counter++;
?>
    <div style="padding: 5px 10px;"><strong><?php print $row["item_title"]?></strong> (ID: <strong><?php print $row["item_id"]?></strong>)</div>
    <script type="text/javascript">
    document.getElementById('progress_per').innerHTML = '<strong><?php print floor((100 / ($chk_need_count + $chk_count)) * ($chk_count + $chk_counter))?>%</strong>';
    document.getElementById('chk_count').innerHTML = '<?php print $chk_count + $chk_counter?>';
    document.getElementById('chk_need_count').innerHTML = '<?php print $chk_need_count - $chk_counter?>';
    document.getElementById('progress_bar').style.width = '<?php print floor((100 / ($chk_need_count + $chk_count)) * ($chk_count + $chk_counter))?>%';
    </script>
    <div style="padding: 0 10px;">
<?php
            flush();

            $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
                SET `check_flag`=1
                WHERE `item_id`=" . $row["item_id"];
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error()); 

            $CNCAT["item"] = $row;

            $mail_to = $CNCAT["item"]["item_author_email"];

            if (!empty($mail_to)) {
                $reply_to = "";
                $mail_from = $CNCAT["config"]["admin_email"];
                $title = $CNCAT["config"]["use_translit"]? $row["item_title_translite"]: $row["item_title"]; 
        
                $CNCAT["item"]["_ext_url"] = "http://" . $_SERVER["HTTP_HOST"] . $CNCAT_ENGINE->url->createUrlExt($row["item_id"], $title);
                $mail_body = cn_str_replace("{DISPLAY BACKLINKS}", $CNCAT_ENGINE->misc->getBacklinks($row["item_id"]), $mail_body);
                $mail_body = $CNCAT_ENGINE->tpl->renderTemplateString("mail_body", $mail_body);
                $mail_body = cn_str_replace("%CATNAME%", $CNCAT["config"]["catalog_title"], $mail_body);

                $mail_subject = $CNCAT_ENGINE->tpl->renderTemplateString("mail_subject", $mail_subject);
                $mail_subject = cn_str_replace("%CATNAME%", $CNCAT["config"]["catalog_title"], $mail_subject);
    
                @mail(
                    $mail_to,
                    $CNCAT_ENGINE->misc->qp_enc($mail_subject),
                    $mail_body,
                    (!empty($mail_from) ? ("From: " . $CNCAT_ENGINE->misc->qp_enc($CNCAT["config"]["catalog_title"]) . " <" . $mail_from . ">\r\n") : "") .
                    (!empty($reply_to) ? ("Reply-to: " . $mail_reply_to . "\r\n") : "")
                    . "Content-type: text/plain; charset=" . $CNCAT["lang"]["charset"] . "\r\n"
                );

                print "<a href=\"mailto:" . htmlspecialchars($row["item_author_email"]) . "\">" . htmlspecialchars($row["item_author_email"]) . "</a>";
                print "<span style=\"color: green;\">" . $CNCAT["lang"]["send"] . "</span>";
            } else {
                print "<span style=\"color: red;\">" . $CNCAT["lang"]["empty_email"] . "</span>";
            }

            print "</div>";
            print "<div class=\"deline\"></div>";
        }

        flush();
        ob_flush();
?>
    <script type="text/javascript">
        windowRefresh();
    </script>
</body>
</html>
<?php
        exit;
    } else {
        cn_syncAll();
        header("Location: index.php?act=check&mode=report");
        exit;
    }

/*******************************************************************************
 * CHECK LIST END
 ******************************************************************************/
} elseif ($_mode == "listclear") {
/*******************************************************************************
 * CLEAR LIST BEGIN
 ******************************************************************************/
    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
        WHERE `moder_id`=" . (isModer() ? getModerId() : 0) . " AND `check_flag`=0";
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=check");
    exit;
/*******************************************************************************
 * CLEAR LIST END
 ******************************************************************************/
} elseif ($_mode == "listcheck") {
/*******************************************************************************
 * PREPARE TO LIST CHECK BEGIN
 ******************************************************************************/
    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
        WHERE `moder_id`=" . (isModer() ? getModerId() : 0) . " AND `check_flag`=1";
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=check&mode=check&link=" . $_GET["link"] . "&back=" . $_GET["back"] . "&pr=" . $_GET["pr"] . "&cy=" . $_GET["cy"] . "&il=" . $_GET["il"] . "&tl=" . $_GET["tl"] . "&apr=" . $_GET["apr"] . "&rand=" . $_GET["rand"]);
    exit;
/*******************************************************************************
 * PREPARE TO LIST CHECK END
 ******************************************************************************/
} elseif ($_mode == "reportdel") {
/*******************************************************************************
 * DELETE REPORT BEGIN
 ******************************************************************************/
    $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
        WHERE `moder_id`=" . (isModer() ? getModerId() : 0) . " AND `check_flag`=1";
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=check");
    exit;
/*******************************************************************************
 * DELETE REPORT END
 ******************************************************************************/
}

if ($_mode == "report") {
/*******************************************************************************
 * REPORT BEGIN
 ******************************************************************************/
    $_type = (int)$_GET["type"];
    $_page = (int)$_GET["page"];

    if (!isset($_REQUEST["sort"])) {
        $_sort = 3;
    } else {
        $_sort = (int)$_REQUEST["sort"];
    }

    $_dir = (int)$_GET["dir"];

    // 0 - all links
    // 1 - work links
    // 2 - not work links
    if ($_type < 0) {
        $_type = 0;
    } elseif ($_type > 2) {
        $_type = 2;
    }

    include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["proccess"]?> / <?php print $CNCAT["lang"]["report"]?></h1>
<a href="index.php?act=check"><?php print $CNCAT["lang"]["turn"]?></a>, <a href="index.php?act=check&mode=reportdel"><?php print $CNCAT["lang"]["delete_report"]?></a>
<div class="deline"></div>
<form action="index.php">
    <input type="hidden" name="act" value="check" />
    <input type="hidden" name="mode" value="report" />
    <p>
        <label><input type="radio" name="type" value="0" <?php print $_type == 0 ? "checked=\"checked\"" : ""?> /> <?php print $CNCAT["lang"]["all"]?></label>
        <label><input type="radio" name="type" value="1" <?php print $_type == 1 ? "checked=\"checked\"" : ""?> /> <?php print $CNCAT["lang"]["work"]?></label>
        <label><input type="radio" name="type" value="2" <?php print $_type == 2 ? "checked=\"checked\"" : ""?> /> <?php print $CNCAT["lang"]["not_work"]?></label>
        <label><input type="submit" class="submit" value="<?php print $CNCAT["lang"]["do_select"]?>" /></label>
    </p>
    <p><?php displaySort($_sort, $_dir)?></p>
    <input type="hidden" name="page" value="<?php print $_page?>" />
</form>
<div class="deline"></div>
<?php
    $query = "SELECT `item_id` FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
        WHERE `moder_id`=" . (isAdmin() ? 0 : (int)$_SESSION["ismoder"]) . " AND `check_flag`=1";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $_items_id = array();
    
    while ($row = mysql_fetch_assoc($res)) {
        $_items_id[] = $row["item_id"];
    }
    
    $_cond = array("item_id IN (" . join(",", $_items_id) . ")");

    if ($_type == 1) {
        $_cond[] = "link_chk_work_res=1";
    } elseif ($_type == 2) {
        $_cond[] = "link_chk_work_res=0";
    }

    $_items_count = 0;
    
    if ($_items_id) {
        $_items_count = getItemsCount($_cond);
    }

    if ($_items_count) {
        $_SESSION["global_sql_cond"] = $_cond;
        $_SESSION["global_referer"] = $_SERVER["REQUEST_URI"];

        print "<p>" . $CNCAT["lang"]["check_links_count"] . " <strong>" . $_items_count . "</strong></p>";
?>
<?php
        displayPagebar(
            array(
                "act" => "check",
                "mode" => "report",
                "type" => $_GET["type"],
                "sort" => $_GET["sort"],
                "dir" => $_GET["dir"]
            ),
            $_page, $_items_count
        );
?>
<form action="index.php" method="post">
<?php
        $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
        $query = "SELECT c.action_comm,i." . join(", i.", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items` i
        LEFT JOIN `" . $CNCAT["config"]["db"]["prefix"] . "linkact_comm` c ON c.item_id = i.item_id
        WHERE i." . join(" AND i.", $_cond) . "
        " . getSqlSort($_sort, $_dir) . "
        " . getSqlPager($_page);
        print renderItems($query);
        print $CNCAT_ENGINE->tpl->renderTemplate("admin_table_items_bottom");
        displayPagebar(
            array(
                "act" => "check",
                "mode" => "report",
                "type" => $_GET["type"],
                "sort" => $_GET["sort"],
                "dir" => $_GET["dir"]
            ),
            $_page, $_items_count
        );
?>
<?php
    } else {
        print "<span class=\"not_found\">" . $CNCAT["lang"]["no_links"] . "</span>";
    }
/*******************************************************************************
 * REPORT END
 ******************************************************************************/
} else {
/*******************************************************************************
 * CHECK LIST BEGIN
 ******************************************************************************/
    $_page = (int)$_GET["page"];
    $_sort = (int)$_GET["sort"];
    $_dir = (int)$_GET["dir"];

    $query = "SELECT `item_id` FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkcheck`
        WHERE `moder_id`=" . (isAdmin() ? 0 : getModerId()) . " AND `check_flag`=0";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $_items_id = array();
    
    while ($row = mysql_fetch_assoc($res)) {
        $_items_id[] = $row["item_id"];
    }

    $_items_count = 0;
    
    if ($_items_id) {
        $_cond = array("`item_id` IN (" . join(",", $_items_id) . ")");
        $_items_count = getItemsCount($_cond);
    }

    $CNCAT["admin"]["act"] = 'check';
    
    if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
        $query = "SELECT " . join(", ", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items` 
            WHERE " . join(" AND ", $_cond) . "
            " . getSqlSort($_sort, $_dir) . "
            LIMIT 1 OFFSET " . (($_page + 1) * $CNCAT["config"]["items_per_page_admin"] - 1);
    
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());  
        $items = array(mysql_fetch_assoc($res));

        if (!empty($items[0])) {
            appendCats($items);
            appendCheck($items);

            $items_str = renderItem($items[0]);
            $items_str = '"' . str_replace(
                array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
                array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'),
                $items_str) .
            '"';
    
            ob_start();
            displayPagebar(
                array(
                    "act" => "check",
                    "sort" => $_GET["sort"],
                    "dir" => $_GET["dir"]
                ),
                $_page, $_items_count
            );
            $pages_str = ob_get_clean();
            $pages_str = '"' . str_replace(
                array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
                array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'),
                $pages_str) .
            '"';
    
            print '[' . $items_str . ', ' . $pages_str . ']';
        } else {
            print '[]';
        }
    
        exit;
    }
    
    include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["links_and_articles"]?> / <?php print $CNCAT["lang"]["proccess"]?></h1>
<?php print $CNCAT["lang"]["check_hint"]?>
<div class="deline"></div>
<a href="index.php?act=check&mode=listclear"><?php print $CNCAT["lang"]["clear_turn"]?></a>, <a href="index.php?act=check&mode=report"><?php print $CNCAT["lang"]["last_report"]?></a>
<div class="deline"></div>
<?php
    if ($_items_count) {
        $_SESSION["global_sql_cond"] = $_cond;
        $_SESSION["global_referer"] = $_SERVER["REQUEST_URI"];
?>
<div>
    <?php if ($_GET['form'] == 'sendmail') {?>
    <div style="float: left; background: white; border-style: solid; border-color: #c2cdd1; border-width: 1px 1px 0 1px;  padding: 5px 9px;"><a href="index.php?act=check" style="padding: 5px;"><?php print $CNCAT["lang"]["links_check"]?></a></div>
    <div style="float: left; background: #fefece; border-style: solid; border-color: #c2cdd1; border-width: 1px 1px 0 0; padding: 5px 14px; font-weight: bold;"><?php print $CNCAT["lang"]["delivery"]?></div>
    <?php } else {?>
    <div style="float: left; background: #fefece; border-style: solid; border-color: #c2cdd1; border-width: 1px 1px 0 1px; padding: 5px 14px; font-weight: bold;"><?php print $CNCAT["lang"]["links_check"]?></div>
    <div style="float: left; background: white; border-style: solid; border-color: #c2cdd1; border-width: 1px 1px 0 0;  padding: 5px 9px;"><a href="index.php?act=check&form=sendmail" style="padding: 5px;"><?php print $CNCAT["lang"]["delivery"]?></a></div>
    <?php }?>
</div>
<br style="clear: both;" />
<table class="form">
    <?php if ($_GET['form'] == 'sendmail') {?>
    <form action="index.php?act=check&mode=sendmail" method="post">
        <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["delivery_settings"]?></td></tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["mail_subject"]?></td>
            <td class="field"><input type="text" class="text" name="mail_subject" value="" /></td>
        </tr>
        <tr><td class="deline" colspan="2"></td></tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["mail_body"]?></td>
            <td class="field"><textarea name="mail_body"></textarea></td>
        </tr>
        <tr><td class="deline" colspan="2"></td></tr>
        <tr><td class="name"><?php print $CNCAT["lang"]["check_links_on_page"]?></td><td class="field"><input type="text" class="text" name="il" value="5" /></td></tr>
        <tr><td class="name"><?php print $CNCAT["lang"]["check_timelimit"]?></td><td class="field"><input type="text" class="text" name="tl" value="100" /></td></tr>
    <?php } else {?>
    <form action="index.php" method="get">
        <input type="hidden" name="act" value="check" />
        <input type="hidden" name="mode" value="listcheck" />
        <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["check_options"]?></td></tr>
        <tr><td class="name"><?php print $CNCAT["lang"]["check_work"]?></td><td class="field"><input type="checkbox" name="link" <?php print $CNCAT["config"]["check_link"] ? "checked=\"checked\"" : ""?> /></td></tr>
        <tr><td class="name"><?php print $CNCAT["lang"]["check_back_link"]?></td><td class="field"><input type="checkbox" name="back" <?php print $CNCAT["config"]["check_back_link"] ? "checked=\"checked\"" : ""?> /></td></tr>
        <tr><td class="name"><?php print $CNCAT["lang"]["check_pr"]?></td><td class="field"><input type="checkbox" name="pr" <?php print $CNCAT["config"]["check_pr"] ? "checked=\"checked\"" : ""?> /></td></tr>
        <tr><td class="name"><?php print $CNCAT["lang"]["check_cy"]?></td><td class="field"><input type="checkbox" name="cy" <?php print $CNCAT["config"]["check_cy"] ? "checked=\"checked\"" : ""?> /></td></tr>
        <tr><td class="name"><?php print $CNCAT["lang"]["check_favicon"]?></td><td class="field"><input type="checkbox" name="favicon" <?php print $CNCAT["config"]["check_favicon"] ? "checked=\"checked\"" : ""?> /></td></tr>
        <tr><td class="deline" colspan="2"></td></tr>
        <tr><td class="name"><?php print $CNCAT["lang"]["check_links_on_page"]?></td><td class="field"><input type="text" class="text" name="il" value="5" /></td></tr>
        <tr><td class="name"><?php print $CNCAT["lang"]["check_timelimit"]?></td><td class="field"><input type="text" class="text" name="tl" value="100" /></td></tr>
        <tr><td class="deline" colspan="2"></td></tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["auto_approve"]?></td>
            <td class="field">
                <select name="apr"<?php print $CN_TYPE == "free" ? " disabled=\"disabled\" " : ""?>>
                    <option value="0" selected="selected"><?php print $CNCAT["lang"]["never"]?></option>
                    <option value="1"><?php print $CNCAT["lang"]["links_work"]?></option>
                    <option value="2"><?php print $CNCAT["lang"]["links_work_with_back"]?></option>
                </select>
            </td>
        </tr>
    <?php }?>

    <tr><td class="deline" colspan="2"></td></tr>
    <input type="hidden" name="rand" value="<?php print md5(microtime())?>" />
    <?php if ($_GET['form'] == 'sendmail') {?>
    <tr><td class="submit" colspan="2"><input type="submit" class="submit" value="<?php print $CNCAT["lang"]["do_send"]?>" /></td></tr>
    <?php } else {?>
    <tr><td class="submit" colspan="2"><input type="submit" class="submit" value="<?php print $CNCAT["lang"]["do_check"]?>" /></td></tr>
    <?php }?>
</form>
</table>

<?php if ($_GET['form'] == 'sendmail') {?>
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
    <div style="float: right;"><a href="" onclick="showTips(this); return false;" style="text-decoration: none; padding: 1px 5px;">&#9660;</a></div>
    <div><strong><?php print $CNCAT["lang"]["fields_list"]?>:</strong></div>
    <div id="tips" style="display: none;"><?php print $CNCAT["lang"]["mail_notify_tips"]?></div>
</div>
<?php }?>

<div class="deline"></div>
<form action="index.php" method="get">
    <input type="hidden" name="act" value="check" />
<?php
        displaySort($_sort, $_dir);
?>
    <input type="hidden" name="page" value="<?php print $_page?>" />
</form>
<div class="deline"></div>
<?php
        print "<p>" . $CNCAT["lang"]["check_links_count"] . " <strong>" . $_items_count . "</strong></p>";
?>
<?php
        displayPagebar(
            array(
                "act" => "check",
                "sort" => $_GET["sort"],
                "dir" => $_GET["dir"]
            ),
            $_page, $_items_count
        );
?>
<form action="index.php" method="post">
<?php
        $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("admin_item", "ITEM");
        $query = "SELECT " . join(", ", $fields["int"]) . " FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`   
            WHERE " . join(" AND ", $_cond) . "
            " . getSqlSort($_sort, $_dir) . "
            " . getSqlPager($_page);

        print renderItems($query);
?>
<div class="deline"></div>
<p>
    <label><input type="radio" name="global" value="0" checked="checked" /> <?php print $CNCAT["lang"]["with_all_select"]?></label>
    <label><input type="radio" name="global" value="1" /> <?php print $CNCAT["lang"]["with_all_find"]?></label>
</p>
<p>
    <input type="button" value="<?php print $CNCAT["lang"]["do_delete"]?>" class="submit" onclick="this.disabled='disabled'; this.form.action='index.php?act=links&mode=delete'; this.form.submit()" />
    <input type="button" value="<?php print $CNCAT["lang"]["do_asnew"]?>" class="submit" onclick="this.disabled='disabled'; this.form.action='index.php?act=links&mode=asnew'; this.form.submit()" />
    <input type="button" value="<?php print $CNCAT["lang"]["do_approve"]?>" class="submit" onclick="this.disabled='disabled'; this.form.action='index.php?act=links&mode=approve'; this.form.submit()" />
</p>
</form>
<?php
        displayPagebar(
            array(
                "act" => "check",
                "sort" => $_GET["sort"],
                "dir" => $_GET["dir"]
            ),
            $_page, $_items_count
        );
    } else {
        print "<span class=\"not_found\">" . $CNCAT["lang"]["no_links"] . "</span>";
    }
/*******************************************************************************
 * CHECK LIST END
 ******************************************************************************/
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
