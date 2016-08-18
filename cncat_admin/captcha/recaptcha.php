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

require_once($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_engine_lib"] . 'recaptchalib.php'); 

/**
 * Validate captcha
 * 
 * @return "DONE" if validated
 */
function recaptchaValid()
{
    GLOBAL $CNCAT;
    $privatekey = $CNCAT["config"]["recaptcha_private_key"];
    # the response from reCAPTCHA
    $resp = null;
    # the error code from reCAPTCHA, if any
    $error = null;
  
    # was there a reCAPTCHA response?
    if ($_POST["recaptcha_response_field"]) {
        $resp = recaptcha_check_answer ($privatekey,
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);
  
        if ($resp->is_valid)
                return "DONE";
        return $resp->error;
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
function recaptchaCaptchaHtml($error = null)
{   
    GLOBAL $CNCAT_ENGINE, $CNCAT;
              
    $publickey = $CNCAT["config"]["recaptcha_public_key"];
    $CNCAT["page"]["recaptcha"] = recaptcha_get_html($publickey, $error);
    $CNCAT_ENGINE->tpl->loadTemplates($CNCAT["config"]["default_theme"], "add");
    return $CNCAT_ENGINE->tpl->renderTemplate("field_recaptcha");  
}