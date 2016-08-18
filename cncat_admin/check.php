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

require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_engine_common"] . "cnrtxt.php";
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_engine_lib"] . "idna_convert_class.php";

/*******************************************************************************
 * Check functions
 ******************************************************************************/
function cn_joinUrl($parsed, $req=false) 
{ 
    if (!is_array($parsed)) return false;
    if (!$req)
    {
        $url = $parsed["scheme"] ? $parsed["scheme"].":".((cn_strtolower($parsed["scheme"]) == "mailto") ? "":"//"): "";
        $url .= $parsed["user"] ? $parsed["user"].($parsed["pass"]? ":".$parsed["pass"]:"")."@":"";
        $url .= $parsed["host"] ? $parsed["host"] : "";
        if (!($parsed["scheme"]=="http" && $parsed["port"]==80 || $parsed["scheme"]=="https" && $parsed["port"]==443))
            $url .= $parsed["port"] ? ":".$parsed["port"] : "";
    }
    $path = $parsed["path"];
    if (!empty ($path))
        $url .=  cn_substr ($path, 0, 1)=="/" ? $path : "/".$path;
    else
        $url .= "/";
    $url .= $parsed["query"] ? "?".$parsed["query"] : "";
    $url .= $parsed["fragment"] ? "#".$parsed["fragment"] : "";
    return $url; 
}

function comment($str)
{
    return $str . "\n";   
}

/**
* Deletes bounding quotes from tag attribite value
*/
function cn_trimQuotes($str)
{
    $length = cn_strlen($str);
    if (cn_substr($str, 0, 1)=="\"" && cn_substr($str, $length-1, 1)=="\"")
        return cn_substr ($str, 1, $length-2);
    if (cn_substr($str, 0, 1)=="'" && cn_substr($str, $length-1, 1)=="'")
        return cn_substr ($str, 1, $length-2);
    return $str;        
}

/** 
*   Parses URL
*   @param url URL to parse
*   @param parsed previous result of parse (if redirected). New url merged with parsed result if is not absolute.
*   @return The same result format as standard parse_url() function
*/
function cn_parseUrl($url, $parsed=null)
{
    global $CNCAT_SITE_LANG,$CNCAT;
    $urlparts = @parse_url(cn_trim($url));

    // Invalid URL
    if ($urlparts===false)
        return null;

    // No http:// or https:// specified 
    if (empty($urlparts["scheme"]))
    {
        if ($parsed==null)
            return null;   
        $urlparts["scheme"] = $parsed["scheme"];
        $urlparts["host"] = $parsed["host"];
        $urlparts["port"] = $parsed["port"];
    }
    
    if ($CNCAT["lang"]["charset"] == "utf-8")
    {
        $urlparts["host"] = idnaConvert($urlparts["host"]);
    }
    elseif ($CNCAT["lang"]["charset"] == "windows-1251")
    {  
        $utf8_string = cn_utf_encode($urlparts["host"]);
        $urlparts["host"] = idnaConvert($utf8_string);
    }
    elseif (function_exists('iconv'))
    {
        $utf8_string = iconv($CNCAT["lang"]["charset"], "utf-8", $urlparts["host"]);
        $urlparts["host"] = idnaConvert($utf8_string);
    }
    // If relative link (redirect to file with the same path)
    $newPath = $urlparts["path"];
    if (cn_substr ($newPath, 0, 1)!="/")
    {
        if (cn_substr($newPath, 0, 2)=="./")
            $newPath = cn_substr ($newPath, 2);
        
        $oldPath = $parsed["path"];
        $p = strrpos ($oldPath, "/");   
        if ($p !== false)
            $urlparts["path"] = cn_substr($oldPath, 0, $p+1).$newPath;   
    }
    
    // Port        
    if (empty($urlparts["port"]))
        $urlparts["port"] = ($urlparts["scheme"]=="https" ? 443 : 80);
      
    return $urlparts;
}

/** 
*   Checks the URL.
*   @param url URL
*   @param checkBack If ==1 - check back link
*   @return Check result
*/
function cn_checkLink($url, $checkWork, $checkBack, $checkPR, $checkCY, $timeout=5, $maxRedirect=10) {
    GLOBAL $CNCAT, $CNCAT_ENGINE;

    $result = array ("work"=>0, "back"=>0, "pr"=>-1, "cy"=>-1, "favicon" => array(), "comment"=>"", "error"=>"");                    
    $contents = null;
    $parsedUrl = null;
    $redirectCount = 0;
    $orig_url = $url;
    //phpinfo();
    
    // Some regexps
    $pregAttrValue=
        '('.
        '(?:\s*\"(?:[^"]*)\")|'.      // "..."
        "(?:\s*\'(?:[^']*)\')|".      // '...'
        '(?:[^\s\>]*)'.             // not quoted value limited by space or ">"
        ')';    
    $pregTagStart = "<\s*";
    $pregAttribute ="\s*(?:(?:([\w-]+)\s*=".$pregAttrValue.")|(?:[^\s\>]+))";
    $pregAttributeHref ="\s*(?:(?:(href)\s*=".$pregAttrValue.")|(?:[^\s\>]+))";
    $pregTagEnd = "(?:\s*)>";

    if ($checkWork) {
        $result["comment"] .= $CNCAT["lang"]["log_check_work"] . ": " . $url . "\n";

        //*** Get page contents
        do {
            // Parsing URL
            $parsedUrl = cn_parseUrl($url, $parsedUrl);
            $newUrl = cn_joinUrl($parsedUrl);
            $newUrlReq = cn_joinUrl($parsedUrl, true);

            if (!empty ($redirect) && ++$redirectCount>$maxRedirect)
            {
                $result["comment"] .= $CNCAT["lang"]["check_com_redirect_max"] . "\n";
                $CNCAT_ENGINE->misc->itemLog(0, $result["comment"]);
                return $result;            
            }
           
            if ($redirect>=300 && $redirect<=399) // HTTP 301, 302
            {
                $result["comment"] .= $CNCAT["lang"]["check_com_redirect_http"]." ".$redirect." => ".$url."\n";
            }
            elseif ($redirect==1) // META
            {
                $result["comment"] .= $CNCAT["lang"]["check_com_redirect_meta"]." => ".$url."\n";
            }

            $redirect = false;
            
            if ($parsedUrl==null)
            {
                $result["comment"] .= $CNCAT["lang"]["check_com_incorrect_url"].": ".$url . "\n";
                $CNCAT_ENGINE->misc->itemLog(0, $result["comment"]);
                return $result;
            }
            
          //  print_r($parsedUrl);
          //  echo "<br>";
          //  echo $url."<br>";
            
            // Is URL format correct?
            if (strpos($url, $parsedUrl['host']) !== false)
            {   
                $url = $newUrl;      
            
                if (!preg_match( '/^(http|https):\/\/((localhost)|([a-z0-9]+([\-\.\_]{1}[a-z0-9]+)*\.[a-z]{2,5})|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?.*$/i' ,$url))
                {
                    $result["comment"] .= $CNCAT["lang"]["check_com_incorrect_url"].": ".$url . "\n";
                    $CNCAT_ENGINE->misc->itemLog(0, $result["comment"]);
                    return $result;
                };
            }
            // Trying to get page contents using sockets
            if ($CNCAT["config"]["check_method"] == 0 && function_exists("fsockopen"))
            {
                // Creating socket       
                $address = @gethostbyname($parsedUrl["host"]);
                if ($timeout>0)
                    $socket = @fsockopen($address, $parsedUrl["port"], $errno, $errstr, $timeout);
                else
                    $socket = @fsockopen($address, $parsedUrl["port"], $errno, $errstr);
                if (!$socket) 
                {
                    if ($errno==10060) {
                        $result["comment"] .= $CNCAT["lang"]["check_com_timeout"]. "\n";
                    } else {
                        $result["comment"] .= $CNCAT["lang"]["check_com_fsockopen"]." ".$errno.": ".$errstr. "\n";
                    }
                    $CNCAT_ENGINE->misc->itemLog(0, $result["comment"]);
                    return $result;
                }    
                /*stream_set_blocking($socket, FALSE ); //THIS IS IMPORTANT
                stream_set_timeout($socket, $timeout);
                */
/*                echo $timeout;
                ob_flush(); flush();
                
                die;
 */               
                // Request            
                $request = "GET ".$newUrlReq." HTTP/1.0\r\n";
                
                if (!empty($CNCAT["config"]["check_user_agent"])) {
                    $request .= "User-Agent: " . trim($CNCAT["config"]["check_user_agent"]) . "\r\n";
                }

                //$request = "GET / HTTP/1.0\r\n";
                $request .= "Host: ".$parsedUrl["host"]."\r\n\r\n";
                fwrite ($socket, $request);
                
                // Response
                $response = "";
                while (!feof($socket)) 
                    $response .= fgets($socket, 2048);
                fclose ($socket);
               
/*                echo $request."<br>";
                echo $response."<br>";
                die;
  */             
                // Processing read data
                $bodyPos = cn_strpos($response, "\r\n\r\n");
                if($pos === false)
                {
                    $contents = $body;
                }
                else
                {
                    $header = cn_substr($response, 0, $bodyPos);
                    $contents = cn_substr($response, $bodyPos + 4);

                    $params = array();
                    $lines = explode("\r\n", $header);
                    $status = explode (" ", $lines[0]);
                    $statusCode = $status[1];
                    foreach($lines as $line)
                        if(($pos = cn_strpos($line, ':')) !== false)
                            $params[cn_strtolower(cn_trim(cn_substr($line, 0, $pos)))] = cn_trim(cn_substr($line, $pos+1));

                    if ($statusCode>=200 && $statusCode<=299)
                    {
                        $result["work"] = true;                    
                    }
                    else
                    if ($statusCode>=300 && $statusCode<=399)
                    {
                        $location = $params["location"];
                        if (!empty ($location))
                        {
                            $url = $location;
                            $redirect = $statusCode;  
                        }
                        else
                        {
                            $result["comment"] .= $CNCAT["lang"]["check_com_redirect_empty"]."\n";  
                            $CNCAT_ENGINE->misc->itemLog(0, $result["comment"]);
                            return $result;
                        }
                    }        
                    else
                    if ($statusCode==404)
                    {
                        $result["comment"] .= $CNCAT["lang"]["check_com_404"].": ".$lines[0] . "\n";  
                        $CNCAT_ENGINE->misc->itemLog(0, $result["comment"]);
                        return $result;
                    }
                    else
                    {
                        $result["comment"] .= $CNCAT["lang"]["check_com_error"].": ".$lines[0] . "\n";  
                        $CNCAT_ENGINE->misc->itemLog(0, $result["comment"]);
                        return $result;
                    };
                }                        
            } 
            else 
            // If sockets functions disabled, trying file(). 
            // This method can't use timeout and log redirects in headers (only in <meta>).
            if (ini_get("allow_url_fopen")==1)
            {   
                $contents = @file_get_contents($url);
                if (!empty($contents))
                    $result["work"] = true;
            }
            else 
            // No way to download page contents
            {
                $result["comment"] .= $CNCAT["lang"]["check_err_noway"] . "\n";
                //$result["error"] .= comment($CNCAT["lang"]["check_err_noway"]);
                $CNCAT_ENGINE->misc->itemLog(0, $result["comment"]);
                return $result;
            }

            // Check for <meta> redirect
            // like <head> ... <meta http-equiv="refresh" content="0; url=http://www.somesite.com"> ... </head>        
            if (!empty ($contents))
            {
                if ($CNCAT["config"]["check_favicon"]) {
                    $result["favicon"] = cn_getFavicon($url, $contents);
                }

                // Search for "<meta>"
                $tagName = "meta";
                $pregTag = $pregTagStart.$tagName."\s+((".$pregAttribute.")*)".$pregTagEnd;
                if (preg_match ("/<\s*head[^>]*>.*".$pregTag.".*<\/head\s*>/is", $contents, $match))
                {   
                    /*echo "<textarea style='width:400px;height:400px'>";
                    print_r ($match[1]);
                    echo "</textarea>";
                      */

                    // Check <meta>'s attributes
                    if (preg_match_all ("/".$pregAttribute."/is" . $CN_STRINGS["preg"], $match[1], $attrs1, PREG_SET_ORDER))
                    {
                        $attrs = array();
                        for ($i=0; $i<count($attrs1); $i++)
                            $attrs[cn_strtolower($attrs1[$i][1])] = cn_trimQuotes($attrs1[$i][2]);
                    /*echo "<textarea style='width:400px;height:400px'>";
                    print_r ($attrs1);
                    echo "</textarea>";
                      */
                        
                        
                        // Редирект через META считается только если время обновления = 0
                        if (cn_strtolower($attrs["http-equiv"])=="refresh")
                        {
                            // Fetch URL
                            $pregUrlValue=
                                '('.
                                '(?:\s*\"(?:[^"]*)\")|'.      // "..."
                                "(?:\s*\'(?:[^']*)\')|".      // '...'
                                '(?:[^\s\;]*)'.             // not quoted value limited by space or ";"
                                ')';    
                            
                            if (preg_match ("/^0;url=".$pregUrlValue."/is" . $CN_STRINGS["preg"], $attrs["content"], $match))
                            {
                                $redirect = 1;
                                $url = cn_trimQuotes($match[1]);
                                $result["work"]=false;
                            };
                        }
                    }
                }   
            }
        } while ($redirect);

        if ($result["work"]) {
            $result["comment"] .= $CNCAT["lang"]["log_check_work_yes"] . "\n";
        } else {
            $result["comment"] .= $CNCAT["lang"]["log_check_work_no"] . "\n";
        }

        //*** Check back link if needed
        if ($checkBack && $result["work"]) {
            $result["comment"] .= $CNCAT["lang"]["log_check_back"] . ": " . $url . "\n";

            $disallow = false;

            if ($CNCAT["config"]["check_robots_txt"]) {
                if (empty($CNCAT["config"]["check_bots_list"])) {
                    $rules = cn_rtxt_parse_url($url);

                    if ($rules) {
                        $result["comment"] .= $CNCAT["lang"]["log_rtxt_analyse"] . "\n";
                        $disallow = cn_rtxt_match($rules, $url);
                    }
                } else {
                    $rules = cn_rtxt_parse_url($url);

                    if ($rules) {
                        $result["comment"] .= $CNCAT["lang"]["log_rtxt_analyse"]. "\n";
                        $disallow = cn_rtxt_match($rules, $url, '*');
    
                        if (!$disallow) {
                            foreach (explode(',', $CNCAT["config"]["check_bots_list"]) as $bot) {
                                $disallow = cn_rtxt_match($rules, $url, trim($bot));
    
                                if ($disallow) {
                                    break;
                                }
                            }
                        }
                    }
                }
                
                if ($disallow) {
                    $result["comment"] .= $CNCAT["lang"]["log_rtxt_find_disallow"] . "\n";
                }
            }

            if ($disallow) {
                $result["back"] = 0;
            } else {
                // Удаляем пустые строки на странице и делаем трим для непустых
                $contents1 = explode ("\n", $contents);                
                $count = count($contents1);
                for ($i=0; $i<$count; $i++)
                {
                    $contents1[$i] = cn_trim($contents1[$i]);
                    if (empty ($contents1[$i]))
                        unset($contents1[$i]);               
                }
                $contents = join ("\n", $contents1);
    
                // charset
                $charset = '';
                
                if (isset($params['content-type'])) {
                    preg_match("#^(.*);\\s*charset\\s*=\\s*([^;]*)?\$#Usi", $params['content-type'], $m);
                    $charset = cn_strtolower(cn_trim($m[2]));
                }
    
                if (empty($charset)) {
                    // get charset from meta
                    if (preg_match("#<meta\\s+http-equiv\\s*=\\s*(\"|\'|)content-type(\\1)(.*)>#Usi", $contents, $m)) { //<?php
                        if (preg_match("#content=(\"|\'|)(.*);\\s+charset\\s*=\\s*(.*)(\\1)#Usi", cn_trim($m[3]), $m2)) {
                            $charset = cn_strtolower(cn_trim($m2[3]));
                        }
                    }elseif (preg_match("#<meta\\s+(.*)?\\s+http-equiv\\s*=\\s*(\"|\'|)content-type(\\2)(.*)>#Usi", $contents, $m)) { //<?php       
                        if (preg_match("#content=(\"|\'|)(.*);\\s+charset\\s*=\\s*(.*)(\\1)#Usi", cn_trim($m[1]), $m2)) {
                            $charset = cn_strtolower(cn_trim($m2[3]));
                        }
                    }
                    elseif (preg_match("#<meta\\s+charset\\s*=\\s*(\"|\'|)([^>]*?)(\\1)\\s*\/>#Usi", $contents, $m)) { //<?php       
                        $charset = cn_strtolower(cn_trim($m[2]));
                    }
                }
                   
                if (empty($charset)) {
                    $charset = cn_strtolower($CNCAT["lang"]["charset"]);
                }

                $charset = cn_strtolower(cn_trim($charset, " \"';"));
                $convert = ($charset != $CNCAT["lang"]["charset"]);
                
                if ($convert) {
                    $iconv = false;
                    $mbstring = false;
                    
                    if (function_exists("mb_convert_encoding")) {
                        $mbstring = true;
                    } elseif (function_exists("iconv")) {
                        $iconv = true;
                    }
                }
    
                //print $charset;
                // Getting list of back link variants            
                $query = "SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . "backlinks`";        
                $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    
                $contents = preg_replace('/<\s*noindex\s*>(.*?)<\/\s*noindex\s*>/si', '', $contents);
                $contents = preg_replace('/<a[^<]*?rel="[^"]*?nofollow[^"]*?"[^>]*?>/si', '', $contents);
                //<?  
                 
                $backlinks = array();
                
                while ($bl = mysql_fetch_assoc($res)) {
                    $backlinks[] = array_merge($bl, array("_backurl" => cn_getBackUrl("%SITEID%")));
                    $backlinks[] = array_merge($bl, array("_backurl" => cn_getBackUrl2("%SITEID%")));
                    $backlinks[] = array_merge($bl, array("_backurl" => cn_getBackUrl("%REF%")));
                }
    
                foreach ($backlinks as $bl) {
                    if ($result["back"]) {
                        break;
                    }
    
                    $checkMethod = $bl["check_method"];
                    $preg = "";
    
                    // Creating PREG expression depending on check method
                    if ($checkMethod=="code") {
                        $blcode = $bl["check_code"];
    
                        if ($convert) {
                            $result["comment"] .= $CNCAT["lang"]["log_convert_encoding"] . ": " . $charset . " => " . $CNCAT["lang"]["charset"] . "\n";

                            if ($mbstring) {
                                $blcode = mb_convert_encoding($blcode, $charset, $CNCAT["lang"]["charset"]);
                            } elseif ($iconv) {
                                $iconv = iconv($CNCAT["lang"]["charset"], $charset, $blcode);
                            } else {
                                $result["comment"] .= $CNCAT["lang"]["log_convert_encoding_failed"] . "\n";
                            }

                            if (!$blcode) {
                                $blcode = $bl["check_code"];
                            }
                        }

                        if (empty($blcode)) {
                            continue;
                        }

                        $blcode = cn_str_replace ("%CATNAME%", $CNCAT["config"]["catalog_title"], $blcode);
                        $blcode = cn_str_replace ("%BACKURL%", $bl["_backurl"], $blcode);
                        $preg = "/".preg_quote ($blcode, "/")."/Ui";
                        $preg = cn_str_replace ("%SITEID%", "(\d*)", $preg);
                        $preg = cn_str_replace ("%REF%", "r(\w+)", $preg);
                    }

                    if ($checkMethod=="preg") {
                        $preg = $bl["check_preg"];
                    }

                    // Check PREG expression
                    if (!empty($preg))
                    {
                        if (preg_match ($preg . $CN_STRINGS["preg"], $contents, $matches))
                        {
                            if (!empty ($matches))
                            {
                                $result["back"] = true;
                                break;   
                            }
                        }
                    }
                    
                    if ($checkMethod=="url")
                    {
                        $backlink = $bl["check_url"];
                        $backlink = cn_str_replace("%BACKURL%", $bl["_backurl"], $backlink);
                        
                        if (cn_substr($backlink, -1) == '/') {
                            $backlink = cn_substr($backlink, 0, -1);
                        }
                        
                        $preglink = preg_quote($backlink, "/");
                        $preglink = cn_str_replace("%SITEID%", "(\d+)", $preglink);
                        $preglink = cn_str_replace("%REF%", "r(\w+)", $preglink);
                        
                        if (cn_substr($preglink, -2, 2) == "\*") {
                            $preglink = cn_substr ($preglink, 0, cn_strlen($preglink)-2)."(.*)";
                        }

                        $preglink = "/^(".$preglink.")\/?$/i";

                        $tagName = "a";
                        $pregTag = $pregTagStart.$tagName."\s+(?:(?:".$pregAttribute.")*)".$pregTagEnd;
                        $pregHref ="(?:(?:href\s*=".$pregAttrValue."))";
    
                        if (preg_match_all ("/" . $pregTag . $CN_STRINGS["preg"] . "/", $contents, $matches))
                        {
                            foreach ($matches[0] as $aTag)
                            {
                                if (preg_match ("/".$pregHref."/is" . $CN_STRINGS["preg"], $aTag, $match))
                                {
                                    $href = cn_trimQuotes($match[1]);
                                    if (preg_match ($preglink . $CN_STRINGS["preg"], $href, $matches1))
                                    {
                                        /*if (cn_substr($matches1[0], -1) == '/') {
                                            $matches1[0] = cn_substr($matches1[0], 0, -1);
                                        }*/

                                        if (cn_substr($href, -1) == '/') {
                                            $href = cn_substr($href, 0, -1);
                                        }

                                        if ($matches1[1]==$href)
                                        {
                                            $result["back"] = true;
                                            break;
                                        }   
                                    }
                                };
                            }
                        }
                    }
                }
            }

            if ($result["back"]) {
                $result["comment"] .= $CNCAT["lang"]["log_check_back_yes"] . "\n";
            } else {
                $result["comment"] .= $CNCAT["lang"]["log_check_back_no"] . "\n";
            }
        };    
    };

    if ($checkPR) {
        $result["pr"] = (int)cn_calcRankPR($orig_url, $result["comment"]);   
        $result["comment"] .= $CNCAT["lang"]["log_check_pr"] . ": " . $result["pr"] . "\n";
    }

    if ($checkCY) {
        $result["cy"] = (int)cn_calcRankCY(urlencode($orig_url), $result["comment"]);
        $result["comment"] .= $CNCAT["lang"]["log_check_cy"] . ": " . $result["cy"] . "\n";
    }

    if (!empty($result["comment"])) {
        $CNCAT_ENGINE->misc->itemLog(0, $result["comment"]);
    }

    return $result;
};

function cn_getFavicon($url, $html) {
    $mime = "";
    $fav_url = "";

    if (preg_match("#<link\s+rel\s*=\s*[\"|'](shortcut icon|icon)[\"|'](.*)/{0,1}>#Usi" . $CN_STRINGS["preg"], $html, $match)) {
        $params = $match[2];
        preg_match("#href\s*=\s*[\"|'](.*?)[\"|']#i" . $CN_STRINGS["preg"], $params, $match);
        $path = trim($match[1]);
        preg_match("#type\s*=\s*[\"|'](.*?)[\"|']#i" . $CN_STRINGS["preg"], $params, $match);
        $mime = trim($match[1]);

        if (empty($mime)) {
            $mime = "image/x-icon";
        }

        if (cn_substr($path, 0, 7) == "http://" || cn_substr($path, 0, 8) == "https://") {
            $fav_url = $path;
        } else {
            if (cn_substr($path, 0, 1) != "/") {
                if (cn_substr($url, -1) != "/") {
                    $url .= "/";
                }

                $fav_url = $url . $path;
            } else {
                $urlp = parse_url($url);
                $scheme = $urlp["scheme"];
                $host = $urlp["host"];

                $fav_url = $scheme . "://" . $host . $path;
            }
        }

        if ($mime == "image/x-icon" || $mime == "image/gif") {
            $data = @file_get_contents($fav_url);
        }
    } else {
        $url_params = parse_url($url);
        $fav_url = $url_params["scheme"] . "://" . $url_params["host"] . "/favicon.ico";

        $fp = @fsockopen($url_params["host"], 80, $errno, $errstr, 30);

        if ($fp) {
            fputs($fp, "GET /favicon.ico HTTP/1.0\r\n");
        	fputs($fp, "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)\r\n");
        	fputs($fp, "Host: " . $url_params["host"] . "\r\n");
        	fputs($fp, "Connection: Close\r\n\r\n");

            $res = "";

            while (!feof($fp)) {
                $res .= fgets ($fp, 4096);
            }

            @list($res_heders, $res_data) = explode("\r\n\r\n", $res, 2);

            if (preg_match("/^Content-Type:\s*(.*)/m", $res_heders, $match)) {
                $type = strtolower($match[1]);

                if (in_array($type, array("image/x-icon", "image/vnd.microsoft.icon"))) {
                    $mime = "image/x-icon";
                    $data = $res_data;
                }
            }
        }
    }

    if (strlen($data) > 66560) {
        $data = "";
    }

    return array("data" => $data, "mime" => $mime, "url" => $fav_url);
}

function cn_calcRankCY($url, &$comment)
{
    global $CNCAT;
    
    if ($CNCAT["config"]["check_method"] == 0 && function_exists("fsockopen"))
    {
	$fp = @fsockopen ("bar-navig.yandex.ru", 80, $errno, $errstr, 30);
	if (!$fp) 
        {
            return -1;
        }

	fputs($fp,"GET /u?ver=2&id=1328476&lang=1049&url=".$url."&show=1&thc=0 HTTP/1.0\r\n");
	fputs($fp,"Accept: */*\r\n");
	fputs($fp,"Accept-Encoding: gzip, deflate\r\n");
	fputs($fp,"User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)\r\n");
	fputs($fp,"Host: bar-navig.yandex.ru\r\n");
	fputs($fp,"Connection: Close\r\n");
	fputs($fp,"Cookie: yandexuid=557581074596310\r\n\r\n");
	$data="";
	while (!feof($fp)) 
            $data .= fgets ($fp,4096);
        $cy=0;
	if (preg_match ("/.*\<tcy rang=\"[0-9]+\" value=\"([0-9]+)\"\/>.*/", $data, $regs)) $cy=intval($regs[1]);
	fclose ($fp);
        return $cy;
    }
    else
    {

        $yandex_url = "http://bar-navig.yandex.ru/u?ver=2&id=1328476&lang=1049&url=".$url."&show=1&thc=0"; // url to get from google
        if ($handle=@fopen($yandex_url,"rb"))
        {
            while(true)
            {
                    $data=fread($handle,8192);
                    if (cn_strlen($data)==0)
                    {
                            break;
                    }
                    $contents .= $data;
            }
            fclose($handle);
            if (preg_match ("/.*\<tcy rang=\"[0-9]+\" value=\"([0-9]+)\"\/>.*/", $contents, $regs))
                $cy=intval($regs[1]);

            return $cy;
        }
        else
            return -1;
    }
}


function cn_calcRankPR($url, &$comment)
{ 
 
    define('GOOGLE_MAGIC', 0xE6359A60);


    $pr=get_pr($url);
	return($pr[1]);
};    


function cn_nooverflow($a) 
{ 
    while ($a<-2147483648) 
        $a+=2147483648+2147483648; 
    while ($a>2147483647) 
        $a-=2147483648+2147483648; 
    return $a; 
} 

function cn_zeroFill($x, $bits) 
{ 
   if ($bits==0) return $x; 
   if ($bits==32) return 0; 
   
   $y = shr(ands($x,0x7FFFFFFF) , $bits);
   if (ands(0x80000000 , $x)) { 
       $y |= (shl(1,(31-$bits))); 
   }
   return $y; 
}
 
function cn_mix($a,$b,$c) 
{ 
    $a=(int)$a; $b=(int)$b; $c=(int)$c; 
    $a -= $b; $a -= $c; $a=cn_nooverflow($a); $a = xors($a,(cn_zeroFill($c,13))); 
    $b -= $c; $b -= $a; $b=cn_nooverflow($b); $b = xors($b,(shl($a,8))); 
    $c -= $a; $c -= $b; $c=cn_nooverflow($c); $c = xors($c,(cn_zeroFill($b,13))); 
        
    $a -= $b; $a -= $c; $a=cn_nooverflow($a); $a = xors($a,(cn_zeroFill($c,12))); 
    $b -= $c; $b -= $a; $b=cn_nooverflow($b); $b = xors($b,(shl($a,16))); 
    $c -= $a; $c -= $b; $c=cn_nooverflow($c); $c = xors($c,(cn_zeroFill($b,5))); 
        
    $a -= $b; $a -= $c; $a=cn_nooverflow($a); $a = xors($a,(cn_zeroFill($c,3))); 
    $b -= $c; $b -= $a; $b=cn_nooverflow($b); $b = xors($b,(shl($a,10))); 
    $c -= $a; $c -= $b; $c=cn_nooverflow($c); $c = xors($c,(cn_zeroFill($b,15))); 
        
    return array($a,$b,$c); 
} 
function dec2bin($dec)
{
    $result    = '';
    $shift    = 0;

    if ($dec<0)
    {
        $minus=true;                        // отрицательное
        $dec=-1*($dec+1);                   // делаем NOT, чтоб делить положительное число, но ставим флаг что отрицательное
    } else {
        $minus=false;
    }

    while ( pow(2, $shift) < $dec )         // стандартные наработки
    {   
        ++$shift;
    }
    while ( 0 <= $shift )
    {
        $pow = pow(2, $shift);
        if ( $pow <= $dec )
        {
            $dec-= $pow;
            $result = $result . ($minus?'0':'1');   // если отрицательное число у нас, то NOT
    } else {
        $result = $result . ($minus?'1':'0');
        }
        --$shift;
    }
    $result=str_pad($result, 64, ($minus?"1":"0"), STR_PAD_LEFT);
// заполняем результат слева, чтобы было 64 символа (дальше поймете почему)
    return $result;
}
function xors ($a, $b)   // переменные передаются в десятичной форме
{
    $c='0000000000000000000000000000000000000000000000000000000000000000';

    $a=dec2bin($a);
    $b=dec2bin($b);

    for ($i=63;$i>=0;$i--)
    {
       $c[$i]=((int)$a[$i]^(int)$b[$i]); 
    }

    $c=bin2dec($c);    
    return $c;
}

function ands ($a, $b)   // переменные передаются в десятичной форме
{
    $c='0000000000000000000000000000000000000000000000000000000000000000';

    $a=dec2bin($a);
    $b=dec2bin($b);

    for ($i=63;$i>=0;$i--)
    {
       $c[$i]=((int)$a[$i]&(int)$b[$i]); 
    }

    $c=bin2dec($c);    
    return $c;
}


function shl($a, $bit)    
{
    $a=dec2bin($a);
    $a=str_pad($a, 64+$bit, "0", STR_PAD_RIGHT); // добавляем справа "0" количество $bit
    $c=substr($a,$bit);     // а теперь отрезаем, тоесть сдвинули влево на $bit
    $c=bin2dec($c);       

    return $c;
}
function shr($a, $bit)     
{
    $a=dec2bin($a);
    $a=str_pad($a, 64+$bit, "0", STR_PAD_LEFT);
    $c=substr($a,0, -$bit);
    $c=bin2dec($c);        
    return $c;
}
function bin2dec($c)
{
 if ($c>'0000000000000000000000000000000001111111111111111111111111111111')
 {
 
  for ($i=63;$i>=0;$i--)
  {
   $c[$i]=((int)$c[$i]^1); //^1;
  }

  $c=base_convert(substr($c,32), 2, 10);
  $c=-1*($c+1);
 } else {
  $c=bindec($c);
 }
   
    return $c;
}
function cn_GoogleCH($url, $length=null, $init=GOOGLE_MAGIC) 
{ 
    if(is_null($length)) 
    { 
        $length = sizeof($url); 
    } 
    
    $a = $b = 0x9E3779B9; 
    $c = $init; 
    $k = 0; 
    $len = $length; 
    while($len >= 12) 
    { 
        $a += ($url[$k+0] +(shl($url[$k+1],8)) +(shl($url[$k+2],16)) +(shl($url[$k+3],24))); 
        $b += ($url[$k+4] +(shl($url[$k+5],8)) +(shl($url[$k+6],16)) +(shl($url[$k+7],24))); 
        $c += ($url[$k+8] +(shl($url[$k+9],8)) +(shl($url[$k+10],16))+(shl($url[$k+11],24))); 
        $cn_mix = cn_mix($a,$b,$c); 
        $a = $cn_mix[0]; $b = $cn_mix[1]; $c = $cn_mix[2]; 
        $k += 12; 
        $len -= 12; 
    } 
    $c += $length; 
    switch($len) 
    { 
        case 11: $c+=(shl($url[$k+10],24)); 
        case 10: $c+=(shl($url[$k+9],16)); 
        case 9 : $c+=(shl($url[$k+8],8)); 
        case 8 : $b+=(shl($url[$k+7],24)); 
        case 7 : $b+=(shl($url[$k+6],16)); 
        case 6 : $b+=(shl($url[$k+5],8)); 
        case 5 : $b+=($url[$k+4]); 
        case 4 : $a+=(shl($url[$k+3],24)); 
        case 3 : $a+=(shl($url[$k+2],16)); 
        case 2 : $a+=(shl($url[$k+1],8)); 
        case 1 : $a+=($url[$k+0]); 
    }
    $cn_mix = cn_mix($a,$b,$c);
    return $cn_mix[2]; 
} 

   
//converts a string into an array of integers containing the numeric value of the char
function cn_strord($string) 
{
    for($i=0;$i<strlen($string);$i++) 
    {
        $result[$i] = ord($string{$i});
    }    
    return $result;
}



function strToNum($str, $check, $magic) {
	$int32Unit = 4294967296;  // 2^32
 
	$length = strlen($str);
	for ($i = 0; $i < $length; $i++) {
      $check *= $magic;
      if ($check >= $int32Unit) {
        	$check = ($check - $int32Unit * (int) ($check / $int32Unit));
        	//if the check less than -2^31
        	$check = ($check < -2147483648) ? ($check + $int32Unit) : $check;
      }
      $check += ord($str{$i});
  }
  return $check;
}
/**
  * Получаем хеш URL-а
  * @param	string	$string
  * @return	integer
*/
function hashUrl($string) {
  $check1 = strToNum($string, 0x1505, 0x21);
  $check2 = strToNum($string, 0, 0x1003F);
  
  $check1 >>= 2;
  $check1 = (($check1 >> 4) & 0x3FFFFC0 ) | ($check1 & 0x3F);
  $check1 = (($check1 >> 4) & 0x3FFC00 ) | ($check1 & 0x3FF);
  $check1 = (($check1 >> 4) & 0x3C000 ) | ($check1 & 0x3FFF);
  
  $T1 = (((($check1 & 0x3C0) << 4) | ($check1 & 0x3C)) <<2 ) | ($check2 & 0xF0F );
  $T2 = (((($check1 & 0xFFFFC000) << 4) | ($check1 & 0x3C00)) << 0xA) | ($check2 & 0xF0F0000 );
  return ($T1 | $T2);
}
/**
  * Получаем чексум URL-а
  * @param	integer	$Hashnum	хеш URL-а
  * @return	integer
*/
function checkHash($hashNum) {
  $checkByte = 0;
  $flag = 0;
  $hashStr = sprintf('%u', $hashNum) ;
  $length = strlen($hashStr);
  for ($i = $length - 1;  $i >= 0;  $i --) {
      $re = $hashStr{$i};
      if (1 === ($flag % 2)) {
      	$re += $re;
      	$re = (int)($re / 10) + ($re % 10);
      }
      $checkByte += $re;
      $flag ++;
  }
  
  $checkByte %= 10;
  if (0 !== $checkByte) {
      $checkByte = 10 - $checkByte;
      if (1 === ($flag % 2) ) {
      	if (1 === ($checkByte % 2)) {
      		$checkByte += 9;
      	}
      	$checkByte >>= 1;
      }
  }

  return '7' . $checkByte . $hashStr;
}






function get_pr($url, $timeout = 30)
{
    global $CNCAT;
    $result=array("",-1);
    
    if (($url.""!="")&&($url.""!="http://"))
    {
        // check for protocol
        if (cn_substr(cn_strtolower($url),0,7)!="http://")
        {
        	$url="http://".$url;
        }

        $url=rtrim($url, "/");
        //$url="info:".$url; 
         
	      $checksum=checkHash(hashUrl($url));//cn_GoogleCH(cn_strord($url));

        //$checksum = ($checksum >= 0? $checksum: 4294967296 + $checksum);
          
        $contents="";

        // let's get ranking
        // this way could cause problems because the Browser Useragent is not set...
        if ($CNCAT["config"]["check_method"] == 0 && function_exists("fsockopen"))
        {
			$google_url="/tbr?client=navclient-auto&features=Rank&ch={$checksum}&q=info:"  . $url; // url to get from google
            // Creating socket  
            $address = "toolbarqueries.google.com";

            if ($timeout>0)
                $socket = @fsockopen($address, 80, $errno, $errstr, $timeout);
            else
                $socket = @fsockopen($address, 80, $errno, $errstr);
            if (!$socket)
            {
               $contents="Connection unavailable";
               break;
            } 
            $request = "GET ".$google_url." HTTP/1.0\r\n";
            fputs($socket, $request);
            fputs($socket,"Accept: */*\r\n");
            fputs($socket,"User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)\r\n");
            fputs($socket,"Host: ".$address."\r\n\r\n");
            fputs($socket,"Connection: Close\r\n");
            
            // Response
            while (!feof($socket))
                $contents .= fgets($socket, 2048);
            fclose ($socket);
            
            $contents = strstr( $contents,"Rank" );
            
        }
        else
        {
            $google_url="http://toolbarqueries.google.com/tbr?client=navclient-auto&features=Rank&ch={$checksum}&q=info:"  . $url;
            if ($handle=@fopen($google_url,"rb"))
            {
                    while(true):
                            $data=fread($handle,8192);
                            if (cn_strlen($data)==0):
                                    break;
                            endif;
                            $contents.=$data;
                    endwhile;
                    fclose($handle);
            }
            else
                $contents="Connection unavailable";
        }
        $result[0]=$contents;
        // Rank_1:1:0 = 0
        // Rank_1:1:5 = 5
        // Rank_1:1:9 = 9
        // Rank_1:2:10 = 10 etc
        $p=explode(":",$contents);
        if (isset($p[2]))
        {
            $result[1]=$p[2];
        }
    }
    
    return $result;
}
//TODO go to router?
function cn_getBackUrl($resId) {
    global $CNCAT;

    return "http://" . $_SERVER["HTTP_HOST"] . $CNCAT["abs"] ."{$CNCAT["system"]["dir_prefix"]}from.php?" . $resId;
}

function cn_getBackUrl2($resId) {
    global $CNCAT;

    return "http://" . $_SERVER["HTTP_HOST"] . $CNCAT["abs"] ."from.php?" . $resId;
}
function idnaConvert($string)
{
    $idna_convert = new idna_convert();
    return $idna_convert->encode($string);
}
?>
