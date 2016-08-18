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

require_once  $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_product"]."search.php"; 
if (!get_magic_quotes_gpc())
  $_GET["cn_query"] = addslashes($_GET["cn_query"]);

$CNCAT["page"]["search_query"] = cn_substr(cn_trim(urldecode($_GET["cn_query"])), 0, 255);
$CNCAT["page"]["search_query"] = $CNCAT["lang"]["charset"] == "utf8"?$CNCAT["page"]["search_query"]: iconv("UTF-8", $CNCAT["lang"]["charset"], $CNCAT["page"]["search_query"]);
$CNCAT["page"]["search_query"] = preg_replace("/[<>\\/&%+]+/" . $CN_STRINGS["preg"], "", $CNCAT["page"]["search_query"]);           
$CNCAT["page"]["page_num"] = intval ($_GET["p"]);
$CNCAT["page"]["sort_order"] = intval ($_GET["s"]);
$CNCAT["page"]["start_item_num"] = $CNCAT["page"]["page_num"]*$CNCAT["config"]["items_per_page"]+1;

$CNCAT_ENGINE->tpl->loadTemplates ($CNCAT["config"]["default_theme"], "search");
$CNCAT_ENGINE->loadBanners("search");

prepareToRender();

$CNCAT["render_result"] = cn_render();

return;

/**
*   Init global variables before render
*/
function prepareToRender()
{
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $CNCAT["page"]["search_form_url"] = $CNCAT_ENGINE->url->createCnUrl("search");
    $CNCAT["page"]["add_url"] = $CNCAT_ENGINE->url->createCnUrl("add");
    $CNCAT["page"]["add_article_url"] = $CNCAT_ENGINE->url->createCnUrl("add_article");
    $CNCAT["page"]["map_url"] = $CNCAT_ENGINE->url->createCnUrl("map");
    $dbPrefix = $CNCAT["config"]["db"]["prefix"];
    
    $cond = createSearchCondition ($CNCAT["page"]["search_query"]);
    
    $CNCAT["sql"]["itemfilter"]["fields"] = $cond["fields"] ? ",".$cond["fields"] : "";
    $CNCAT["sql"]["itemfilter"]["from"] = $dbPrefix."items it ";
    $CNCAT["sql"]["itemfilter"]["join"] = "";
    $CNCAT["sql"]["itemfilter"]["where"] = " it.item_status=1 AND ".$cond["where"];            

    $CNCAT["sql"]["countfilter"]["fields"] = $CNCAT["sql"]["itemfilter"]["fields"];
    $CNCAT["sql"]["countfilter"]["from"] = $CNCAT["sql"]["itemfilter"]["from"];
    $CNCAT["sql"]["countfilter"]["join"] = $CNCAT["sql"]["itemfilter"]["join"];  
    $CNCAT["sql"]["countfilter"]["where"] = $CNCAT["sql"]["itemfilter"]["where"];            
      

    $query = "SELECT COUNT(*) AS cnt  FROM ".$CNCAT["sql"]["countfilter"]["from"].
                " ".$CNCAT["sql"]["countfilter"]["join"].
                " WHERE ".$CNCAT["sql"]["countfilter"]["where"]." ".$CNCAT["sql"]["countfilter"]["category"].
                " ";
    $res = $CNCAT_ENGINE->db->query($query, "Items count in the current category") or $CNCAT_ENGINE->displayErrorDB (mysql_error());    

    if ($row = mysql_fetch_assoc ($res))
    {           
        $CNCAT["page"]["cat_item_count"] = $row["cnt"];
    }

    $CNCAT["page"]["page_count"] = ceil($CNCAT["page"]["cat_item_count"]/$CNCAT["config"]["items_per_page"]);

    if ($CNCAT["page"]["page_count"]==0)
        $CNCAT["page"]["page_count"]=1;

    if ($CNCAT["page"]["page_num"] < 0)
        $CNCAT["page"]["page_num"] = 0;
    if ($CNCAT["page"]["page_num"] >= $CNCAT["page"]["page_count"])
        $CNCAT["page"]["page_num"] = $CNCAT["page"]["page_count"]-1;

    if ($CNCAT["config"]["search_method"]==0)
    {
	    $CNCAT["sql"]["itemorder"][9] = array(
	        "", "", "", 1, 0
	    );
	}

    if (is_numeric ($_GET["s"]))
        $CNCAT["page"]["sort_order"] = intval($_GET["s"]);    
    else
        $CNCAT["page"]["sort_order"] = $CNCAT["config"]["search_method"]==0 ? 9 : $CNCAT["config"]["default_sort_order"];
        
    if ($CNCAT["page"]["sort_order"]<0 || !isset ($CNCAT["sql"]["itemorder"][$CNCAT["page"]["sort_order"]]))
        $CNCAT["page"]["sort_order"] = $CNCAT["config"]["default_sort_order"];

    if (empty($CNCAT["page"]["sort_order"]))
        $CNCAT["page"]["sort_order"] = 0;
    
    $CNCAT["page"]["sort_dir"] = ($CNCAT["sql"]["itemorder"][$CNCAT["page"]["sort_order"]][3] + intval ($_GET["d"])) % 2;
}  
    
/**
*   Renders whole page
*   @return render result
*/
function cn_render()
{   
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $CNCAT["page"]["search_form_url"] = $CNCAT_ENGINE->url->createCnUrl("search");
    $items = renderItems("item");
    $pages = renderPages();
    $sort = renderSort();
    
    $result = $CNCAT_ENGINE->tpl->renderTemplate ("search");    
    
    if ($CNCAT["system"]["debug_templates"])
        $result = preg_replace ("/\{DISPLAY (\w+)}/U".$CN_STRINGS["preg"], "{DISPLAY $1 }$0{/DISPLAY $1}", $result);

    $result = cn_str_replace ("{DISPLAY ITEMS}", $items, $result);
    $result = cn_str_replace ("{DISPLAY PAGES}", $pages, $result);
    $result = cn_str_replace ("{DISPLAY SORT}", $sort, $result);
    $result = cn_str_replace ("{DISPLAY FILTERS}", "", $result);
    
    return $result;
}

/**
*   Renders items
*   @param tplName name of template for item
*   @return render result
*/
function renderItems($tplName)
{
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $result="";

    $cid = $CNCAT["page"]["cid"];

    $dbPrefix = $CNCAT["config"]["db"]["prefix"];
    $itemsPerPage = $CNCAT["config"]["items_per_page"];

    $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("item", "ITEM", "items");
    $fieldsSqlInt = "it.".join(", it.", $fields["int"]);    
    foreach ($CNCAT["config"]["extfields"]["items"][0] as $name => $field) {
        $fieldsSqlInt .= ", `" . $name . "`";
    }
    $limitSql = " LIMIT ".($CNCAT["page"]["page_num"]*$itemsPerPage).", ".$itemsPerPage;
    $forcedIndex = $CNCAT["sql"]["itemorder"][$CNCAT["page"]["sort_order"]][2];

    if (!empty($forcedIndex)) {
        $forcedIndex = " USE INDEX (".$forcedIndex.") ";
    }

    if ($CNCAT["config"]["search_method"] == 1) {
        $orderSql = "ORDER BY " . $CNCAT["sql"]["itemorder"][$CNCAT["page"]["sort_order"]][$CNCAT["page"]["sort_dir"]];
    } else {
        if ($CNCAT["page"]["sort_order"] != 9) {
            $orderSql = "ORDER BY " . $CNCAT["sql"]["itemorder"][$CNCAT["page"]["sort_order"]][$CNCAT["page"]["sort_dir"]];
        } else {
            $orderSql = "";
        }
    }
    
    $query = "SELECT `it`.`item_type`, ".$fieldsSqlInt.$CNCAT["sql"]["itemfilter"]["fields"].
                " FROM ".$CNCAT["sql"]["itemfilter"]["from"].
                " ".$CNCAT["sql"]["itemfilter"]["join"]. 
                " WHERE ".$CNCAT["sql"]["itemfilter"]["where"]." ".$CNCAT["sql"]["itemfilter"]["category"].
                " " . $orderSql.
                $limitSql;
    // Loading items
    $res = $CNCAT_ENGINE->db->query($query, "Items") or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $count = 0;
    $items = array();
    $itemIds = "";
    while ($item = mysql_fetch_assoc($res))
    {           
        $items[$item["item_id"]] = $item;
        if ($count>0)
            $itemIds .= ",";
        $itemIds .= $item["item_id"];       
        $count++;
    }

    if ($CNCAT_ENGINE->misc->isModer()) {
        if (!empty($items)) {
            // Append cats to items
            $query = "SELECT `item_id`, `cat_id` FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat`
                WHERE `item_id` IN (" . $itemIds . ")";
            $res = $CNCAT_ENGINE->db->query($query, "Items cats") or $CNCAT_ENGINE->displayErrorDB(mysql_error());
            while ($itemcat = mysql_fetch_assoc($res)) {
                $cats[$itemcat["item_id"]][] = $itemcat["cat_id"];
            }
        }
    }
    
    // Rendering items
    $i = 0;
    foreach ($items as $CNCAT["item"])
    {
        $CNCAT_ENGINE->render->prepareItemToDisplay ($CNCAT["item"]);
        $CNCAT["item"]["_number"] = $i;
        $CNCAT["item"]["_count"] = $count;
        $CNCAT["item"]["_isfirst"] = $i==0;
        $CNCAT["item"]["_islast"] = $i==$count-1;        

        if (!$CNCAT["item"]["link_target"]) {
            $CNCAT["item"]["link_target"] = $CNCAT["config"]["link_target"];
        }
        if (!$CNCAT["item"]["item_display_ext"]) {
            $CNCAT["item"]["item_display_ext"] = $CNCAT["config"]["display_ext"];
        }
        $title = $CNCAT["config"]["use_translit"]? $CNCAT["item"]["item_title_translite"]: $CNCAT["item"]["item_title"];
        $CNCAT["item"]["_ext_url"] = $CNCAT_ENGINE->url->createUrlExt($CNCAT["item"]["item_id"], $title);

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

        if ($CNCAT_ENGINE->misc->isAdmin()) {
            $CNCAT["item"]["_control_bar"] = true;
        } elseif ($CNCAT_ENGINE->misc->isModer()) {
            $CNCAT["item"]["_control_bar"] = count(array_diff($cats[$CNCAT["item"]["item_id"]], $CNCAT_ENGINE->misc->getModerCats())) < count($cats[$CNCAT["item"]["item_id"]]);
        } else {
            $CNCAT["item"]["_control_bar"] = false;
        }
        $rat_res = array();
        for ($j = 0; $j <= 10; $j++) {
            $CNCAT["admin"]["item_rating_value"] = $j;
            if ($j == (int)$CNCAT["item"]["item_rating_moder"]) {
                $rat_res[] = $CNCAT_ENGINE->tpl->renderTemplate("admin_rating_num_active");
            } else {
                $rat_res[] = $CNCAT_ENGINE->tpl->renderTemplate("admin_rating_num");
            }
        }
        
        $ext_res = "";
        
        foreach ($CNCAT["config"]["extfields"]["items"][$CNCAT["item"]["item_type"]] as $name => $field) {
            if (
                !$field["active"] ||
                !in_array(2, $field["display"])
            ) {
                continue;
            }

            $CNCAT["extfield"] = $field;
            $CNCAT["extfield"]["name"] = $name;
            $CNCAT["extfield"]["value"] = $CNCAT["item"][$name];

            $ext_res .= $CNCAT_ENGINE->tpl->renderTemplate("item_ext_field");
        }

        $rat_res = $CNCAT_ENGINE->tpl->renderTemplate("admin_rating_top") . join($CNCAT_ENGINE->tpl->renderTemplate("admin_rating_delim"), $rat_res) . $CNCAT_ENGINE->tpl->renderTemplate("admin_rating_bottom");
        $it_res = $CNCAT_ENGINE->tpl->renderTemplate($CNCAT["item"]["item_type"] == 1 ? "article" : "item");
        $result .= cn_str_replace(array("{DISPLAY ADMIN_RATING}", "{DISPLAY EXTFIELDS}"), array($rat_res, $ext_res), $it_res);
        $i++;
    }
    
    $result = $CNCAT_ENGINE->tpl->renderTemplate ("items_top").$result.$CNCAT_ENGINE->tpl->renderTemplate("items_bottom");        
    return $result;
}
  

/**
*   Renders sorting controls
*   @return render result
*/
function renderSort()
{
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    
    $result = "";
    $result .= $CNCAT_ENGINE->tpl->renderTemplate ("sort_top");  
    
    $sort_order = $CNCAT["sql"]["itemorder"];

    foreach ($sort_order as $k => $v) {
        if ($k != 9 && !in_array($k, explode(",", $CNCAT["config"]["use_sort_orders"]))) {
            unset($sort_order[$k]);
        }
    }         
    foreach ($sort_order as $i=>$sortOrder)
    {
        if ($sortOrder[4]) {
            continue;
        }

        $CNCAT["sort"]["id"] = $i;
        $CNCAT["sort"]["title"] = $CNCAT["lang"]["sort_by_".$i];
        $CNCAT["sort"]["url"] = $CNCAT_ENGINE->url->createUrlSortSearch($CNCAT["sort"]["id"]);
        if ($i>0)
            $result .= $CNCAT_ENGINE->tpl->renderTemplate ("sort_delim");  
        $result .= $CNCAT_ENGINE->tpl->renderTemplate ("sort");  
    }
    $result .= $CNCAT_ENGINE->tpl->renderTemplate ("sort_bottom");  
    return $result;
}


/**
*   Renders page navigation
*   @return render result
*/
function renderPages()
{       
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    if ($CNCAT["page"]["cat_item_count"] > $CNCAT["config"]["items_per_page"])
        return $CNCAT_ENGINE->render->renderPageNavigation($CNCAT_ENGINE->url->createUrlPageSearch ("{PAGE}"), $CNCAT["page"]["page_count"], $CNCAT["page"]["page_num"]);
    else
        return "";    
}

  
?>
