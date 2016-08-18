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

    class CNCatTemplates
    {    
        var $templateFiles = array(
            "index" => array ("index", "item", "article", "category", "common"),
            "map" => array ("map", "common"),
            "search" => array ("search", "index", "item", "article", "common"),
            "add" => array("add", "mail", "common", "js", "add_article"),
            "extended" => array("extended_article", "extended", "common", "item", "article"),
            "admin" => array("admin", "common", "mail", "js"),
            "error" => array("404", "common")
        );
        var $templateCond = array();
        var $templates = array();
        var $themeUrl = "";

        function renderTemplateString($tplName, $contents) {
            $compiled = $this->compileTemplateFile($contents);
            $this->templates[$tplName]["body"] = $compiled;

            return $this->renderTemplate($tplName);
        }
        
        /**
        *   Loads a set of templates from specified theme
        *   @param $themeName theme name
        *   @param $setName template set name
        */
        function loadTemplates($themeName, $setName)
        {
            GLOBAL $CNCAT, $CNCAT_ENGINE, $CN_STRINGS;
            $this->themeUrl = $CNCAT["abs"].$CNCAT["system"]["dir_config_themes"].$themeName."/";
            $themeDir = $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config_themes"].$themeName;

            if (empty($themeName) || !is_dir($themeDir)) {
                $themeName = 'default';
                $this->themeUrl = $CNCAT["abs"].$CNCAT["system"]["dir_config_themes"].$themeName."/";
                $CNCAT["config"]["default_theme"] = 'default';
            }

            if (is_array($setName)) {
                $tamplateFiles = $setName;
            } else {
                $tamplateFiles = $this->templateFiles[$setName];
            }

            foreach ($tamplateFiles as $file)
            {
                $fileName = $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config_themes"].$themeName."/".$file.".tpl";
                
                if ($file == "admin" && $themeName != "default") {
                    $ver_file_default = @file($CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config_themes"]."default/admin_ver.txt");
                    $ver_file_user = @file($CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config_themes"].$themeName."/admin_ver.txt");
                    $ver_default = $ver_file_default[0];

                    if ($ver_file_default[0] > $ver_file_user[0]) {
                        $fileName = $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config_themes"]."default/".$file.".tpl";
                    }
                }
                
                if (!file_exists($fileName))
                {
                    $fileName = $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config_themes"]."default/".$file.".tpl";
                    if (!file_exists($fileName)) {
                        $CNCAT_ENGINE->displayError ("Error loading template file '".$fileName."'.");
                    }
                }                      
                $compiled = $this->compileTemplateFile(file_get_contents($fileName));

                $p1 = 0;
                while (($p1 = cn_strpos($compiled, "{TEMPLATE ", $p1)) !== false)
                {
                    $p1+=10;
                    if (($p2 = cn_strpos($compiled, "}", $p1)) !== false)
                    {
                        $matches = array();
                        $str = cn_trim(cn_substr ($compiled, $p1, $p2-$p1));

                        preg_match("/(\w+).*/si".$CN_STRINGS["preg"], $str, $matches);
                        $tplName = $matches[1];
                        preg_match_all ("/(\w+)\s*\[([\w\s\,]*)\]/si".$CN_STRINGS["preg"], $str, $matches, PREG_SET_ORDER);

                        foreach ($matches as $match)
                        {   
                            $attrs = explode (",", $match[2]);
                            if (is_array($attrs))
                            {
                                foreach ($attrs as $attr)                        
                                {
                                    $this->templates[$tplName]["fields"][$match[1]][] = cn_trim($attr);
                                }
                            }
                        }

                        if (($p3 = cn_strpos($compiled, "{/TEMPLATE}", $p2)) !== false)
                        {                           
                            $this->templates[$tplName]["body"] = cn_substr ($compiled, $p2+1, $p3-$p2-1);
                            //echo "$tplName<textarea style='width:600px;height:300px'>".$this->templates[$tplName]["body"]."</textarea><br>";
                            $p1 = $p3+10;
                        }
                        else
                            break;
                    }
                    else
                        break;
                }
                
            }
        }
        
        /**
        *   Compiles template to use with eval() function
        *   @param $template template as string
        */
        function compileTemplateFile($template)
        {
            GLOBAL $CNCAT, $CNCAT_ENGINE, $CN_STRINGS;
            
            // Replace \ with \\        
            $template = cn_str_replace ("\\", "\\\\", $template);
            
            // Replace " with \"
            $template = cn_str_replace ("\"", "\\\"", $template);
                
            // Compile conditions
            $pVar  = "(?:\\\$\w+(?:\[\w+\])*)";
            $pValue= "(?:\d+|\'(?:[^\'])*\'|".$pVar.")"; // digit | 'string' | variable
            $pSign = "(?:\=\=|\!\=|\>|\<|\>\=|\<\=|\%|\+|\-|\*|\/|\|\||\&\&)";
      
            $pExpr = "(?:(?:!){0,1}\s*".$pValue."(?:\s*".$pSign."\s*".$pValue.")*)";
      
            // Compile function calls                                  
            $pFunctName = "(?:\$CNCAT_ENGINE->url->createUrlSort|\$CNCAT_ENGINE->url->createUrlCat|cn_str|cn_str_nl2br|nl2br|cn_date|url|intval|ceil|floor|round|is_numeric|cn_copyright)";
            $pFunctParams = "(?:".$pExpr."(?:\s*,\s*".$pExpr.")*)";

            $pFunctCall = $pFunctName."\s*\(\s*".$pFunctParams."\s*\)";
            
            $pExpr = "(?:".$pExpr."|".$pFunctCall.")";
            $pExpr = $pExpr."(?:\s*".$pSign."\s*".$pExpr.")*";
            
           // Conditions
            $this->templateCond = array();
            $template = preg_replace_callback ("/\{if\s+(".$pExpr.")\s*\}|\{else\}|\{endif\}/Usi", array ($this, "templateCondCallback"), $template);
            $template = preg_replace ("/\{\s*(".$pFunctCall.")\s*\}/Usi".$CN_STRINGS["preg"], "\".($1).\"", $template);
            // Replace "{{}" and "{}}" with "{" and "}"    
            $template = cn_str_replace ("{{}", "{", $template);
            $template = cn_str_replace ("{}}", "}", $template);
            
            // Deny access to $GLOBALS
            $template = cn_str_replace ("\$GLOBALS", "\\\$GLOBALS", $template);
            $template = cn_str_replace ("\${GLOBALS", "\\\${GLOBALS", $template);
            
            return $template;
        }

        function templateCondCallback($matches)
        {
            $match = cn_strtoupper($matches[0]);
            
            if (substr ($match, 0, 3)=="{IF")
            {
                array_push($this->templateCond, "IF");
                return "\".((".$matches[1].") ? \"";
            }
            
            if (substr ($match, 0, 6)=="{ELSE}")
            {   
                array_pop ($this->templateCond);                    
                array_push ($this->templateCond, "ELSE");
                return "\" : \"";
            }
        
            if (substr ($match, 0, 7)=="{ENDIF}")
            {
                $last = array_pop ($this->templateCond);
                if ($last=="IF")
                {
                    return "\" : \"\").\"";
                }
                if ($last=="ELSE")
                {
                    return "\").\"";
                }
            }
        }
        
        
        /**
        *   Returns compiled template by name    
        */
        function getTemplate($name)
        {
            GLOBAL $CNCAT, $CNCAT_ENGINE, $CN_STRINGS;
            $template = $this->templates[$name]["body"];
            
            // Include other templates
            $match = array();
            $i=0;
            while (preg_match ("/\{INCLUDE\s+(\w+)\}/i".$CN_STRINGS["preg"], $template, $match) && $i<100)
            {
                $template = cn_str_replace ($match[0], $this->templates[$match[1]]["body"], $template);
                $i++;
            };

            return $template;
        }
        
        function renderTemplate($name)
        {   
            GLOBAL $CNCAT, $CNCAT_ENGINE;
            if ($CNCAT["system"]["debug"])
            	$timeStart = cncatGetMicrotime();
            $THEMEURL = $this->themeUrl;
            
            $tpl = $this->getTemplate($name);
            $e = "\$result=\"".$tpl."\";";
            eval ($e);
                        
            if ($CNCAT["system"]["debug_templates"])
                $result = "{".$name."}".$result."{/".$name."}";
                
            
            if ($CNCAT["system"]["debug"])
            {
            	$timeStop = cncatGetMicrotime();
            	$CNCAT["system"]["debug_templates_result"][$name]["time"] = $timeStop-$timeStart;
            	$CNCAT["system"]["debug_templates_result"][$name]["count"] ++;
            }

            return $result;
        }

        function templateExists($name)
        {
            return isset ($this->templates[$name]);   
        }
    
        function getThemeUrl() {
            return $this->themeUrl;
        }
    }    

        function cn_str($s) {
            global $CNCAT;

            if (isset($CNCAT["system"]["str_sid"])) {
                if (cn_substr($s, 0, 32) == $CNCAT["system"]["str_sid"]) {
                    return cn_substr($s, 32);
                }
            }

            return htmlspecialchars ($s);
        }

        function cn_str_nl2br($s) {
            return nl2br(cn_str($s));
        }

        function cn_date($t) {
            GLOBAL $CNCAT;
            if ($t == "0000-00-00 00:00:00") return "";
            return date($CNCAT["config"]["date_format"], strtotime($t));
        }

        function cn_copyright($p) {
            GLOBAL $CNCAT_COPYRIGHT;

            return $CNCAT_COPYRIGHT;
        }

        function cn_copyright_free() {
            global $CNCAT_COPYRIGHT;
            return '<b' . 'r />' . $CNCAT_COPYRIGHT . '<b' . 'r />';
        }

        function cn_url($s)
        {
            return  cn_str_replace ("<", "%3C", 
                    cn_str_replace (">", "%3E", $s));
        }
?>