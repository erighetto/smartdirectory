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

//init configuration
require_once dirname(__FILE__) . "/cncat_init.php";

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

//Include files by action
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_action"] . $CNCAT["action_file"];

if (file_exists($CNCAT["system"]["dir_root"] . "_top.php")) {
    include $CNCAT["system"]["dir_root"] . "_top.php";
}

// reconnect
$res = $CNCAT_ENGINE->db->query('SELECT DATABASE() AS db');
$row = mysql_fetch_assoc($res);
if ($row["db"] != $CNCAT["config"]["db"]["name"]) {
    $CNCAT_ENGINE->db->CNCatDatabase();
}
// end of reconnect

echo $CNCAT["render_result"];// . (cn_copyright_free());

if (file_exists($CNCAT["system"]["dir_root"] . "_bottom.php")) {
    include $CNCAT["system"]["dir_root"] . "_bottom.php";
}

if ($CNCAT["system"]["debug"]) {
	  cncatShowRenderStats();
}