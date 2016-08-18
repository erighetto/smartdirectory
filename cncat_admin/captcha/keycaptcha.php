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

require_once($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_engine_lib"] . 'keycaptchalib.php'); 

/**
 * Validate captcha
 * 
 * @return "DONE" if validated
 */
function keycaptchaValid()
{
    GLOBAL $CNCAT;
    
    $kc_o = new KeyCAPTCHA_CLASS($CNCAT["config"]["keycaptcha_private_key"]);
    if ($kc_o->check_result($_POST['capcode'])) {
    	return "DONE";
    }
    return false;
}

/**
 * render Captcha
 * 
 * @global $CNCAT_ENGINE
 * @global $CNCAT
 * @return result of render 
 */
function keycaptchaCaptchaHtml($error = null)
{   
    GLOBAL $CNCAT_ENGINE, $CNCAT;
    
    $kc_o = new KeyCAPTCHA_CLASS($CNCAT["config"]["keycaptcha_private_key"]);
    $CNCAT["page"]["keycaptcha"] = $kc_o->render_js();
    $CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], "add");
    return $CNCAT_ENGINE->tpl->renderTemplate("field_keycaptcha");  
}
