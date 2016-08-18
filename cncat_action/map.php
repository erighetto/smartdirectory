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

$CNCAT['page']['title'] = $CNCAT['lang']['menu_map']  . ' / ' . $CNCAT['page']['title'];
$CNCAT_ENGINE->tpl->loadTemplates ($CNCAT["config"]["default_theme"], "map");
$CNCAT_ENGINE->loadBanners("map");

$CNCAT["render_result"] = cn_render();
return;    

/**
*   Renders whole page
*   @return render result
*/
function cn_render()
{   
    GLOBAL $CNCAT, $CNCAT_ENGINE;
    $CNCAT["page"]["search_form_url"] = $CNCAT_ENGINE->url->createCnUrl("search");
    $CNCAT["page"]["add_url"] = $CNCAT_ENGINE->url->createCnUrl("add");
    $CNCAT["page"]["add_article_url"] = $CNCAT_ENGINE->url->createCnUrl("add_article");
    $CNCAT["page"]["map_url"] = $CNCAT_ENGINE->url->createCnUrl("map");
    $categories = renderCategories();
    $result = $CNCAT_ENGINE->tpl->renderTemplate ("map");    
    
    if ($CNCAT["system"]["debug_templates"])
        $result = preg_replace ("/\{DISPLAY (\w+)}/U".$CN_STRINGS["preg"], "{DISPLAY $1 }$0{/DISPLAY $1}", $result);

    $result = cn_str_replace ("{DISPLAY CATEGORIES}", $categories, $result);
    
    return $result;
}

/**
 * Render categories
 * @global $CNCAT
 * @global $CNCAT_ENGINE
 * @return $result 
 */
function renderCategories()
{
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $fields = $CNCAT_ENGINE->db->getRecordFieldsForSelect("map_cat", "CAT", "cats");
    $fieldsSql = "c.id, c.parent_id, c.path, c.path_full, c.sort_order, c.tree_level, c.display, c.child_id_list ".(is_array ($fields) ? ", c.".join(", c.", $fields["int"]) : "");
    $CNCAT["page"]["sort_order"] = $CNCAT["config"]["default_sort_order"];
    
    $cid = $CNCAT["page"]["cid"];
    $showSubCats = $CNCAT["config"]["show_subcats"]==3 ||
                   $CNCAT["config"]["show_subcats"]==2 && $cid!=$CNCAT["root_cat_id"] ||
                   $CNCAT["config"]["show_subcats"]==1 && $cid==$CNCAT["root_cat_id"];

    $CNCAT["page"]["show_subcats"] = $showSubCats;
                   
    $catOrder = $CNCAT["config"]["cat_sort"]==1 ? "c.title_full" : "c.sort_order_global";
    $dbPrefix = $CNCAT["config"]["db"]["prefix"];
    
    // Getting categories                                                 
    $query = "SELECT ".$fieldsSql." FROM ".$dbPrefix."cats c ORDER BY ".$catOrder;
    $res = $CNCAT_ENGINE->db->query($query, "Categories") or $CNCAT_ENGINE->displayErrorDB (mysql_error());

    // No render if no categories
	$catCount = mysql_num_rows($res);
	if ($catCount==0) 
        return "";

    $cats = array();
    $hide_cat_ids = array();
    
    while ($row = mysql_fetch_assoc($res)) {
        if (!$row["display"]) {
            $hide_cat_ids[] = $row["id"];

            foreach ((array)explode(',', $row["child_id_list"]) as $cid) {
                $hide_cat_ids[] = $cid;
            }
        }

        $cats[] = $row;
    }

    $lastLevel = 0;
    foreach ($cats as $cat) {
        $CNCAT["cat"] = $cat;
        
        if (in_array($cat["id"], $hide_cat_ids)) {
            continue;
        }

        if ($CNCAT["cat"]["id"]==$CNCAT["root_cat_id"])
            continue;
        $CNCAT_ENGINE->render->prepareCategoryToDisplay ($CNCAT["cat"]);
        $level = $CNCAT["cat"]["tree_level"];
        while ($level>$lastLevel)
        {
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("map_cat_next_level");
            $lastLevel++;
        }   
        
        while ($level<$lastLevel)
        {
            $result .= $CNCAT_ENGINE->tpl->renderTemplate("map_cat_prev_level");
            $lastLevel--;
        }   

        $result .= $CNCAT_ENGINE->tpl->renderTemplate("map_cat");
        
    }
    
    while (0<$CNCAT["cat"]["tree_level"])
    {
        $CNCAT["cat"]["tree_level"]--;
        $result .= $CNCAT_ENGINE->tpl->renderTemplate("map_cat_prev_level");
    }   
    
    return $result;
}
  
  
?>
