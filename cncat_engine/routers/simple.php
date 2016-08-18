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

class SimpleRouter extends RouterFactory {
    var $http_query_sep;
    function SimpleRouter($ADMIN_INTERFACE)
    {   
        GLOBAL $CNCAT;
        $this->http_query_sep = cn_strstr($CNCAT["config"]["cncat_url"], "?")== true? "&":"?";
        
        if ( $ADMIN_INTERFACE ) {return $ADMIN_INTERFACE;}
        $_GET["c"] = intval($_GET["c"]);
        $_GET["s"] = isset($_GET["s"])? intval($_GET["s"]): -1;
        $_GET["p"] = intval($_GET["p"]);
        $_GET["t"] = isset($_GET["t"])? intval($_GET["t"]): -1;
        //TODO init "d" param
        $_GET["d"] = 0;
        $this->checkActions();
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
        $result = $CNCAT["config"]["cncat_url"] . $this->http_query_sep . 'c=' . $catId;
        
        $result .= $this->getFilterParams();

        if (!empty($CNCAT["page"]["sort_order"]) && $CNCAT["page"]["sort_order"] != $CNCAT["config"]["default_sort_order"]) {
            $result .= "&amp;s=" . $CNCAT["page"]["sort_order"];
        }
         
        if (isset($CNCAT["page"]["item_type"]) &&  $CNCAT["page"]["item_type"] >= 0) {
            $result .= "&amp;t=" . $CNCAT["page"]["item_type"];
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

        $result =  $CNCAT["config"]["cncat_url"] . $this->http_query_sep . "c=" . $CNCAT["page"]["cid"]."&s=".$sortOrder."&p=".$CNCAT["page"]["page_num"];
        $result .= $this->getFilterParams();    
    
        if (isset($CNCAT["page"]["item_type"]) && $CNCAT["page"]["item_type"] >= 0) {
            $result .= "&t=" . $CNCAT["page"]["item_type"];
        }
        
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
        $result .= $this->http_query_sep . 'action=search&s=' . $sortOrder . '&p=' . $CNCAT["page"]["page_num"]
                . '&cn_query=' . urlencode($CNCAT["page"]["search_query"]);
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
        
        $result = $CNCAT["config"]["cncat_url"] . $this->http_query_sep . "c=" . $CNCAT["page"]["cid"] . "&amp;s="
                . $CNCAT["page"]["sort_order"] . "&amp;p=" . $page;

        if (!empty($CNCAT["page"]["filter_values"])) {
            foreach ($CNCAT["page"]["filter_values"] as $id => $state) {
                $result .= "&amp;f" . $id . "=1";
            }
        }

        if (isset($CNCAT["page"]["item_type"]) &&  $CNCAT["page"]["item_type"] >= 0) {
            $result .= "&amp;t=" . $CNCAT["page"]["item_type"];
        }
        
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
        $result .= $this->http_query_sep .  "action=search&s=" . $CNCAT["page"]["sort_order"] . "&p="
                . $page . "&cn_query=" . urlencode($CNCAT["page"]["search_query"]);

        return $result;
    }
 
    function createUrlItemType($type)
    {
        GLOBAL $CNCAT;
        $result = $CNCAT["config"]["cncat_url"] . $this->http_query_sep . "c=" . $CNCAT["page"]["cid"]
                . "&s=" . $CNCAT["page"]["sort_order"] . "&p=" . $CNCAT["page"]["page_num"];

        $result .= $this->getFilterParams();

        if (isset($type) &&  $type >= 0) {
            $result .= "&t=" . $type;
        }

        return $result;
    }

    function createUrlExt($id) {
        GLOBAL $CNCAT;

        return $CNCAT["config"]["cncat_url"] . $this->http_query_sep . "action=ext&id=" . $id;
    }
    
    function getFilterParams()
    {
        GLOBAL $CNCAT;
        $fils = '';
        if (!empty($CNCAT["page"]["filter_values"])) {
            foreach ($CNCAT["page"]["filter_values"] as $id => $state) {
                $fils .= "&f" . $id . "=1";
            }
        }
        return $fils;
    }
    function redirect($action, $params)
    {
        GLOBAL $CNCAT;
        header("Location: {$CNCAT["config"]["cncat_url"]}{$this->http_query_sep}action={$action}&{$params}");
    }
    function createCnUrl($action)
    {
        GLOBAL $CNCAT;

        return $CNCAT["config"]["cncat_url"] . $this->http_query_sep . "action=" . $action;
    }
}