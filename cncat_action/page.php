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

$CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], array("common", "static_page"));

$page_name = isset($_GET["id"]) ? substr($_GET["id"], 0, 255) : "";
$page_is_found = false;
if (!$page_name)
{
    $url = explode ("/", cn_trim($CNCAT['uri'], "/"));
    $page_name = array_pop($url); 
}
//Get page from DB
if (!empty($page_name)) {
    $query = "
        SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "pages`
        WHERE name = '" . mysql_escape_string($page_name) . "'
    ";
    $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    if (mysql_num_rows($result)) {
        $page_is_found = true;
    }
}

if (!$page_is_found) { 
    $url = explode ("/", cn_trim($CNCAT['uri'], "/"));
    $page_name = array_pop($url);
    
    if (preg_match('/^[0-9]+$/', $page_name)) {
        $page_id = intval($page_name);
        $query = "
            SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "pages`
            WHERE id = '" . $page_id . "'
        ";
        $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

        if (mysql_num_rows($result)) {
            $page_is_found = true;
        }
    }
}

if (!$page_is_found) {
    $CNCAT_ENGINE->misc->error404();
    exit;
}

// Render page
$CNCAT["page"]["search_form_url"] = $CNCAT_ENGINE->url->createCnUrl("search");
$CNCAT["page"]["add_url"] = $CNCAT_ENGINE->url->createCnUrl("add");
$CNCAT["page"]["add_article_url"] = $CNCAT_ENGINE->url->createCnUrl("add_article");
$CNCAT["page"]["map_url"] = $CNCAT_ENGINE->url->createCnUrl("map");

$CNCAT["static_page"] = mysql_fetch_assoc($result);

$CNCAT["render_result"] =  $CNCAT_ENGINE->tpl->renderTemplate("static_page");

?>
