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

/******************************************************************************
    Sync
******************************************************************************/
$CNCAT["system"]["dir_config"] = 'cncat_config/'; 
if (!empty($_GET["sync"])) {
    define('INSTALLING', 1);
    define('CNCAT_DIR', dirname(dirname(__FILE__)));
    
    if (file_exists(CNCAT_DIR . '/'.$CNCAT["system"]["dir_config"].'config.php')) { 
        require_once CNCAT_DIR . '/cncat_init.php';
        $CNCAT_ENGINE = cncatCreateObject ("engine", "CNCatEngine");
        $CNCAT_ENGINE->initDB();
		$CNCAT_ENGINE->initConfig();
        require_once CNCAT_DIR . '/cncat_admin/sync_all.php';
        
        cn_syncCats();
    }

    header("Location: index.php?step=7");
    exit;
}
$CAT_URL = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'],"install/"));

/******************************************************************************
    Init
******************************************************************************/
error_reporting(E_ALL & ~E_NOTICE);
session_start();
ini_set("magic_quotes_runtime", 0);

if (get_magic_quotes_gpc()) {
    $_GET = stripslashes_array($_GET);
    $_POST = stripslashes_array($_POST);

    if (isset($_SESSION)) {
        $_SESSION = stripslashes_array($_SESSION);
    }
}

function stripslashes_array($array) {
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            $array[$k] = stripslashes_array($array[$k]);
        } else {
            $array[$k] = stripslashes($array[$k]);
        }
    }

    return $array;
}

if (ini_get('register_globals') == 1) {
    unregister_globals(
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

function unregister_globals() {
    foreach (func_get_args() as $name) {
        if (isset($GLOBALS[$name])) {
            foreach ($GLOBALS[$name] as $key => $value) {
                if (isset($GLOBALS[$key])) {
                    unset($GLOBALS[$key]);
                }
            }
        }
    }
}

function import_scheme($filename, $prefix) {
    global $INSTALL, $LANG;

    $sql = file_get_contents($filename);
    $queries = explode(';', $sql);

    foreach ($queries as $query) {
        $query = str_replace('%prefix%', $prefix, $query);
        $query = trim($query);

        if (empty($query)) {
            continue;
        }

        if (!mysql_query($query)) {
            $INSTALL['errors'][] = $LANG['tablescreate_error'] . ": " . mysql_error();
            break;
        }
    }
}

function import_data($filename, $prefix) {
    global $INSTALL, $LANG;

    $queries = file($filename);

    foreach ($queries as $query) {
        $query = str_replace('%prefix%', $prefix, $query);
        $query = trim($query);

        if (empty($query)) {
            continue;
        }

        if (!mysql_query($query)) {
            $INSTALL['errors'][] = $LANG['tablesinsert_error'] . ': ' . mysql_error();
            break;
        }
    }
}

$CNCAT_VERSION = '4.4.2';
$CNCAT_STATUS = '';
$CNCAT_SITE = 'http://www.cn-software.com/';

$INSTALL = array();
$INSTALL['errors'] = array();

// Step
$INSTALL['step_count'] = 7;
$INSTALL['step'] = 1;

if (!empty($_POST['step']) && is_numeric($_POST['step'])) {
    $INSTALL['step'] = $_POST['step'];
} elseif (!empty($_GET['step']) && is_numeric($_GET['step'])) {
    $INSTALL['step'] = $_GET['step'];
}

if ($INSTALL['step'] < 1 || $INSTALL['step'] > $INSTALL['step_count']) {
    $INSTALL['step'] = 1;
}

// Lang
$INSTALL['available_langs'] = array(
    'en' => 'English',
    'ru' => 'Russian',
    'ua' => 'Ukrainian',
    'cz' => 'Czech',
    'bg' => 'Bulgarian',
    'pl' => 'Polish'
);

if ($INSTALL['step'] == 1 && isset($_POST['doPost'])) {
    $_SESSION['lang'] = $_POST['lang'];
}

$INSTALL['lang'] = !empty($_SESSION['lang']) ? $_SESSION['lang'] : null;

if (empty($INSTALL['lang']) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    preg_match_all('/([a-z-]+)(?:;q=([0-9.]+))?/', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $accept_langs);

    foreach ($accept_langs[1] as $lang) {
        $code = substr($lang, 0, 2);

        if ($code == 'en') {
            $INSTALL['lang'] = 'en';
            break;
        }

        if ($code == 'ru') {
            $INSTALL['lang'] = 'ru';
            break;
        }

        if ($code == 'ua' || $code == 'uk') {
            $INSTALL['lang'] = 'ua';
            break;
        }
    }
}

if (empty($INSTALL['lang']) || !isset($INSTALL['available_langs'][$INSTALL['lang']])) {
    $INSTALL['lang'] = 'en';
}

$lang_file = 'lang_' . $INSTALL['lang'] . '.php';
$LANG = array();

@include 'lang_en.php';

if (file_exists($lang_file)) {
    include $lang_file;
} else {
    die('Language file can not load.');
}

if ($INSTALL['lang'] == 'ru') {
    $CNCAT_SITE .= 'ru/';
} else {
    $CNCAT_SITE .= 'en/';
}


// Auth
$_SESSION['is_auth'] = !isset($_SESSION['is_auth']) ? false : $_SESSION['is_auth'];
$config_path = $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_config"].'config.php'; 
if (@file_exists($config_path) && !$_SESSION['is_auth']) {
    $INSTALL['step'] = 1;

    $CNCAT = array('config' => array());
    include $config_path;

    if (isset($CNCAT['config']['alogin']) && isset($CNCAT['config']['apassword'])) {
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            if ($_POST['login'] == $CNCAT['config']['alogin'] && md5($_POST['passw']) == $CNCAT['config']['apassword']) {
                $_SESSION['is_auth'] = true;
            } else {
                $INSTALL['errors'][] = $LANG['login_or_pass_incorect'];
            }
        }
    } else {
        $_SESSION['is_auth'] = true;
    }
} else {
    $_SESSION['is_auth'] = true;
}

$INSTALL['manual_html_url'] =
    'http://www.cn-software.com/' . ($INSTALL['lang'] == 'ru' ? 'ru' : 'en') .
    '/docs/cncat/' . $CNCAT_VERSION . '/HTML';

// Mod rewrite
$INSTALL['mod_rewrite_support'] = !empty($_GET['m']);

$INSTALL['langs'] = array(
    'en'      => 'English',
    'en_utf8' => 'English (UTF-8)',
    'ru'      => 'Russian',
    'ru_utf8' => 'Russian (UTF-8)',
    'ua'      => 'Ukrainian',
    'ua_utf8' => 'Ukrainian (UTF-8)',
    'cz'      => 'Czech',
    'cz_utf8' => 'Czech (UTF-8)',
    'bg'      => 'Bulgarian',
    'bg_utf8' => 'Bulgarian (UTF-8)',
    'pl'      => 'Polish',
    'pl_utf8' => 'Polish (UTF-8)'
);
$INSTALL['themes'] = array(
    'default' => 'Default',
    'modern' => 'Modern',
);
$INSTALL['tables'] = array(
    'actlog',
    'backlinks',
    'banners',
    'bwlist',
    'cats',
    'checklog',
    'comments',
    'config',
    'fields',
    'filters',
    'filtvals',
    'images',
    'img_cats',
    'itemcat',
    'itemfilt',
    'items',
    'jumps',
    'linkact_comm',
    'linkcheck',
    'mail',
    'modercat',
    'moders',
);

$INSTALL['step_title'] = array(
    1 => $LANG["install_lang_select"],
    2 => $LANG["license"],
    3 => $LANG["system_req"],
    4 => $LANG["db_and_product_settings"],
    5 => $LANG["db_and_product_settings"],
    6 => $LANG["config_file_create"],
    7 => $LANG["install_completion"],
);

/******************************************************************************
    Handlers
******************************************************************************/

if ($INSTALL['step'] == 1) {
    if (isset($_POST['doPost'])) {
        $_POST = array();
        $INSTALL['step'] = 2;
    }
}

if ($INSTALL['step'] == 2) {
    if (isset($_POST['doPost'])) {
        $_POST = array();
        $INSTALL['step'] = 3;
    }
}

if ($INSTALL['step'] == 3) {
    if (isset($_POST['doPost'])) {
        $_POST = array();
        $INSTALL['step'] = 4;
    }
}

if ($INSTALL['step'] == 4) {
    $INSTALL['type'] = null;
    $INSTALL['version_detect'] = null;

    if (isset($_POST["doPost"])) {
        // DB params
        $db = (array)$_POST["db"];

        if (empty($db['host'])) {
            $INSTALL['errors'][] = $LANG["must_be_enter_dbserver"];
        }

        if (empty($db['user'])) {
            $INSTALL['errors'][] = $LANG["must_be_enter_dblogin"];
        }

        if (empty($db['name'])) {
            $INSTALL['errors'][] = $LANG["must_be_enter_dbname"];
        }

        $db['lang'] = 'en';

        if ($INSTALL['lang'] == 'ru' || $INSTALL['lang'] == 'ua') {
            $db['lang'] = 'ru';
        }

        if (!$INSTALL['errors']) {
            if (@mysql_connect($db["host"], $db["user"], $db["password"])) {
                mysql_query("SET NAMES 'utf8'");

                if (!mysql_select_db($db["name"])) {
                    if (!mysql_query("CREATE DATABASE " . mysql_escape_string($db["name"]))) {
                        $INSTALL['errors'][] = $LANG["dbcreate_error"];
                    } else {
                        mysql_select_db($db["name"]);
                    }
                }
            } else {
                $INSTALL['errors'][] = $LANG["dbconnect_error"];
            }
        }
        
        if (!$INSTALL['errors']) {
            $tables = array();

            if ($res1 = mysql_query("SHOW TABLES FROM `" . mysql_escape_string($db['name']) . "` LIKE '" . mysql_escape_string($db['prefix']) . "%'")) {
                while ($row1 = mysql_fetch_row($res1)) {
                    $res2 = mysql_query("DESCRIBE `" . $row1[0] . "`");
                    $tables[substr($row1[0], strlen($db['prefix']))] = array();

                    while ($row2 = mysql_fetch_row($res2)) {
                        $tables[substr($row1[0], strlen($db['prefix']))][] = $row2[0];
                    }
                }
            }

            $exists_tables = array();

            foreach ($INSTALL['tables'] as $table) {
                if (isset($tables[$table])) {
                    $exists_tables[] = $table;
                }
            }

            $INSTALL['version_detect'] = null;

            if ($exists_tables) {
                $INSTALL['version_detect'] = '4.0';
                $INSTALL['version_code'] = 400;
                if (in_array('item_type', $tables['items'])) {
                    $INSTALL['version_detect'] = '4.1';
                    $INSTALL['version_code'] = 410;
                }

                if (isset($tables['banners'], $tables['images'], $tables['img_cats'])) {
                    $INSTALL['version_detect'] = '4.1.1';
                    $INSTALL['version_code'] = 411;
                }

                if (isset($tables['mail'])) {
                    $INSTALL['version_detect'] = '4.1.2';
                    $INSTALL['version_code'] = 412;
                }

                if (isset($tables['fields'])) {
                    $INSTALL['version_detect'] = '4.1.3';
                    $INSTALL['version_code'] = 413;
                }

                if (isset($tables['comments'], $tables['checklog'])) {
                    $INSTALL['version_detect'] = '4.2';
                    $INSTALL['version_code'] = 420;
                }

                if (in_array('display', $tables['cats'])) {
                    $INSTALL['version_detect'] = '4.3';
                    $INSTALL['version_code'] = 430;
                }

                if (in_array('item_submit_type', $tables['items'])) {
                    $INSTALL['version_detect'] = '4.3.1';
                    $INSTALL['version_code'] = 431;
                }

                if (in_array('item_token', $tables['items'])) {
                    $INSTALL['version_detect'] = '4.3.2';
                    $INSTALL['version_code'] = 432;
                }
                if (in_array('last_check', $tables['items'])) {
                    $INSTALL['version_detect'] = '4.3.3';
                    $INSTALL['version_code'] = 433;
                }
                if (isset($tables['linkact_comm'])) {
                    $INSTALL['version_detect'] = '4.3.4';
                    $INSTALL['version_code'] = 434;
                }
                if (in_array('item_title_translite', $tables['items'])) {
                    $INSTALL['version_detect'] = '4.4.0';
                    $INSTALL['version_code'] = 440;
                }
                if (in_array('imgbr_allow', $tables['moders'])) {
                    $INSTALL['version_detect'] = '4.4.1';
                    $INSTALL['version_code'] = 441;
                }
                if (!isset($tables['cnkey'])) {
                    $INSTALL['version_detect'] = '4.4.2';
                    $INSTALL['version_code'] = 442;
                }

            }   		


            $INSTALL['type'] = 'install';

            if ($INSTALL['version_detect']) {
                if ($INSTALL['version_detect'] != $CNCAT_VERSION) {
                    $INSTALL['type'] = 'update';
                } else {
                    $INSTALL['type'] = 'clear';
                }
            }

            $_POST['action'] = isset($_POST['action']) ? $_POST['action'] : '';

            if ($INSTALL['type'] == 'update') {
                if ($_POST['action'] == 'update') {
                    
                    if ($INSTALL['version_code'] == 400) {
                        import_data('update/4_1_0_' . $db['lang'] . '.sql', $db['prefix']);
                    }
                    if ($INSTALL['version_code'] <= 410) {
                        import_data('update/4_1_1_' . $db['lang'] . '.sql', $db['prefix']);
                    }
                    if ($INSTALL['version_code'] <= 411) {
                        import_data('update/4_1_2_' . $db['lang'] . '.sql', $db['prefix']);
                    }
                    if ($INSTALL['version_code'] <= 412) {
                        import_data('update/4_1_3_' . $db['lang'] . '.sql', $db['prefix']);
                    }
                    if ($INSTALL['version_code'] <= 413) {
                        import_data('update/4_2_0_' . $db['lang'] . '.sql', $db['prefix']);
                    }
                    if ($INSTALL['version_code'] <= 420) {
                        import_data('update/4_3_0_' . $db['lang'] . '.sql', $db['prefix']);
                    }
                    if ($INSTALL['version_code'] <= 430) {
                        import_data('update/4_3_1_' . $db['lang'] . '.sql', $db['prefix']);
                    }
                    if ($INSTALL['version_code'] <= 432) {
                        import_data('update/4_3_2_' . $db['lang'] . '.sql', $db['prefix']);
                    }
                    if ($INSTALL['version_code'] <= 433) {
                        import_data('update/4_3_3_' . $db['lang'] . '.sql', $db['prefix']);
                    }
                    if ($INSTALL['version_code'] <= 434) {
                        import_data('update/4_3_4_' . $db['lang'] . '.sql', $db['prefix']);
                    }
                    if ($INSTALL['version_code'] <= 440) {
                        import_data('update/4_4_0_' . $db['lang'] . '.sql', $db['prefix']);
                    }
                    if ($INSTALL['version_code'] <= 441) {
                        import_data('update/4_4_1_' . $db['lang'] . '.sql', $db['prefix']);
                    }
		    if ($INSTALL['version_code'] <= 442) {
                        import_data('update/4_4_2_' . $db['lang'] . '.sql', $db['prefix']);
                    }
                    mysql_query("REPLACE `" . $_SESSION['db']['prefix'] . "config` (`name`, `value`) VALUES ('cncat_url' , '" . mysql_escape_string(trim($_POST['cncat_url'], "/")."/") . "')");
            
                    if (!$INSTALL['errors']) {
                        $_SESSION['db'] = $db;
        
                        $_POST = array();
                        $INSTALL['step'] = 7;
                    }

                    header('Location: index.php?sync=1');
                    exit;
                } elseif ($_POST['action'] == 'clear') {
                    $tables_drop = array();
        
                    foreach ($INSTALL['tables'] as $table) {
                        $tables_drop[] = '`' . $db['prefix'] . $table . '`';
                    }

                    if ($tables_drop) {
                        mysql_query('DROP TABLE IF EXISTS ' . implode(',', $tables_drop));
                    }

                    import_scheme('cncat.sql', $db['prefix']);
                    mysql_query("REPLACE `" . $_SESSION['db']['prefix'] . "config` (`name`, `value`) VALUES ('cncat_url' , '" . mysql_escape_string(trim($_POST['cncat_url'], "/")."/") . "')");
            
                    if (!$INSTALL['errors']) {
                        $_SESSION['db'] = $db;
        
                        $_POST = array();
                        $INSTALL['step'] = 5;
                    }
                }
            } elseif ($INSTALL['type'] == 'clear') {
                if ($INSTALL['version_detect'] && $_POST['action'] == 'clear') {
                    $tables_drop = array();

                    foreach ($INSTALL['tables'] as $table) {
                        $tables_drop[] = '`' . $db['prefix'] . $table . '`';
                    }

                    if ($tables_drop) {
                        mysql_query('DROP TABLE IF EXISTS ' . implode(',', $tables_drop));
                    }

                    import_scheme('cncat.sql', $db['prefix']);
                    mysql_query("REPLACE `" . $_SESSION['db']['prefix'] . "config` (`name`, `value`) VALUES ('cncat_url' , '" . mysql_escape_string(trim($_POST['cncat_url'], "/")."/") . "')");
            
                    if (!$INSTALL['errors']) {
                        $_SESSION['db'] = $db;
        
                        $_POST = array();
                        $INSTALL['step'] = 5;
                    }
                }
            } elseif ($INSTALL['type'] == 'install') {
                if ($_POST['action'] == 'install') {
                    import_scheme('cncat.sql', $db['prefix']);
                    import_data('cncat_data_' . $db['lang'] . '.sql', $db['prefix']);

                    if (!$INSTALL['errors']) {
                        $_SESSION['db'] = $db;
        
                        $_POST = array();
                        $INSTALL['step'] = 5;
                    }
                }
            }
        }
    } else {
        $_POST["db"] = array();
        $_POST["db"]["host"] = "localhost";
        $_POST["db"]["prefix"] = "cncat4_";

        if (file_exists("../{$CNCAT["system"]["dir_config"]}config.php")) {
            $CNCAT["config"] = array();
            @include "../{$CNCAT["system"]["dir_config"]}config.php";
            $_POST["db"] = $CNCAT["config"]["db"];
        }
    }
}

if ($INSTALL['step'] == 5) {
    if (isset($_POST["doPost"])) {
        // language
        $lang = $_POST["lang"];

        if (!isset($INSTALL['langs'][$lang])) {
            $lang = 'en_utf8';
        }

        // theme
        $theme = $_POST["theme"];

        if (!isset($INSTALL['themes'][$theme])) {
            $theme = 'default';
        }

        // data lang
        $data_lang = $_POST["data_lang"];

        if ($data_lang != 'ru' && $data_lang != 'en') {
            $data_lang = 'en';
        }

        // charset
        $db = array();
        $db['charset'] = "utf8";

        if (substr($lang, -4) == 'utf8') {
            $db['charset'] = 'utf8';
        } elseif ($lang == 'ru' || $lang == 'ua' || $lang == 'bg') {
            $db['charset'] = 'cp1251';
        } elseif ($lang == 'cz' || $lang == 'pl') {
            $db['charset'] = 'cp1250';
        } else {
            $db['charset'] = 'latin1';
        }

        // Admin
        $admin = (array)$_POST["admin"];

        if (empty($admin["login"])) {
            $INSTALL['errors'][] = $LANG["must_be_enter_alogin"];
        }

        if (empty($admin["passw"])) {
            $INSTALL['errors'][] = $LANG["must_be_enter_apass"];
        } elseif ($admin["passw"] != $admin["passw2"]) {
            $INSTALL['errors'][] = $LANG["invalid_confirm_apass"];
        }

        if (!$INSTALL['errors']) {
            if (
                !@mysql_connect($_SESSION['db']['host'], $_SESSION['db']['user'], $_SESSION['db']['password']) ||
                !@mysql_select_db($_SESSION['db']['name'])
            ) {
                $INSTALL['errors'][] = $LANG["dbconnect_error"];
            }
        }

        if (!$INSTALL['errors']) {
            mysql_query("SET NAMES 'utf8'");
            import_data('cncat_data_' . $data_lang . '.sql', $_SESSION['db']['prefix']);
            mysql_query("REPLACE `" . $_SESSION['db']['prefix'] . "config` (`name`, `value`) VALUES ('cncat_url' , '" . mysql_escape_string(trim($_POST['cncat_url'], "/")."/") . "')");
            mysql_query("UPDATE `" . $_SESSION['db']['prefix'] . "config` SET `value`='" . mysql_escape_string($theme) . "' WHERE `name`='default_theme'");
            mysql_query("UPDATE `" . $_SESSION['db']['prefix'] . "config` SET `value`='" . mysql_escape_string($lang) . "' WHERE `name`='language'");
            mysql_query("UPDATE `" . $_SESSION['db']['prefix'] . "config` SET `value`=" . ($INSTALL['mod_rewrite_support'] ? 2 : 0) . " WHERE `name`='url_style'");
    
            $_SESSION["db"]["charset"] = $db['charset'];
            $_SESSION["admin"] = array(
                "login" => $admin["login"],
                "passw" => md5($admin["passw"])
            );

            $_POST = array();
            $INSTALL['step'] = 6;
        }
    } else {
        if (file_exists("../{$CNCAT["system"]["dir_config"]}config.php")) {
            $CNCAT["config"] = array();
            @include "../{$CNCAT["system"]["dir_config"]}config.php";
            $_POST["db"] = $CNCAT["config"]["db"];
        }

        $_POST["admin"]["login"] = $CNCAT["config"]["alogin"];
    }
}

if ($INSTALL['step'] == 6) {
    if (isset($_POST['doPost'])) {
        $_POST = array();
        $INSTALL['step'] = 7;
    } else {
        $config = "<?php\n";
        $config .= "// Common config settings\n";
        $config .= "// All settings in this file will override the settings loaded from database\n";
        $config .= "\$CNCAT[\"config\"][\"alogin\"] = \"" . str_replace("\"", "\\\"", $_SESSION["admin"]["login"]) . "\";\n";
        $config .= "\$CNCAT[\"config\"][\"apassword\"] = \"" . str_replace("\"", "\\\"", $_SESSION["admin"]["passw"]) . "\";\n\n";
        $config .= "\$CNCAT[\"config\"][\"db\"][\"host\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["host"]) . "\";\n";
        $config .= "\$CNCAT[\"config\"][\"db\"][\"user\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["user"]) . "\";\n";
        $config .= "\$CNCAT[\"config\"][\"db\"][\"password\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["password"]) . "\";\n";
        $config .= "\$CNCAT[\"config\"][\"db\"][\"name\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["name"]) . "\";\n";
        $config .= "\$CNCAT[\"config\"][\"db\"][\"prefix\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["prefix"]) . "\";\n";
        $config .= "\$CNCAT[\"config\"][\"db\"][\"charset\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["charset"]) . "\";\n";
        if (file_exists('extra_config.conf')) $config .=  file_get_contents('extra_config.conf');
        if (!file_exists('../'.$CNCAT["system"]["dir_config"].'config.php')) {
            if (($f = @fopen('../'.$CNCAT["system"]["dir_config"].'config.php', 'w+t')) && @fwrite($f, $config)) {
                $INSTALL['step'] = 7;
            }
        } else {
            $CNCAT["config"] = array();
            @include "../{$CNCAT["system"]["dir_config"]}config.php";
            
            $cdb1 = $CNCAT["config"]["db"];
            $cdb2 = $_SESSION["db"];

            if (
                $cdb1['host'] == $cdb2['host'] &&
                $cdb1['user'] == $cdb2['user'] &&
                $cdb1['password'] == $cdb2['password'] &&
                $cdb1['name'] == $cdb2['name'] &&
                $cdb1['prefix'] == $cdb2['prefix'] &&
                $cdb1['charset'] == $cdb2['charset']
            ) {
                if (
                    $CNCAT["config"]["alogin"] == $_SESSION["admin"]["login"] &&
                    $CNCAT["config"]["apassword"] == $_SESSION["admin"]["passw"]
                ) {
                    $INSTALL['step'] = 7;
                }
            }
        }
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php print str_replace('%NAME%', 'CNCat ' . $CNCAT_VERSION . (!empty($CNCAT_STATUS) ? ' ' . $CNCAT_STATUS : ''), $LANG['cncat_installation'])?></title>
        <style type="text/css">
        td,th,body,input,select {font-family:tahoma,sans-serif;font-size:11px;}
        a,a:visited {text-decoration:none;color:blue;}
        a:hover {text-decoration:underline}
        .t0 {background-color:#9bc871;}
        .t1 {background-color:#e8f0f4;}
        .t2 {background-color:#ffffff;}
        .ttl {width:100%;background:#9bc871;color:white;padding:6px;text-align:left;}
        .m0 {margin:0px;}
        .errors li {color:red;}
        .warn {color:red;}
        </style>
    </head>
<body>
    <table cellspacing="0" cellpadding="5" border="0" width="100%">
        <tr><th class="ttl" style="text-align:left;">CNCat <?php print $CNCAT_VERSION . (!empty($CNCAT_STATUS) ? ' ' . $CNCAT_STATUS : '')?>. <?php print str_replace(array("%NUM%", "%COUNT%", "%NAME%"), array($INSTALL['step'], $INSTALL['step_count'], $INSTALL['step_title'][$INSTALL['step']]), $LANG["step"])?></th></tr>
    </table>
    <br />
    <table cellspacing="1" cellpadding="3" border="0" align="center" width="650">
        <tr><td align="right">
            <a target="_blank" href="<?php print $CNCAT_SITE?>">CNCat</a> |
            <a target="_blank" href="<?php print $INSTALL['manual_html_url']?>"><?php print $LANG["install_manual"]?></a> <!--(<a target="_blank" href="<?php print $INSTALL['manual_pdf_url']?>">PDF</a>)-->
        </td></tr>
    </table>
    <br />
<?php

if (!$_SESSION['is_auth']) {
    $INSTALL['step'] = 0;
?>
    <form action="?" method="post">
<?php
    if ($INSTALL['errors']) {
?>
        <table cellspacing="1" cellpadding="3" border="0" align="center" width="650">
            <tr><td>
<?php
    print "<p>" . $LANG["error_after_process"] . ":</p>";
    print "<ul class=\"errors\">\n";
    print "<li>" . join("</li><li>", $INSTALL['errors']) . "</li>";
    print "</ul>\n";
?>
            </td></tr>
        </table>
<?php
    }
?>
        <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
            <tr><th class="ttl" colspan="2"><?php print $LANG['authorization']?></th></tr>
            <tr class="t2">
                <td width="100%" colspan="2">
                    <?php print $LANG['auth_for_install']?>
                </td>
            </tr>
            <tr class="t1">
                <td width="100%"><?php print $LANG['login']?>:</td>
                <td><input type="text" name="login" class="text" style="width:300px;" value="<?php print htmlspecialchars($_POST['login'])?>" /></td>
            </tr>
            <tr class="t2">
                <td width="100%"><?php print $LANG['pass']?>:</td>
                <td><input type="password" name="passw" class="text" style="width:300px;" /></td>
            </tr>
            <tr class="t1"><td colspan=2 align=right>
                <input type="submit" style="margin: 10px;" value="&nbsp;&nbsp;<?php print $LANG["next"]?>&nbsp;&nbsp;&gt;&gt;" />
                <input type="button" style="margin: 10px;" value="&nbsp;&nbsp;<?php print $LANG["cancel"]?>&nbsp;&nbsp;" onclick="location.href='../'" />
            </td></tr>
        </table>
    </form>
<?php
}

/******************************************************************************
    Step 1
******************************************************************************/
if ($INSTALL['step'] == 1) {
?>
    <form action="?" method="post">
        <input type="hidden" name="step" value="1" />
        <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
            <tr><th class="ttl" colspan="2"><?php print $LANG['install_lang_select']?></th></tr>
            <tr class="t2">
                <td width="100%"><?php print $LANG['language']?>:</td>
                <td><select name="lang" style="width:300px;">
<?php
    foreach ($INSTALL['available_langs'] as $code => $lang) {
?>
                    <option value="<?php print $code?>"<?php print $INSTALL['lang'] == $code ? ' selected="selected"' : ''?>><?php print $lang?></option>
<?php
    }
?>
                </select></td>
            </tr>
            <tr class="t1"><td colspan=2 align=right>
                <input type="submit" name="doPost" style="margin: 10px;" value="&nbsp;&nbsp;<?php print $LANG["next"]?>&nbsp;&nbsp;&gt;&gt;" />
                <input type="button" style="margin: 10px;" value="&nbsp;&nbsp;<?php print $LANG["cancel"]?>&nbsp;&nbsp;" onclick="location.href='../'" />
            </td></tr>
        </table>
    </form>
<?php
/******************************************************************************
    Step 2
******************************************************************************/
} elseif ($INSTALL['step'] == 2) {
?>
    <form action="?" method="post">
        <input type="hidden" name="step" value="2" />
        <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
            <tr><th class="ttl" colspan="2"><?php print $LANG["license"]?></th></tr>
            <tr class="t2">
                <td width="100%" colspan="2">
                    <?php print $LANG["read_license"]?>:
                </td>
            </tr>
            <tr class="t1">
                <td width="100%" colspan="2">
                    <textarea style="width: 100%; height: 400px;" readonly="readonly"><?php print @file_get_contents('../COPYRIGHT');?></textarea>
                </td>
            </tr>
            <tr class="t2">
                <td width="100%" colspan="2">
                    <input type="checkbox" id="accept_license" style="vertical-align: middle;" onclick="this.form.doPost.disabled=!this.checked;" /> <label for="accept_license"><?php print $LANG["accept_license"]?></label>
                </td>
            </tr>
            <tr class="t1"><td colspan=2 align=right>
                <input type="submit" name="doPost" style="margin: 10px;" value="&nbsp;&nbsp;<?php print $LANG["next"]?>&nbsp;&nbsp;&gt;&gt;" disabled="disabled" />
                <input type="button" style="margin: 10px;" value="&nbsp;&nbsp;<?php print $LANG["cancel"]?>&nbsp;&nbsp;" onclick="location.href='../'" />
            </td></tr>
        </table>
    </form>
<?php
/******************************************************************************
    Step 3
******************************************************************************/
} elseif ($INSTALL['step'] == 3) {
    $check = array();
    $info = array();
    
    $check["php_version"] = version_compare(PHP_VERSION, "5.0", ">=");
    $info["php_version"] = PHP_VERSION;

    $check["php_mysql"] = extension_loaded("mysql");
    $check["php_gd"] = extension_loaded("gd") || extension_loaded("gd2");
    $check["php_mbstring"] = extension_loaded("mbstring");
	$check["php_iconv"] = extension_loaded("iconv");
?>
    <form action="?" method="post">
        <input type="hidden" name="step" value="3" />
        <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
            <tr><th class="ttl" colspan="2"><?php print $LANG["check_result"]?></th></tr>
            <tr class="t2">
                <td width="100%" colspan="2">
                    <table border="0">
                    <tr class="t1"><td style="padding: 5px; width: 150px;">PHP 5.+</td><td style="padding: 5px;"><strong><?php print $check["php_version"] ? "<span style=\"color: green\">" . $LANG["yes"] . "</span>" : "<span style=\"color: red\">" . $LANG["no"] . "</span>"?></strong></td></tr>
                    <tr><td style="padding: 5px; width: 150px;">MySQL</td><td style="padding: 5px;"><strong><?php print $check["php_mysql"] ? "<span style=\"color: green\">" . $LANG["yes"] . "</span>" : "<span style=\"color: red\">" . $LANG["no"] . "</span>"?></strong></td></tr>
                    <tr class="t1"><td style="padding: 5px; width: 150px;">GD</td><td style="padding: 5px;"><strong><?php print $check["php_gd"] ? "<span style=\"color: green\">" . $LANG["yes"] . "</span>" : "<span style=\"color: red\">" . $LANG["no"] . "</span>"?></strong></td></tr>
                    <tr><td style="padding: 5px; width: 150px;">MB String</td><td style="padding: 5px;"><strong><?php print $check["php_mbstring"] ? "<span style=\"color: green\">" . $LANG["yes"] . "</span>" : "<span style=\"color: red\">" . $LANG["no"] . "</span>"?></strong></td></tr>
					<tr class="t1"><td style="padding: 5px; width: 150px;">Iconv</td><td style="padding: 5px;"><strong><?php print $check["php_iconv"] ? "<span style=\"color: green\">" . $LANG["yes"] . "</span>" : "<span style=\"color: red\">" . $LANG["no"] . "</span>"?></strong></td></tr>
                    </table>
                </td>
            </tr>
<?php
    if (ini_get('register_globals') == 1) {
?>
            <tr class="t1">
                <td>
                    <p><?php print $LANG["register_globals_warning"]?></p>
                    <p><strong><?php print $LANG["can_continue_install"]?></strong></p>
                </td>
            </tr>
<?php
    }
?>
<?php
    if (!$INSTALL['mod_rewrite_support']) {
?>
            <tr class="t1">
                <td>
                    <p><?php print $LANG["mod_rewrite_warning"]?></p>
                    <p><strong><?php print $LANG["can_continue_install"]?></strong></p>
                </td>
            </tr>
<?php
    }  
?>
<?php
    if (in_array(false, $check)) {
?>
            <tr class="t1">
                <td>
                    <p><?php print $LANG["system_req_warning"]?></p>
                    <p><strong><?php print $LANG["can_continue_install"]?></strong></p>
                </td>
            </tr>
<?php
    }
?>
            <tr class="t1"><td colspan=2 align=right>
                <input type="submit" name="doPost" style="margin: 10px;" value="&nbsp;&nbsp;<?php print $LANG["next"]?>&nbsp;&nbsp;&gt;&gt;" />
                <input type="button" style="margin: 10px;" value="&nbsp;&nbsp;<?php print $LANG["cancel"]?>&nbsp;&nbsp;" onclick="location.href='../'" />
            </td></tr>
        </table>
    </form>
<?php
} elseif ($INSTALL['step'] == 4) {
?>
    <form action="?" method="post">
        <input type="hidden" name="step" value="4" />
<?php
    if ($INSTALL['errors']) {
?>
        <table cellspacing="1" cellpadding="3" border="0" align="center" width="650">
            <tr><td>
<?php
    print "<p>" . $LANG["error_after_process"] . ":</p>";
    print "<ul class=\"errors\">\n";
    print "<li>" . join("</li><li>", $INSTALL['errors']) . "</li>";
    print "</ul>\n";
?>
            </td></tr>
        </table>
<?php
    }

    if ($INSTALL['type'] == 'update') {
?>
        <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
            <tr><th class="ttl" colspan="2"><?php print $LANG['update']?></th></tr>
            <tr class="t2"><td colspan="2"><?php print $LANG['']?>
                <?php print str_replace('%VERSION%', $INSTALL['version_detect'], $LANG['detect_old_version'])?><br />
                <p>
                    <input type="radio" name="action" value="update" id="update" checked="checked" style="vertical-align: bottom;" />
                    <label for="update"><strong><?php print str_replace('%VERSION%', $CNCAT_VERSION, $LANG['update_to_version'])?></strong></label>
                    <div style="color: red;"><?php print $LANG['update_warning']?></div>
                </p>
                <p>
                    <input type="radio" name="action" value="clear" id="reinstall" style="vertical-align: bottom;" />
                    <label for="install"><?php print str_replace('%VERSION%', $CNCAT_VERSION, $LANG['clear_and_install'])?></label>
                    <div style="color: red;"><?php print $LANG['reinstall_warning']?></div>
                </p>
            </td></tr>
            <tr class="t1">
                <td width="100%"><?php print $LANG["cncat_url"]?>:</td>
                <td><input type="text" name="cncat_url" value="<?php print $_POST["cncat_url"]?$_POST["cncat_url"]:"http://{$_SERVER['HTTP_HOST']}{$CAT_URL}"?>" style="width:300px;" /></td>
            </tr> 
<?php
    } elseif ($INSTALL['type'] == 'clear')  {
?>
        <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
            <tr><th class="ttl" colspan="2"><?php print $LANG['install']?></th></tr>
            <tr class="t2"><td colspan="2">
                <?php print str_replace('%VERSION%', $INSTALL['version_detect'], $LANG['detect_last_version'])?><br />
                <p>
                    <input type="checkbox" name="action" value="clear" id="install" style="vertical-align: middle;" />
                    <label for="install"><?php print $LANG['clear_and_reinstall']?></label>
                </p>
                <p style="color: red;">
                    <?php print $LANG['reinstall_warning']?>
                </p>
            </td></tr>
            <tr class="t1">
                <td width="100%"><?php print $LANG["cncat_url"]?>:</td>
                <td><input type="text" name="cncat_url" value="<?php print $_POST["cncat_url"]?$_POST["cncat_url"]:"http://{$_SERVER['HTTP_HOST']}{$CAT_URL}"?>" style="width:300px;" /></td>
            </tr> 
<?php
    } else {
?>
            <input type="hidden" name="action" value="install" />
<?php
    }  
?>
        <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
            <tr><th class="ttl" colspan="2"><?php print $LANG["mysql_settings"]?></th></tr>
            <tr class="t2">
                <td width="100%"><?php print $LANG["mysql_server"]?>:</td>
                <td><input type="text" name="db[host]" value="<?php print $_POST["db"]["host"]?>" style="width:300px;" /></td>
            </tr>
            <tr class="t1">
                <td width="100%"><?php print $LANG["mysql_login"]?>:</td>
                <td><input type="text" name="db[user]" value="<?php print $_POST["db"]["user"]?>" style="width:300px;" /></td>
            </tr>
            <tr class="t2">
                <td width="100%"><?php print $LANG["mysql_pass"]?>:</td>
                <td><input type="text" name="db[password]" value="<?php print $_POST["db"]["password"]?>" style="width:300px;" /></td>
            </tr>
            <tr class="t1">
                <td width="100%"><?php print $LANG["db_name"]?>:</td>
                <td><input type="text" name="db[name]" value="<?php print $_POST["db"]["name"]?>" style="width:300px;" /></td>
            </tr>
            <tr class="t2">
                <td width="100%"><?php print $LANG["table_prefix"]?>:</td>
                <td><input type="text" name="db[prefix]" value="<?php print $_POST["db"]["prefix"]?>" style="width:300px;" /></td>
            </tr>
        </table>
        <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
            <tr class="t1"><td colspan=2 align=right>
                <input type="submit" style="margin: 10px;" name="doPost" value="&nbsp;&nbsp;<?php print $LANG["next"]?>&nbsp;&nbsp;&gt;&gt;" />
                <input type="button" style="margin: 10px;" value="&nbsp;&nbsp;<?php print $LANG["cancel"]?>&nbsp;&nbsp;" onclick="location.href='../'" />
            </td></tr>
        </table>
    </form>
<?php
} elseif ($INSTALL['step'] == 5) {
?>
    <form action="?" method="post">
        <input type="hidden" name="step" value="5" />
<?php
    if ($INSTALL['errors']) {
?>
    <table cellspacing="1" cellpadding="3" border="0" align="center" width="650">
        <tr><td>
<?php
        print "<p>" . $LANG["error_after_process"] . ":</p>";
        print "<ul class=\"errors\">\n";
        print "<li>" . join("</li><li>", $INSTALL['errors']) . "</li>";
        print "</ul>\n";
?>
        </td></tr>
    </table>
<?php
    }
?>
    <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
        <tr><th class="ttl" colspan="2"><?php print $LANG["product_settings"]?></th></tr>
        <tr class="t2">
            <td width="100%"><?php print $LANG["cncat_url"]?>:</td>
            <td><input type="text" name="cncat_url" value="<?php print $_POST["cncat_url"]?$_POST["cncat_url"]:"http://{$_SERVER['HTTP_HOST']}{$CAT_URL}"?>" style="width:300px;" /></td>
        </tr>
        <tr class="t1">
            <td width="100%"><?php print $LANG["interface_lang"]?>:</td>
            <td>
                <select name="lang" style="width: 300px;">
<?php
    foreach ($INSTALL['langs'] as $code => $lang) {
        $selected = ($_POST['lang'] == $code) || (empty($_POST['lang']) && substr($code, 0, 2) == $INSTALL['lang']);
?>
                    <option value="<?php print $code?>"<?php print $selected ? ' selected="selected"' : ''?>><?php print $lang?></option>
<?php
}
?>
                </select>
            </td>
        </tr>
        <tr class="t2">
            <td width="100%"><?php print $LANG['base_data_lang']?>:</td>
            <td>
                <select name="data_lang" style="width: 300px;">
                    <?php
                    $ru = $_POST['data_lang'] == 'ru' || (empty($_POST['data_lang']) && ($INSTALL['lang'] == 'ru' || $INSTALL['lang'] == 'ua'));
                    ?>
                    <option value="en" <?php print !$ru ? ' selected="selected"' : ''?>>English</option>
                    <option value="ru" <?php print $ru ? ' selected="selected"' : ''?>>Russian</option>
                </select>
            </td>
        </tr>
    </table>
    <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
        <tr><th class="ttl" colspan="2"><?php print $LANG["theme"]?></th></tr>
        <tr class="t2">
            <td colspan="2" width="100%">
                <table><tr>
                    <td><input type="radio" name="theme" value="default" id="theme_default"<?php print $_POST['theme'] == 'default' || empty($_POST['theme']) ? ' checked="checked"' : ''?> /> <label for="theme_default">Default</label>
                    <br /><img src="themes/default.gif" /></td>
                    <td><input type="radio" name="theme" value="modern" id="theme_modern"<?php print $_POST['theme'] == 'modern' ? ' checked="checked"' : ''?> /> <label for="theme_modern">Modern</label>
                    <br /><img src="themes/modern.gif" /></td>
                </tr></table>
            </td>
        </tr>
    </table>
    <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
        <tr><th class="ttl" colspan="2"><?php print $LANG["administration"]?></th></tr>
        <tr class="t2">
            <td width="100%"><?php print $LANG["login"]?>:</td>
            <td><input type="text" name="admin[login]" value="<?php print $_POST["admin"]["login"]?>" style="width:300px;" /></td>
        </tr>
        <tr class="t1">
            <td width="100%"><?php print $LANG["pass"]?>:</td>
            <td><input type="password" name="admin[passw]" value="<?php print $_POST["admin"]["passw"]?>" style="width:300px;" /></td>
        </tr>
        <tr class="t2">
            <td width="100%"><?php print $LANG["confirm_pass"]?>:</td>
            <td><input type="password" name="admin[passw2]" value="<?php print $_POST["admin"]["passw2"]?>" style="width:300px;" /></td>
        </tr>
    </table>
    <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
        <tr class="t1"><td colspan=2 align=right>
            <input type="submit" style="margin: 10px;" name="doPost" value="&nbsp;&nbsp;<?php print $LANG["next"]?>&nbsp;&nbsp;&gt;&gt;" />
            <input type="button" style="margin: 10px;" value="&nbsp;&nbsp;<?php print $LANG["cancel"]?>&nbsp;&nbsp;" onclick="location.href='../'" />
        </td></tr>
    </table>
</form>
<?php
} elseif ($INSTALL['step'] == 6) {
?>
    <form action="?" method="post">
        <input type="hidden" name="step" value="6" />
        <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
            <tr><th class="ttl" colspan="2"><?php print str_replace("%NAME%", "config.php", $LANG["file"])?></th></tr>
            <tr class="t1"><td width="100%" colspan="2"><?php print str_replace("%DIR%", $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_config"], str_replace("%FILE%", "config.php", $LANG["create_config_file"]))?>:</td></tr>
            <tr class="t2">
                <td width="100%" colspan="2">
        <textarea rows="14" cols="80"><?php
    print "<?php\n";
    print "// Common config settings\n";
    print "// All settings in this file will override the settings loaded from database\n";
    print "\$CNCAT[\"config\"][\"alogin\"] = \"" . str_replace("\"", "\\\"", $_SESSION["admin"]["login"]) . "\";\n";
    print "\$CNCAT[\"config\"][\"apassword\"] = \"" . str_replace("\"", "\\\"", $_SESSION["admin"]["passw"]) . "\";\n\n";
    print "\$CNCAT[\"config\"][\"db\"][\"host\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["host"]) . "\";\n";
    print "\$CNCAT[\"config\"][\"db\"][\"user\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["user"]) . "\";\n";
    print "\$CNCAT[\"config\"][\"db\"][\"password\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["password"]) . "\";\n";
    print "\$CNCAT[\"config\"][\"db\"][\"name\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["name"]) . "\";\n";
    print "\$CNCAT[\"config\"][\"db\"][\"prefix\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["prefix"]) . "\";\n";
    print "\$CNCAT[\"config\"][\"db\"][\"charset\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["charset"]) . "\";\n";
    if (file_exists('extra_config.conf')) print  file_get_contents('extra_config.conf');
    ?>
        </textarea>
                </td>
            </tr>
            <tr class="t1"><td colspan=2 align=right>
                <input type="submit" style="margin: 10px;" name="doPost" value="&nbsp;&nbsp;<?php print $LANG["next"]?>&nbsp;&nbsp;&gt;&gt;" />
                <input type="button" style="margin: 10px;" value="&nbsp;&nbsp;<?php print $LANG["cancel"]?>&nbsp;&nbsp;" onclick="location.href='../'" />
            </td></tr>
        </table>
    </form>
<?php
} elseif ($INSTALL['step'] == 7) {
?>
    <table cellspacing="1" cellpadding="6" border="0" align="center" width="650">
        <tr><th class="ttl"><?php print $LANG["further_actions"]?></th></tr>
        <tr class="t1"><td>
            <?php print str_replace("%VERSION%", $CNCAT_VERSION, $LANG["install_complete"])?>
        </td></tr> 
<?php
    $is_config = false;

    if (@file_exists("../{$CNCAT["system"]["dir_config"]}config.php")) {
        $CNCAT["config"] = array();
        @include "../{$CNCAT["system"]["dir_config"]}config.php";

        if (isset($CNCAT["config"]["db"]) && isset($CNCAT["config"]["alogin"]) && isset($CNCAT["config"]["apassword"])) {
            $is_config = true;
        }
    }

    if (!$is_config) {
?>
        <tr class="t2"><td width="100%" colspan="2">
            <p><?php print $LANG["config_file_warning"]?></p>
            <?php print str_replace("%DIR%", $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_config"], str_replace("%FILE%", "config.php", $LANG["create_config_file"]))?>:
        </td></tr>
        <tr class="t1">
            <td>
                <textarea rows="14" cols="80"><?php
    print "<?php\n";
    print "// Common config settings\n";
    print "// All settings in this file will override the settings loaded from database\n";
    print "\$CNCAT[\"config\"][\"alogin\"] = \"" . str_replace("\"", "\\\"", $_SESSION["admin"]["login"]) . "\";\n";
    print "\$CNCAT[\"config\"][\"apassword\"] = \"" . str_replace("\"", "\\\"", $_SESSION["admin"]["passw"]) . "\";\n\n";
    print "\$CNCAT[\"config\"][\"db\"][\"host\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["host"]) . "\";\n";
    print "\$CNCAT[\"config\"][\"db\"][\"user\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["user"]) . "\";\n";
    print "\$CNCAT[\"config\"][\"db\"][\"password\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["password"]) . "\";\n";
    print "\$CNCAT[\"config\"][\"db\"][\"name\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["name"]) . "\";\n";
    print "\$CNCAT[\"config\"][\"db\"][\"prefix\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["prefix"]) . "\";\n";
    print "\$CNCAT[\"config\"][\"db\"][\"charset\"]=\"" . str_replace("\"", "\\\"", $_SESSION["db"]["charset"]) . "\";\n";
    if (file_exists('extra_config.conf')) print  file_get_contents('extra_config.conf');
    ?></textarea>
            </td>
        </tr>
<?php
    }
    $cncat_url = "../";
    if (file_exists(dirname(dirname(__FILE__)) . '/'.$CNCAT["system"]["dir_config"].'config.php')) { 
        include (dirname(dirname(__FILE__)) . '/'.$CNCAT["system"]["dir_config"].'config.php');
        if (@mysql_connect($CNCAT["config"]["db"]["host"], $CNCAT["config"]["db"]["user"], $CNCAT["config"]["db"]["password"])) {
                mysql_query("SET NAMES 'utf8'");
                mysql_select_db($CNCAT["config"]["db"]["name"]);
        }
        $res = mysql_query("SELECT value from `" . $_SESSION['db']['prefix'] . "config` where name = 'cncat_url'") or $CNCAT_ENGINE->displayErrorDB(mysql_error());
        $cncat_url = mysql_fetch_assoc($res);  
    } 
?>                                                                                                          
        <tr class="t2"><td><a href="<?php print $cncat_url['value']?>"><?php print $LANG["go_to_cat"]?> &gt;&gt;</a></td></tr>
<?php
}     
?>
    <table cellspacing="1" cellpadding="3" align="center" width="650" style="border-top: 1px solid gray;">
        <tr><td>
            <a target="_blank" href="http://www.cn-software.com/<?php print $_lang == "ru" ? "ru" : "en"?>/cncat/">CNCat <?php print $CNCAT_VERSION . (!empty($CNCAT_STATUS) ? ' ' . $CNCAT_STATUS : '') . ','?></a>
            Copyright &copy; 2002-<?php print date('Y,')?> &quot;CN-Software&quot; Ltd.
        </td></tr>
    </table>
</body>
</html>
