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

require_once @dirname(__FILE__) . "/lang_en.php";
$lang_file = dirname(__FILE__) . "/lang_" . $CNCAT["config"]["language"] . ".php";

if (file_exists($lang_file)) {
    require_once $lang_file;
}

$CNCAT["plugins"]["static_pages"]["title"] = $CNCAT["plugins"]["static_pages"]["lang"]["_title"];
?>
