<?php
    if (!defined("CNCAT_ENGINE")) die();

    function cn_extFieldValidate($field, $value, &$error) {
        GLOBAL $CNCAT;
        $error = "";

        // user defined validators
        switch ($field) {
            /*
            case "ext_int1":
                if ((int)$value < 0) {
                    $error = $CNCAT["lang"]["validator_ext_int1"];
                }
            break;
            */
        }

        // universal validators
        if (!empty($error)) {
            return;
        }

        switch ($CNCAT["config"]["extfields"]["items"][$field]["type"]) {
            case 1:
            break;
            case 2:
            break;
            case 3:
                if (cn_strlen($value) > 255) {
                    $error = cn_str_replace(array("%FIELD%", "%LEN%", "%COUNT%"), array($CNCAT["config"]["extfields"]["items"][$field]["title"], 255, cn_strlen($value)), $CNCAT["lang"]["field_to_long"]);
                }
            break;
            case 4:
                if (!empty($value) && !preg_match("#^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}$#", $value)) {
                    $error = $CNCAT["lang"]["invalid_date_format"];
                }
            break;
            case 5:
                if (cn_strlen($value) > 16777215) {
                    $error = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["config"]["extfields"]["items"][$field]["title"], 255), $CNCAT["lang"]["field_to_long"]);
                }
            break;
        }
    }

    // Sorting orders
    $CNCAT["config"]["sortorders"]["items"] = array (
        /*
        100 => array (
            "url" => "url", // Part of URL
            "sql" => array ("link_url", "link_url DESC", "") // ORDER BY ...
        ),
        */
        /* 
        101 => array (
            "url" => "appdate",
            "sql" => array ("", "", "")
        ),
        */ 
    );

    $CNCAT["config"]["cncat3"]["seprefix"] = "dir";
    $CNCAT["config"]["cncat3"]["sesortstr"] = "pop,ttl,mdr,pin,pr,cy";
    
    // TinyMCE options
    $CNCAT["config"]["tinymce"] = "
        language : 'en',
        mode : 'exact',
        theme : 'advanced',
        convert_urls: false,
        relative_urls: false,
        plugins : 'fullscreen,preview,paste',
        theme_advanced_toolbar_location : 'top',
        theme_advanced_buttons1 : 'bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,formatselect',
        theme_advanced_buttons2 : 'cut,copy,paste,pasteword,separator,bullist,numlist,separator,outdent,indent,separator,undo,redo,separator,link,unlink,anchor,image,separator,fullscreen,preview,code',
        theme_advanced_buttons3 : 'hr,removeformat,visualaid,separator,sub,sup,separator,charmap',
        skin : 'default'
    ";
?>
