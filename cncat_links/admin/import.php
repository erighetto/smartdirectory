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
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "auth.php";

if (!isAdmin()) {
    accessDenied();
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["import"]?></h1>
<div class="deline"></div>

<table class="list" width="550">
<tr><td class="title" colspan="3"><?php print $CNCAT["lang"]["import_from_cncat_4x"]?></td></tr>
<tr><td class="item"><a href="index.php?act=import_4x"><?php print $CNCAT["lang"]["import_from_xml_and_db"]?></a></td></tr>
<tr><td>&nbsp;</td></tr>

<tr><td class="title" colspan="3"><?php print $CNCAT["lang"]["import_from_cncat_3x_2x_1x"]?></td></tr>
<tr><td class="item"><a href="index.php?act=import_xml"><?php print $CNCAT["lang"]["import_from_xml"]?></a></td></tr>
<tr><td class="item"><a href="index.php?act=import_db"><?php print $CNCAT["lang"]["import_from_db"]?></a></td></tr>
<tr><td class="item"><a href="index.php?act=import_theme"><?php print $CNCAT["lang"]["import_theme"]?></a></td></tr>
<tr><td>&nbsp;</td></tr>

<tr><td class="title" colspan="3"><?php print $CNCAT["lang"]["import_from_other_cats"]?></td></tr>
<tr><td class="item"><a href="index.php?act=import_scripto"><?php print $CNCAT["lang"]["import_from_scripto"]?></a></td></tr>
<tr><td>&nbsp;</td></tr>
</table>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
