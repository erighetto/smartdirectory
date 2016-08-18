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

if (!defined("ADMIN_INTERFACE")) die();
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "./auth.php";

if (!isAdmin()) {
    accessDenied();
}

require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "sync_all.php";
@set_time_limit(3600);

if (!function_exists('htmlspecialchars_decode')) {
    function htmlspecialchars_decode($text, $quote = ENT_NOQUOTES) {
        return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote)));
    }
}

function xml_decode($string)
{
    $string = str_replace ("&#13;", "\r", $string);
    $string = str_replace ("&#10;", "\n", $string);
    $string = str_replace ("]]&gt;", "]]>", $string);

    return htmlspecialchars_decode($string, ENT_QUOTES);
};

function CheckUploadError($filename,$error) 
{
	GLOBAL $LANG;
	if ($error==UPLOAD_ERR_OK) return;
	if ($error==UPLOAD_ERR_NO_FILE) return;
	if (!empty($filename)) print "<P><B style='color:red'>".$filename."</B>: "; else print "<P><B style='color:red'>Error:</B> ";
	if ($error==UPLOAD_ERR_INI_SIZE) print "The uploaded file exceeds the upload_max_filesize directive in php.ini.";else
	if ($error==UPLOAD_ERR_FORM_SIZE) print "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";else
	if ($error==UPLOAD_ERR_PARTIAL) print "The uploaded file was only partially uploaded.";
	else
		echo $error;
	exit;
}

if ($_GET["op"]=="ok") 
{
    //cn_syncAll();

	include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";

    print "<h1>" . $CNCAT["lang"]["import_cncat_2x_3x"] . "</h1>\n";
    print "<a href=\"index.php?act=import\">" . $CNCAT["lang"]["import"] . "</a>\n";
    print "<div class=\"deline\"></div>\n";
	print "<P><strong>" . $CNCAT["lang"]["import_finish"] . "</strong></P>\n";
	print "<UL>\n";
	print "<LI>" . $CNCAT["lang"]["import_links_count"] . ": <strong>".intval($_GET["l"])."</strong>\n";
	print "<LI>" . $CNCAT["lang"]["import_cats_count"] . ": <strong>".intval($_GET["r"])."</strong>\n";
	print "</UL>\n";
    
    if (intval ($_GET["b"])==1)
    {
	    print "<P style='color:red'>".$LANG["plugin_import_backlink_warning"]."</P>\n";
    }
	include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
	exit;
}

if ($_SERVER["REQUEST_METHOD"]=="POST") {
    include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
	$overwrite=intval($_POST["idoverwrite"]);

    mysql_query("SET NAMES 'utf8'");

	$c_r=$c_l=0;
	/* Rubrics import */
	CheckUploadError($_FILES['filer']['name'],$_FILES['filer']['error']);
	if (true)
    {
        $root = array("title" => $CNCAT["lang"]["default_root_cat_title"], "image" => "", "image_mime" => "", "descr" => "");

	    print "Importing rubrics<br>\n";flush();
	    if ($_POST["del"]=="on") 
        {
		    mysql_query("TRUNCATE TABLE `".$CNCAT["config"]["db"]["prefix"]."cats`") or die(mysql_error());
	    } else {
            $res = mysql_query("SELECT `title`, `image`, `image_mime`, `descr` FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats` WHERE `parent_id`=-1") or die(mysql_error());

            if ($row = mysql_fetch_assoc($res)) {
                $root = $row;
            }

            mysql_query("DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats` WHERE `parent_id`=-1") or die(mysql_error());
	    }

		function r_startElement($parser, $name, $attrs) 
        {
			GLOBAL $CNCAT,$overwrite,$c_r;
			if ($name=="RUBRIC") 
            {
				foreach	($attrs as $k=>$v) $attrs[$k]= $v;

                $sortorder = isset($attrs["SORTORDER"]) ? ", sort_order='".intval($attrs["SORTORDER"])."'" : "";
                $disableadd = isset($attrs["DISABLEADD"]) ? ", disable_add='".intval($attrs["DISABLEADD"])."'" : "";

		/* Skip exisiting */
				if ($overwrite==0) 
                {
					$r=mysql_query("SELECT count(*) FROM ".$CNCAT["config"]["db"]["prefix"]."cats WHERE id='".intval($attrs["INDEX"])."';");
					if (mysql_result($r,0,0)==0) 
                    {
						$c_r++;
						mysql_query("INSERT INTO ".$CNCAT["config"]["db"]["prefix"]."cats SET id='".intval($attrs["INDEX"])."', parent_id='".intval($attrs["PARENT"])."', title='".mysql_escape_string(xml_decode($attrs["NAME"]))."', title_full='', path='".mysql_escape_string($attrs["URL"])."'".$sortorder.$disableadd.", path_full='', child_id_list='', image='', descr='';");
					}
				}
				/* Overwrite exisiting */
				else 
                {
					mysql_query("DELETE FROM ".$CNCAT["config"]["db"]["prefix"]."cats WHERE id='".intval($attrs["INDEX"])."';");
					mysql_query("INSERT INTO ".$CNCAT["config"]["db"]["prefix"]."cats SET id='".intval($attrs["INDEX"])."', parent_id='".intval($attrs["PARENT"])."', title='".mysql_escape_string(xml_decode($attrs["NAME"]))."', title_full='', path='".mysql_escape_string($attrs["URL"])."'".$sortorder.$disableadd.", path_full='', child_id_list='', image='', descr='';");
					$c_r++;
				}

                $_SESSION["current_byte_index"] = xml_get_current_byte_index($parser);
			}
    	}

		function r_endElement($parser, $name) {}
		function r_characterData($parser, $data) {}

        $charset = "";
        $xmldata = "";
        $filename = $_FILES['filer']['tmp_name'];

        if (empty($filename) && !empty($_POST["sfiler"])) {
            $filename = $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_export"] . 'cncat_rubrics.xml';
        }

		$xml_parser = xml_parser_create();
		xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);

		if ((double)phpversion() >= 5) {
            xml_parser_set_option($xml_parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        }

		xml_set_element_handler($xml_parser, "r_startElement", "r_endElement");
		xml_set_character_data_handler($xml_parser, "r_characterData");

        if (file_exists($filename)) {
            if ($f = fopen($filename, 'rb')) {
                $str = fgets($f);
                preg_match("/<?xml.+?encoding=\s*\"(.*?)\"/", $str, $m);
                $charset = $m[1];

                if (empty($charset)) {
                    $charset = 'UTF-8';
                }

                $convert = (double)phpversion() < 5 && strtoupper($charset) != 'UTF-8';
                $iconv = function_exists('iconv');
                $mb = function_exists('mb_convert_string');

                rewind($f);

                while (!feof($f)) {
                    if ($convert) {
                        if ($iconv) {
                            $xmldata = iconv($charset, 'UTF-8', fread($f, 4096));
                        } elseif ($mb) {
                            $xmldata = mb_convert_encoding(fread($f, 4096), 'UTF-8', $charset);
                        } else {
                            $xmldata = fread($f, 4096);
                        }
                    } else {
                        $xmldata = fread($f, 4096);
                    }

            		if (!xml_parse($xml_parser, $xmldata))  {
            	        die(sprintf("XML error: %s at line %d",
            				xml_error_string(xml_get_error_code($xml_parser)),
            				xml_get_current_line_number($xml_parser)));
            		}
                }
            }
        }

		xml_parser_free($xml_parser);

            $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "cats`
                (`id`, `title`, `title_full`, `parent_id`, `path_full`, `child_id_list`, `image`, `image_mime`, `descr`)
                VALUES (
                    0,
                    '" . $root["title"] . "',
                    '',
                    '-1',
                    '',
                    '',
                    '" . $root["image"] . "',
                    '" . $root["image_mime"] . "',
                    '" . $root["descr"] . "'
                )";
            mysql_query($query) or die(mysql_error());
            $root_id = (int)mysql_insert_id();
        
        mysql_query("UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "cats` SET `parent_id`=" . $root_id . " WHERE `parent_id`=0") or die(mysql_error());
    }

	/* Links import */
	CheckUploadError($_FILES['filel']['name'],$_FILES['filel']['error']);
	if (true)
    {
	    print "Importing links<br>\n";flush();
	    if ($_POST["del"]=="on") 
        {
		    mysql_query("TRUNCATE TABLE `".$CNCAT["config"]["db"]["prefix"]."items`") or die(mysql_error());
		    mysql_query("TRUNCATE TABLE `".$CNCAT["config"]["db"]["prefix"]."itemcat`") or die(mysql_error());
	    }
		function l_startElement($parser, $name, $attrs) 
        {
			GLOBAL $F,$d, $CNCAT, $overwrite;
			$d="";
			if ($name=="LINK") 
            {
                $F=Array();
            }
            else
            if ($name=="LINCAT")
            {
                $SQL2 = "INSERT INTO ".$CNCAT["config"]["db"]["prefix"]."itemcat SET item_id='".(int)$attrs["LID"]."', cat_id='".(int)$attrs["CID"]."', priority='".(int)$attrs["PRIORITY"]."';";
                
                /* Skip exisiting */
				if ($overwrite==0) 
                {
					$r=mysql_query("SELECT count(*) FROM ".$CNCAT["config"]["db"]["prefix"]."itemcat WHERE item_id='".intval($attrs["LID"])."' and cat_id='".intval($attrs["CID"])."';") or die(mysql_error());
					if (mysql_result($r,0,0)==0) 
                    {
						mysql_query($SQL2) or die(mysql_error());
						$c_l++;
					}
				}
				/* Overwrite exisiting */
				else 
                {
					mysql_query("DELETE FROM ".$CNCAT["config"]["db"]["prefix"]."itemcat WHERE item_id='".intval($attrs["LID"])."' and cat_id='".intval($attrs["CID"])."' and priority>0;") or die(mysql_error());
					mysql_query($SQL2) or die(mysql_error());
				};
                
            }
   		}

		function l_endElement($parser, $name) 
        {
			GLOBAL $F,$d,$CNCAT,$overwrite,$c_l;
			if ($name=="LINK") 
            {
			    $nf = array(
                    "LID" => "item_id",
                    "TITLE" => "item_title",
                    "DESCRIPTION" => "item_descr",
                    "URL" => "link_url",
                    //"CAT1" => "",
                    "GIN" => "link_jumps_from",
                    "GOUT" => "link_jumps_to",
                    "MODER_VOTE" => "item_rating_moder",
                    "EMAIL" => "item_author_email",
                    "TYPE" => "item_status",
                    "BROKEN" => "link_broken_warning",
                    "INSERT_DATE" => "item_insert_date",
                    "RESFIELD1" => "ext_text1",
                    "RESFIELD2" => "ext_text2",
                    "RESFIELD3" => "ext_text3",
                    "FULLDESC" => "item_descr_full",
                    "KEYWORDS" => "item_meta_keywords",
                    //"MAIL_SENDED" => "",
                    //"CHECKFLAG" => "",
                    "CHECKWORKRES" => "link_chk_work_res",
                    "CHECKWORKDATE" => "link_chk_work_date",
                    "CHECKBACKRES" => "link_chk_back_res",
                    "CHECKBACKDATE" => "link_chk_back_date",
                    //"CHECKCOMMENT" => "",
                    "PR" => "link_rating_pr",
                    "PRDATE" => "link_pr_date",
                    "CY" => "link_rating_cy",
                    "CYDATE" => "link_cy_date"
                );
				$SQL="";
				while (list ($key, $val) = each ($F)) {
// LID, TITLE, DESCRIPTION, URL, CAT1, GOUT, MODER_VOTE, EMAIL, TYPE, BROKEN, INSERT_DATE, RESFIELD1, RESFIELD2, RESFIELD3, FULLDESC, KEYWORDS, MAIL_SENDED, CHECKFLAG, CHECKWORKRES, CHECKWORKDATE, CHECKBACKRES, CHECKBACKDATE, CHECKCOMMENT, PR, PRDATE, CY, CYDATE
                    if (isset($nf[$key])) {
                        $SQL.=$nf[$key]."='".mysql_escape_string(xml_decode($val))."', ";

                        if ($key == "MODER_VOTE" && $val == 10) {
                            $SQL.="item_favour=2, ";
                        }
                    }
                }
                $SQL.="item_submit_date='".mysql_escape_string(xml_decode($F["INSERT_DATE"]))."', item_image='', link_favicon=''";
				$SQL="INSERT INTO ".$CNCAT["config"]["db"]["prefix"]."items SET ".$SQL.";";
                $SQL2 = "INSERT INTO ".$CNCAT["config"]["db"]["prefix"]."itemcat SET item_id='".$F["LID"]."', cat_id='".$F["CAT1"]."', priority='0';";
                				
                /* Skip exisiting */
				if ($overwrite==0) 
                {
					$r=mysql_query("SELECT count(*) FROM ".$CNCAT["config"]["db"]["prefix"]."items WHERE item_id='".intval($F["LID"])."';") or die(mysql_error());
					if (mysql_result($r,0,0)==0) 
                    {
						mysql_query($SQL) or die(mysql_error());
						mysql_query($SQL2) or die(mysql_error());
						$c_l++;
					}
				}
				/* Overwrite exisiting */
				else 
                {
					mysql_query("DELETE FROM ".$CNCAT["config"]["db"]["prefix"]."items WHERE item_id='".intval($F["LID"])."';") or die(mysql_error());
					mysql_query("DELETE FROM ".$CNCAT["config"]["db"]["prefix"]."itemcat WHERE item_id='".intval($F["LID"])."';") or die(mysql_error());
					mysql_query($SQL) or die(mysql_error());
					mysql_query($SQL2) or die(mysql_error());
					$c_l++;
				};
			}            
			else $F[$name]=$d;
    	}

        
		function l_characterData($parser, $data) 
        {
			GLOBAL $d;
			$d.=$data;
		}

        $charset = "";
        $xmldata = "";
        $filename = $_FILES['filel']['tmp_name'];

        if (empty($filename) && !empty($_POST["sfilel"])) {
            $filename = $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_export"] . 'cncat_links.xml';
        }

		$xml_parser = xml_parser_create();
		xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);

		if ((double)phpversion() >= 5) {
            xml_parser_set_option($xml_parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        }

		xml_set_element_handler($xml_parser, "l_startElement", "l_endElement");
		xml_set_character_data_handler($xml_parser, "l_characterData");

        if (file_exists($filename)) {
            if ($f = fopen($filename, 'rb')) {
                $str = fgets($f);
                preg_match("/<?xml.+?encoding=\s*\"(.*?)\"/", $str, $m);
                $charset = $m[1];

                if (empty($charset)) {
                    $charset = 'UTF-8';
                }

                $convert = (double)phpversion() < 5 && strtoupper($charset) != 'UTF-8';
                $iconv = function_exists('iconv');
                $mb = function_exists('mb_convert_string');

                rewind($f);

                while (!feof($f)) {
                    if ($convert) {
                        if ($iconv) {
                            $xmldata = iconv($charset, 'UTF-8', fread($f, 4096));
                        } elseif ($mb) {
                            $xmldata = mb_convert_encoding(fread($f, 4096), 'UTF-8', $charset);
                        } else {
                            $xmldata = fread($f, 4096);
                        }
                    } else {
                        $xmldata = fread($f, 4096);
                    }

            		if (!xml_parse($xml_parser, $xmldata))  {
            	        die(sprintf("XML error: %s at line %d",
            				xml_error_string(xml_get_error_code($xml_parser)),
            				xml_get_current_line_number($xml_parser)));
            		}
                }
            }
        }

		xml_parser_free($xml_parser);
	}

	print "Sync<br>\n";
    cn_syncAll();

	print ("<script type=\"text/javascript\">location.href='index.php?act=import_xml&op=ok&l=".$c_l."&r=".$c_r."&b=".$blWarning."'</script>\n");

    include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
	exit;
}

//if (!defined ("INSTALL"))
{
    include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
    <h1><?php print $CNCAT["lang"]["import_cncat_2x_3x"]?></h1>
    <a href="index.php?act=import"><?php print $CNCAT["lang"]["import"]?></a>
    <div class="deline"></div>
<?php
    $files = array();

    if (file_exists($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_export"] . 'cncat_links.xml')) {
        $files[] = 'cncat_links.xml';
    }

    if (file_exists($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_export"] . 'cncat_rubrics.xml')) {
        $files[] = 'cncat_rubrics.xml';
    }
?>
    <table class="form">
        <form action="index.php?act=import_xml" method="post" enctype="multipart/form-data">
    <tr>
        <td class="name"><label for="chkbx1"><?php print $CNCAT["lang"]["clear_before_import"]?></label></td>
        <td class="field"><input id="chkbx1" type="checkbox" name="del"></td>
    </tr>
    <tr><td colspan=2 class="deline"></td></tr>

    <tr>
        <td class="name"><label for="radio1"><?php print $CNCAT["lang"]["import_overwrite_exists"]?></label></td>
        <td class="field"><input id="radio1" name="idoverwrite" type="radio" value="1"></td>
    </tr>
    <tr>
        <td class="name"><label for="radio2"><?php print $CNCAT["lang"]["import_skip_exists"]?></label></td>
        <td class="field"><input id="radio2" checked="checked" name="idoverwrite" class="checkbox" type="radio" value="0"></td>
    </tr>
    <tr><td colspan=2 class="deline"></td></tr>

    <tr>
        <td class="name"><?php print $CNCAT["lang"]["file_with_cats"]?></td>
        <td class="field"><input class="text" type="file" name="filer"></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["file_with_links"]?></td>
        <td class="field"><input class="text" type="file" name="filel"></td>
    </tr>
    <tr><td colspan=2 class="deline"></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["import_server_list"]?>:</td><td class="field">
<?php
    if ($files) {
        foreach ($files as $file) {
?>
    <div>
        <label><input type="checkbox" name="sfile<?php print preg_match('/^cncat_links/i', $file) ? 'l' : 'r'?>" />
        <strong><?php print htmlspecialchars($file)?></strong></label>
    </div>
<?php
        }
    } else {
        print "<p><span class=\"not_found\">" . $CNCAT["lang"]["no_files"] . "</span></p>";
    }
?>
    </td></tr>
    <tr><td colspan=2 class="deline"></td></tr>

    <tr><td colspan=2 class="submit"><input type="submit" class="submit" value="<?php print $CNCAT["lang"]["do_import"]?>"></td></tr>
    </form></table>
    <?php

    include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
}
?>
