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

//category ID       
$CNCAT["page"]["cid"] = $_GET["c"];
//page number
$CNCAT["page"]["page_num"] = $_GET["p"];
//item type filter
$CNCAT["page"]["item_type"] = (1 == $_GET["t"] || 0 === $_GET["t"]) ? $_GET["t"] : -1;
//item sort
$CNCAT["page"]["sort_order"] = $_GET["s"];
if ($CNCAT["page"]["sort_order"] < 0 || !isset($CNCAT["sql"]["itemorder"][$CNCAT["page"]["sort_order"]])) {
    $CNCAT["page"]["sort_order"] = $CNCAT["config"]["default_sort_order"];
}
//TODO what this?)
$CNCAT["page"]["sort_dir"] = ($CNCAT["sql"]["itemorder"][$CNCAT["page"]["sort_order"]][3] + $_GET["d"]) % 2;

//custom filters
$CNCAT["page"]["filter_values"] = array();

//TODO revision this cycle
foreach ($_GET as $key => $value) {
    if (cn_substr($key, 0, 1) == "f") {
        $filvalId = cn_substr($key, 1);
        
        if (is_numeric($filvalId) || $filvalId == "t") {
            $CNCAT["page"]["filter_values"][$filvalId] = intval($value);
        }
    }
}

$CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], "index");

// Preparing to render
prepareToRender();

// Rendering page
$CNCAT["render_result"] = cn_render();

return;
/**
*   Init global variables before render
*/
function prepareToRender() {
    GLOBAL $CNCAT, $CNCAT_ENGINE;
        
    $cid = $CNCAT["page"]["cid"];
    $CNCAT["page"]["search_form_url"] = $CNCAT_ENGINE->url->createCnUrl("search");
    $CNCAT["page"]["add_url"] = $CNCAT_ENGINE->url->createCnUrl("add");
    $CNCAT["page"]["add_article_url"] = $CNCAT_ENGINE->url->createCnUrl("add_article");
    $CNCAT["page"]["map_url"] = $CNCAT_ENGINE->url->createCnUrl("map");
                    
    // Generating common page variables
    //$CNCAT["page"]["start_item_num"] = $CNCAT["page"]["page_num"] * $CNCAT["config"]["items_per_page"]+1;
    
    $curCatFields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("cat", "CAT", "cats");
    $curCatFields["int"][] = "child_id_list, path_full, tree_level, items_sort_order";

    $CNCAT["user"] = array();
    $CNCAT["user"]["id"] = $CNCAT_ENGINE->misc->isAdmin() || $CNCAT_ENGINE->misc->isModer() ? 1 : 0;
    $CNCAT["user"]["login"] = "";
    
    if ($CNCAT["user"]["id"]) {
        $CNCAT["user"]["login"] = $_SESSION["user_login"];
    }

    $CNCAT["lang"]["you_login_as"] = cn_str_replace("%LOGIN%", $CNCAT["user"]["login"], $CNCAT["lang"]["you_login_as"]);

    $CNCAT["page"]["cat"] = getCategory($cid, $curCatFields);

    if (empty($CNCAT["page"]["cat"]["id"])) {
        $CNCAT_ENGINE->misc->error404();
        exit;
    }
    
    if ($CNCAT["page"]["cat"]["items_sort_order"] >= 0 && isset($CNCAT["sql"]["itemorder"][$CNCAT["page"]["cat"]["items_sort_order"]])) {
        $CNCAT["page"]["sort_order"] = $CNCAT["page"]["cat"]["items_sort_order"];
    }
    
    $CNCAT["page"]["keywords"] = $CNCAT["page"]["cat"]["meta_keywords"];
    $CNCAT["page"]["description"] = $CNCAT["page"]["cat"]["meta_descr"];

    // CNCat 4.0 beta
    $CNCAT["meta"]["keywords"] = $CNCAT["page"]["keywords"];
    $CNCAT["meta"]["description"] = $CNCAT["page"]["description"];

    $CNCAT["page"]["show_cat_path"] = $cid!=$CNCAT["root_cat_id"] || $CNCAT["config"]["show_path_on_main"]==1;

    $CNCAT["page"]["show_items"] = $cid!=$CNCAT["root_cat_id"] || $CNCAT["config"]["show_items_on_main"]==1;
    $CNCAT["page"]["show_new_items"] = 
        $CNCAT["config"]["show_new_items"]==3 ||
        $CNCAT["config"]["show_new_items"]==2 && $cid!=$CNCAT["root_cat_id"] ||
        $CNCAT["config"]["show_new_items"]==1 && $cid==$CNCAT["root_cat_id"];

    // RSS
    $CNCAT["page"]["show_rss"] = false;

    if ($CNCAT["config"]["rss_display"] > 0) {
        if ($CNCAT["config"]["rss_display"] == 1 && $CNCAT["page"]["cid"] == $CNCAT["root_cat_id"]) {
            $CNCAT["page"]["show_rss"] = true;
        } elseif ($CNCAT["config"]["rss_display"] == 2 && $CNCAT["page"]["cid"] != $CNCAT["root_cat_id"]) {
            $CNCAT["page"]["show_rss"] = true;
        } elseif ($CNCAT["config"]["rss_display"] == 3) {
            $CNCAT["page"]["show_rss"] = true;
        }
    }

    // Loading filters
    $CNCAT["page"]["filter_used"] = count($CNCAT["page"]["filter_values"])>0 && $CNCAT["config"]["use_filters"] ? 1 : 0;

    $dbPrefix = $CNCAT["config"]["db"]["prefix"];
    $CNCAT["filter_list"] = array();
    
    if ($CNCAT["config"]["use_filters"])
    {
        $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("filter", "FILTER", "filters");
        $fieldsSql = "f.id, f.title, f.sort_order ".(is_array ($fields) ? ", f.".join(", f.", $fields["int"]) : "");    

        $query = "SELECT ".$fieldsSql." FROM ".$dbPrefix."filters f ORDER BY sort_order, title";
        $res = $CNCAT_ENGINE->db->query ($query, "Filter list") or $CNCAT_ENGINE->displayErrorDB (mysql_error());
        while ($filter = mysql_fetch_assoc($res))
        {
            $CNCAT["filter_list"][$filter["id"]] = $filter;   
        }
        
        // Getting filter values
        $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("filtval", "FILTVAL", "filtvals");
        $fieldsSql = "v.id, v.filter_id, v.title, v.sort_order ".(is_array ($fields) ? ", v.".join(", v.", $fields["int"]) : "");    
        $query = "SELECT ".$fieldsSql." FROM ".$dbPrefix."filtvals v ORDER BY sort_order, title";
        $res = $CNCAT_ENGINE->db->query ($query, "Filter values") or $CNCAT_ENGINE->displayErrorDB (mysql_error());
        $filtvals = array();
        $max_val = 0;
        while ($filtval = mysql_fetch_assoc($res))
        {               
            $filtval["_checked"] = $CNCAT["page"]["filter_values"][$filtval["id"]] ? 1 : 0;
            $CNCAT["filter_list"][$filtval["filter_id"]]["_values"][] = $filtval;
            $max_val = max($max_val, $filtval["id"]);
        }
    }

    // Generate SQL filter for items (used here and in renderItems())
    $forcedIndex = $CNCAT["sql"]["itemorder"][$CNCAT["page"]["sort_order"]][2];
    if (!empty ($forcedIndex))
        $forcedIndex = " USE INDEX (".$forcedIndex.") ";

    $forcedIndexNew = "USE INDEX (new_items) ";
        
    if ($CNCAT["page"]["filter_used"]) // If using filters
    {
        $CNCAT["sql"]["itemfilter"]["distinct"] = "";
        $CNCAT["sql"]["itemfilter"]["from"] = $dbPrefix."itemcat ic ";
        $CNCAT["sql"]["itemfilter"]["join"] = "";
        $CNCAT["sql"]["itemfilter"]["where"] = "";
                
        $usedFiltersCount = 0;
        $i = 1;
        $items_type = array();
        foreach ($CNCAT["filter_list"] as $filter)
        {
            $checked = array();
            foreach ((array)$filter["_values"] as $value)
            {
                if ($value["_checked"]) {
                    if ($value["id"] == $val1_id || $value["id"] == $val2_id) {
                        continue;
                    }
                    
                    $checked[] = $value["id"];
                }
            }
            if (!empty ($checked))
            {
                $usedFiltersCount++;
                $CNCAT["sql"]["itemfilter"]["join"] .= " LEFT JOIN ".$dbPrefix."itemfilt lf".$i." on ic.item_id=lf".$i.".item_id ";            
                $CNCAT["sql"]["itemfilter"]["where"] .= " AND lf".$i.".filtval_id in (".join (",", $checked).") ";
                $i++;    
            }
        }

        $CNCAT["sql"]["countfilter"]["distinct"] = "";
        $CNCAT["sql"]["countfilter"]["from"] = $CNCAT["sql"]["itemfilter"]["from"];
        $CNCAT["sql"]["countfilter"]["join"] = $CNCAT["sql"]["itemfilter"]["join"];
        $CNCAT["sql"]["countfilter"]["where"] = " ic.item_status=1 ".$CNCAT["sql"]["itemfilter"]["where"]; 

        $CNCAT["sql"]["itemfilter"]["join"] .= " LEFT JOIN ".$dbPrefix."items it %FORCE_INDEX% on ic.item_id=it.item_id ";
        $CNCAT["sql"]["itemfilter"]["where"] = " it.item_status=1 ".$CNCAT["sql"]["itemfilter"]["where"];
    }
    else // no filters selected
    {           
        if ($cid != $CNCAT["root_cat_id"])
        {
		    $CNCAT["sql"]["itemfilter"]["distinct"] = "";
            $CNCAT["sql"]["itemfilter"]["from"] = $dbPrefix."itemcat ic ";
            $CNCAT["sql"]["itemfilter"]["join"] = " LEFT JOIN ".$dbPrefix."items it %FORCE_INDEX% on ic.item_id=it.item_id ";
            $CNCAT["sql"]["itemfilter"]["where"] = " it.item_status=1 ";

            $CNCAT["sql"]["countfilter"]["distinct"] = "";
            $CNCAT["sql"]["countfilter"]["from"] = $dbPrefix."itemcat ic ";
            $CNCAT["sql"]["countfilter"]["join"] = "";
            $CNCAT["sql"]["countfilter"]["where"] = " ic.item_status=1 ";
        }
        else
        {               
            $CNCAT["sql"]["itemfilter"]["distinct"] = "";
            $CNCAT["sql"]["itemfilter"]["from"] = $dbPrefix."items it %FORCE_INDEX%";
            $CNCAT["sql"]["itemfilter"]["join"] = "";
            $CNCAT["sql"]["itemfilter"]["where"] = " it.item_status=1 ";            

            $CNCAT["sql"]["countfilter"]["distinct"] = "";
            $CNCAT["sql"]["countfilter"]["from"] = $dbPrefix."itemcat ic ";
            $CNCAT["sql"]["countfilter"]["join"] = "";  
            $CNCAT["sql"]["countfilter"]["where"] = " ic.item_status=1 ";
        }  

    }
                                            
    if ($CNCAT["page"]["item_type"] >= 0) { 
        $CNCAT["sql"]["itemfilter"]["where"] .= " AND it.item_type = " . $CNCAT["page"]["item_type"];
        $CNCAT["sql"]["countfilter"]["where"] .= " AND ic.item_type = " . $CNCAT["page"]["item_type"];
    } elseif (
        $cid == $CNCAT["root_cat_id"]
        && isset($CNCAT["config"]["item_type_on_main"])
        && $CNCAT["config"]["item_type_on_main"] >= 0
    ) {
        $CNCAT["sql"]["itemfilter"]["where"] .= " AND it.item_type = " . $CNCAT["config"]["item_type_on_main"];
        $CNCAT["sql"]["countfilter"]["where"] .= " AND ic.item_type = " . $CNCAT["config"]["item_type_on_main"];
    }
    
    // Category filter
    if ($cid!=$CNCAT["root_cat_id"])
    {
        if ($CNCAT["config"]["show_child_items"]==1 && !empty($CNCAT["page"]["cat"]["child_id_list"]))
            $CNCAT["sql"]["itemfilter"]["category"] = " AND ic.cat_id in (".$cid.",".$CNCAT["page"]["cat"]["child_id_list"].")"; 
        else
            $CNCAT["sql"]["itemfilter"]["category"] = " AND ic.cat_id=".$cid; 
    }
    else
    {
        $CNCAT["sql"]["itemfilter"]["category"] = "";            
    }    
    $CNCAT["sql"]["countfilter"]["category"] = $CNCAT["sql"]["itemfilter"]["category"];            
    
    
    // Filters for new items 
    $CNCAT["sql"]["itemfilter_new"]["distinct"] = cn_str_replace ("%FORCE_INDEX%", $forcedIndexNew, $CNCAT["sql"]["itemfilter"]["distinct"]);
    $CNCAT["sql"]["itemfilter_new"]["from"] = cn_str_replace ("%FORCE_INDEX%", $forcedIndexNew, $CNCAT["sql"]["itemfilter"]["from"]);
    $CNCAT["sql"]["itemfilter_new"]["join"] = cn_str_replace ("%FORCE_INDEX%", $forcedIndexNew, $CNCAT["sql"]["itemfilter"]["join"]);
    $CNCAT["sql"]["itemfilter_new"]["where"] = cn_str_replace ("%FORCE_INDEX%", $forcedIndexNew, $CNCAT["sql"]["itemfilter"]["where"]);            
    $CNCAT["sql"]["itemfilter_new"]["category"] = $CNCAT["sql"]["itemfilter"]["category"];            
    
    // Force index for items in category
    $CNCAT["sql"]["itemfilter"]["from"] = cn_str_replace ("%FORCE_INDEX%", $forcedIndex, $CNCAT["sql"]["itemfilter"]["from"]);
    $CNCAT["sql"]["itemfilter"]["join"] = cn_str_replace ("%FORCE_INDEX%", $forcedIndex, $CNCAT["sql"]["itemfilter"]["join"]);
    $CNCAT["sql"]["itemfilter"]["where"] = cn_str_replace ("%FORCE_INDEX%", $forcedIndex, $CNCAT["sql"]["itemfilter"]["where"]);            
        
    // Getting item count for the current category
    if (
        $CNCAT["page"]["filter_used"]
        || $CNCAT["page"]["item_type"] >= 0
        || (
            $cid == $CNCAT["root_cat_id"]
            && isset($CNCAT["config"]["item_type_on_main"])
            && $CNCAT["config"]["item_type_on_main"] >= 0
        )
    ) {
        $query = "SELECT count(distinct(ic.item_id)) AS cnt FROM ".$CNCAT["sql"]["countfilter"]["from"].
                    " ".$CNCAT["sql"]["countfilter"]["join"].
                    " WHERE ".$CNCAT["sql"]["countfilter"]["where"]." ".$CNCAT["sql"]["countfilter"]["category"].
                    " ";
      
        $res = $CNCAT_ENGINE->db->query($query, "Items count in the current category") or $CNCAT_ENGINE->displayErrorDB (mysql_error());    

        if ($row = mysql_fetch_assoc ($res))
        {           
            $CNCAT["page"]["cat_item_count"] = $row["cnt"];
        }
    }
    else
    {
        if ($cid==$CNCAT["root_cat_id"] || $CNCAT["config"]["show_child_items"])
            $CNCAT["page"]["cat_item_count"] = $CNCAT["page"]["cat"]["item_count_full"];
        else
            $CNCAT["page"]["cat_item_count"] = $CNCAT["page"]["cat"]["item_count"];
    }

    if ($CNCAT["config"]["items_per_page"] == 0) {
        $CNCAT["config"]["items_per_page"] = 1;
    }

    $CNCAT["page"]["page_count"] = ceil($CNCAT["page"]["cat_item_count"] / $CNCAT["config"]["items_per_page"]);

    if ($CNCAT["page"]["page_count"]==0)
        $CNCAT["page"]["page_count"]=1;
    
    if ($CNCAT["page"]["page_num"] < 0)
        $CNCAT["page"]["page_num"] = 0;
    if ($CNCAT["page"]["page_num"] >= $CNCAT["page"]["page_count"])
        $CNCAT["page"]["page_num"] = $CNCAT["page"]["page_count"]-1;
        
    $CNCAT["page"]["start_item_num"] = $CNCAT["page"]["page_num"] * $CNCAT["config"]["items_per_page"]+1;

    $CNCAT["page"]["static_page_links"] = empty($CNCAT["config"]["static_page_enable"])? "": renderStaticPages();

    $CNCAT_ENGINE->loadBanners('cncat');
}

/**
 * Calculate item's count by filtering
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @return array 
 */
function calcItemCountFiltered()
{
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $result = array();
    
    $dbPrefix = $CNCAT["config"]["db"]["prefix"];
    
    // Fetching all categories
    $query = "SELECT id, parent_id, is_link, id_real from ".$dbPrefix."cats";
    $res = $CNCAT_ENGINE->db->query($query, "calcItemCountFiltered() - 1") or $CNCAT_ENGINE->displayErrorDB (mysql_error());
    while ($row = mysql_fetch_assoc ($res))
    {
        $result[$row["id"]] = $row;   
    }

    // Getting item counts    
    $query = "SELECT count(distinct(ic.item_id)) AS cnt, ic.cat_id FROM ".$CNCAT["sql"]["countfilter"]["from"].
                " ".$CNCAT["sql"]["countfilter"]["join"].
                " WHERE ".$CNCAT["sql"]["countfilter"]["where"]." ".
                " GROUP BY ic.cat_id ";
                
    $res = $CNCAT_ENGINE->db->query($query, "calcItemCountFiltered() - 2") or $CNCAT_ENGINE->displayErrorDB (mysql_error());
    while ($row = mysql_fetch_assoc ($res))
    {
        $result[$row["cat_id"]]["item_count"] = $row["cnt"];        
    }
    
    // Calculating for parents
    foreach ($result as $cat)
    {
        $count = $cat["item_count"];
        while ($cat["parent_id"]>0)
        {
            $result[$cat["parent_id"]]["item_count"] += $count;
            $cat = $result[$cat["parent_id"]];
        }
    }
    
    // Calculating count for category-links
    foreach ($result as $id=>$cat)
    {
        if ($cat["is_link"])
            $result[$id]["item_count"] = $result[$cat["id_real"]]["item_count"];
    }
    
    return $result;
};



/**
*   Renders whole page
*   @return render result
*/
function cn_render()
{   
    GLOBAL $CNCAT, $CNCAT_ENGINE, $CN_STRINGS;
     
    // Rendering parts of the page. Order of rendering is important.    
    $items = $CNCAT["page"]["show_items"] ? renderItems("item") : "";

    $newitems = $CNCAT["page"]["show_new_items"] ? renderNewItems() : "";
    $newarticles = $CNCAT["page"]["show_new_items"] ? renderNewArticles() : "";
    
    $categories = renderCategories();
    $rubricators = renderFilters();
    $statistics = renderStatistics();

    $catpath = $CNCAT["page"]["show_cat_path"] ? renderCatPath() : "";
    
    if ($CNCAT["page"]["show_items"])
    {   
        $pages = renderPages();
        $sort = renderSort();
        $itemtype = renderItemTypes();
    }
    else
    {
        $pages = "";
        $sort = "";  
    }

    // Rendering main template and replacing keywords with rendered parts
    $result = $CNCAT_ENGINE->tpl->renderTemplate ("index");    
    
    if ($CNCAT["system"]["debug_templates"])
        $result = preg_replace ("/\{DISPLAY (\w+)}/U".$CN_STRINGS["preg"], "{DISPLAY $1 }$0{/DISPLAY $1}", $result);
    
    $result = cn_str_replace ("{DISPLAY FILTERS}", $rubricators, $result);
    $result = cn_str_replace ("{DISPLAY CAT_PATH}", $catpath, $result);
    $result = cn_str_replace ("{DISPLAY CATEGORIES}", $categories, $result);
    $result = cn_str_replace ("{DISPLAY PAGES}", $pages, $result);
    $result = cn_str_replace ("{DISPLAY SORT}", $sort, $result);
    $result = cn_str_replace ("{DISPLAY ITEMS}", $items, $result);
    $result = cn_str_replace ("{DISPLAY NEW_ITEMS}", $newitems, $result);
    $result = cn_str_replace ("{DISPLAY NEW_ARTICLES}", $newarticles, $result);
    $result = cn_str_replace ("{DISPLAY ITEM_TYPES}", $itemtype, $result);
    $result = cn_str_replace ("{DISPLAY STATISTICS}", $statistics, $result);
    
    return $result;
}

    

/**
*   Renders block of new items
*   @return render result
*/
function renderNewItems()
{
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $result = "";
    
    if ($CNCAT["page"]["show_new_items"])
    {
        $cid = $CNCAT["page"]["cid"];
        $dbPrefix = $CNCAT["config"]["db"]["prefix"];

        $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("newitem", "ITEM", "items");
        $fieldsSqlInt = "it.".join(", it.", $fields["int"]);    
        $limitSql = " LIMIT ".intval($CNCAT["config"]["show_new_items_count"]);

        $orderSql = " it.item_submit_date DESC, it.item_insert_date DESC";

        $whereSql = " AND it.item_type=0 ";

        if ($CNCAT["config"]["show_new_items_period"]) {
            $whereSql .= " AND it.item_submit_date > (NOW() - INTERVAL " . $CNCAT["config"]["show_new_items_period"] . " DAY) ";
        }

        $query = "SELECT DISTINCT ".$fieldsSqlInt." FROM ".$CNCAT["sql"]["itemfilter_new"]["from"].
                    " ".$CNCAT["sql"]["itemfilter_new"]["join"].
                    " WHERE ".$CNCAT["sql"]["itemfilter_new"]["where"].$whereSql." ".$CNCAT["sql"]["itemfilter_new"]["category"]." ".
                    " ORDER BY ".$orderSql.$limitSql;

        $res = $CNCAT_ENGINE->db->query ($query, "New items") or $CNCAT_ENGINE->displayErrorDB (mysql_error());
        if (mysql_num_rows($res)>0)
        {
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("newitems_top");
            while ($CNCAT["item"] = mysql_fetch_assoc($res))
            {
                $CNCAT_ENGINE->render->prepareItemToDisplay ($CNCAT["item"]);
                if (!$CNCAT["item"]["link_target"]) {
                    $CNCAT["item"]["link_target"] = $CNCAT["config"]["link_target"];
                }
                
                if ($CNCAT["config"]["new_items_descr_len"] > 0 && cn_strlen($CNCAT["item"]["item_descr"]) > $CNCAT["config"]["new_items_descr_len"]) {
                    $CNCAT["item"]["item_descr"] = rtrim(cn_substr($CNCAT["item"]["item_descr"], 0, $CNCAT["config"]["new_items_descr_len"]));
                    $CNCAT["item"]["item_descr"] = cn_substr($CNCAT["item"]["item_descr"], 0, cn_strrpos($CNCAT["item"]["item_descr"], " "));
                    $CNCAT["item"]["item_descr"] .= "...";
                }

                if ($CNCAT["config"]["add_short_wysiwyg"]) {
                    $CNCAT["item"]["item_descr"] = $CNCAT["system"]["str_sid"] . $CNCAT["item"]["item_descr"];
                }

                $result .= $CNCAT_ENGINE->tpl->renderTemplate ("newitem");
            }
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("newitems_bottom");
        }
    };    
    return $result;    
}

/**
 * Render block with new articles
 * 
 * @global $CNCAT
 * @global $CNCAT_ENGINE $CNCAT_ENGINE
 * @return $result 
 */
function renderNewArticles()
{
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $result = "";

    if ($CNCAT["page"]["show_new_items"] && $CNCAT_ENGINE->tpl->templateExists("newarticle"))
    {
        $cid = $CNCAT["page"]["cid"];
        $dbPrefix = $CNCAT["config"]["db"]["prefix"];

        $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("newarticle", "ITEM", "items");
        $fieldsSqlInt = "it.".join(", it.", $fields["int"]);    
        $limitSql = " LIMIT ".intval($CNCAT["config"]["show_new_articles_count"]);

        $orderSql = " it.item_submit_date DESC, it.item_insert_date DESC";
        
        $whereSql = " AND it.item_type=1 ";

        if ($CNCAT["config"]["show_new_articles_period"]) {
            $whereSql .= " AND it.item_submit_date > (NOW() - INTERVAL " . $CNCAT["config"]["show_new_articles_period"] . " DAY) ";
        }

        $query = "SELECT DISTINCT ".$fieldsSqlInt." FROM ".$CNCAT["sql"]["itemfilter_new"]["from"].
                    " ".$CNCAT["sql"]["itemfilter_new"]["join"].
                    " WHERE ".$CNCAT["sql"]["itemfilter_new"]["where"].$whereSql." ".$CNCAT["sql"]["itemfilter_new"]["category"]." ".
                    " ORDER BY ".$orderSql.$limitSql;

        $res = $CNCAT_ENGINE->db->query ($query, "New articles") or $CNCAT_ENGINE->displayErrorDB (mysql_error());
        if (mysql_num_rows($res)>0)
        {
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("newarticles_top");
            while ($CNCAT["item"] = mysql_fetch_assoc($res))
            {
                $CNCAT_ENGINE->render->prepareItemToDisplay ($CNCAT["item"]);
                $title = $CNCAT["config"]["use_translit"]? $CNCAT["item"]["item_title_translite"]: $CNCAT["item"]["item_title"]; 
                $CNCAT["item"]["_ext_url"] = $CNCAT_ENGINE->url->createUrlExt(
                    $CNCAT["item"]["item_id"], 
                    $title
                );
                if (!$CNCAT["item"]["link_target"]) {
                    $CNCAT["item"]["link_target"] = $CNCAT["config"]["link_target"];
                }
                
                if ($CNCAT["config"]["new_items_descr_len"] > 0 && cn_strlen($CNCAT["item"]["item_descr"]) > $CNCAT["config"]["new_items_descr_len"]) {
                    $CNCAT["item"]["item_descr"] = rtrim(cn_substr($CNCAT["item"]["item_descr"], 0, $CNCAT["config"]["new_items_descr_len"]));
                    $CNCAT["item"]["item_descr"] = cn_substr($CNCAT["item"]["item_descr"], 0, cn_strrpos($CNCAT["item"]["item_descr"], " "));
                    $CNCAT["item"]["item_descr"] .= "...";
                }

                if ($CNCAT["config"]["add_article_short_wysiwyg"]) {
                    $CNCAT["item"]["item_descr"] = $CNCAT["system"]["str_sid"] . $CNCAT["item"]["item_descr"];
                }

                $result .= $CNCAT_ENGINE->tpl->renderTemplate ("newarticle");
            }
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("newarticles_bottom");
        }
    };
    
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
    $fieldsSqlInt = "it.".join(", it.", $fields["int"]) . ", it.link_favicon_url, it.link_favicon_mime, it.item_type, it.item_com_count";

    foreach ($CNCAT["config"]["extfields"]["items"][0] as $name => $field) {
        if ($field["type"] == 6 || (
            !$field["active"] && !$CNCAT["config"]["extfields"]["items"][0][$name]["active"]
        )) {
            continue;
        }

        $fieldsSqlInt .= ", `" . $name . "`";
    }
    
    $limitSql = " LIMIT ".($CNCAT["page"]["page_num"]*$itemsPerPage).", ".$itemsPerPage;
    

    $orderSql = $CNCAT["sql"]["itemorder"][$CNCAT["page"]["sort_order"]][$CNCAT["page"]["sort_dir"]];
    
    $query = "SELECT ".$CNCAT["sql"]["itemfilter"]["distinct"]." DISTINCT ".$fieldsSqlInt.
                " FROM ".$CNCAT["sql"]["itemfilter"]["from"].
                " ".$CNCAT["sql"]["itemfilter"]["join"]. 
                " WHERE ".$CNCAT["sql"]["itemfilter"]["where"]." ".$CNCAT["sql"]["itemfilter"]["category"]." ".
                " ORDER BY ".$orderSql.$limitSql;

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
        if ($items) {
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
        if (!$CNCAT["item"]["link_target"]) {
            $CNCAT["item"]["link_target"] = $CNCAT["config"]["link_target"];
        }
        if (!$CNCAT["item"]["item_display_ext"]) {
            $CNCAT["item"]["item_display_ext"] = $CNCAT["config"]["display_ext"];
        }
        $title = $CNCAT["config"]["use_translit"]? $CNCAT["item"]["item_title_translite"]: $CNCAT["item"]["item_title"]; 
        $CNCAT["item"]["_ext_url"] = $CNCAT_ENGINE->url->createUrlExt(
            $CNCAT["item"]["item_id"], 
            $title
        );
        $CNCAT["item"]["_number"] = $i;
        $CNCAT["item"]["_count"] = $count;
        $CNCAT["item"]["_isfirst"] = $i==0;
        $CNCAT["item"]["_islast"] = $i==$count-1;  

        // favicon
        if ($CNCAT["config"]["favicon_display"] == 1) {
            if ($CNCAT["item"]["link_favicon_mime"]) {
                $CNCAT["item"]["_favicon_url"] = $CNCAT["abs"] . "{$CNCAT["system"]["dir_prefix"]}image.php?favicon=" . $CNCAT["item"]["item_id"];
            }
        } elseif ($CNCAT["config"]["favicon_display"] == 2) {
            if ($CNCAT["item"]["link_favicon_url"]) {
                $CNCAT["item"]["_favicon_url"] = $CNCAT["item"]["link_favicon_url"];
            }
        }

        if ($CNCAT["config"]["favicon_display"] && $CNCAT["config"]["favicon_yandex"] && !$CNCAT["item"]["_favicon_url"]) {
            $urlp = @parse_url($CNCAT["item"]["link_url"]);
            $CNCAT["item"]["_favicon_url"] = "http://favicon.yandex.ru/favicon/" . $urlp["host"];
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
        
        // extfields
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
        
        if (
            (
                ($CNCAT["config"]["add_short_wysiwyg"] && $CNCAT["item"]["item_type"] == 0)
                || ($CNCAT["config"]["add_article_short_wysiwyg"] && $CNCAT["item"]["item_type"] == 1)
            ) && !empty($CNCAT["item"]["item_descr"])
        ) {
            $CNCAT["item"]["item_descr"] = $CNCAT["system"]["str_sid"] . $CNCAT["item"]["item_descr"];
        }
      
        
        $it_res = $CNCAT_ENGINE->tpl->renderTemplate($CNCAT["item"]["item_type"] == 1 ? "article" : "item");
        $result .= cn_str_replace(array("{DISPLAY ADMIN_RATING}", "{DISPLAY EXTFIELDS}"), array($rat_res, $ext_res), $it_res);
        $i++;
    }

    $result = $CNCAT_ENGINE->tpl->renderTemplate("items_top").$result.$CNCAT_ENGINE->tpl->renderTemplate("items_bottom");        

    return $result;
}

/**
*   Renders block of categories
*   @return render result
*/
function renderCategories()
{
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("cat", "CAT", "cats");
    $fieldsSql = "c.id, c.is_link, c.id_real, c.parent_id, c.path, c.path_full, c.sort_order ".(is_array ($fields) ? ", c.".join(", c.", $fields["int"]) : "");

    $cid = $CNCAT["page"]["cid"];
    $showSubCats = $CNCAT["config"]["show_subcats"]==3 ||
                   $CNCAT["config"]["show_subcats"]==2 && $cid!=$CNCAT["root_cat_id"] ||
                   $CNCAT["config"]["show_subcats"]==1 && $cid==$CNCAT["root_cat_id"];

    $CNCAT["page"]["show_subcats"] = $showSubCats;
                   
    $catOrder = $CNCAT["config"]["cat_sort"] == 1 ? "c.title" : "c.sort_order, c.title";
    $dbPrefix = $CNCAT["config"]["db"]["prefix"];
    
    // Getting categories                                                 
    $query = "SELECT ".$fieldsSql." FROM ".$dbPrefix."cats c WHERE c.parent_id=".$cid." AND c.display = 1 ORDER BY ".$catOrder;
    $res = $CNCAT_ENGINE->db->query ($query, "Categories") or $CNCAT_ENGINE->displayErrorDB (mysql_error());

    // No render if no categories
	$catCount = mysql_num_rows($res);
	if ($catCount==0) 
        return "";
    
    // Reading categories into memory
    $cats = array();
    $catIds = array(); // list of IDs to fetch subcats
    while ($cat = mysql_fetch_assoc($res))
    {    
        $CNCAT_ENGINE->render->prepareCategoryToDisplay($cat);
        
        $cats[] = $cat;
        $catIds[] = $cat["id"];
    }
    
    // Getting subcategories
    $subCats = array();
    if ($showSubCats)
    {
        $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("subcat", "SUBCAT", "cats");
        $fieldsSql = "c.id, c.is_link, c.id_real, c.parent_id, c.path, c.path_full ".(is_array ($fields) ? ", c.".join(", c.", $fields["int"]) : "");
        $query = "
            SELECT " . $fieldsSql . "
            FROM " . $dbPrefix . "cats c
            WHERE c.parent_id IN (" . join(",", $catIds) . ")
                AND c.display = 1
            ORDER BY " . $catOrder . "
        ";
	    $resSub = $CNCAT_ENGINE->db->query ($query, "Subcategories") or $CNCAT_ENGINE->displayErrorDB (mysql_error());    
        while ($subCat = mysql_fetch_assoc($resSub))
        {
            $CNCAT_ENGINE->render->prepareCategoryToDisplay($subCat);
            $subCats[$subCat["parent_id"]][] = $subCat;
        }

    };
        
    // Calculating item counts if filters are used
    if ($CNCAT["page"]["item_type"] >= 0 || ($CNCAT["config"]["use_filters"] && count($CNCAT["page"]["filter_values"])>0)) 
    {
        $itemCounts = calcItemCountFiltered();
        for ($i=0; $i<count ($cats); $i++)
            $cats[$i]["item_count_full"] = intval ($itemCounts[$cats[$i]["id"]]["item_count"]);
        
        foreach ($subCats as $catId=>$cat)
            for ($i=0; $i<count ($cat); $i++)
                $subCats[$catId][$i]["item_count_full"] = intval ($itemCounts[$subCats[$catId][$i]["id"]]["item_count"]);
    }
    
    // Rendering  
    $result .= $CNCAT_ENGINE->tpl->renderTemplate ("cats_top");  

    if ($cid==$CNCAT["root_cat_id"])
        $colCount = $CNCAT["config"]["cat_col_count_root"];
    else
        $colCount = $CNCAT["config"]["cat_col_count"];
    
    if ($colCount==0) 
        $colCount = intval (ceil(sqrt($catCount)));

    $colWidth = intval (100/$colCount);    
    $rowCount = intval (ceil($catCount/$colCount));
    
    $CNCAT["page"]["cat_col_count"] = $colCount;
    $CNCAT["page"]["cat_col_width"] = $colWidth;
    
    $result .= $CNCAT_ENGINE->tpl->renderTemplate ("cats_column_top");  
    $i = 0;
    foreach ($cats as $CNCAT["cat"])
    {
        $CNCAT["cat"]["_number"] = $i;
        $CNCAT["cat"]["_count"] = $catCount;
        $CNCAT["cat"]["_isfirst"] = $i==0;
        $CNCAT["cat"]["_islast"] = $i==$catCount-1;        
        $CNCAT["cat"]["_colcount"] = $colCount;
        $CNCAT["cat"]["_colwidth"] = $colWidth;
        
        if ($i>0 && $i%$rowCount==0)
        {
            $result .= $CNCAT_ENGINE->tpl->renderTemplate ("cats_column_bottom");              
            $result .= $CNCAT_ENGINE->tpl->renderTemplate ("cats_column_top");  
        }
          
        // Category
        $curCat = $CNCAT_ENGINE->tpl->renderTemplate ("cat");
        
        // Subcategories
        if ($showSubCats)
        {
            $subCatStr = $CNCAT_ENGINE->tpl->renderTemplate ("subcats_top");
            if (is_array ($subCats[$CNCAT["cat"]["id"]]))
            {
                $subCatCount = count ($subCats[$CNCAT["cat"]["id"]]);
                $j=0;                        
                foreach ($subCats[$CNCAT["cat"]["id"]] as $CNCAT["subcat"])
                {
                    if ($j>=$CNCAT["config"]["show_subcats_count"])
                    {
                        $subCatStr .= $CNCAT_ENGINE->tpl->renderTemplate ("subcats_more");
                        break;
                    }

                    $CNCAT["subcat"]["_number"] = $j;
                    $CNCAT["subcat"]["_count"] = $subCatCount;
                    $CNCAT["subcat"]["_isfirst"] = $j==0;
                    $CNCAT["subcat"]["_islast"] = $j==$subCatCount-1;        
                    
                    if ($j>0)
                        $subCatStr .= $CNCAT_ENGINE->tpl->renderTemplate ("subcats_delim");   

                    $subCatStr .= $CNCAT_ENGINE->tpl->renderTemplate ("subcat");   

                    $j++;
                }
            }
             
            $subCatStr .= $CNCAT_ENGINE->tpl->renderTemplate ("subcats_bottom");

            $curCat = cn_str_replace ("{DISPLAY SUBCATS}", $subCatStr, $curCat);               
        }
        else
        {
            $curCat = cn_str_replace ("{DISPLAY SUBCATS}", "", $curCat);   
        }
                
        $result .= $curCat;                                                         
        $i++;
    }
    $result .= $CNCAT_ENGINE->tpl->renderTemplate ("cats_column_bottom");  
    $result .= $CNCAT_ENGINE->tpl->renderTemplate ("cats_bottom");  
    return $result;
}

/**
*   Renders filters
*   @return render result
*/
function renderFilters() {
    GLOBAL $CNCAT, $CNCAT_ENGINE;
              
    if (!$CNCAT["config"]["use_filters"] || !$CNCAT["filter_list"])
        return "";

    // Rendering filters
    $result = "";
    $result .= $CNCAT_ENGINE->tpl->renderTemplate("filters_top");
    $i = 0;

    foreach ((array)$CNCAT["filter_list"] as $CNCAT["filter"]) {
        $i++;

        if ($i > 0) { 
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("filters_delim");
        }

        $result .= $CNCAT_ENGINE->tpl->renderTemplate ("filter_top");

        $j=0;

        foreach ((array)$CNCAT["filter"]["_values"] as $CNCAT["filtval"]) {
            $j++;

            if (!isset($CNCAT["filtval"]["_number"])) {
                $CNCAT["filtval"]["_number"] = 0;
            }

            $CNCAT["filtval"]["_number"] = $j;

            if ($j>0) {
                $result .= $CNCAT_ENGINE->tpl->renderTemplate("filtval_delim");
            }

            $result .= $CNCAT_ENGINE->tpl->renderTemplate ("filtval");
        }

        $result .= $CNCAT_ENGINE->tpl->renderTemplate ("filter_bottom");
    }

    if ($CNCAT_ENGINE->tpl->templateExists("filters_end")) {
        $result .= $CNCAT_ENGINE->tpl->renderTemplate("filters_end");
    } else {
        $result .= $CNCAT_ENGINE->tpl->renderTemplate("filters_bottom");
    }
    return $result;
};

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
        if (!in_array($k, explode(",", $CNCAT["config"]["use_sort_orders"]))) {
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
        $CNCAT["sort"]["url"] = $CNCAT_ENGINE->url->createUrlSortIndex($CNCAT["sort"]["id"]);
        if ($i>0)
            $result .= $CNCAT_ENGINE->tpl->renderTemplate ("sort_delim");  
        $result .= $CNCAT_ENGINE->tpl->renderTemplate ("sort");  
    }
    $result .= $CNCAT_ENGINE->tpl->renderTemplate ("sort_bottom");  
    return $result;
}

/**
*   Renders path to the current category
*   @return render result
*/
function renderCatPath()
{
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $result = "";

    $catPathIds = explode ("/",$CNCAT["page"]["cat"]["id_full"]);
    $cats = getCategory($catPathIds, $CNCAT_ENGINE->db->getRecordFieldsForSelect("cat", "CAT", "cats"));

    if ($CNCAT["page"]["cid"]==$CNCAT["root_cat_id"])
    {
        $CNCAT["cat"] = $cats[$CNCAT["root_cat_id"]];
        $result =   $CNCAT_ENGINE->tpl->renderTemplate("catpath_top").
                    $CNCAT_ENGINE->tpl->renderTemplate("catpath_currentcat").
                    $CNCAT_ENGINE->tpl->renderTemplate("catpath_bottom");
    }
    else
    {
        // Root category
        $CNCAT["cat"] = $cats[$CNCAT["root_cat_id"]];

        $result = $CNCAT_ENGINE->tpl->renderTemplate("catpath_top");
        $result .=  $CNCAT_ENGINE->tpl->renderTemplate("catpath_rootcat");

        // Other parent categories
        for ($i=1; $i<count ($catPathIds)-1; $i++)
        {
            $CNCAT["cat"] = $cats[$catPathIds[$i]];
            $result .=  $CNCAT_ENGINE->tpl->renderTemplate("catpath_parentcat_delim").
                        $CNCAT_ENGINE->tpl->renderTemplate("catpath_parentcat");
        }

        // Current category
        $CNCAT["cat"] = $cats[$CNCAT["page"]["cid"]];
        $CNCAT['cat']['_canedit'] = $CNCAT_ENGINE->misc->isAdmin();
        $result .=  $CNCAT_ENGINE->tpl->renderTemplate("catpath_currentcat_delim").
                    $CNCAT_ENGINE->tpl->renderTemplate("catpath_currentcat");

        $result .= $CNCAT_ENGINE->tpl->renderTemplate("catpath_bottom");
    }

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
        return $CNCAT_ENGINE->render->renderPageNavigation($CNCAT_ENGINE->url->createUrlPageIndex ("{PAGE}"), $CNCAT["page"]["page_count"], $CNCAT["page"]["page_num"],8);
    else
        return "";    
}

/**
 * Render Item's types
 * @global $CNCAT
 * @global $CNCAT_ENGINE $CNCAT_ENGINE
 * @return $result 
 */
function renderItemTypes() {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $result = "";
    $result .= $CNCAT_ENGINE->tpl->renderTemplate("itemtypes_top"); 

    $types = array(-1 => $CNCAT["lang"]["itemtypes_all"], 0 => $CNCAT["lang"]["links"], 1 => $CNCAT["lang"]["articles"]);
    $i = 0;

    foreach ($types as $id => $title) {
        $CNCAT["itemtype"]["id"] = $id;
        $CNCAT["itemtype"]["title"] = $title;
        $CNCAT["itemtype"]["url"] = $CNCAT_ENGINE->url->createUrlItemType($id);

        if ($i++ > 0) {
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("itemtypes_delim");
        }

        $result .= $CNCAT_ENGINE->tpl->renderTemplate("itemtype");  
    }
    $result .= $CNCAT_ENGINE->tpl->renderTemplate("itemtypes_bottom");  
    return $result;
}

/**
 * Render block with statistics of catalog
 * @global $CNCAT
 * @global $CNCAT_ENGINE $CNCAT_ENGINE
 * @return $result
 */
function renderStatistics() {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $CNCAT["page"]["show_stats"] = false;

    if ($CNCAT["config"]["show_stats"] > 0) {
        if ($CNCAT["config"]["show_stats"] == 1 && $CNCAT["page"]["cid"] == $CNCAT["root_cat_id"]) {
            $CNCAT["page"]["show_stats"] = true;
        } elseif ($CNCAT["config"]["show_stats"] == 2 && $CNCAT["page"]["cid"] != $CNCAT["show_stats"]) {
            $CNCAT["page"]["show_stats"] = true;
        } elseif ($CNCAT["config"]["show_stats"] == 3) {
            $CNCAT["page"]["show_stats"] = true;
        }
    }

    $query = "
        SELECT item_type, COUNT(*) AS count FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
        WHERE item_status = 1
        GROUP BY item_type
    ";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $items_count = array(0, 0);

    while ($row = mysql_fetch_assoc($res)) {
        $items_count[$row["item_type"]] = $row["count"];
    }

    $CNCAT["page"]["items_count_full"] = $items_count[0] + $items_count[1];
    $CNCAT["page"]["links_count_full"] = $items_count[0];
    $CNCAT["page"]["articles_count_full"] = $items_count[1];

    // Cats count
    $query = "SELECT COUNT(*) AS count FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats`";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $row = mysql_fetch_assoc($res);

    $CNCAT["page"]["cats_count_full"] = $row["count"];

    // Last update date
    $query = "
        SELECT item_submit_date FROM `" . $CNCAT["config"]["db"]["prefix"] . "items`
        ORDER BY item_submit_date DESC
        LIMIT 1
    ";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    $row = mysql_fetch_assoc($res);

    $CNCAT["page"]["last_update"] = $row["item_submit_date"];

    $result = $CNCAT_ENGINE->tpl->renderTemplate("statistics");  
    return $result;
}

/**
*   Returns information about specified category
*   @param cid category ID or array if IDs
*   @param fields array of fields to fetch
*   @return associative array with category fields OR array of arrays (cid=>fields)
*/
function getCategory($cid, $fields)
{
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    if (!$cid) return array();

    $fieldsSql = "c.id, c.id_full, c.parent_id, c.path, c.item_count, c.path_full, c.link_count, c.article_count, c.article_count_full, c.link_count_full, c.last_update ".(is_array ($fields) ? ", c.".join(", c.", $fields["int"]) : "");

    if (is_array($cid))
    {
        foreach ($cid as $k => $v) {
            if ((int)$v == 0) {
                unset($cid[$k]);
            }
        }

        if (!$cid) return array();

        $query = "SELECT ".$fieldsSql." FROM ".$CNCAT["config"]["db"]["prefix"]."cats c WHERE c.id in (".join(",", $cid).")";
        
        $result = array();        
        $res = $CNCAT_ENGINE->db->query ($query, "getCategory(".join (",",$cid).")") or $CNCAT_ENGINE->displayErrorDB (mysql_error());
	    while ($cat = mysql_fetch_assoc($res))
        {
            $CNCAT_ENGINE->render->prepareCategoryToDisplay ($cat);            
            $result[$cat["id"]] = $cat;
        } 
        return $result;        
    }
    else
    {
        $query = "SELECT ".$fieldsSql." FROM ".$CNCAT["config"]["db"]["prefix"]."cats c WHERE c.id=".$cid;
        
        $res = $CNCAT_ENGINE->db->query ($query, "getCategory(".$cid.")") or $CNCAT_ENGINE->displayErrorDB (mysql_error());
	    if ($cat = mysql_fetch_assoc($res))
        {
            $CNCAT_ENGINE->render->prepareCategoryToDisplay ($cat);
            return $cat;        
        }
    }
}

/**
 * Render page for plugin "Static pages"
 * @global $CNCAT
 * @global $CNCAT_ENGINE $CNCAT_ENGINE
 * @return result of render 
 */
function renderStaticPages()
{
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $query = "SELECT `title`, `name` FROM `".$CNCAT["config"]["db"]["prefix"]."pages` WHERE `page_order` != 0  ORDER BY `page_order` ASC";

    $res = $CNCAT_ENGINE->db->query ( $query, "get static page (renderStaticPages)" ) or $CNCAT_ENGINE->displayErrorDB (mysql_error());
    $result = array();
    while ($sp = mysql_fetch_assoc($res))
    {
        $CNCAT["page"]["static_page"] = $sp;
        $result[] = $CNCAT_ENGINE->tpl->renderTemplate("static_page_link");
    }
    return implode(" &nbsp; ", $result);
}
?>
