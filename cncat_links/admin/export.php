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

$_mode = $_GET["mode"];

define("EXPORT_MODE", 0);
define("EXPORT_ALIASES", 1);
define("EXPORT_BINARY", 2);
define("EXPORT_BINARY_CONVERT", 3);
define("EXPORT_GZIP", 4);
define("EXPORT_GZIP_LEVEL", 5);

define("EXPORT_RETURN", 100);
define("EXPORT_FLUSH", 101);
define("EXPORT_SAVE", 102);

class CNCatExportXml {
    var $_params = array(
        EXPORT_MODE => EXPORT_RETURN,
        EXPORT_ALIASES => 0,
        EXPORT_BINARY => 1,
        EXPORT_BINARY_CONVERT => 1,
        EXPORT_GZIP => 0,
        EXPORT_GZIP_LEVEL => 6
    );
    var $_file_name = "";
    var $_file_handler = null;
    var $_tables = array();
    var $_fields = array();
    var $_data = '';

    function CNCatExportXml($file_name) {
        $this->_file_name = strftime($file_name);
    }

    function run() {
        GLOBAL $CNCAT, $CNCAT_ENGINE;

        if ($this->_params[EXPORT_MODE] == EXPORT_FLUSH) {
            if ($this->_params[EXPORT_GZIP]) {
                header("Content-Type: application/x-gzip");
                header("Content-Disposition: attachment; filename=" . $this->_file_name);
            } else {
                header("Content-Type: text/xml");
                header("Content-Disposition: attachment; filename=" . $this->_file_name);
            }
        } elseif ($this->_params[EXPORT_MODE] == EXPORT_SAVE) {
            if ($this->_params[EXPORT_GZIP]) {
                $this->_fh = gzopen($this->_file_name, "wb");
            } else {
                $this->_fh = fopen($this->_file_name, "wb");
            }
        } elseif ($this->_params[EXPORT_MODE] == EXPORT_RETURN) {
            return $this->_data ;
        }

        foreach ($this->_tables as $table) {
            $res = mysql_query("SHOW FIELDS FROM `" . $CNCAT["config"]["db"]["prefix"] . $table . "`");
            $field_num = 0;

            while ($field = mysql_fetch_assoc($res)) {
                $field_num++;

                $this->_fields[$table][$field["Field"]] = array(
                    "name" => $field["Field"],
                    "id" => $field_num,
                    "type" => $this->_getFieldType($field["Type"]),
                    "default" => $field["Default"],
                    "null" => ($field["Null"] == "YES" || (!empty($field["Null"]) && $field["Null"] != "NO"))
                );
            }
        }

        $_xml = "<?xml version=\"1.0\"?>\n"; //<?
        $_xml .= "<export version=\"1.0\">\n";

        foreach ($this->_tables as $table) {
            $_xml .= "<fields for=\"" . $table . "\">\n";

            foreach ($this->_fields[$table] as $field) {
                $_xml .= "<field name=\"" . $field["name"] . "\" ";
                $_xml .= $this->_params[EXPORT_ALIASES] ? "id=\"" . $field["id"] . "\" " : "";
                $_xml .= "type=\"" . $field["type"] . "\" ";
                $_xml .= "default=\"" . $field["default"] . "\" ";
                $_xml .= $field["null"] ? "null=\"null\" />\n" : "/>\n";
                $xml .= "/>\n";
            }

            $_xml .= "</fields>\n";
        }

        $this->_strOut($_xml);

        // data...
        foreach ($this->_tables as $table) {
            $_xml = "<records for=\"" . $table . "\">\n";
            $this->_strOut($_xml);

            $CNCAT_ENGINE->db->query("SET NAMES 'utf8'");
            $res = $CNCAT_ENGINE->db->query("SELECT * FROM `" . $CNCAT["config"]["db"]["prefix"] . $table . "`");
            $CNCAT_ENGINE->db->query("SET NAMES '" . $CNCAT['config']['db']['charset'] . "'");

            $row_num = 0;

            while ($row = mysql_fetch_assoc($res)) {
                $row_num++;
                $_xml = "<r id=\"" . $row_num . "\">\n";

                foreach ($row as $name => $value) {
                    if (empty($value)) {
                        continue;
                    }

                    $_xml .= "<" . ($this->_params[EXPORT_ALIASES] ? "f id=\"" . $this->_fields[$table][$name]["id"] . "\"" : $this->_fields[$table][$name]["name"])  . ">";

                    if ($this->_fields[$table][$name]["type"] == "string") {
                        $_xml .= "<![CDATA[" . $this->_dataEncode($value) . "]]>";
                    } elseif ($this->_fields[$table][$name]["type"] == "binary") {
                        if ($this->_params[EXPORT_BINARY]) {
                            $_xml .= "<![CDATA[" . $this->_binaryEncode($value) . "]]>";
                        }
                    } elseif ($this->_fields[$table][$name]["type"] == "number") {
                        $_xml .= $value;
                    }

                    $_xml .= "</" . ($this->_params[EXPORT_ALIASES] ? "f" : $this->_fields[$table][$name]["name"])  . ">\n";
                }

                $_xml .= "</r>\n";
                $this->_strOut($_xml);
            }

            $_xml = "</records>\n";
            $this->_strOut($_xml);
        }

        $_xml = "</export>";
        $this->_strOut($_xml);

        if ($this->_params[EXPORT_GZIP] && !empty($this->_data)) {
            $this->_data = gzencode($this->_data);
        }

        if ($this->_params[EXPORT_MODE] == EXPORT_FLUSH) {
            if ($this->_params[EXPORT_GZIP]) {
                print $this->_data;
            }

            exit;
        } elseif ($this->_params[EXPORT_MODE] == EXPORT_SAVE) {
            if ($this->_params[EXPORT_GZIP]) {
                gzclose($this->_fh);
            } else {
                fclose($this->_fh);
            }
        } elseif ($this->_params[EXPORT_MODE] == EXPORT_RETURN) {
            return $this->_data;
        }
    }

    function _getFieldType($field_type) {
        if (cn_strpos($field_type, "int") !== false || cn_strpos($field["Type"], "float") !== false) {
            return "number";
        } elseif (cn_strpos($field_type, "blob") !== false) {
            return "binary";
        } else {
            return "string";
        }
    }

    function _strOut($str) {
        if ($this->_params[EXPORT_MODE] == EXPORT_FLUSH) {
            if ($this->_params[EXPORT_GZIP]) {
                $this->_data .= $str;
            } else {
                print $str;
            }
        } elseif ($this->_params[EXPORT_MODE] == EXPORT_SAVE) {
            if ($this->_params[EXPORT_GZIP]) {
                gzwrite($this->_fh, $str);
            } else {
                fwrite($this->_fh, $str);
            }
        } elseif ($this->_params[EXPORT_MODE] == EXPORT_RETURN) {
            $this->_data .= $str;
        }
    }

    function _binaryEncode($str) {
        if ($this->_params[EXPORT_BINARY_CONVERT]) {
            return base64_encode($str);
        } else {
            return $str;
        }
    }

    function _dataEncode($str) {
        return cn_str_replace(
            array("]]>", "\r", "\n"),
            array("]]&gt;", "&#13;", "&#10;"),
            $str
        );
    }

    function assignTable($table_name) {
        $this->_tables[] = $table_name;
    }

    function setParam($name, $value) {
        $this->_params[$name] = $value;
    }
}

$_is_save = false;

if ($_mode == "export" && isset($_POST["doPost"])) {
    $_tables = (array)$_POST["tables"];
    $_binary = !empty($_POST["binary"]);
    $_pack = !empty($_POST["pack"]) && defined("FORCE_GZIP");
    //$_filename = cn_substr($_POST["filename"], 0, 255);
    $_method = (int)$_POST["method"];

    if ($_method != EXPORT_FLUSH && $_method != EXPORT_SAVE) {
        $_method = EXPORT_FLUSH;
    }

    if ($_method == EXPORT_SAVE && !is_writeable($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_export"])) {
        $_method = EXPORT_FLUSH;
    }

    @setcookie("cncat_export_tables", join(",", $_tables), strtotime("+1 Year"));
    @setcookie("cncat_export_binary", $_binary, strtotime("+1 Year"));
    @setcookie("cncat_export_pack", $_pack, strtotime("+1 Year"));
    @setcookie("cncat_export_filename", $_filename, strtotime("+1 Year"));
    @setcookie("cncat_export_method", $_method, strtotime("+1 Year"));

    $_COOKIE["cncat_export_tables"] = join(",", $_tables);
    $_COOKIE["cncat_export_binary"] = $_binary;
    $_COOKIE["cncat_export_pack"] = $_pack;
    $_COOKIE["cncat_export_method"] = $_method;
    $_COOKIE["cncat_export_filename"] = $_filename;

    if ($_tables) {
        //if ($_method == EXPORT_SAVE) {
            $_filename = basename($_filename);
        //}

        //$_filename .= $_pack ? ".xml.gz" : ".xml";
        $_filename = strftime("cncat_%Y%m%d%H%M%S");

        if (in_array("cat", $_tables)) {
            $_filename .= "_cat";
        }

        if (in_array("bl", $_tables)) {
            $_filename .= "_bl";
        }

        if (in_array("bwl", $_tables)) {
            $_filename .= "_bwl";
        }

        if (in_array("cfg", $_tables)) {
            $_filename .= "_cfg";
        }

        if (in_array("bnr", $_tables)) {
            $_filename .= "_bnr";
        }

        if (in_array("img", $_tables)) {
            $_filename .= "_img";
        }

        $_filename .= $_pack ? ".xml.gz" : ".xml";

        if ($_method == EXPORT_SAVE) {
            $_export = new CNCatExportXml($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_export"] . $_filename);
        } else {
            $_export = new CNCatExportXml($_filename);
        }

        $_export->setParam(EXPORT_GZIP, $_pack);
        $_export->setParam(EXPORT_ALIASES, true);
        $_export->setParam(EXPORT_MODE, $_method);
        $_export->setParam(EXPORT_BINARY, $_binary);
        $_export->setParam(EXPORT_BINARY_CONVERT, true);

        if (in_array("cat", $_tables)) {
            $_export->assignTable("cats");
            $_export->assignTable("items");
            $_export->assignTable("itemcat");
            $_export->assignTable("filters");
            $_export->assignTable("filtvals");
            $_export->assignTable("itemfilt");
            $_export->assignTable("moders");
            $_export->assignTable("modercat");
            $_export->assignTable("fields");
            $_export->assignTable("comments");
        }

        if (in_array("bl", $_tables)) {
            $_export->assignTable("backlinks");
        }

        if (in_array("bwl", $_tables)) {
            $_export->assignTable("bwlist");
        }

        if (in_array("cfg", $_tables)) {
            $_export->assignTable("config");
        }

        if (in_array("bnr", $_tables)) {
            $_export->assignTable("banners");
        }

        if (in_array("img", $_tables)) {
            $_export->assignTable("images");
            $_export->assignTable("img_cats");
        }

        @set_time_limit(3600);
        $_export->run();
        $_is_save = true;
    }
}
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["export"]?></h1>
<?php if (!is_writeable($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_export"])) {?>
<div class="error_box">
    <?php print $CNCAT["lang"]["save_export_failed"]?><br />
</div>
<?php }?>
<?php if (!defined("FORCE_GZIP")) {?>
<div class="error_box">
    <?php print $CNCAT["lang"]["not_support_pack"]?>
</div>
<?php }?>
<?php if (!extension_loaded('xml')) {?>
<div class="error_box">
    XML модуль не загружен. Обратитесь к администратору для устранения ошибки.
</div>
<?php }?>
<?php
if ($_method == EXPORT_SAVE && $_is_save) {
    print "<p>" . $CNCAT["lang"]["export_save_in_file"] . " <a href=\"" . $CNCAT["abs"] . $CNCAT["system"]["dir_export"] . strftime($_filename) . "\" target=\"_blank\"><strong>" . strftime($_filename) . "</strong></a>.</p>";
}

$_tables = explode(",", $_COOKIE["cncat_export_tables"]);
$_binary = isset($_COOKIE["cncat_export_binary"]) ? $_COOKIE["cncat_export_binary"] : true;
$_pack = isset($_COOKIE["cncat_export_pack"]) ? $_COOKIE["cncat_export_pack"] : false;
$_method = isset($_COOKIE["cncat_export_method"]) ? $_COOKIE["cncat_export_method"] : EXPORT_FLUSH;
?>
<table class="form">
<form action="index.php?act=export&mode=export" method="post">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["export_options"]?></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["export_data"]?></td>
        <td class="field">
            <input type="checkbox" name="tables[]" id="tables_cat" value="cat" <?php print in_array("cat", $_tables) ? "checked=\"checked\"" : ""?> /> <label for="tables_cat"><?php print $CNCAT["lang"]["export_cat"]?></label><br />
            <input type="checkbox" name="tables[]" id="tables_bl" value="bl" <?php print in_array("bl", $_tables) ? "checked=\"checked\"" : ""?> /> <label for="tables_bl"><?php print $CNCAT["lang"]["export_bl"]?></label><br />
            <input type="checkbox" name="tables[]" id="tables_bwl" value="bwl" <?php print in_array("bwl", $_tables) ? "checked=\"checked\"" : ""?> /> <label for="tables_bwl"><?php print $CNCAT["lang"]["export_bwl"]?></label><br />
            <input type="checkbox" name="tables[]" id="tables_cfg" value="cfg" <?php print in_array("cfg", $_tables) ? "checked=\"checked\"" : ""?> /> <label for="tables_cfg"><?php print $CNCAT["lang"]["export_cfg"]?></label><br />
            <input type="checkbox" name="tables[]" id="tables_bnr" value="bnr" <?php print in_array("bnr", $_tables) ? "checked=\"checked\"" : ""?> /> <label for="tables_bnr"><?php print $CNCAT["lang"]["banners"]?></label><br />
            <input type="checkbox" name="tables[]" id="tables_img" value="img" <?php print in_array("img", $_tables) ? "checked=\"checked\"" : ""?> /> <label for="tables_img"><?php print $CNCAT["lang"]["images"]?></label><br />
        </td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["export_binary"]?></td>
        <td class="field"><input type="checkbox" name="binary" <?php print $_binary ? "checked=\"checked\"" : ""?> /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["export_pack"]?></td>
        <td class="field">
            <input type="checkbox" name="pack" <?php print !defined("FORCE_GZIP") ? "disabled=\"disabled\"" : ($_pack ? "checked=\"checked\"" : "")?> />
        </td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["export_storage"]?></td>
        <td class="field">
            <input type="radio" name="method" id="save" value="<?php print EXPORT_SAVE?>" <?php print !is_writeable($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_export"]) ? "disabled=\"disabled\"" : ($_method == EXPORT_SAVE ? "checked=\"checked\"" : "")?> /> <label for="save"><?php print $CNCAT["lang"]["export_save_on_server"]?></label><br />
            <input type="radio" name="method" id="upload" value="<?php print EXPORT_FLUSH?>" <?php print !is_writeable($CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_export"]) || $_method == EXPORT_FLUSH ? "checked=\"checked\"" : ""?>/> <label for="upload"><?php print $CNCAT["lang"]["export_upload"]?></label>
        </td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr><td class="submit" colspan="2"><input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_export"]?>" /></td></tr>
</form>
</table>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
