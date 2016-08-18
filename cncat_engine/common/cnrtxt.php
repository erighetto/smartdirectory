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
 * Match URL in robots.txt
 * @param $disallow
 * @param  $url
 * @param  $agent
 * @return boolean
 */
function cn_rtxt_match($disallow, $url, $agent = null) {
    $url_params = @parse_url($url); 
    $url = $url_params['path'];

    if ($agent != null) {
        $agent = cn_strtolower($agent);

        if (!isset($disallow[$agent])) {
            return false;
        }

        $disallow = array($agent => $disallow[$agent]);
    }

    foreach ($disallow as $agent => $agent_list) {
        foreach ($agent_list as $dis_url) {
            $dis_url = cn_str_replace(".", "\.", $dis_url);
            $dis_url = cn_str_replace("*", ".+", $dis_url);
            $dis_url = cn_str_replace("?", "\?", $dis_url);
            
            if (preg_match("#$dis_url#s",$url))
                return true;
        }
    }

    return false;
}
/**
 * Parse url
 * @param $url
 * @return array
 */
function cn_rtxt_parse_url($url) {
    return cn_rtxt_parse(cn_rtxt_fetch($url));
}
/**
 * Parse Text of robots.txt
 * @param type $text
 * @return type 
 */
function cn_rtxt_parse($text) {
    if (empty($text)) {
        return array();
    }

    $disallow = array();
    $agent = '';
    $lines = explode("\n", $text);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line[0] == '#') { 
            continue;
        } elseif ($line[0] == 'u' || $line[0] == 'U') { // User-Agent
            if (preg_match('/^User-Agent:\s*(.+)/i', $line, $match)) {
                $agent = cn_strtolower($match[1]);
            }
        } elseif ($line[0] == 'd' || $line[0] == 'D') { // Disallow
            if (preg_match('/^Disallow:\s*(.+)/i', $line, $match)) {
                if (empty($agent)) {
                    return array();
                }

                $disallow[$agent][] = $match[1];
            }
        }
    }

    return $disallow;
}
/**
 * Fetch URL
 * @param $url
 * @return string
 */
function cn_rtxt_fetch($url) {
    $url_params = parse_url($url);
    $rtxt_url = $url_params['scheme'] . '://' . $url_params['host'] . '/robots.txt';

    $text = @file_get_contents($rtxt_url);

    return $text;
}

?>
