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

define("ADMIN_INTERFACE", true);  

////////////////////////////////////////////////////////////////////////////////
define("UAX33GT9", $_SERVER["SERVER_NAME"]);
define("ABD3D088", $_SERVER["PHP_SELF"]);//$_SERVER["SCRIPT_NAME"];
define("FGT33DDJ", $_SERVER["HTTP_HOST"]);
////////////////////////////////////////////////////////////////////////////////
$ADMIN_INTERFACE = ADMIN_INTERFACE;

require_once "./../cncat_init.php"; 
// Initializing engine
if ($CNCAT["system"]["debug"])
    	$CNCAT["system"]["debug_time_engine_init_start"] = cncatGetMicrotime();
$CNCAT_ENGINE = cncatCreateObject ("engine", "CNCatEngine");
$CNCAT_ENGINE->init();
if ($CNCAT["system"]["debug"])
	$CNCAT["system"]["debug_time_engine_init_stop"] = cncatGetMicrotime();
session_start();

$CNCAT['page']['title'] = $CNCAT['config']['catalog_title'];
header("Content-type: text/html; charset=" . $CNCAT["lang"]["charset"]);
require_once "./funcs.php";
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_engine_lib"] . "idna_convert_class.php";

$CN_NEED_SERVER_NAME = UAX33GT9;
$CN_NEED_SCRIPT_NAME = ABD3D088;
$CN_NEED_HTTP_HOST = FGT33DDJ;


if (
    $CN_NEED_SERVER_NAME != $_SERVER["SERVER_NAME"] ||
    $CN_NEED_SCRIPT_NAME != $_SERVER["PHP_SELF"] ||
    $CN_NEED_HTTP_HOST != $_SERVER["HTTP_HOST"]
) {
    exit;
}

$action = basename($_REQUEST["act"]);

if ($action != "license") {
  $idna_convert = new idna_convert();
  
  if ($CNCAT["lang"]["charset"] == "utf-8")
  {
      $_SERVER["HTTP_HOST"] = $idna_convert->decode($_SERVER["HTTP_HOST"]);
  }
  elseif ($CNCAT["lang"]["charset"] == "windows-1251")
  {  
      $_SERVER["HTTP_HOST"] = cn_utf_to_win1251( $idna_convert->decode($_SERVER["HTTP_HOST"]) );
  }
  elseif (function_exists('iconv'))
  {
      $_SERVER["HTTP_HOST"] = $idna_convert->decode($_SERVER["HTTP_HOST"]);
      $_SERVER["HTTP_HOST"] = iconv("utf-8",$CNCAT["lang"]["charset"], $_SERVER["HTTP_HOST"]);
  }
  
  unset($idna_convert);
}

if (empty($action) || !preg_match("/^[a-z_0-9]+$/i", $action)) {
    $action = "links";
}

$module = $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_engine_admin"] . $action . ".php";

// check engine module
if (!file_exists($module)) {
    $module = $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_product_admin"] . $action . ".php";

    // check product module
    if (!file_exists($module)) {
        $module = $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "plugins/" . $action . "/index.php";

        if (!file_exists($module)) {
            $action = "links";
            $module = $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_product_admin"] . "links.php";
        }   
    }
}

if ($_GET["act"] == "logout") {
    unset($_SESSION["isadmin"]);
    unset($_SESSION["ismoder"]);
    unset($_SESSION["moder_imgbr_allow"]);
    
    header("Location: index.php");
    exit;
}

$_GET["act"] = $action;
$CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], "admin");

// load admin module

header("Content-type: text/html; charset=" . $CNCAT["lang"]["charset"]);
include_once $module;

if ($CNCAT["system"]["debug"]) {
    cncatShowRenderStats();
}
?>
