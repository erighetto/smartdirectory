<?php 
class RouterFactory {
    /**
     * Loading router class
     * 
     * @global $CNCAT
     * @global $ADMIN_INTERFACE
     * @return load_router 
     */
    function getFactory() {
        GLOBAL $CNCAT, $ADMIN_INTERFACE;
        switch ($CNCAT['config']['url_style']){
            case 1:
                $load_router = array("file_name"  => "urls3.php",
                                     "class_name" => "Urls3Router"); 
                break;
            case 2:
                $load_router = array("file_name"  => "urls4.php",
                                     "class_name" => "Urls4Router");              
                break;
            case 3:
                $load_router = array("file_name"  => "furls.php",
                                     "class_name" => "FurlsRouter");              
                break;
            case 0:
            default:
                $load_router = array("file_name"  => "simple.php",
                                     "class_name" => "SimpleRouter"); 
        }
        $router = $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_engine_classes"] . "routers/" . $load_router["file_name"];
        if (file_exists($router)) {
            require_once $router;
            return new $load_router["class_name"]($ADMIN_INTERFACE);
        } else {
            return null;
        }
    }
    /**
     * Check Action
     * @global $CNCAT
     * @param $uri
     * @return boolean
     */
    function checkActions($uri = null)
    {
        GLOBAL $CNCAT;
        $actions = array( "category"    => "category.php",
                          "ext"         => "ext.php",
                          "add"         => "add.php",
                          "add_article" => "add_article.php",
                          "bot_add"     => "bot_add.php",
                          "search"      => "search.php",
                          "map"         => "map.php",
                          "page"        => "page.php"
        );
        if (isset($_GET['action']) && isset($actions[$_GET['action']])){
            $CNCAT["action_file"] = $actions[$_GET['action']];
            return true;
        }
        if ($uri != null){
            $uri = explode('/', $uri);
            $action = @array_shift($uri);
            
            if (isset($action) && isset($actions[$action])){
                $CNCAT["action_file"] = $actions[$action];
                $_GET['action'] = $action;
                return true;
            }
        }
        $_GET["action"] = "category";
        $CNCAT["action_file"] = $actions[$_GET["action"]];
        return false;
    }
    
    function createUrlCat($catId, $path="") {}
    function createUrlSortIndex($sortOrder) {}
    function createUrlSortSearch($sortOrder){}
    function createUrlPageIndex($page)      {}
    function createUrlPageSearch($page)     {}
    function createUrlItemType($type)       {}
    function createUrlExt($id)              {}
    function redirect($action, $params)     {}
    function createCnUrl($action)           {}
    
}