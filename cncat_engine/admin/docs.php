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

if (!defined("ADMIN_INTERFACE")) die();
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "./auth.php";


$_type = isset($_GET['type']) ? $_GET['type'] : '';
$_lang = isset($_GET['lang']) ? $_GET['lang'] : '';

$_manual_lang = "en";
$_manual_charset = strstr($CNCAT["config"]["language"], "_");

if (substr($CNCAT["config"]["language"], 0, 2) == "ru" || substr($CNCAT["config"]["language"], 0, 2) == "ua") {
    $_manual_lang = "ru";
}

if (!empty($_lang) && ($_lang == "ru" || $_lang == "en")) {
    $_manual_lang = $_lang;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";

if (!empty($_type)) {
    $manual_file = $CNCAT["system"]["dir_root"] . "cncat_manual/" . $_manual_lang . "/manual" . $_manual_charset . ".html";

    $manual = file_get_contents($manual_file);
    $manual = str_replace('%ROOT%', $CNCAT["abs"], $manual);
    $manual = str_replace('%INDEX_ROOT%', $CNCAT["abs"] . "cncat_manual/" . $_manual_lang . "/", $manual);

?>
    <div style="padding: 10px;">
    <?php print $manual?>
    </div>
<?php
} else {
?>
    <h1><?php print $CNCAT["lang"]["documentation"]?></h1>
    <div class="deline"></div>
    
    <table class="list" width="550">
    <tr><td class="title" colspan="3"><?php print $CNCAT["lang"]["cncat_manual"]?></td></tr>
    <tr>
        <td class="item">HTML</td>
        <td class="item" width="30"><a target="_blank" href="http://cn-software.com/en/docs/cncat/4.4/HTML/index.html">EN</a></td>
        <td class="item" width="30"><a target="_blank" href="http://cn-software.com/ru/docs/cncat/4.4/HTML/index.html">RU</a></td>
    </tr>
    <tr>
        <td class="item">PDF</td>
        <td class="item" width="30"><a target="_blank" href="http://www.cn-software.com/en/docs/cncat/4.4/CNCat_44.pdf">EN</a></td>
        <td class="item" width="30"><a target="_blank" href="http://www.cn-software.com/ru/docs/cncat/4.4/CNCat_44.pdf">RU</a></td>
    </tr>
    </table>
<?php
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
