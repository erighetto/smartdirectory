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
* 	Creates SQL query condition for search string
* 	@param q query string
* 	@return array ("fields"=>additional fields ("_relevance"), "where"=>where clause)
*/
function createSearchCondition($q) {
    global $CNCAT;

	$q = cn_trim ($q);
	$uq = cn_strtoupper ($q);

    $fields = "";
    
    $where = "";
    $fields = "";

	/* Simple search */
	if ($CNCAT["config"]["search_method"] == 1) {
        $words = preg_split("/\s+/", $q);

        foreach ($words as $num => $word) {
            if ($num > 5) {
                break;
            }
    
            if (!empty($where)) {
                $where .= "OR ";
            }
    
            $word = cn_str_replace(array("%", "_"), array("\\%", "\\_"), $word);
            $where .= "(";
    	    $where .= "item_title LIKE '%" . mysql_escape_string($word) . "%' OR ";
    	    $where .= "item_descr LIKE '%" . mysql_escape_string($word) . "%' OR ";
    	    $where .= "item_descr_full LIKE '%" . mysql_escape_string($word) . "%' OR ";
    	    $where .= "item_meta_descr LIKE '%" . mysql_escape_string($word) . "%' OR ";
    	    $where .= "item_meta_keywords LIKE '%" . mysql_escape_string($word) . "%' OR ";
    	    $where .= "link_url LIKE '%" . mysql_escape_string($word) . "%'";

    	    foreach ($CNCAT["config"]["extfields"]["items"][0] as $name => $field) {
    	        if (
                    ($field["active"] || $CNCAT["config"]["extfields"]["items"][1][$name]["active"]) &&
                    ($field["search"] || $CNCAT["config"]["extfields"]["items"][1][$name]["search"])
                ) {
                    if ($field["type"] == 3 || $field["type"] == 5) {
    	               $where .= " OR `" . $name . "` LIKE '%" . mysql_escape_string($word) . "%'";
                    }
                }
            }
    	    
    	    $where .= ")";
        }
    } else {
        $match = "MATCH (item_title, item_descr, item_descr_full, item_meta_keywords, item_meta_descr, link_url";
        
        foreach ($CNCAT["config"]["extfields"]["items"][0] as $name => $field) {
            if ($field["type"] == 3 || $field["type"] == 5) {
                $match .= ", `" . $name . "`";
            }
        }
        
        $match .= ") AGAINST ('".mysql_escape_string($q)."')";
      
        $fields = $match." as _relevance";
        //$fields = "";
        $where = $match.">0";
	}

	return array ("fields"=>$fields, "where"=>$where);	
}
  
?>
