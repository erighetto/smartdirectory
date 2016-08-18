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

    class CNCatRenderProduct extends CNCatRender
    {
        /**
        * Prepares item to display
        * @param item - item description
        */
        function prepareItemToDisplay(&$item)
        {
            
            if ($CNCAT["config"]["directurls"]==1 || $CNCAT["config"]["directurls"]==2 && $item["force_direct_url"])
                $item["link"] = $item["url"];
            else
                $item["link"] = $CNCAT["config"]["cncat_url"]."jump.php?".$item["item_id"];
        }
    }
?>
