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
require_once "./../cncat_init.php"; 
$CNCAT_ENGINE = cncatCreateObject ("engine", "CNCatEngine");
$CNCAT_ENGINE->initDB();    
// mark as bad 
if (isset($_GET["bad"])) {
    $item_id = (int)$_GET["bad"];
    $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "items` SET `link_broken_warning`=1 WHERE `item_id`=" . $item_id;
    $CNCAT_ENGINE->db->query($query, null, false) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    print "<html>
    <head>
        <title>Mark link as bad</title>
    </head>
    <body>
        <script type=\"text/javascript\">
            window.close();
        </script>
    </body>
</html>";

    exit;
}