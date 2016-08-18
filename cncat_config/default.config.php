<?php
// Common config settings
// All settings in this file will override the settings loaded from database
$CNCAT["config"]["alogin"] = "admin";
$CNCAT["config"]["apassword"] = "d8578edf8458ce06fbc5bb76a58c5ca4";

$CNCAT["config"]["db"]["host"]="localhost";
$CNCAT["config"]["db"]["user"]="root";
$CNCAT["config"]["db"]["password"]="";
$CNCAT["config"]["db"]["name"]="cncat";
$CNCAT["config"]["db"]["prefix"]="cncat4_";
$CNCAT["config"]["db"]["charset"]="utf8";

/*define("CNCAT_ADMIN_DIR", "admin"); // имя папки с разделом администрировани
$CNCAT["system"]["dir_admin"] = CNCAT_ADMIN_DIR . "/";

if (substr(dirname($_SERVER["PHP_SELF"]), -5) == CNCAT_ADMIN_DIR) {
    $CNCAT["abs"] = substr(dirname($_SERVER["PHP_SELF"]), 0, -5);
}*/

//$CNCAT["system"]["dir_config_themes"] = "themes/";
?>
