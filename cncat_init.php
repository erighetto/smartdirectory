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

    error_reporting(E_ALL ^ E_NOTICE);
    define("CNCAT_ENGINE", 1);

    // Magic quotes fix
    ini_set("magic_quotes_runtime", 0);
    
    if (get_magic_quotes_gpc()) {
        $_GET = cn_stripslashes_array($_GET);
        $_POST = cn_stripslashes_array($_POST);
        $_COOKIE = cn_stripslashes_array($_COOKIE);
        $_FILES = cn_stripslashes_array($_FILES);

        if (isset($_SESSION)) {
            $_SESSION = cn_stripslashes_array($_SESSION);
        }
    }

    // Register globals fix
    if (ini_get('register_globals') == 1) {
        cn_unregister_globals(
            '_POST',
            '_GET',
            '_COOKIE',
            '_REQUEST',
            '_SERVER',
            '_ENV',
            '_FILES',
            '_SESSION'
        );
    }

    function cn_stripslashes_array($array) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $array[$k] = cn_stripslashes_array($array[$k]);
            } else {
				if (is_object($array[$k])) continue;
                $array[$k] = stripslashes($array[$k]);
            }
        }
    
        return $array;
    }

    function cn_unregister_globals() {
        foreach (func_get_args() as $name) {
            if (isset($GLOBALS[$name])) {
                foreach ($GLOBALS[$name] as $key => $value) {
                    unset($GLOBALS[$key]);
                }
            }
        }
    }

    GLOBAL $CNCAT;
    GLOBAL $CNCAT_ENGINE;
    $CNCAT  = array();

    /***************************************
    * System constants
    ***************************************/
    // Index file and filename prefix
    $CNCAT["system"]["index"] = "cncat.php";   // Name of the index file of CNCat
    $CNCAT["system"]["dir_prefix"] = "cncat_"; // Directory prefix

    // Main directories
    $CNCAT["system"]["dir_root"] = dirname(__FILE__)."/";
    $CNCAT["system"]["dir_action"]  = $CNCAT["system"]["dir_prefix"]."action/";    // Actions
    $CNCAT["system"]["dir_admin"]  = $CNCAT["system"]["dir_prefix"]."admin/";    // Admin
    $CNCAT["system"]["dir_engine"]  = $CNCAT["system"]["dir_prefix"]."engine/";    // Engine
    $CNCAT["system"]["dir_product"] = $CNCAT["system"]["dir_prefix"]."links/";     // Product
    $CNCAT["system"]["dir_config"]  = $CNCAT["system"]["dir_prefix"]."config/";    // Config
    $CNCAT["system"]["dir_export"]  = $CNCAT["system"]["dir_prefix"]."export/";    // Export
    $CNCAT["system"]["dir_manual"]  = $CNCAT["system"]["dir_prefix"]."manual/";    // Manual  
    $CNCAT["abs"] = str_replace("//","/", "/" .trim(str_replace(chr(92), 
                                                    "/", 
                                                    substr($CNCAT["system"]["dir_root"],
                                                           strlen($_SERVER["DOCUMENT_ROOT"]))), 
                                                    "/") . "/"); // Absolute path
      // Admin plugins
    $CNCAT["system"]["dir_admin_plugins"]  = $CNCAT["system"]["dir_admin"]."plugins/";

    // Engine directories
    $CNCAT["system"]["dir_engine_classes"]=$CNCAT["system"]["dir_engine"]."";   // Classes
    $CNCAT["system"]["dir_engine_admin"] = $CNCAT["system"]["dir_engine"]."admin/";    // Admin scripts
    $CNCAT["system"]["dir_engine_common"]= $CNCAT["system"]["dir_engine"]."common/";   // Common scripts
    $CNCAT["system"]["dir_engine_files"] = $CNCAT["system"]["dir_engine"]."files/";    // Directory for uploaded files and images
    $CNCAT["system"]["dir_engine_lang"] =  $CNCAT["system"]["dir_engine"]."lang/";     // Language files
    $CNCAT["system"]["dir_engine_images"]= $CNCAT["system"]["dir_engine"]."images/";   // Image files
    $CNCAT["system"]["dir_engine_lib"] = $CNCAT["system"]["dir_engine"]."lib/";
    $CNCAT["system"]["dir_engine_scripts"]= $CNCAT["system"]["dir_engine"]."scripts/";   // Script files
    $CNCAT["system"]["dir_engine_styles"]= $CNCAT["system"]["dir_engine"]."styles/";   // Style files

    // Product directories
    $CNCAT["system"]["dir_product_classes"] = $CNCAT["system"]["dir_product"]."";    // Classes
    $CNCAT["system"]["dir_product_admin"] = $CNCAT["system"]["dir_product"]."admin/";    // Admin scripts
    $CNCAT["system"]["dir_product_lang"]   = $CNCAT["system"]["dir_product"]."lang/";    // Language files
    $CNCAT["system"]["dir_product_man"]    = $CNCAT["system"]["dir_product"]."man/";     // Manual
    $CNCAT["system"]["dir_product_images"] = $CNCAT["system"]["dir_product"]."images/";  // Themes
    
    // Config directories
    $CNCAT["system"]["dir_config_classes"]  = $CNCAT["system"]["dir_config"]."classes/";    // User classes
    $CNCAT["system"]["dir_config_themes"]  = $CNCAT["system"]["dir_config"]."themes/";    // Themes
    $CNCAT["system"]["dir_config_lang"]    = $CNCAT["system"]["dir_config"]."lang/";      // User's language files
    
    // Image directories     
    
    // Other    
    $CNCAT["system"]["debug"]=0;
    $CNCAT["system"]["debug_templates"] = 0;

    // Generate SID for unhtmlspecialchar strings
    $CNCAT["system"]["str_sid"] = md5(time());

    $base_uri = '';

    if (empty($_SERVER['PHP_SELF'])) {
        $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
    }

    // check install
    if (!defined('INSTALLING')) {
        if (
            is_file($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_config"] . "/config.php") &&
            is_dir($CNCAT["system"]["dir_root"] . "install")
        ) {
            header("Content-type: text/html; charset=UTF-8");
    
            print '<html><head><title>CNCat 4.4.2</title>';
            print '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
            print '<style type="text/css" media="all">';
            print 'body {background: white;}';
            print 'p, li {font-family: Tahoma;font-size: 11px;color: black;}';
            print '</style></head><body>';
            print '<p><a target="_blank" href="http://www.cn-software.com/ru/cncat/">CNCat</a> | ';
            print '<a target="_blank" href="http://cn-software.com/ru/cncat/manual/">Инструкция по установке</a></p>';
            print '<p>Если каталог уже установлен, то удалите папку <strong>install</strong>, иначе запустите <a href="' . $CNCAT["abs"] . 'install/">мастер установки</a>.</p>';
            print '<hr />';
            print '<p><a target="_blank" href="http://www.cn-software.com/en/cncat/">CNCat</a> | ';
            print '<a target="_blank" href="http://cn-software.com/en/cncat/manual/">Installation instruction</a></p>';
            print '<p>If the catalogue has been already installed, then you should delete <strong>install</strong> folder; otherwise launch the <a href="' . $CNCAT["abs"] . 'install/">installation wizard</a>.</p>';
            print '</body>';
            print '</html>';
    
            exit;
        } elseif (!is_file($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_config"] . "/config.php")) {     
            header("Content-type: text/html; charset=UTF-8");
?>
<html>
    <head>
        <title>CNCat 4.4.1</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style type="text/css" media="all">
        body {
            background: white;
        }
        p, li {
            font-family: Tahoma;
            font-size: 11px;
            color: black;
        }
        </style>
    </head>
    <body>
        <p><a target="_blank" href="http://www.cn-software.com/ru/cncat/">CNCat</a> | <a target="_blank" href="http://cn-software.com/ru/cncat/manual/">Инструкция по установке</a></p>
        <p>Прежде чем приступить к работе с каталогом, произведите его установку.</p>
        <ol>
            <li>Запустите <a href="<?php print $CNCAT["abs"];?>install/">мастер установки</a>.</li>
            <li>Удалите папку <strong>install</strong>.</li>
        </ol>
        <hr />
        <p><a target="_blank" href="http://www.cn-software.com/en/cncat/">CNCat</a> | <a target="_blank" href="http://cn-software.com/en/cncat/manual/">Installation instruction</a></p>
        <p>You should install the catalogue before working with it.</p>
        <ol>
            <li>Launch the <a href="<?php print $CNCAT["abs"];?>install/">installation wizard</a>.</li>
            <li>Delete folder <strong>install</strong>.</li>
        </ol>
    </body>
</html>
<?php
            exit;
        }
    }   

    /***************************************
    * Common includes
    ***************************************/

    // System functions
    require_once $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_engine_common"]."system.php";    

    if ($CNCAT["system"]["debug"])
    	$CNCAT["system"]["debug_time_start"] = cncatGetMicrotime();

    // Configs
    ob_start();
    require_once $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config"]."config.php";
    ob_end_clean();

    $CNCAT["config"]["extfields"]["items"] = array();
    $CNCAT["config"]["sortorders"]["items"] = array();
    $CNCAT["banner"] = array();

    ob_start();
    if (file_exists($CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config"]."config_ext.php")) {
	   require_once $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config"]."config_ext.php";
	}
    ob_end_clean();

    // CNStrings library
    require_once $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_engine_common"]."cnstrings.php";    
?>
