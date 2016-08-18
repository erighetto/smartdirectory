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
if (!isAdmin()) accessDenied();

if (!function_exists("htmlspecialchars_decode")) {
    function htmlspecialchars_decode($text, $quote = ENT_NOQUOTES) {
        return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote)));
    }
}

$_mode = $_GET["mode"];
$_errors = array();

if ($_mode == "import") {
	$start_time = cncatGetMicrotime();
	
	// check errors
    $_db = cn_substr(mysql_escape_string($_GET["db"]), 0, 32);
    $_prefix = cn_substr(mysql_escape_string($_GET["prefix"]), 0, 32);
	
    @$CNCAT_ENGINE->db->query("USE " . $_db);
    
    if (mysql_errno() != 0) {
        $_errors[] = $CNCAT["lang"]["specified_db_not_exists"];
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

        // check cncat version
    	$_cat_version = false;
    	
    	$CNCAT_ENGINE->db->query("USE " . $_db) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    	
    	$tables = array();
    	$result = $CNCAT_ENGINE->db->query("SHOW TABLES FROM " . $_db) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    	
    	while ($row = mysql_fetch_row($result)) {
    		$tables[] = $row[0];
    	}
    	
    	// 1.x (cat, cat_linear, main)
    	if (compareTables(array($_prefix . 'cat', $_prefix . 'cat_linear', $_prefix . 'main'), $tables)) {
    		$_cat_version = '1';
    	}
    	
    	// 2.x (cat, cat_linear, main, mail, tamplates)
    	// new tables mail, templates
    	if (compareTables(array($_prefix . 'cat', $_prefix . 'cat_inear', $_prefix . 'main', $_prefix . 'mail', $_prefix . 'templates'), $tables)) {
    		$_cat_version = '2';
    	
    	    $result = $CNCAT_ENGINE->db->query("DESCRIBE " . $_prefix . "cat") or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    	    
    	    while ($row = mysql_fetch_assoc($result)) {
    	        if ($row["Field"] == "url") {
    	            $_cat_version = '2.1';
    	            break;
    	        }
    	    }
    	}
    	
    	// 3.x (cat, cat_linear, main, mail, tamplates, lincat, backlinks, config)
    	// new tables lincat, backlinks, config
    	if (compareTables(array($_prefix . 'cat', $_prefix . 'cat_linear', $_prefix . 'main', $_prefix . 'mail', $_prefix . 'templates', $_prefix . 'lincat', $_prefix . 'backlinks', $_prefix . 'config'), $tables)) {
    		$_cat_version = '3';
    	
    	    $result = $CNCAT_ENGINE->db->query("DESCRIBE " . $_prefix . "main") or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    	    
    	    while ($row = mysql_fetch_assoc($result)) {
    	        if ($row["Field"] == "backlinkurl") {
    	        	$_cat_version = '3.1.2';
    	            break;
    	        }
    	    }
    	}

    	if (!$_cat_version) {
    	   $_errors[] = $CNCAT["lang"]["cncat_tables_not_found"];
    	}
    }

    if (empty($_errors)) {
    	if ($_table == "cats") {
    		$query = "SELECT COUNT(*) FROM `" . $_prefix . "cat`";
    	} else
    	if ($_table == "items") {
    		$query = "SELECT COUNT(*) FROM `" . $_prefix . "main`";
    	} else
    	if ($_table == "itemcat") {
    		if ((int)$_cat_version >= 3) {
    		   $query = "SELECT COUNT(*) FROM `" . $_prefix . "lincat`";
    		} else {
    			$query = "SELECT COUNT(*) FROM `" . $_prefix . "main`";
    		}
    	}
    	
    	$result = $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    	list($count) = mysql_fetch_row($result);
    	
        $per = ceil((100 / (int)$count) * $_offset);
    
        if ($per > 100) {
            $per = 100;
        }
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
            </style>
        </head>
    <body>
<?php
        // Force page update
        updatePage($max_exec_time);
?>
        <h1><?php print $CNCAT["lang"]["import_cncat_1x_2x_3x"]?></h1>
        <div style="padding: 1px; margin: 5px 0; border: 1px solid silver;">
            <div style="width: <?php print $per?>%; height: 20px; background: #eee;"></div>
        </div>
        <div style="text-align: center; margin-top: -25px; height: 30px;"><strong id="progress_bar"><?php print $per?>%</strong></div>
<?php
    	// Fields for cats
    	if ($_table == "cats") {
    		if ($count == $_offset) {
    			$_SESSION["import_db_table"] = "items";
    			$_SESSION["import_db_offset"] = 0;
    	
    			updatePage();
    			exit;
    		}
    	
    		print cn_str_replace(array("%OFFSET%", "%COUNT%"), array($_offset, $count), $CNCAT["lang"]["importing_cats"]); flush();
    	
    		if ((int)$_cat_version >= 3) {
    	        $new_fields = explode(",", "id,title,path,parent_id,item_count,sort_order,disable_add");
    	        $old_fields = explode(",", "cid,name,url,parent,count,sortorder,disableadd");
    		} else
    		if ((int)$_cat_version >= 1) {
    	        $new_fields = explode(",", "id,title,parent_id,item_count");
    	        $old_fields = explode(",", "cid,name,parent,count");
    	
    	        if ($_cat_version == '2.1') {
    	        	$new_fields[] = "path";
    	            $old_fields[] = "url";
    	        }
    		}
    
    		// TEXT fields 
            $new_fields[] = "child_id_list";
    		$new_fields[] = "path_full";
            $new_fields[] = "title_full";
            $new_fields[] = "meta_keywords";
            $new_fields[] = "meta_descr";
            $new_fields[] = "image";
            $new_fields[] = "descr";

    	    if ($_offset == 0) {
                $query = "SELECT MAX(`cid`) + 1 FROM `" . $_prefix . "cat`";
                $result = $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
                list($root_id) = mysql_fetch_row($result);
            }
            
    		$fields = "`" . implode("`,`", $old_fields) . "`";
    		$query = "SELECT " . $fields . " FROM `" . $_prefix . "cat` LIMIT " . $_offset . ", 10000";
    		$result = $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    		
    		$CNCAT_ENGINE->db->query("USE " . $CNCAT["config"]["db"]["name"]) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    		$fields = '`' . implode('`,`', $new_fields) . '`';
    	
    		if ($_offset == 0) {
                clearTable("cats");
    			$root = array("id" => $root_id, "title" => $CNCAT["lang"]["default_root_cat_title"], "image" => "", "image_mime" => "", "descr" => "");
    		    $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "cats`
    	            (`id`, `title`, `title_full`, `parent_id`, `path_full`, `child_id_list`, `image`, `image_mime`, `descr`)
    	            VALUES (
    	                " . $root["id"] . ",
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
    	        $_SESSION["import_db_root_cat_id"] = $root_id;
    		}

    		while ($item = mysql_fetch_assoc($result)) {
    		    $values = convertFields($item);
    			if ($values["parent_id"] == 0) $values["parent_id"] = $_SESSION["import_db_root_cat_id"];
    		    $query = "INSERT INTO " . $CNCAT["config"]["db"]["prefix"] . "cats (" . $fields . ") VALUES(" . implode(",", $values) . ")";

    		    //echo $query;
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
    	    
    	// Importing items
    	if ($_table == "items") {
    		if ($count == $_offset) {
    			$_SESSION["import_db_table"] = "itemcat";
    			$_SESSION["import_db_offset"] = 0;
    	
    			updatePage();
    			exit;
    		}
  
            print str_replace(array("%OFFSET%", "%COUNT%"), array($_offset, $count), $CNCAT["lang"]["importing_items"]); flush();

    		if ((int)$_cat_version >= 3) {
    		    $new_fields = explode(",", "item_id,item_title,item_descr,link_url,link_jumps_from,link_jumps_to,item_rating_moder,item_author_email,item_status,link_broken_warning,item_insert_date,item_descr_full,item_meta_keywords,link_chk_work_res,link_chk_work_date,link_chk_back_res,link_chk_back_date,link_rating_pr,link_pr_date,link_rating_cy,link_cy_date");
    		    $old_fields = explode(",", "lid,title,description,url,gin,gout,moder_vote,email,type,broken,insert_date,fulldesc,keywords,checkworkres,checkworkdate,checkbackres,checkbackdate,pr,prdate,cy,cydate");
    	        
    		    if ($_cat_version = "3.1.2") {
    		    	$new_fields[] = "link_back_link_url";
    		    	$old_fields[] = "backlinkurl";
    		    }
    		} else
    		if ((int)$_cat_version >= 2) {
    			$new_fields = explode(",", "item_id,item_title,item_descr,link_url,link_jumps_from,link_jumps_to,item_rating_moder,item_author_email,item_status,link_broken_warning,item_insert_date");
    	        $old_fields = explode(",", "lid,title,description,url,gin,gout,moder_vote,email,type,broken,insert_date");
    		} else
    		if ((int)$_cat_version >= 1) {
    	        $new_fields = explode(",", "item_id,item_title,item_descr,link_url,link_jumps_from,link_jumps_to,item_rating_moder,item_author_email,item_status,link_broken_warning");
    	        $old_fields = explode(",", "lid,title,description,url,gin,gout,moder_vote,email,type,broken");
    		}

    		if (!array_search("link_back_link_url", $new_fields)) {
                $new_fields[] = "link_back_link_url";
    		}

    		if ((int)$_cat_version < 3) {
                $new_fields[] = "item_descr_full";
                $new_fields[] = "item_meta_keywords";
                $new_fields[] = "item_meta_descr";
    		}

            $new_fields[] = "item_image";
            $new_fields[] = "link_favicon";
            $new_fields[] = "link_chk_comment";
            $new_fields[] = "ext_text1";
            $new_fields[] = "ext_text2";
            $new_fields[] = "ext_text3";

    		$fields = "`" . implode("`,`", $old_fields) . "`";
    		$query = "SELECT " . $fields . " FROM `" . $_prefix . "main` LIMIT " . $_offset . ", 50000";
    		$result = $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    	
    		$CNCAT_ENGINE->db->query("USE " . $CNCAT["config"]["db"]["name"]) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    	
    		if ($_offset == 0) {
    			clearTable("items");
    		}
    		
    		while ($item = mysql_fetch_assoc($result)) {
                $fields = '`' . implode('`,`', $new_fields) . '`';
    		    $values = convertFields($item);

    	        if (isset($item["moder_vote"]) && $item["moder_vote"] == 10) {
    	            $fields .= ", `item_favour`";
    	            $values["item_favour"] = 2;
                }

    		    $query = "INSERT INTO " . $CNCAT["config"]["db"]["prefix"] . "items (" . $fields . ") VALUES(" . implode(",", $values) . ")";
    		    //print $query;
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
    	
    	// Importing itemcat
    	if ($_table == "itemcat") {	
    		if ($count == $_offset) {
    		    print $CNCAT["lang"]["performing_sync"]; flush();

    			$CNCAT_ENGINE->db->query("USE " . $CNCAT["config"]["db"]["name"]) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    			cn_syncAll();
    			//updatePage();
    			print "<script type=\"text/javascript\">document.location.href = 'index.php?act=import_db&mode=final';</script>";
    			exit;
    		}

    		print cn_str_replace(array("%OFFSET%", "%COUNT%"), array($_offset, $count), $CNCAT["lang"]["importing_itemcat"]); flush();
        
    	
    		if ((int)$_cat_version >= 3) {
    	       $query = "SELECT lid, cid AS cat1, priority FROM `" . $_prefix . "lincat` LIMIT " . $_offset . ", 50000";
    		} else {
    	       $query = "SELECT lid, cat1 FROM `" . $_prefix . "main` LIMIT " . $_offset . ", 50000";
    		}
    	
    	    $result = $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    	    $CNCAT_ENGINE->db->query("USE " . $CNCAT["config"]["db"]["name"]) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    	
    	    if ($_offset == 0) {
    			clearTable("itemcat");
    		}
    	
    		while ($itemcat = mysql_fetch_assoc($result)) {
    			if (!isset($itemcat["priority"])) {
    				$itemcat["priority"] = 0;
    			}
    	
    		    $query = "INSERT INTO " . $CNCAT["config"]["db"]["prefix"] . "itemcat (`item_id`, `cat_id`, `priority`, `item_status`) VALUES(" . (int)$itemcat["lid"] . ", " . (int)$itemcat["cat1"] . ", " . (int)$itemcat["priority"] . ", 0)";
    		    $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    		    
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
    }

    $CNCAT_ENGINE->db->query("USE " . $CNCAT["config"]["db"]["name"]) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
}

$_offset = $_SESSION["import_db_offset"] = 0;
$_table = $_SESSION["import_db_table"] = "cats";

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["import_cncat_1x_2x_3x"]?></h1>
<a href="index.php?act=import"><?php print $CNCAT["lang"]["import"]?></a>
<div class="deline"></div>
<div class="error_box"><?php print $CNCAT["lang"]["import_warning"]?></div>
<?php
if (!empty($_errors)) {
    print "<ul class=\"errors\"><li>";
    print implode("<li></li>", $_errors);
    print "</li></ul>";
}

if ($_mode == "final") {
    print "<p><strong>" . $CNCAT["lang"]["import_finish"] . "</strong></p>";
}
?>
<div class="ok_box">
    <?php print $CNCAT["lang"]["import_db_hint"]?>
</div>
<div class="deline"></div>
<form action="index.php" method="get">
<input type="hidden" name="act" value="import_db" />
<input type="hidden" name="mode" value="import" />
<table class="form">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["import_options"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["database_name"]?></td><td class="field"><input type="text" class="text" name="db" value="<?php print cn_str($_GET["db"])?>" /></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["tables_prefix"]?></td><td class="field"><input type="text" class="text" name="prefix"  value="<?php print isset($_GET["prefix"]) ? cn_str($_GET["prefix"]) : "cncat_";?>" /></tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr><td class="submit" colspan="2"><input type="button" class="submit" value="<?php print $CNCAT["lang"]["do_import"]?>" onclick="if(confirm('<?php print $CNCAT["lang"]["really_import"]?>')){this.form.submit();}" /></tr>
</table>
</form>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
	
function updatePage($limit = 0) {
	global $_db, $_prefix;
	$url = "index.php?act=import_db&mode=import&offset=1&db=" . urlencode($_db) . "&prefix=" . urlencode($_prefix);

	if ($limit == 0) {
	   print "<script type=\"text/javascript\">document.location.href = '" . $url . "';</script>";        
    } else {
       print "<script type=\"text/javascript\">setTimeout(\"document.location.href = \'" . $url . "\'\", " . ($limit * 1000) . ");</script>";   
    }

    ob_flush();
}

function convertFields($item) {
	global $old_fields, $new_fields;
	$values = array();
	
    foreach ($new_fields as $key => $field) {
        if (isset($item[$old_fields[$key]])) {
            $values[$field] = $item[$old_fields[$key]];
        } else {
            $values[$field] = '';
        }

        if (!is_numeric($values[$field])) {
            $values[$field] = "'" . mysql_escape_string(htmlspecialchars_decode($values[$field])) . "'";
        }
    }

    return $values;
}

function clearTable($table) {
	global $CNCAT, $CNCAT_ENGINE;

	$CNCAT_ENGINE->db->query("TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . $table . "`") or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
}

function compareTables($tf, $ts) {
	foreach ($tf as $t) {
		if (!in_array($t, $ts)) {
			return false;
		}
	}

	return true;
}
?>
