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

    // CNStrings library
    // Some code for multibyte strings was taken from the Drupal Portal Engine (http://drupal.org)

    //*** Initialization    
    define ('CN_STRINGS_MODE_NATIVE', 0);
    define ('CN_STRINGS_MODE_MULTI', 1);
    define ('CN_STRINGS_MODE_SINGLE', 2);

    $CN_STRINGS = array("mode"=>"", "charset"=>"", "preg"=>"", "error"=>"");
    

    /**
    *   This function should be called before any use of another cnstrings functions
    */
    function cn_strings_init($needUtf8, $charset="")
    {
        GLOBAL $CN_STRINGS;
        
        // If not need to use unicode, return successfull
        if (!$needUtf8)
        {
            $CN_STRINGS["mode"] = CN_STRINGS_NATIVE;            
            $CN_STRINGS["charset"] = $charset;
            $CN_STRINGS["preg"] = "";
            return true;
        }

        // Check if PREG functions support unicode
        if (preg_match('/[à-á]/u', 'â'))  
        {
            $CN_STRINGS["error"] = "The PCRE library in your PHP installation is outdated. This will cause problems when handling Unicode text. If you are running PHP 4.3.3 or higher, make sure you are using the PCRE library supplied by PHP. Please refer to the <a href='http://www.php.net/pcre' target='_blank'>PHP PCRE documentation</a> for more information.";
            return false;
        }

        // Check for mbstring extension
        if (!function_exists('mb_strlen')) 
        {
            $CN_STRINGS["mode"] = CN_STRINGS_SINGLE;
            $CN_STRINGS["charset"] = "utf-8";
            $CN_STRINGS["preg"] = "u";
            return true;
        }
        
        // Check mbstring configuration
        if (ini_get("mbstring.func_overload") != 0) 
        {
            $CN_STRINGS["error"] = "Multibyte string function overloading in PHP is active and must be disabled. Check the php.ini <em>mbstring.func_overload</em> setting. Please refer to the <a href='http://www.php.net/mbstring' target='_blank'>PHP mbstring documentation</a> for more information.";
            return false;
        }
        
        if (ini_get('mbstring.encoding_translation') != 0) 
        {
            $CN_STRINGS["error"] = "Multibyte string input conversion in PHP is active and must be disabled. Check the php.ini <em>mbstring.encoding_translation</em> setting. Please refer to the <a href='http://www.php.net/mbstring' target='_blank'>PHP mbstring documentation</a> for more information.";
            return false;
        }

        if (ini_get('mbstring.http_input') != 'pass') 
        {
            $CN_STRINGS["error"] = "Multibyte string input conversion in PHP is active and must be disabled. Check the php.ini <em>mbstring.http_input</em> setting. Please refer to the <a href='http://www.php.net/mbstring' target='_blank'>PHP mbstring documentation</a> for more information.";
            return false;
        }
        
        if (ini_get('mbstring.http_output') != 'pass') 
        {
            $CN_STRINGS["error"] = "Multibyte string output conversion in PHP is active and must be disabled. Check the php.ini <em>mbstring.http_output</em> setting. Please refer to the <a href='http://www.php.net/mbstring' target='_blank'>PHP mbstring documentation</a> for more information.";
            return false;
        }

        // Set appropriate configuration
        mb_internal_encoding("utf-8");
        mb_language("uni");
        $CN_STRINGS["mode"] = CN_STRINGS_MODE_MULTI;
        $CN_STRINGS["charset"] = "utf-8";
        $CN_STRINGS["preg"] = "u";
        return true;
    }    
    

    
    
    //*** String functions    
    
    /**
    * strpos()
    */
    function cn_strpos($haystack, $needle, $offset=NULL)
    {
        GLOBAL $CN_STRINGS;
        
        
        switch ($CN_STRINGS["mode"])
        {
            case CN_STRINGS_MODE_NATIVE:
                return is_null($offset) ? strpos($haystack, $needle) : strpos($haystack, $needle, $offset);
        
            case CN_STRINGS_MODE_MULTI:
                return is_null($offset) ? mb_strpos($haystack, $needle) : mb_strpos($haystack, $needle, $offset);

            case CN_STRINGS_MODE_SINGLE:
                return FALSE;
        }
    
    }
    /**
    * strpos()
    */
    function cn_strstr($haystack, $needle)
    {
        GLOBAL $CN_STRINGS;
        
        switch ($CN_STRINGS["mode"])
        {
            case CN_STRINGS_MODE_NATIVE:
                return strpos($haystack, $needle);
        
            case CN_STRINGS_MODE_MULTI:
                return mb_strpos($haystack, $needle);

            case CN_STRINGS_MODE_SINGLE:
                return FALSE;
        }
    
    }
    /**
     * Strstr function
     */
    function cn_strtr($str, $replace_pairs)
    {
        GLOBAL $CN_STRINGS;
        
        switch ($CN_STRINGS["mode"])
        {
            case CN_STRINGS_MODE_NATIVE:
                return strtr($str, $replace_pairs);
        
            case CN_STRINGS_MODE_MULTI:
                return str_replace(array_keys($replace_pairs), array_values($replace_pairs), $str);
            case CN_STRINGS_MODE_SINGLE:
                return FALSE;
        }
    }
    /**
    * strrpos()
    */
    function cn_strrpos($haystack, $needle, $offset=NULL)
    {
        GLOBAL $CN_STRINGS;
        
        
        switch ($CN_STRINGS["mode"])
        {
            case CN_STRINGS_MODE_NATIVE:
                return is_null($offset) ? strrpos($haystack, $needle) : strpos($haystack, $needle, $offset);
        
            case CN_STRINGS_MODE_MULTI:
                return is_null($offset) ? mb_strrpos($haystack, $needle) : mb_strpos($haystack, $needle, $offset);

            case CN_STRINGS_MODE_SINGLE:
                return FALSE;
        }
    
    }
    

    /**
    * cn_str_replace()
    */
    function cn_str_replace($search, $replace, $subject)
    {
        return str_replace ($search, $replace, $subject);
    }

    /**
    * cn_str_replace()
    */
/*    function cn_str_replace($search, $replace, $subject, &$count)
    {
        return str_replace ($search, $replace, $subject, $count);
    }
  */  
    function cn_substr($str, $start, $length=NULL)
    {
        GLOBAL $CN_STRINGS;
        
        switch ($CN_STRINGS["mode"])
        {
            case CN_STRINGS_MODE_NATIVE:
                return is_null($length) ? substr($str, $start) : substr ($str, $start, $length);

            case CN_STRINGS_MODE_MULTI:
                return is_null($length) ? mb_substr($str, $start) : mb_substr ($str, $start, $length);

            case CN_STRINGS_MODE_SINGLE:
                return is_null($length) ? mb_substr($str, $start) : mb_substr ($str, $start, $length);
        }
    }

    function _cn_substr($str, $f, $l = null) {
        preg_match('/$/Ds', $str, $m, PREG_OFFSET_CAPTURE);
        $str_l = $m[0][1];

    	if ($l !== null) {
    		if (($l < 0 && -$l > $str_l)) {
    			return false;
    		} elseif ($l > $str_l) {
    			$l = $str_l;
    		}
    	} else {
    		$l = $str_l;
    	}
    
    	if ($f > $str_l || ($f < 0 && -$f > $str_l)) {
    		return false;
    	}
    
    	if ($l < 0 && ($l + $str_l - $f) < 0) {
    		return false;
    	}
    
    	if ($f < 0) {
    		$f = $str_l + $f;
    
    		if ($f < 0) {
    			$f = 0;
    		}
    	}
    
    	if ($l < 0) {
    		$l = ($str_l - $f) + $l;
    
    		if ($l < 0) {
    			$l = 0;
    		}
    	}
    
    	if ($f >= $str_l) {
    		return false;
    	}
    
    	if (($f + $l) > $str_l) {
    		$l = $str_l - $f;
    	}
    
        $str_ret = '';
        $i = 0;
    
        while ($i < $l) {
            $str_ret .= $str[$f + $i++];
        }
    
        return $str_ret;
    }
    
    function _cn_strlen($str) {
        preg_match('/$/Ds', $str, $m, PREG_OFFSET_CAPTURE);
    
        return $m[0][1];
    }

    
    /**
    * strlen()
    */
    function cn_strlen($str)
    {
        GLOBAL $CN_STRINGS;
            
        switch ($CN_STRINGS["mode"])
        {
            case CN_STRINGS_MODE_NATIVE:
                return strlen ($str);
        
            case CN_STRINGS_MODE_MULTI:
                return mb_strlen ($str);

            case CN_STRINGS_MODE_SINGLE:
                return 0;
        }
        
    }
    
    /**
    * strtoupper()
    */
    function cn_strtoupper($str)
    {
        GLOBAL $CN_STRINGS;
        
        switch ($CN_STRINGS["mode"])
        {
            case CN_STRINGS_MODE_NATIVE:
                return strtoupper ($str);
        
            case CN_STRINGS_MODE_MULTI:
                return mb_strtoupper ($str);

            case CN_STRINGS_MODE_SINGLE:
                return 0;
        }
    }

    /**
    * strtolower()
    */
    function cn_strtolower($str)
    {
        GLOBAL $CN_STRINGS;
        
        switch ($CN_STRINGS["mode"])
        {
            case CN_STRINGS_MODE_NATIVE:
                return strtolower ($str);
        
            case CN_STRINGS_MODE_MULTI:
                return mb_strtolower ($str);

            case CN_STRINGS_MODE_SINGLE:
                return 0;
        }
    }
    
    /**
    * trim()
    * Does not support UTF-8 characters in $charlist yet
    */
    function cn_trim($str, $charlist=NULL)
    {
        return $charlist==NULL ? trim ($str) : trim ($str, $charlist);
    }

    /**
     *  cn_wordwrap ( string str [, int width [, string break [, boolean cut]]] )
     * cut unsupported
     */
    function cn_wordwrap($str, $width = 75, $break = "\n", $cut = false) {
        GLOBAL $CN_STRINGS;

        $words = preg_split("/\s+/" . $CN_STRINGS["preg"], $str);
        $str = "";
        $line = "";

        foreach ($words as $n => $w) {
            if (cn_strlen($line . $w) > $width) {
                $str .= $line . $break;
                $line = "";
            }

            $line .= $w . " ";
        }

        return $str;
    }
    /**
     * Encode to UTF8
     * @param string $s
     * @return string
     */
    function cn_utf_encode($s)
    {
      $c209 = chr(209); $c208 = chr(208); $c129 = chr(129);
      $len = strlen($s);
      for($i=0; $i<$len; $i++)
        {
        $c=ord($s[$i]);
        if ($c>=192 and $c<=239) $t.=$c208.chr($c-48);
        elseif ($c>239) $t.=$c209.chr($c-112);
        elseif ($c==184) $t.=$c209.$c209;
        elseif ($c==168)    $t.=$c208.$c129;
        else $t.=$s[$i];
        }
      return $t; 
    }
    /**
     * UTF8 to cp1251
     * @param string $s
     * @return string
     */
    function cn_utf_to_win1251($s)
    {
        
      for ($c=0;$c<strlen($s);$c++)
           {
              $i=ord($s[$c]);
              if ($i<=127) $out.=$s[$c];
                  if ($byte2){
                      $new_c2=($c1&3)*64+($i&63);
                      $new_c1=($c1>>2)&5;
                      $new_i=$new_c1*256+$new_c2;
                  if ($new_i==1025){
                      $out_i=168;
                  } else {
                      if ($new_i==1105){
                          $out_i=184;
                      } else {
                          $out_i=$new_i-848;
                      }
                  }
                  $out.=chr($out_i);
                  $byte2=false;
                  }
              if (($i>>5)==6) {
                  $c1=$i;
                  $byte2=true;
              }
           }
           return $out;

    }
    /**
     * Transliterate encode
     * @param string $str
     * @return string
     */
    function cn_translitEncode($str) 
    {
        $table = array( 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G',
                              'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH',
                              'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K',
                              'Л' => 'L', 'М' => 'M', 'М' => 'N', 'О' => 'O',
                              'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
                              'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
                              'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'CSH', 'Ь' => '',
                              'Ы' => 'Y', 'Ъ' => '', 'Э' => 'E', 'Ю' => 'YU',
                              'Я' => 'YA',
      
                              'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g',
                              'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
                              'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k',
                              'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
                              'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
                              'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
                              'ч' => 'ch', 'ш' => 'sh', 'щ' => 'csh', 'ь' => '',
                              'ы' => 'y', 'ъ' => '', 'э' => 'e', 'ю' => 'yu',
                              'я' => 'ya',
              );
        $str = strtr($str, $table);
        return  preg_replace('/[^A-Za-z0-9_\-]/', '_', $str);
    }
?>
