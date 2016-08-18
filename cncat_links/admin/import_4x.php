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
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "auth.php";
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "sync_all.php";

if (!isAdmin()) {
    accessDenied();
}

class CNCatImportXml {
    var $_file_name = '';
    var $_parser = null;
    var $_export = array();
    var $_tables = array();
    var $_fields = array();
    var $_index = array();

    var $_is_field_for = '';
    var $_is_fields_for = '';
    var $_is_records_for = '';

    var $_record = array();
    var $_record_id = 0;
    var $_cdata_open = true;

    var $_errors = array();
    var $_rec_count = 0;

    var $_byte_index = 0;
    var $_expat_fix = false;
    var $_start_time = 0;

    function CNCatImportXml($file_name) {
        $this->_file_name = $file_name;
        $this->_start_time = cncatGetMicrotime();

        $this->_parser = xml_parser_create();
        xml_set_object($this->_parser, $this);

        xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, 1);
        xml_set_element_handler($this->_parser, "xmlTagBegin", "xmlTagEnd");
        xml_set_character_data_handler($this->_parser, "xmlCData");
    }

    function run() {
        global $CNCAT, $CNCAT_ENGINE;

        // get byte index
        if (isset($_GET["byte"]) && isset($_SESSION["import_bytes_read"])) {
            $this->_byte_index = (int)$_SESSION["import_bytes_read"];
        }

        if ($this->_byte_index == 0) {
            // save filesize in session
            $_SESSION["import_filesize"] = filesize($this->_file_name);
        } else {
            if (isset($_SESSION["import_finish"]) && $_SESSION["import_finish"] == true) {
                return;
            }

            if (!empty($_SESSION["import_is_records_for"])) {
                $this->_is_records_for = $_SESSION["import_is_records_for"];
            } else {
                return;
            }

            // calculate read offset by header length
            if ($this->_byte_index > 0) {
                $this->_read_offset = ($_SESSION["import_bytes_read"] - ($_SESSION["import_head_index"] + cn_strlen("<records for=\"" . $_SESSION["import_is_records_for"] . "\">")));
            }
        }

        // EXPAT parser fix (different behaviour with libxml2)
        $this->_expat_fix = ((double)phpversion() < 5);

        $CNCAT_ENGINE->db->query("SET NAMES 'utf8'");

        if ($f = @gzopen($this->_file_name, "rb")) {
            flush();
            ob_flush();

            // read header
            if ($this->_byte_index > 0) {
                $data = @gzread($f, $_SESSION["import_head_index"]);
                $data .= "<records for=\"" . $_SESSION["import_is_records_for"] . "\">";

                if (!xml_parse($this->_parser, $data)) {
                    $this->_errors[] = sprintf("XML Error: %s at line %d.",
                        xml_error_string(xml_get_error_code($this->_parser)),
                        xml_get_current_line_number($this->_parser)
                    );

                    @gzclose($f);
                    return;
                }
            }

            // read entries
            gzseek($f, $this->_byte_index);

            while ($data = @gzread($f, 1024)) {
                if (!xml_parse($this->_parser, $data, gzeof($f))) {
                    $this->_errors[] = sprintf("XML Error: %s at line %d.",
                        xml_error_string(xml_get_error_code($this->_parser)),
                        xml_get_current_line_number($this->_parser)
                    );
                    break;
                }
            }

            @gzclose($f);
        } else {
            $this->_errors[] = $CNCAT["lang"]["file_open_error"];
        }
    }

    function xmlTagBegin($xml_parser, $name, $attr) {
        GLOBAL $CNCAT;

        if ($name == "EXPORT") {
            $this->_export["version"] = $attr["VERSION"];
            $this->_export["product"] = $attr["PRODUCT"];
        } elseif ($name == "FIELDS") {
            $this->_fields[$attr["FOR"]] = array();
            $this->_index[$attr["FOR"]] = array();

            $this->_is_fields_for = $attr["FOR"];

            if ($this->_byte_index == 0) {
                mysql_query("TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . $this->_is_fields_for . "`");
            }
        } elseif ($name == "FIELD") {
            if (!empty($this->_is_fields_for)) {
                $this->_fields[$this->_is_fields_for][$attr["ID"]] = array(
                    "name" => $attr["NAME"],
                    "id" => $attr["ID"],
                    "type" => $attr["TYPE"],
                    "default" => $attr["DEFAULT"],
                    "null" => isset($attr["NULL"])
                );
                $this->_index[$this->_is_fields_for][$attr["NAME"]] = $attr["ID"];
            }
        } elseif ($name == "RECORDS") {
            $this->_is_records_for = $attr["FOR"];
            $_SESSION["import_is_records_for"] = $this->_is_records_for;
        } elseif ($name == "R") {
            $this->_record = array();
        } elseif ($name == "F") {
            $this->_record_open = true;
            $this->_is_field_for = $this->_fields[$this->_is_records_for][$attr["ID"]]["name"];
            //print_r($this->_record);
        }
    }

    function xmlTagEnd($xml_parser, $name) {
        GLOBAL $CNCAT;

        if ($name == "R") {
            $values = array();

            foreach ($this->_record as $f => $v) {
                $f_id = $this->_index[$this->_is_records_for][$f];

                switch ($this->_fields[$this->_is_records_for][$f_id]["type"]) {
                    case "number":
                        $values[$f] = $v;
                    break;
                    case "binary":
                        $values[$f] = "'" . mysql_escape_string(base64_decode($v)) . "'";
                    break;
                    default:
                        $values[$f] = "'" . mysql_escape_string($this->_dataDecode($v)) . "'";
                }
            }

            foreach ($this->_fields[$this->_is_records_for] as $field) {
                if (!isset($values[$field["name"]])) {
                    if ($field["null"]) {
                        $values[$field["name"]] = "NULL";
                    } else {
                        switch ($field["type"]) {
                            case "number":
                                $values[$field["name"]] = !empty($field["default"]) ? $field["default"] : 0;
                            break;
                            case "binary":
                                $values[$field["name"]] = "'" . mysql_escape_string(base64_decode($field["default"])) . "'";
                            break;
                            default:
                                $values[$field["name"]] = "'" . mysql_escape_string($this->_dataDecode($field["default"])) . "'";
                        }
                    }
                }
            }
            if ($this->_is_records_for == "banners")
                fix_banners_fields($values);
            $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . $this->_is_records_for  . "` (" . join(",", array_keys($values)) . ") VALUES (" . join(",", $values) . ")";
             mysql_query($query);
        } elseif ($name == "F") {
            $this->_record_open = false;
        } elseif ($name == "FIELDS") {
            $_SESSION["import_head_index"] = xml_get_current_byte_index($this->_parser) + ($this->_expat_fix ? 9 : 0);
        } elseif ($name == "EXPORT") {
            $_SESSION["import_finish"] = true;

            print "<script type=\"text/javascript\">document.location.href = 'index.php?act=import_4x&mode=final';</script>";
            exit;
        }
    }

    function xmlCData($xml_parser, $data) {
        if ($this->_record_open) {
            $f_id = $this->_index[$this->_is_records_for][$this->_is_field_for];
            $this->_record[$this->_is_field_for] .= $data;
        }
    }

    function _dataDecode($str) {
        return cn_str_replace(
            array("]]&gt;", "&#13;", "&#10;"),
            array("]]>", "\r", "\n"),
            $str
        );
    }

    function insertRecord($table, $record) {
    }
}
function fix_banners_fields(&$row)
{
    if (isset($row['page'])){
        $row['bpage'] =  $row['page'];
        unset( $row['page']);
    }
    if (isset($row['comment'])){
        $row['bcomment'] = $row['comment'];
        unset($row['comment']);
    }
    if (isset($row['condition'])){
        $row['bcondition'] = $row['condition'];
        unset($row['condition']);
    }
    if (isset($row['code'])){
        $row['bcode'] = $row['code'];
        unset($row['code']);
    }
}
session_start();

$_mode = $_GET["mode"];
$_type = $_GET["type"];

if ($_mode == "import") {
    $_errors = array();

    if ($_type == "db") {
	   $start_time = cncatGetMicrotime();

        $_db = cn_substr(mysql_escape_string($_GET["db"]), 0, 32);
        $_prefix = cn_substr(mysql_escape_string($_GET["prefix"]), 0, 32);

        @$CNCAT_ENGINE->db->query("USE " . $_db);

    	$tables = array();
    	$result = $CNCAT_ENGINE->db->query("SHOW TABLES FROM " . $_db) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));

        if (mysql_errno() != 0) {
            $_errors[] = $CNCAT["lang"]["specified_db_not_exists"];
        } else {
            while ($row = mysql_fetch_row($result)) {
        		$tables[] = $row[0];
        	}
    
            if (!in_array($_prefix . "cats", $tables) || !in_array($_prefix . "items", $tables) || !in_array($_prefix . "itemcat", $tables)) {
        	   $_errors[] = $CNCAT["lang"]["cncat_tables_not_found"];
            }
        }

        if (empty($_errors)) {
        	$_offset = isset($_SESSION["import_db_offset"]) ? (int)$_SESSION["import_db_offset"] : 0;
        	$_table = isset($_SESSION["import_db_table"]) ? $_SESSION["import_db_table"] : "";

        	// Calculate maximum execution time
        	$time_limit = ini_get("max_execution_time");
        	
        	if ($time_limit < 15 && $time_limit != 0) {
        	    $time_limit = 15;
        	}
        	
        	@set_time_limit($time_limit);
        	$max_exec_time = ini_get("max_execution_time");
        	
        	if ($max_exec_time == 0 || $max_exec_time > 30) {
        	    $max_exec_time = 30;
        	}
    
        	$max_exec_time -= 1;
    	}

        if (empty($_errors)) {
        	if ($_table == "cats") {
        		$query = "SELECT COUNT(*) AS `count` FROM `" . $_prefix . "cats`";
        	} elseif ($_table == "items") {
        		$query = "SELECT COUNT(*) AS `count` FROM `" . $_prefix . "items`";
        	} elseif ($_table == "itemcat") {
        		$query = "SELECT COUNT(*) AS `count` FROM `" . $_prefix . "itemcat`";
        	}
        	
        	$result = $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
        	$row = mysql_fetch_assoc($result);
        	$count = $row["count"];	
?>
    <html>
        <head>
            <title><?php print $CNCAT["lang"]["import_cncat_1x_2x_3x"]?></title>
            <style type="text/css">
                body {
                    background: white;
                    padding: 5px;
                    margin: 5px;
                }
                body, p {
                    font-family:tahoma; font-size:11px; color:#38464b;
                }
                p {
                    margin: 8px 10px;
                }
                a {
                    color:#3176b1;
                }
                h1 {
                    color:#3176b1;font-size:12pt;
                }
                a img {
                    border: 0;
                }
                .title {
                    background:#e8f0f4;border:1px solid #c2cdd1;padding:4px;
                    color:black;font-weight:bold;
                    white-space: nowrap;
                }
                .deline {
                    border-bottom: 1px dashed #c2cdd1;
                    margin: 10px 0;
                }
                .error_box {
                    border: solid 1px red;background-color:#FFEEEE;padding:10px;margin:5px 0;
                    width: 530px;
                }
            </style>
        </head>
    <body>
<?php
            // Force page update
            updatePage($max_exec_time);
            print $CNCAT_ENGINE->render->renderProgressBar(0, $count, $_offset);
            //flush();

            if ($_table == "cats") {
        		if ($count == $_offset) {
        			$_SESSION["import_db_table"] = "items";
        			$_SESSION["import_db_offset"] = 0;
        	
        			updatePage();
        			exit;
        		}

        		print cn_str_replace(array("%OFFSET%", "%COUNT%"), array($_offset, $count), $CNCAT["lang"]["importing_cats"]); flush();
            } elseif ($_table == "items") {
        		if ($count == $_offset) {
        			$_SESSION["import_db_table"] = "itemcat";
        			$_SESSION["import_db_offset"] = 0;
        	
        			updatePage();
        			exit;
        		}

        		print cn_str_replace(array("%OFFSET%", "%COUNT%"), array($_offset, $count), $CNCAT["lang"]["importing_items"]); flush();
            } elseif ($_table == "itemcat") {
        		if ($count == $_offset) {
        		    print $CNCAT["lang"]["performing_sync"]; flush();
    
        			$CNCAT_ENGINE->db->query("USE " . $CNCAT["config"]["db"]["name"]) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
        			cn_syncAll();
        			print "<script type=\"text/javascript\">document.location.href = 'index.php?act=import_4x&mode=final';</script>";
        			exit;
        		}

        		print cn_str_replace(array("%OFFSET%", "%COUNT%"), array($_offset, $count), $CNCAT["lang"]["importing_itemcat"]); flush();
            }

            $query = "DESCRIBE `" . $_prefix . $_table . "`";
            $result = $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
            
            while ($row = mysql_fetch_assoc($result)) {
                $fields[$row['Field']] = $row;
            }

    		$query = "SELECT * FROM `" . $_prefix . $_table . "` LIMIT " . $_offset . ", 10000";
            $result = $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));

    		$CNCAT_ENGINE->db->query("USE " . $CNCAT["config"]["db"]["name"]) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
                
            if ($count > 0 && $_offset == 0) {
                clearTable($_table);
            }

    		while ($item = mysql_fetch_assoc($result)) {
                foreach ($item as $k => $v) {
                    if (empty($v) && !empty($fields[$k]["Null"]) && strtoupper($fields[$k]["Null"]) != 'NO') {
                        $item[$k] = "NULL";
                        continue;
                    }

                    if (!is_numeric($v)) {
                        $item[$k] = "'" . mysql_escape_string($v) . "'";
                    }
                }
            if ($_table == "banners")
                fix_banners_fields($item);
    		    $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . $_table . "` (`" . implode("`,`", array_keys($item)) . "`) VALUES(" . implode(",", $item) . ")";

    		    $CNCAT_ENGINE->db->query($query) or die(mysql_error());
    		    $_SESSION["import_db_offset"]++;

    		    if (
    		    	((cncatGetMicrotime() - $start_time) > ($max_exec_time - 3)) ||
    		    	($_SESSION["import_db_offset"] == $count)
    		    ) {
    				updatePage();
    				exit;
    		    }
            }
    	
            updatePage();
    		exit;
    	}
    } else {
        if (!isset($_GET["byte"]) || (int)$_GET["byte"] == 0) {
            $_SESSION["import_head_index"] = 0;
            $_SESSION["import_bytes_read"] = 0;
            $_SESSION["import_finish"] = false;
            $_SESSION["import_rec_count"] = 0;
            $_SESSION["import_rec_now"] = 0;
            $_SESSION["import_time"] = 0;
    
            if ($_type == "server") {
                $_filename = $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_export"] . basename($_POST["filename"]);
            } elseif ($_type == "client") {
                if ($_FILES["file"]["error"] == UPLOAD_ERR_OK) {
                        $_filename = $_FILES["file"]["tmp_name"];
                } else {
                    $_errors[] = $CNCAT["lang"]["import_upload_error"];
                }
            } else {
                $_errors[] = $CNCAT["lang"]["not_support_import"];
            }
            
            if (!$_errors) {
                if (is_file($_filename) && is_readable($_filename)) {
                    $_SESSION["import_filename"] = $_filename;
                    
                } else {
                    $_errors[] = $CNCAT["lang"]["file_not_found"];
                }
            }
        }
    
        if (!$_errors) {
            
    ?>
    <html>
        <head>
            <title><?php print $CNCAT["lang"]["import_cncat_4x"]?></title>
            <style type="text/css">
                body {
                    background: white;
                    padding: 5px;
                    margin: 5px;
                }
                body, p {
                    font-family:tahoma; font-size:11px; color:#38464b;
                }
                p {
                    margin: 8px 10px;
                }
                a {
                    color:#3176b1;
                }
                h1 {
                    color:#3176b1;font-size:12pt;
                }
                a img {
                    border: 0;
                }
                .title {
                    background:#e8f0f4;border:1px solid #c2cdd1;padding:4px;
                    color:black;font-weight:bold;
                    white-space: nowrap;
                }
                .deline {
                    border-bottom: 1px dashed #c2cdd1;
                    margin: 10px 0;
                }
                .error_box {
                    border: solid 1px red;background-color:#FFEEEE;padding:10px;margin:5px 0;
                    width: 530px;
                }
            </style>
        </head>
    <body>
        <!--<script type="text/javascript">
            function windowRefresh() {
                document.location.href = 'index.php?act=import_4x&mode=import&type=server&byte=1';
            }
    
            window.setTimeout('windowRefresh()', <?php print $max_exec_time * 1000?>);
        </script>-->
        <h1><?php print $CNCAT["lang"]["import_cncat_4x"]?></h1>
    <?php
            flush();
            ob_flush();
            $_import = new CNCatImportXml($_SESSION["import_filename"]);
            $_import->run();
    ?>
    <?php
            exit;
        }
    }

    $CNCAT_ENGINE->db->query("USE " . $CNCAT["config"]["db"]["name"]) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
} elseif ($_mode == "delete") {
    $_filename = $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_export"] . basename($_GET["file"]);

    if (@file_exists($_filename)) {
        if (!@unlink($_filename)) {
            $_errors[] = $CNCAT["lang"]["import_del_failed"];
        } else {
            header("Location: index.php?act=import_4x");
            exit;
        }
    } else {
        $_errors[] = $CNCAT["lang"]["file_not_found"];
    }
}
$_offset = $_SESSION["import_db_offset"] = 0;
$_table = $_SESSION["import_db_table"] = "cats";

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["import_cncat_4x"]?></h1>
<a href="index.php?act=import"><?php print $CNCAT["lang"]["import"]?></a>
<div class="deline"></div>
<?php if (!defined("FORCE_GZIP")) {?>
<div class="error_box">
    <?php print $CNCAT["lang"]["not_support_pack"]?>
</div>
<?php }?>
<div class="error_box"><?php print $CNCAT["lang"]["import_warning"]?></div>
<div class="ok_box"><?php print str_replace("%SIZE%", ini_get('upload_max_filesize'), $CNCAT["lang"]["upload_max_filesize"])?></div>
<?php
if ($_mode == "final") {
    cn_syncAll();
    print '<div class="deline"></div>';
    print "<strong>" . $CNCAT["lang"]["import_finish"] . "</strong>";
}
?>
<div class="deline"></div>
<?php
if ($_mode == "import" || $_mode == "delete") {
    if ($_errors) {
        print "<ul class=\"errors\">";

        foreach ($_errors as $error) {
            print "<li>" . $error ."</li>";
        }

        print "</ul>";
        print "<div class=\"deline\"></div>";
    }
}
?>
<table class="form">
<form action="index.php" method="get">
    <input type="hidden" name="act" value="import_4x" />
    <input type="hidden" name="mode" value="import" />
    <input type="hidden" name="type" value="db" />
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["import_from_db"]?></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["database_name"]?></td>
        <td class="field"><input type="text" name="db" class="text" value="<?php print htmlspecialchars($_GET["db"])?>" /></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["tables_prefix"]?></td>
        <td class="field"><input type="text" name="prefix" class="text" value="<?php print htmlspecialchars($_GET["prefix"])?>" /></td>
    </tr>
    <tr><td class="submit" colspan="2"><input type="button" class="submit" value="<?php print $CNCAT["lang"]["do_import"]?>" onclick="if(confirm('<?php print $CNCAT["lang"]["really_import"]?>')){this.form.submit();}" /></td></tr>
</form>

<form action="index.php?act=import_4x&mode=import&type=server" method="post">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["import_server_list"]?></td></tr>
<?php
    $dirname = $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_export"];
    $files = array();
    $num = 0;

    if ($dh = @opendir($dirname)) {
        while (false !== ($filename = readdir($dh))) {
            if (is_file($dirname . $filename) && is_readable($dirname . $filename)) {
                if (preg_match("/^cncat_([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})_([a-z_]+)\.(xml|xml.gz)$/i", $filename, $match)) {
                    $files[$num] = $match;
                    $files[$num][0] = $filename;
                    $num++;
                }
            }
        }

        rsort($files);
    }

    if ($files) {
?>
    <tr><td colspan="2"><br /><table>
        <tr>
            <td class="title">&nbsp;</td>
            <td class="title"><?php print $CNCAT["lang"]["date"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["time"]?></td>
            <td class="title" width="320"><?php print $CNCAT["lang"]["data"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["type"]?></td>
            <td class="title">&nbsp;</td>
        </tr>
<?php
        $tables = array("cfg" => $CNCAT["lang"]["export_cfg"], "bl" => $CNCAT["lang"]["export_bl"], "bwl" => $CNCAT["lang"]["export_bwl"], "cat" => $CNCAT["lang"]["export_cat"], "img" => $CNCAT["lang"]["images"], "bnr" => $CNCAT["lang"]["banners"]);

        foreach ($files as $num => $match) {
?>
    <tr style="cursor: default; background-color: <?php print $num % 2 ? "#f8f8f8" : "#fefefe"?>" onmouseover="this.style.backgroundColor='#fefede';" onmouseout="this.style.backgroundColor='<?php print $num % 2 ? "#f8f8f8" : "#fefefe"?>';" onclick="r=document.getElementById('filename_<?php print $num?>').checked=true;">
        <td class="item"><input type="radio" name="filename" id="filename_<?php print $num?>" value="<?php print htmlspecialchars($match[0])?>" /></td>
        <td class="item"><?php print $match[1] . "-" . $match[2] . "-" . $match[3]?></td>
        <td class="item"><?php print $match[4] . ":" . $match[5] . ":" . $match[6]?></td>
        <td class="item">
        <a href="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_export"] . $match[0]?>" target="_blank"><?php
            foreach (explode("_", $match[7]) as $t) {
                print $tables[$t] . "<br />";
            }
        ?></a>
        </td>
        <td class="item"><?php print cn_strtoupper($match[8]) == "XML" ? "XML" : "GZIP"?></td>
        <td class="item"><a href="index.php?act=import_4x&mode=delete&file=<?php print $match[0]?>" onclick="return confirm('<?php print cn_str_replace("%NAME%", htmlspecialchars(cn_str_replace("'", "\'", $match[0])), $CNCAT["lang"]["really_delete"])?>')"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/delete.gif" alt="" title="<?php print $CNCAT["lang"]["do_delete"]?>" /></a></td>
    </tr>
<?php
        }
?>
    </table></td></tr>
<?php
    } else {
        print "<tr><td class=\"name\" colspan=\"2\"><span class=\"not_found\">" . $CNCAT["lang"]["no_files"] . "</span></td></tr>";
    }
?>

    <tr><td class="submit" colspan="2"><input type="button" class="submit" value="<?php print $CNCAT["lang"]["do_import"]?>" onclick="if(confirm('<?php print $CNCAT["lang"]["really_import"]?>')){this.form.submit();}" /></td></tr>
</form>

<form action="index.php?act=import_4x&mode=import&type=client" method="post" enctype="multipart/form-data">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["import_from_upload"]?></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["file_with_data"]?></td>
        <td class="field"><input type="file" name="file" class="text" /></td>
    </tr>
    <input type="hidden" name="doPost" value="1" />
    <tr><td class="submit" colspan="2"><input type="button" class="submit" value="<?php print $CNCAT["lang"]["do_import"]?>" onclick="if(confirm('<?php print $CNCAT["lang"]["really_import"]?>')){this.form.submit();}" /></td></tr>
    <tr><td class="deline" colspan="2"></td></tr>
</form>
</table>
<?php
//}
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";

function updatePage($limit = 0) {
	global $_db, $_prefix;
	$url = "index.php?act=import_4x&mode=import&type=db&offset=1&db=" . urlencode($_db) . "&prefix=" . urlencode($_prefix);

	if ($limit == 0) {
	   print "<script type=\"text/javascript\">document.location.href = '" . $url . "';</script>";        
    } else {
       print "<script type=\"text/javascript\">setTimeout(\"document.location.href = \'" . $url . "\'\", " . ($limit * 1000) . ");</script>";   
    }

    ob_flush();
}

function clearTable($table) {
	global $CNCAT, $CNCAT_ENGINE;

	$CNCAT_ENGINE->db->query("TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . $table . "`") or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
}
?>
