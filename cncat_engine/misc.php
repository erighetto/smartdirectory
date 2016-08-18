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

if (!defined("CNCAT_ENGINE")) die();

/**
*   Miscellaneous functions
*/

class CNCatMisc {
    /**
     * Is Admin?
     * @return boolean
     */
    function isAdmin() {
        return $_SESSION["isadmin"] == md5("cncat4admin");
    }
    /**
     * Is Moder?
     * @return int 
     */
    function isModer() {
        return (int)$_SESSION["ismoder"] > 0;
    }
    /**
     * Is moder cat?
     * @param type $cat_id
     * @return boolean
     */
    function isModerCat($cat_id) {
        return in_array($cat_id, $this->getModerCats());
    }
    /**
     * Return moders categories
     * @global $CNCAT_ENGINE
     * @global $CNCAT
     * @staticvar boolean $cats
     * @return array 
     */
    function getModerCats() {
        GLOBAL $CNCAT_ENGINE, $CNCAT;

        static $cats = false;

        if ($cats === false) {
            $query = "SELECT `cid` FROM `" . $CNCAT["config"]["db"]["prefix"] . "modercat`
                WHERE mid=" . $this->getModerId();
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            $cats = array();

            while ($row = mysql_fetch_assoc($res)) {
                $cats[] = $row["cid"];
            }
        }

        return $cats;
    }
    /**
     * Return Moder's session ID
     * @return int
     */
    function getModerId() {
        return (int)$_SESSION["ismoder"];
    }
    /**
     * QP encryption
     * @global $CNCAT $CNCAT
     * @param type $input
     * @param type $line_max
     * @param type $space_conv
     * @return type 
     */
    function qp_enc( $input = "", $line_max = 1000, $space_conv = true ) 
    {
        GLOBAL $CNCAT;
       return "=?" . $CNCAT["lang"]["charset"] . "?B?" . cn_trim(base64_encode($input)) . "?=";
    }
    /**
     * Sending Mail for Admin
     * @global $CNCAT
     * @global $CNCAT_ENGINE
     * @param $item_id
     * @param $cats
     * @return NULL
     */
    function sendMailAdmin($item_id, $cats = array()) {
        GLOBAL $CNCAT, $CNCAT_ENGINE;

        $query = "SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "mail` WHERE `name` = 'admin'";
        $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $mail = mysql_fetch_assoc($result);

        $mail_from = $CNCAT_ENGINE->tpl->renderTemplateString("mail_admin_from", $mail["from"]);
        $mail_reply_to = $CNCAT_ENGINE->tpl->renderTemplateString("mail_admin_reply_to", $mail["reply_to"]);
        $mail_subject = $CNCAT_ENGINE->tpl->renderTemplateString("mail_admin_subject", $mail["subject"]);

        $query = "SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "items` i
                  LEFT JOIN `" . $CNCAT["config"]["db"]["prefix"] . "linkact_comm` c ON c.item_id = i.item_id
                  WHERE i.item_id=" . intval($item_id);
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $CNCAT["item"] = mysql_fetch_assoc($res);
        $title = $CNCAT["config"]["use_translit"]? $CNCAT["item"]["item_title_translite"]: $CNCAT["item"]["item_title"]; 
        
        $CNCAT["item"]["_ext_url"] = $CNCAT_ENGINE->url->createUrlExt($item_id, $title);

        if ($CNCAT["item"]["item_mail_sended"] == 1) return;

        $mail_to = $CNCAT["config"]["admin_email"];

        if (empty($mail_to)) {
            return;
        }

        $mail_body = cn_str_replace("\r\n", "\n", $CNCAT_ENGINE->tpl->renderTemplateString("mail_admin_body", $mail["body"]));
        $mail_body = cn_str_replace("{DISPLAY BACKLINKS}", $this->getBacklinks($item_id), $mail_body);

        $mail_from = cn_trim($mail_from);
        $mail_reply_to = cn_trim($mail_reply_to);
        $mail_subject = cn_trim($mail_subject);

        $mail_body = cn_str_replace("%CATNAME%", $CNCAT["config"]["catalog_title"], $mail_body);
        $mail_subject = cn_str_replace("%CATNAME%", $CNCAT["config"]["catalog_title"], $mail_subject);

        if (!empty($mail_to)) {
            @mail(
                $mail_to,
                $CNCAT_ENGINE->misc->qp_enc($mail_subject),
                //$mail_subject,
                $mail_body,
                (!empty($mail_from) ? "From: " . $CNCAT_ENGINE->misc->qp_enc($CNCAT["config"]["catalog_title"]) . " <" . $mail_from . ">\r\n" : "")
                . "Content-type: text/plain; charset=" . $CNCAT["lang"]["charset"] . "\r\n"
            );
        }

        if ($cats) {
            $mail_cc = array();

            $query = "SELECT DISTINCT `email` FROM `" . $CNCAT["config"]["db"]["prefix"] . "moders` RIGHT JOIN `" . $CNCAT["config"]["db"]["prefix"] . "modercat` ON (id=mid) WHERE `cid` IN (" . join(",", $cats) . ")";
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        
            while ($row = mysql_fetch_assoc($res)) {
                if (!empty($row["email"])) {
                    $mail_cc[] = $row["email"];
                }
            }
        
            foreach ($mail_cc as $mail_to) {
                @mail(
                    $mail_to,
                    $CNCAT_ENGINE->misc->qp_enc($mail_subject),
                    //$mail_subject,
                    $mail_body,
                    (!empty($mail_from) ? "From: " . $CNCAT_ENGINE->misc->qp_enc($CNCAT["config"]["catalog_title"]) . " <" . $mail_from . ">\r\n" : "")
                    . "Content-type: text/plain; charset=" . $CNCAT["lang"]["charset"] . "\r\n"
                );
            }
        }
    }
    /* deprecated in 4.3.4 */
    function sendMailAdd($item_id) {
        $this->sendMail('add', $item_id);
    }

    function sendMailApprove($item_id) {
        $this->sendMail('approve', $item_id);
    }

    function sendMailDecline($item_id) {
        $this->sendMail('decline', $item_id); 
    }

    function sendMailIsolation($item_id) {
        $this->sendMail('isolation', $item_id);
    }
    /**
     * Send mail for user
     */
    function sendMail($mail_type,$item_id)
    {
        GLOBAL $CNCAT, $CNCAT_ENGINE;
        $query = "SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "mail` WHERE `name` = '{$mail_type}'";
        $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $mail = mysql_fetch_assoc($result);

        $mail_from = $CNCAT_ENGINE->tpl->renderTemplateString("mail_{$mail_type}_from", $mail["from"]);
        $mail_reply_to = $CNCAT_ENGINE->tpl->renderTemplateString("mail_{$mail_type}_reply_to", $mail["reply_to"]);
        $mail_subject = $CNCAT_ENGINE->tpl->renderTemplateString("mail_{$mail_type}_subject", $mail["subject"]);
        
        $query = "SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "items` i 
                  LEFT JOIN `" . $CNCAT["config"]["db"]["prefix"] . "linkact_comm` c ON c.item_id = i.item_id 
                  WHERE i.item_id=" . intval($item_id);
        $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $CNCAT["item"] = mysql_fetch_assoc($res);
        $title = $CNCAT["config"]["use_translit"]? $CNCAT["item"]["item_title_translite"]: $CNCAT["item"]["item_title"]; 
        
        $CNCAT["item"]["_ext_url"] = $CNCAT_ENGINE->url->createUrlExt($item_id, $title);

        if ($CNCAT["item"]["item_mail_sended"] == 1) return;

        $mail_to = $CNCAT["item"]["item_author_email"];

        if (empty($mail_to)) {
            return;
        }

        $mail_body = cn_str_replace("\r\n", "\n", $CNCAT_ENGINE->tpl->renderTemplateString("mail_{$mail_type}_body", $mail["body"]));
        $mail_body = cn_str_replace("{DISPLAY BACKLINKS}", $this->getBacklinks($item_id), $mail_body);
        $mail_from = cn_trim($mail_from);
        $mail_reply_to = cn_trim($mail_reply_to);
        $mail_subject = cn_trim($mail_subject);

        $mail_body = cn_str_replace("%CATNAME%", $CNCAT["config"]["catalog_title"], $mail_body);
        $mail_subject = cn_str_replace("%CATNAME%", $CNCAT["config"]["catalog_title"], $mail_subject);

        if (!@mail(
            $mail_to,
            $CNCAT_ENGINE->misc->qp_enc($mail_subject),
            //$mail_subject,
            $mail_body,
            (!empty($mail_from) ? ("From: " . $CNCAT_ENGINE->misc->qp_enc($CNCAT["config"]["catalog_title"]) . " <" . $mail_from . ">\r\n") : "") .
            (!empty($reply_to) ? ("Reply-to: " . $mail_reply_to . "\r\n") : "")
            . "Content-type: text/plain; charset=" . $CNCAT["lang"]["charset"] . "\r\n",
            (!empty($mail_from) ? "-f " . $mail_from : "")
        )) {
            @mail(
                $mail_to,
                $CNCAT_ENGINE->misc->qp_enc($mail_subject),
                //$mail_subject,
                $mail_body,
                (!empty($mail_from) ? ("From: " . $CNCAT_ENGINE->misc->qp_enc($CNCAT["config"]["catalog_title"]) . " <" . $mail_from . ">\r\n") : "") .
                (!empty($reply_to) ? ("Reply-to: " . $mail_reply_to . "\r\n") : "")
                . "Content-type: text/plain; charset=" . $CNCAT["lang"]["charset"] . "\r\n"
            );
        }
    }
    /**
     * Return backlinks for current item_id
     * @global $CNCAT
     * @global $CNCAT_ENGINE
     * @staticvar string $back_links
     * @param $item_id
     * @return string 
     */
    function getBacklinks($item_id) {
        GLOBAL $CNCAT, $CNCAT_ENGINE;
        $dbPrefix = $CNCAT["config"]["db"]["prefix"];

        static $back_links = "";

        if (empty($back_links)) {
            $query = "SELECT `user_code` FROM `" . $dbPrefix . "backlinks` WHERE `disabled`=0 ORDER BY `sort_order`, `id`";
            $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

            while ($row = mysql_fetch_assoc($res)) {
                $back_links .= str_repeat("-", 40) . "\r\n";
                
                $row["user_code"] = cn_str_replace("%CATNAME%", $CNCAT["config"]["catalog_title"], $row["user_code"]);
                $row["user_code"] = cn_str_replace("%BACKURL%", $this->getBackUrl("%SITEID%"), $row["user_code"]);
                $row["user_code"] = cn_str_replace("%SITEID%", $item_id, $row["user_code"]);

                $back_links .= $row["user_code"] . "\r\n";
            }
        }
    
        return $back_links;
    }
    //TODO go to router?
    /**
     * Get backlink URL
     * @global $CNCAT
     * @param $resId
     * @return string
     */
    function getBackUrl($resId) {
        global $CNCAT;
        return "http://" . $_SERVER["HTTP_HOST"] . $CNCAT["abs"] . "{$CNCAT["system"]["dir_prefix"]}from.php?" . $resId;
    }
    /**
     * Generate 404 error page
     * @global $CNCAT
     * @global $CNCAT_ENGINE 
     */
    function error404() {
        global $CNCAT, $CNCAT_ENGINE;
        
        $CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], "error");
        header("HTTP/1.1 404 Not Found");
        
        if (strpos(PHP_SAPI, 'cgi') !== false) {
            header("Status: 404 Not Found");
        }
        header("Content-type: text/html; charset=" . $CNCAT["lang"]["charset"]);
        print $CNCAT_ENGINE->tpl->renderTemplate("404");
        exit;
    }
    /**
     * Clean HTML
     * @param $text
     * @return string
     */
    function cleanHtml($text) {
        // Comments
        $text = preg_replace('/<!--(.*?)-->/s', '', $text);
        // Doctype
        $text = preg_replace('/<!(.*?)>/s', '', $text);
        // Scripts && Styles
        $text = preg_replace('/<\s*(style|script).*>.*<\s*\/\1\s*>/Usi', '', $text);
        // Tags
        $text = preg_replace_callback('/<+\s*(\/?)\s*([a-z0-9]+?)(.*)>/Usi', 'cleanTag', $text);

        return $text;
    }
    /**
     * Create uniq reference ID
     * @return string
     */
    function createBackUrlRef() {
        return uniqid('r');
    }
    /**
     * Loging action
     * @global $CNCAT
     * @param $code
     * @param $comment
     * @param $item_id
     * @param $user_comm 
     */
    function itemLog($code, $comment, $item_id = null, $user_comm = "") {
        global $CNCAT;

        if ($item_id === null) {
            $item_id = $CNCAT["system"]["log"]["item_id"];
        }

        $this->log($code, $comment, array("item_id" => $item_id), $user_comm);
    }
    /**
     * Add log record to DB
     * @global $CNCAT
     * @global $CNCAT_ENGINE
     * @param $code
     * @param $comment
     * @param $fields
     * @param $user_comm 
     */
    function log($code, $comment, $fields, $user_comm) {
        global $CNCAT, $CNCAT_ENGINE;

        if ($this->isModer()) {
            $user_id = $this->getModerId();
        } else {
            $user_id = 0;
        }

        $query = "
            INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "actlog`
                (item_id, user_id, date, code, comment)
            VALUES (
                " . intval($fields["item_id"]) . ", " . $user_id . ", NOW(), " . $code . ", '" . mysql_escape_string($comment) . "'
            )
        ";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $this->save_action_comment($fields["item_id"], $user_comm);
    }
    /**
     * Save action comment
     * @global $CNCAT
     * @global $CNCAT_ENGINE
     * @param $item_id
     * @param $user_comm 
     */
     
    function save_action_comment($item_id, $user_comm)
    {
        global $CNCAT, $CNCAT_ENGINE;
        $item_id =  intval($item_id);
        
        if ($item_id > 0 && strlen($user_comm) > 0)
        { 
            $query = "
                INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "linkact_comm`
                    (item_id,action_comm, act_date)
                VALUES (
                    {$item_id}, '" . mysql_escape_string($user_comm) . "', NOW())";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }
        else if (strlen($user_comm) == 0)
        { 
            $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "linkact_comm`
                      WHERE item_id={$item_id}";
            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        }
        
    }
}
  
/**
 *  Clean tags
 * @param $m
 * @return string 
 */
function cleanTag($m) {
    $tags = array(
        '*'      => array('style', 'title', 'id', 'class'),
        'a'      => array('href', 'target', 'name'),
        'img'    => array('src', 'alt'),
        'ol'     => array(),
        'ul'     => array(),
        'li'     => array(),
        'strong' => array(),
        'em'     => array(),
        'span'   => array(),
        'div'    => array(),
        'p'      => array(),
        'hr'     => array(),
        'br'     => array(),
        'h1'     => array(),
        'h2'     => array(),
        'h3'     => array(),
        'h4'     => array(),
        'h5'     => array(),
        'h6'     => array(),
        'h7'     => array()
    );
    $attrs = array(
        '*:style' => array('accept' => '', 'ignore' => 'background(-image)?\s*:.*url\s*\(.*\)'),
        '*:style' => array('accept' => '', 'ignore' => '(display|visibility)\s*:\s*(none|hidden)'),
        'a:href'  => array('accept' => '', 'ignore' => 'javascript\s*:')
    );

    $open = empty($m[1]);
    $tag_name = $m[2];
    $params = trim($m[3]);

    if (!array_key_exists($tag_name, $tags)) {
        return '';
    }

    // Check attrs
    preg_match_all('/([a-z]+)\s*=\s*(["\'])(.*)\2/Usi', $params, $m);
    $params = '';

    foreach ($m[1] as $i => $param) {
        if (array_search($param, $tags['*']) === false) {
            if (array_search($param, $tags[$tag_name]) === false) {
                continue;
            }
        }

        // Check attr value
        if ((
                !empty($attrs['*:' . $param]['ignore']) &&
                preg_match('/' . $attrs['*:' . $param]['ignore'] . '/Usi', $m[3][$i])
            ) && (
                !empty($attrs['*:' . $param]['accept']) &&
                !preg_match('/' . $attrs['*:' . $param]['ignore'] . '/Usi', $m[3][$i])
        )) {
            continue;
        }

        if ((
                !empty($attrs[$tag_name . ':' . $param]['ignore']) &&
                preg_match('/' . $attrs[$tag_name . ':' . $param]['ignore'] . '/Usi', $m[3][$i])
            ) && (
                !empty($attrs[$tag_name . ':' . $param]['accept']) &&
                !preg_match('/' . $attrs[$tag_name . ':' . $param]['ignore'] . '/Usi', $m[3][$i])
        )) {
            continue;
        }

        $params .= ' ' . $param . '=' . $m[2][$i] . $m[3][$i] . $m[2][$i];
    }

    return $open ? '<' . $tag_name . $params . '>' : '</' . $tag_name . '>';
}
?>
