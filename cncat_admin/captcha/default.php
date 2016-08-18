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

/**
 * Validate captcha
 * 
 * @return "DONE" if validated
 */
function defaultValid()
{
    if (intval($_POST["captcha_code"]) == intval($_SESSION["secret_number"])) 
        return "DONE";
    return ""; 
}

/**
 * render Captcha
 * 
 * @global $CNCAT_ENGINE
 * @global $CNCAT
 * @return result of render 
 */
function defaultCaptchaHtml()
{
    GLOBAL $CNCAT_ENGINE, $CNCAT;

    $CNCAT["add"]["rand"] = time();
    srand(((int)((double)microtime() * 1000003))); 
    $_SESSION["secret_number"] = rand(1000, 9999);
    $CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], "add");
    return $CNCAT_ENGINE->tpl->renderTemplate("field_captcha"); 
}

?>