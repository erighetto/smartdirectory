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
require_once $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_engine_classes"] . "routerfactory.php";

    class CNCatEngine
    {       
        var $url;
        var $db; 
        var $tpl;
        var $render;
        var $misc;

        function CNCatEngine()
        {               
        }
        /**
         * Init CNCat engine objects
         * @global $CNCAT
         * @global $CNCAT_ENGINE
         * @global $CNCAT_PRODUCT
         * @global $CNCAT_PRODUCT_VERSION
         * @global $CNCAT_ENGINE_VERSION
         * @global $CNCAT_SITE_LANG
         * @global $CNCAT_SITE_URL
         * @global $CNCAT_COPYRIGHT
         * @global $CNC_CALL
         * @param $ADMIN_INTERFACE 
         */
        function init($ADMIN_INTERFACE = null)
        {
            GLOBAL $CNCAT, $CNCAT_ENGINE, $CNCAT_PRODUCT, $CNCAT_PRODUCT_VERSION, $CNCAT_ENGINE_VERSION, $CNCAT_SITE_LANG, $CNCAT_SITE_URL, $CNCAT_COPYRIGHT, $CNC_CALL;

            $this->db  	=& cncatCreateObject ("database", "CNCatDatabase");
            $this->tpl 	=& cncatCreateObject ("templates", "CNCatTemplates"); 
            $this->render=&cncatCreateObject ("render", "CNCatRender"); 
            $this->misc =& cncatCreateObject ("misc", "CNCatMisc"); 

            //$this->initABS();
            $this->initConfig();
            $this->initLanguage();
            $this->initStrings();
            $this->initBanners();
            $this->initExtFields();
            
            $this->url = &RouterFactory::getFactory();
                                   
            
            /***************************************
            * SQLs for sorting orders
            * 0 - asc, 1 - desc, 2 - forced index, 3 - default order (0 or 1 - asc or desc), 4 - only for admin
            * Forced index should contain fields: 1 - status, 2 - favour or favour_neg, 3+ - sorting fields.
            * ASC and DESC orders should not be mixed, otherwise MySQL will not use index.
            ***************************************/
            $CNCAT["sql"]["itemorder"] = array (
                // Popularity
                0 => array ("item_favour_neg DESC, link_jumps_from_neg DESC, link_jumps_to_neg DESC, item_title DESC", 
                            "item_favour_neg, link_jumps_from_neg, link_jumps_to_neg, item_title",
                            "popularity",
                            1,
                            0),
                // Title
                1 => array ("item_favour_neg, item_title", 
                            "item_favour_neg DESC, item_title DESC", 
                            "title",
                            0,
                            0),
                // Moderator rating
                2 => array ("item_favour_neg DESC, item_rating_moder_neg DESC, item_title DESC", 
                            "item_favour_neg, item_rating_moder_neg, item_title",                     
                            "rating_moder",
                            1,
                            0), 
                // Submit date
	            3 => array ("item_favour, item_submit_date, item_insert_date",
                            "item_favour DESC, item_submit_date DESC, item_insert_date DESC",                                          
                            "submit_date",
                            1,
                            0),
                // Google PR
                4 => array ("item_favour_neg DESC, link_rating_pr_neg DESC, item_title DESC", 
                            "item_favour_neg, link_rating_pr_neg, item_title",                     
                            "rating_pr",
                            1,
                            0),
                // Yandex CY
                5 => array ("item_favour_neg DESC, link_rating_cy_neg DESC, item_title DESC", 
                            "item_favour_neg, link_rating_cy_neg, item_title",                     
                            "rating_cy",
                            1,
                            0),
                // ID
                6 => array ("item_id ASC, item_title ASC", 
                            "item_id DESC, item_title DESC",
                            "",
                            1,
                            1),
                10 => array ("item_favour_neg DESC, item_rating_users_neg DESC, item_title DESC", 
                            "item_favour_neg, item_rating_users_neg, item_title",
                            "rating_users",
                            1,
                            0),
                // Insert date
                /*7 => array ("item_insert_date DESC, item_title DESC", 
                            "item_insert_date, item_title",
                            "",
                            1,
                            1),
                // Submit date
                8 => array ("item_submit_date DESC, item_title DESC", 
                            "item_submit_date, item_title",
                            "",
                            1,
                            1)*/
            );

            // If user's sort orders are defined - add them to the array
            if (is_array ($CNCAT["config"]["sortorders"]["items"]))
            {
                foreach ($CNCAT["config"]["sortorders"]["items"] as $sortId => $sortOrder)
                {
                    $CNCAT["sql"]["itemorder"][$sortId][0] = $sortOrder["sql"][0];
                    $CNCAT["sql"]["itemorder"][$sortId][1] = $sortOrder["sql"][1];
                    $CNCAT["sql"]["itemorder"][$sortId][2] = $sortOrder["sql"][2];
                    $CNCAT["sql"]["itemorder"][$sortId][3] = $sortOrder["sql"][3];
                    //$CNCAT["lang"]["sort_by_" . $sortId] = $sortOrder["title"][strtok($CNCAT["config"]["language"], "_")];
                }
            }

            /***************************************
            * BASE CONSTANTS
            ***************************************/
            $CNCAT_PRODUCT = "CNCat Links";
            $CNCAT_PRODUCT_VERSION = "4.4.2";
            $CNCAT_ENGINE_VERSION = "4.0";
	        $CNCAT_SITE_LANG = (strtok($CNCAT["config"]["language"], "_")=="ru" ? "ru" : "en");
            $CNCAT_SITE_URL = "http://www.cn-software.com/" . $CNCAT_SITE_LANG . "/cncat/";
            $CNCAT_COPYRIGHT .= "<div style=\"color: gray; font-size: 90%;\">Powered by <a onclick=\"window.open(this.href);return false;\" href=\"" . $CNCAT_SITE_URL . "\" style=\"color: gray;\">CNCat " . $CNCAT_PRODUCT_VERSION . "</a></div>";
            $CNC_CALL = false;
       }
       /**
        * Init DB connection
        */
        function initDB()
        {
            if(!isset($this->db))
                $this->db  	=& cncatCreateObject ("database", "CNCatDatabase");
        }
		
		function initMisc()
        {
            if(!isset($this->misc))
                $this->misc =& cncatCreateObject ("misc", "CNCatMisc");
        }
        /**
        * Loading config
        */
        function initConfig() {
            GLOBAL $CNCAT;

            $res = $this->db->query("SELECT `name`, `value` FROM `" . $CNCAT["config"]["db"]["prefix"]."config`", "Config") or $this->displayError(mysql_error());

            while ($row = mysql_fetch_assoc($res)) {
                $CNCAT["config"][$row["name"]] = isset($CNCAT["config"][$row["name"]])? $CNCAT["config"][$row["name"]]: $row["value"];
            }
			/***************************************
            * Determine ID of the root category.
            * If no category specified in $_GET - set it to Root ID.
            ***************************************/
            $res = $this->db->query ("SELECT id FROM ".$CNCAT["config"]["db"]["prefix"]."cats WHERE parent_id=-1;", "Root category ID") or $this->displayErrorDB(mysql_error());
            if ($cat = mysql_fetch_assoc($res)) 
            {
                $CNCAT["root_cat_id"] = $cat["id"];
                if ($_GET["c"] == 0)
                    $_GET["c"] = $CNCAT["root_cat_id"];
            }
        }

        /**
        * Loading language files
        */
        function initLanguage()
        {
                GLOBAL $CNCAT, $CNCAT_ENGINE;
                /* Engine language file */    
                $CNCAT["lang"] = array();
                @include_once $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_engine_lang"]."lang_en.php";

                if ($CNCAT["config"]["language"] != "en" && !empty ($CNCAT["config"]["language"]))
                {
                    $langFile = $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_engine_lang"]."lang_".$CNCAT["config"]["language"].".php";
                    if (!require_once($langFile))
   	                    $this->displayWarning ("Engine language file &quot;".$langFile."&quot; not found.");
				}

                /* Product language */
                @include_once $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_product_lang"]."lang_en.php";
                if ($CNCAT["config"]["language"] != "en" && !empty ($CNCAT["config"]["language"]))
                {
                    $langFile = $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_product_lang"]."lang_".$CNCAT["config"]["language"].".php";
                    if (!@include ($langDir.$langFile))
   	                    $this->displayWarning ("Product language file &quot;".$langFile."&quot; not found.");
                }

                /* User's language file (may not exist) */
                @include_once $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config_lang"]."lang_en.php";
                if ($CNCAT["config"]["language"] != "en" && !empty ($CNCAT["config"]["language"]))
                {
                    $langFile = $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config_lang"]."lang_".$CNCAT["config"]["language"].".php";
                    @include ($langDir.$langFile);
                }
                
                /* Copy extended field names to $CNCAT["lang"] */
                if (is_array ($CNCAT["config"]["extfields"])) {
                    $lang = strtok($CNCAT["config"]["language"], "_");

                    foreach ($CNCAT["config"]["extfields"] as $tableName=>$extFields) {
                        foreach ($extFields as $extName=>$extValue) {
                            if (isset($extValue["title"][$lang])) {
                                $CNCAT["lang"]["extfield_".$extName] = $extValue["title"][$lang];
                            }
                        }
                    }
                }
        }
        
        
        
        /** 
        * String functions init
        */
        function initStrings()
        {
            GLOBAL $CNCAT, $CNCAT_ENGINE, $CN_STRINGS;
            
            if (!cn_strings_init($CNCAT["lang"]["charset"]=="utf-8", $CNCAT["lang"]["charset"]))
            {
                $this->displayWarning("Can not use UTF-8 character set.<br><b>Reason: </b>".$CN_STRINGS["error"]."<br><br><b>Solution: </b>Please set up your PHP interpreter or specify another language file in {$CNCAT["system"]["dir_config"]}config.php file.");
                die;
            };
        }
        
        /**
        * Calculating absolute URL of CNCat root ($CNCAT["abs"])
        */
        function initABS()
        {
            GLOBAL $CNCAT, $CNCAT_ENGINE;
            if (!isset ($CNCAT["abs"]))
            {
                // Get PHP_SELF
                if (empty($_SERVER["PHP_SELF"])) {
                    $_SERVER["PHP_SELF"] = $_SERVER["SCRIPT_NAME"];
                }

                $len = strlen($CNCAT["system"]["dir_admin"]) + 1;

                if ($CNCAT["config"]["joomla"])
                {
                    $CNCAT["joomla_abs"] = rtrim(str_replace("\\", "/", dirname($_SERVER["PHP_SELF"])), "/") . "/";
                    if (cn_substr($CNCAT["joomla_abs"],-$len,$len)=='/'.$CNCAT["system"]["dir_admin"])
                    {
                        $CNCAT["abs"] = cn_substr($CNCAT["joomla_abs"],0,-$len) . '/';
                    }
                    else
                    {
                        $CNCAT["abs"] = $CNCAT["joomla_abs"] . "components/com_cncat/cncat/";
                    }
               } elseif ($CNCAT["config"]["wordpress"]) {
                    $CNCAT["wp_abs"] = rtrim(str_replace("\\", "/", dirname($_SERVER["PHP_SELF"])), "/") . "/";
                    if (cn_substr($CNCAT["wp_abs"],-$len,$len) == '/'.$CNCAT["system"]["dir_admin"]) {
                        $CNCAT["abs"] = cn_substr($CNCAT["wp_abs"],0,-$len) . '/';
                    }
                    else
                    {
                        $CNCAT["abs"] = $CNCAT["wp_abs"] . "cncat/";
                    }
                } elseif ($CNCAT["config"]["drupal"]) {
                    $CNCAT["drupal_abs"] = rtrim(str_replace("\\", "/", dirname($_SERVER["PHP_SELF"])), "/") . "/";

                    if (cn_substr($CNCAT["drupal_abs"],-$len,$len) == '/'.$CNCAT["system"]["dir_admin"]) {
                        $CNCAT["abs"] = cn_substr($CNCAT["drupal_abs"],0,-$len) . '/';
                    } else {
                        $CNCAT["abs"] = $CNCAT["drupal_abs"] . "modules/cncat/cncat/";
                    }
                }
                else
                {
                    $CNCAT["abs"] = dirname($_SERVER["PHP_SELF"]);
    	              $CNCAT["abs"] = cn_str_replace("\\","/",$CNCAT["abs"]);
                    $CNCAT["abs"] = rtrim($CNCAT["abs"], "/");

                    if (cn_substr($CNCAT["abs"] . "/",-$len,$len) == '/'.$CNCAT["system"]["dir_admin"])
                    {
                        $CNCAT["abs"] = cn_substr($CNCAT["abs"] . "/",0,-$len);
                    }

                    $CNCAT["abs"] .= "/";
                }
            }
        }
        /**
         * Init banner's data
         * @global $CNCAT
         * @global $CNCAT_ENGINE 
         */
        function initBanners() {
            GLOBAL $CNCAT, $CNCAT_ENGINE;

            $CNCAT["banners"] = array();
        
            if ($CNCAT["config"]["show_banners"]) {

                $query = "
                    SELECT bcode, `bcondition`, position, bpage, pattern, item_type, enable_php, cat_id, child_cats, on_cat_main
                    FROM" . chr(32) . $CNCAT["config"]["db"]["prefix"] . "banners
                    WHERE disabled=0" . chr(32) . "ORDER BY sort_order ASC, id ASC";

                $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB (mysql_error());
    
                while ($banner = mysql_fetch_assoc($result)) {
                    $CNCAT["banners"][] = $banner;
                }
            }
        }
        /**
         * Load banners
         * @global $CNCAT
         * @param $page 
         */
        function loadBanners($page) {
            global $CNCAT;

            foreach ($CNCAT["banners"] as $banner) {
                $use_banner = false;

                if ($banner["bpage"] == 0) {
                    $use_banner = true;
                } elseif ($banner['bpage'] == 4) {
                    if (!empty($banner['pattern']) && preg_match($banner['pattern'], $_SERVER['REQUEST_URI'])) {
                        $use_banner = true;
                    }
                }

                $base_uri = cn_substr($_SERVER['REQUEST_URI'], strlen($CNCAT['abs']));

                if ($banner["bpage"] == 1 && empty($base_uri)) {
                    $use_banner = true;
                } elseif ($banner["bpage"] == 2 && !empty($base_uri)) {
                    $use_banner = true;
                }

                if ($page == 'cncat') {
                    if ($banner["bpage"] == 3) {
                        if (
                            $CNCAT["page"]["cid"] == $banner["cat_id"] ||
                            ($banner["child_cats"] && in_array($banner["cat_id"], explode("/", $CNCAT["page"]["cat"]["id_full"])))
                        ) {
                            $use_banner = true;
                        }
                        
                        if ($banner["on_cat_main"] && (
                            $CNCAT["page"]["sort_order"] != $CNCAT["config"]["default_sort_order"] ||
                            $CNCAT["page"]["page_num"] != 0 ||
                            $CNCAT["page"]["item_type"] != -1
                        )) {
                            $use_banner = false;
                        }
                    }
                } elseif ($page == 'search') {
                    if ($banner["page"] == 5) {
                        $use_banner = true;
                    }
                } elseif ($page == 'map') {
                    if ($banner["bpage"] == 6) {
                        $use_banner = true;
                    }
                } elseif ($page == 'add') {
                    if ($banner["bpage"] == 7) {
                        $use_banner = true;
                    }
                } elseif ($page == 'add_article') {
                    if ($banner["bpage"] == 8) {
                        $use_banner = true;
                    }
                } elseif ($page == 'ext0') {
                    if ($banner["bpage"] == 9 && in_array(0, explode(',', $banner['item_type']))) {
                        $use_banner = true;
                    }
                } elseif ($page == 'ext1') {
                    if ($banner["bpage"] == 9 && in_array(1, explode(',', $banner['item_type']))) {
                        $use_banner = true;
                    }
                }

                if ($use_banner) {
                    if (!empty($banner["bcondition"])) {
                        $cond_code = "return (" . $banner["bcondition"] . ");";

                        if (eval($cond_code) == false) {
                            continue;
                        }
                    }
                    
                    if ($banner["enable_php"]) {
                        ob_start();
                        eval('?' . '>' . $banner["bcode"]);
                        $CNCAT["banner"][$banner["position"]] = ob_get_clean();
                    } else {
                        $CNCAT["banner"][$banner["position"]] = $banner["bcode"];
                    }
                }
            }

            $banners = $CNCAT["banner"];
            $CNCAT["banner"] = array();

            // Rendering banners
            if (file_exists($CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config"]."banners.php")) {
                include_once $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config"]."banners.php";
            }

            $CNCAT["banner"] = array_merge($banners, $CNCAT["banner"]);
        }
        /**
         * Init ext. fields
         * @global $CNCAT
         * @global $CNCAT_ENGINE 
         */
        function initExtFields() {
            global $CNCAT, $CNCAT_ENGINE;

            $query = "SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "fields` ORDER BY `sort_order` ASC, `name` ASC";
            $result = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error()); 

            while ($row = mysql_fetch_assoc($result)) {
                $CNCAT["config"]["extfields"]["items"][$row["item_type"]][$row["name"]] = array(
                    "type"       => $row["type"],
                    "active"     => $row["active"],
                    "item_type"  => $row["item_type"],
                    "title"      => $row["title"],
                    "display"    => explode(",", $row["display"]),
                    "search"     => $row["search"],
                    "sort_order" => $row["sort_order"],
                    "required"   => $row["required"]
                );
            }
            
            if (!isset($CNCAT["config"]["extfields"]["items"][0])) {
                $CNCAT["config"]["extfields"]["items"][0] = array();
            }
            
            if (!isset($CNCAT["config"]["extfields"]["items"][1])) {
                $CNCAT["config"]["extfields"]["items"][1] = array();
            }
        }

    ////////////////////////////
    // System functions
    ////////////////////////////
    
        /**
        *   Displays error message and dies.
        */
        function displayError($msg)
        {   
            GLOBAL $CNCAT, $CNCAT_ENGINE;
            $charset = $CNCAT["lang"]["charset"];
            if (empty ($charset))
                $charset = "";
	        ?>
                <HTML><HEAD>
	            <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?php echo $charset ?>">
	            </HEAD><BODY>
                <font color="red"><b>Critical error: </b><?php echo $msg; ?></font>
                </BODY></HTML>
            <?php         
            die;
        }

        /**
        *   Displays database error message and dies.
        */
        function displayErrorDB($msg)
        {   
            GLOBAL $CNCAT, $CNCAT_ENGINE;
            $charset = $CNCAT["lang"]["charset"];
            if (empty ($charset))
                $charset = "";
	        ?>
                <HTML><HEAD>
	            <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?php echo $charset?>">
	            </HEAD><BODY>
                <?php if ($CNCAT["system"]["debug"]) echo $CNCAT["system"]["debug_db_log"]; ?>
                <font color="red"><b>Database error: </b><?php echo $msg; ?></font>
                </BODY></HTML>
            <?php         
            die;
        }
        
        
        /**
        * Displays warning message 
        */
        function displayWarning($msg)
        {   
            GLOBAL $CNCAT, $CNCAT_ENGINE;
            $charset = $CNCAT["lang"]["charset"];
            if (empty ($charset))
                $charset = "";
	        ?>
                <HTML><HEAD>
	            <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?php echo $charset?>">
	            </HEAD><BODY>
                <font color="#880000"><b>Warning:</b> <?php echo $msg; ?></font>
                </BODY></HTML>
            <?php         
        }
    }
?>
