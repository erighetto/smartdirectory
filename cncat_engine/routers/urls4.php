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

class Urls4Router extends RouterFactory {
    
    function Urls4Router($ADMIN_INTERFACE)
    {   
        GLOBAL $CNCAT, $CN_STRINGS, $CNCAT_ENGINE;
        if ( $ADMIN_INTERFACE ) {return $ADMIN_INTERFACE;}
        $_GET["t"] = -1;
        $_GET["p"] = 0;
        $_GET["s"] = -1;
        $_GET["c"] = $CNCAT["root_cat_id"];        
        
        $uri_parsed = parse_url($_SERVER["REQUEST_URI"]);
        $url_parsed = parse_url($CNCAT["config"]["cncat_url"]);
        $CNCAT['uri'] = cn_substr($uri_parsed['path'], cn_strlen($url_parsed['path']));
        
        if (preg_match ("/item(\d+)\.html/Ui".$CN_STRINGS["preg"], $CNCAT['uri'], $matches))
        {
      	    $_GET["id"] = intval($matches[1]);
        	  $_GET["action"] = 'ext';
        } 
        $this->checkActions($CNCAT['uri']);
        if($CNCAT['uri'] && $_GET["action"] == "category")
        {
            $url = explode ("/", cn_trim($CNCAT['uri'], "/"));
            
            $urlc = count($url);
            
            if (cn_substr($url[$urlc-1], 0, 1) == "?") 
            {
                unset($url[$urlc-1]);        
                $urlc--;
            }
            $param = @array_pop($url);
            if (cn_substr($param,-5)==".html") 
            {
                if (preg_match ("/s(\d+)(?:d(\d+)){0,1}p(\d+)(?:t(\d+))?\.html/Ui".$CN_STRINGS["preg"], $param, $matches)) {
              	    $_GET["s"] = intval($matches[1]);
              	    $_GET["d"] = intval($matches[2]);
              	    $_GET["p"] = intval($matches[3]);
              	    $_GET["t"] = isset($matches[4]) ? intval($matches[4]) : -1;
          	    } else {
          	       $url_not_found = true;
                }
        
                $param = @array_pop($url);      
            }
            
            while(cn_strpos($param, "f") !== false){
                $f_id = intval(cn_substr($param,1));
                if($f_id){
                    $_GET["f" . $f_id] = 1;
                    $param = @array_pop($url);
                }
                else{
                    break;
                }      
            }
            @array_push($url, $param);
            $catPath = cn_trim (join("/", $url), "/");
            
            if (!empty($catPath)) {
                if (strstr($CNCAT["config"]["language"], "_utf8") == "_utf8") {
                    $catPath = urldecode($catPath);
                }
        
                $query = "
                    SELECT id, parent_id
                    FROM `". $CNCAT["config"]["db"]["prefix"]."cats`
                    WHERE
                        path_full = '".mysql_escape_string($catPath)."'
                        AND is_link = 0
                ";
                $res = $CNCAT_ENGINE->db->query($query, "Category ID by path") or $CNCAT_ENGINE->displayErrorDB(mysql_error());
          
                if ($cat = mysql_fetch_assoc($res)) {
                    $_GET["c"] = $cat["id"];
                } else {
                    if ($CNCAT["config"]["handle_not_exists"] == 1) {
                        $_GET["c"] = $CNCAT["root_cat_id"];
                    } else { 
                        $CNCAT_ENGINE->misc->error404();
                        exit;
                    }
                }
            } elseif($_GET["c"] != $CNCAT["root_cat_id"]) {
                if ($CNCAT["config"]["handle_not_exists"] == 1) {
                    $_GET["c"] = $CNCAT["root_cat_id"];
                } else {   
                    $CNCAT_ENGINE->misc->error404();
                    exit;
                }
            }
        } elseif($CNCAT['uri'] && $_GET["action"] == "search") {   
            $url = explode ("/", cn_trim($CNCAT['uri'], "/"));
            $_GET["cn_query"] = array_pop($url);
            $param = array_pop($url); 
            $this->checkParam("s", $param, $url); 
            $this->checkParam("p", $param, $url); 
        } elseif($CNCAT['uri'] && ($_GET["action"] == "add" || $_GET["action"] == "add_article")) {
            $url = explode ("/", cn_trim($CNCAT['uri'], "/"));
            $_GET["submit"] = array_pop($url);
        }       
    }
    /**
    *   Creates URL for specified category according to current URL scheme ($CNCAT["config"]["url_style"])
    *   @param catId id of the category
    *   @param path full path to the category ($CNCAT["cat"]["path_full"])
    *   @return URL
    */
    function createUrlCat($catId, $path="")
    {
        GLOBAL $CNCAT;
        
        $result = "";
        $CNCAT["page"]["sort_order"] = isset($CNCAT["page"]["sort_order"])?$CNCAT["page"]["sort_order"]:$CNCAT["config"]["default_sort_order"];
        $path = implode('/', array_map('urlencode', explode('/', $path)));
                
        if (!empty ($path))        
            $result .= $CNCAT["config"]["cncat_url"].$path."/";
        else
            $result .= $CNCAT["config"]["cncat_url"];
        $result .= $this->getFilterParams();
        if ($CNCAT["page"]["item_type"] >= 0 || $CNCAT["page"]["sort_order"] != $CNCAT["config"]["default_sort_order"]) {
            $result .= "s".$CNCAT["page"]["sort_order"]."p0";

            if (isset($CNCAT["page"]["item_type"]) &&  $CNCAT["page"]["item_type"] >= 0) {
                $result .= "t" . $CNCAT["page"]["item_type"];
            }
            
            $result .= ".html";
        }

        return $result;    
    }

    /**
    *   Creates URL for specified sort order according to current URL scheme ($CNCAT["config"]["url_style"])
    *   @param sortOrder id of sort order
    *   @return URL
    */
    function createUrlSortIndex($sortOrder)
    {
        GLOBAL $CNCAT;

        $result = $CNCAT["config"]["cncat_url"] . $CNCAT["page"]["cat"]["path_full"];
        
        if (!empty ($CNCAT["page"]["cat"]["path_full"]))
            $result .= "/";
        $result .= $this->getFilterParams();    
        $result .= "s".$sortOrder."p".$CNCAT["page"]["page_num"];

        if (isset($CNCAT["page"]["item_type"]) &&  $CNCAT["page"]["item_type"] >= 0) {
            $result .= "t" . $CNCAT["page"]["item_type"];
        }
        
        $result .= ".html";

        return $result;
    }

    /**
    *   Creates URL for specified sort order according to current URL scheme ($CNCAT["config"]["url_style"])
    *   @param sortOrder id of sort order
    *   @return URL
    */
    function createUrlSortSearch($sortOrder)
    {
        GLOBAL $CNCAT;

        $result = $CNCAT["config"]["cncat_url"];
        $result .= 'search/p' . $CNCAT["page"]["page_num"] . '/s' . $sortOrder 
                . '/' . urlencode($CNCAT["page"]["search_query"]);
        return $result;
    }

    /**
    *   Creates URL of index page for specified page number according to current URL scheme ($CNCAT["config"]["url_style"])
    *   @param page page number
    *   @return URL
    */
    function createUrlPageIndex($page)
    {
        GLOBAL $CNCAT;
        
        $result = $CNCAT["config"]["cncat_url"] . $CNCAT["page"]["cat"]["path_full"];
        if (!empty ($CNCAT["page"]["cat"]["path_full"]))
            $result .= "/";
        $result .= $this->getFilterParams();   
        $result .= "s".$CNCAT["page"]["sort_order"]."p".$page;

        if (isset($CNCAT["page"]["item_type"]) &&  $CNCAT["page"]["item_type"] >= 0) {
            $result .= "t" . $CNCAT["page"]["item_type"];
        }
        
        $result .= ".html";
        return $result;
    }

    /**
    *   Creates URL of search page for specified page number according to current URL scheme ($CNCAT["config"]["url_style"])
    *   @param page page number
    *   @return URL
    */
    function createUrlPageSearch($page)
    {
        GLOBAL $CNCAT;

        $result = $CNCAT["config"]["cncat_url"];
        $result .= "search/p" . $page . "/s" . $CNCAT["page"]["sort_order"] . "/"
                . urlencode($CNCAT["page"]["search_query"]);

        return $result;
    }
 
    function createUrlItemType($type)
    {
        GLOBAL $CNCAT;
    
        $result = $CNCAT["config"]["cncat_url"] . $CNCAT["page"]["cat"]["path_full"];
        if (!empty ($CNCAT["page"]["cat"]["path_full"]))
            $result .= "/";
        $result .= $this->getFilterParams();
        $result .= "s".$CNCAT["page"]["sort_order"]."p".$CNCAT["page"]["page_num"];

        if (isset($type) &&  $type >= 0) {
            $result .= "t" . $type;
        }
        
        $result .= ".html";
        
        return $result;
    }

    function createUrlExt($id) {
        GLOBAL $CNCAT;

        return $CNCAT["config"]["cncat_url"] . "item" . $id . ".html";
    }
    
    function getFilterParams()
    {
        GLOBAL $CNCAT;
        $fils = array();
        if (!empty($CNCAT["page"]["filter_values"])) {
            foreach ($CNCAT["page"]["filter_values"] as $id => $state) {
                $fils[] = "f{$id}/";
            }
        }
        return implode($fils);
    }
    function createCnUrl($action="")
    {
        GLOBAL $CNCAT;

        return $CNCAT["config"]["cncat_url"] . $action .'/';
    }
    
    function redirect($action, $params)
    {
        GLOBAL $CNCAT;
        header("Location: {$CNCAT["config"]["cncat_url"]}{$action}/{$params}");
    }
    function checkParam($paramName, &$paramValue, &$url)
    {
        if (cn_strpos($paramValue, $paramName) !== false){
            $param_id = cn_substr($paramValue,1);
            if (is_numeric($param_id)) {
                $paramValue = @array_pop($url);
                $_GET[$paramName] = intval($param_id);
            }
        }
    }
}