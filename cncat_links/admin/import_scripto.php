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

defined("ADMIN_INTERFACE") or exit;

require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "auth.php";
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "sync_all.php";

if (!isAdmin()) accessDenied();

$_mode = isset($_GET["mode"]) ? $_GET["mode"] : "";
$_errors = array();

if ($_mode == "import") {
    $cncat_fields = array(
        'cats' => array(
            'id рубрики'      => 'id',
            'id родительской' => 'parent_id',
            'название'        => 'title',
            'идентификатор'   => 'path',
            'сортировка'      => 'sort_order'
        ),
        'items' => array(
            'title ссылки'     => 'item_title',
            'адрес'            => 'link_url',
            'краткое описание' => 'item_descr',
            'полное описание'  => 'item_descr_full',
            'e-mail админа'    => 'item_author_email'
        )
    );

    $query = "SET NAMES 'cp1251'";
    $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));

    $scripto_fields = array();
    $is_row_line = false;
    $table = '';
    $cat_root_id = 1;

    ini_set('auto_detect_line_endings', 1);
    $fh = fopen($_FILES['file']['tmp_name'], 'r');

    if ($fh) {
        while (!feof($fh)) {
            $data = html_entity_decode(trim(fgets($fh)));
            $row = explode(';', $data);
    
            if (!$is_row_line) {
                if (in_array('PageRank', $row)) {
                    $table = 'items';
    
                    foreach ($row as $k => $v) {
                        if (empty($v)) continue;
                        $scripto_fields['items'][$v] = $k;
                    }
    
                    $CNCAT_ENGINE->db->query("TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "items`") or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
                    $CNCAT_ENGINE->db->query("TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "itemcat`") or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
                } elseif (in_array('id родительской', $row)) {
                    $table = 'cats';
    
                    foreach ($row as $k => $v) {
                        if (empty($v)) continue;
                        $scripto_fields['cats'][$v] = $k;
                    }
    
                    $CNCAT_ENGINE->db->query("TRUNCATE TABLE `" . $CNCAT["config"]["db"]["prefix"] . "cats`") or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
                }
    
                $is_row_line = true;
                continue;
            }
    
            if ($data == '!endcats;' || $data == '!endlinks;') {
                $is_row_line = false;
            }

            if (count($row) > 1 && $is_row_line) {
                if ($table == 'items') {
                    $row_data = array();
                    $row_data['item_status'] = 1;
                    $cat_id = 0;
    
                    foreach ($scripto_fields['items'] as $field => $index) {
                        if (isset($cncat_fields['items'][$field])) {
                            $row_data[$cncat_fields['items'][$field]] = $row[$index];
                        }
    
                        if ($field == 'id рубрики') {
                            $cat_id = $row[$index];
                        }
                    }

                    $row_data['item_title'] = cn_substr($row_data['item_title'], 0, 255);
                    $row_data['link_url'] = cn_substr($row_data['link_url'], 0, 255);
                    $row_data['item_author_email'] = cn_substr($row_data['item_author_email'], 0, 255);

                    $fields = array_keys($row_data);
                    $values = array();
    
                    foreach ($row_data as $v) {
                        if (is_numeric($v)) {
                            $values[] = $v;
                        } else {
                            $values[] = "'" . mysql_escape_string($v) . "'";
                        }
                    }
    
                    $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "items` (`"
                        . implode("`,`", $fields) . "`) VALUES ("
                        . implode(",", $values) . ")
                    ";
                    //echo $query."<hr>";
                    $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
                    $item_id = mysql_insert_id();
    
                    $query = "
                        INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "itemcat`
                            (`item_id`, `cat_id`, `item_status`)
                        VALUES (" . $item_id . ", " . $cat_id . ", 1)
                    ";
                    $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
                } elseif ($table == 'cats') {
                    $row_data = array();
                    $row_data['title_full'] = '';
                    $row_data['path_full'] = '';
                    $row_data['child_id_list'] = '';

                    foreach ($scripto_fields['cats'] as $field => $index) {
                        if (isset($cncat_fields['cats'][$field])) {
                            $row_data[$cncat_fields['cats'][$field]] = $row[$index];
                        }
                    }
    
                    $row_data['title'] = cn_substr($row_data['title'], 0, 255);
                    $row_data['path'] = cn_substr($row_data['path'], 0, 255);

                    if ($row_data['id'] >= $cat_root_id) {
                        $cat_root_id = $row_data['id'] + 1;
                    }
    
                    $fields = array_keys($row_data);
                    $values = array();
    
                    foreach ($row_data as $v) {
                        if (is_numeric($v)) {
                            $values[] = $v;
                        } else {
                            $values[] = "'" . mysql_escape_string($v) . "'";
                        }
                    }
    
                    $query = "INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "cats` (`"
                        . implode("`,`", $fields) . "`) VALUES ("
                        . implode(",", $values) . ")
                    ";
                    $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
                }
            }
        }
    
        $query = "SET NAMES '" . $CNCAT["config"]["db"]["charset"] . "'";
        $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));

        // insert root category
    	$query = "
            INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "cats`
                (`id`, `parent_id`, `title`, `title_full`, `path`, `path_full`, `child_id_list`)
            VALUES (" . $cat_root_id . ", -1, '" . mysql_escape_string($CNCAT["lang"]["default_root_cat_title"]) . "', '', '', '', '')
        ";
        $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
    
        // update parent_id
        $query = "
            UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "cats` SET `parent_id` = " . $cat_root_id . " WHERE `parent_id` = 0
        ";
        $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));

        // sync
        //cn_syncCats();
        $CNCAT["root_cat_id"] = $cat_root_id;
        cn_syncAll();
    }

    header("Location: index.php?act=import_scripto&mode=final");
    exit;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["import_from_scripto"]?></h1>
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
<form action="index.php?act=import_scripto&mode=import" method="post" enctype="multipart/form-data">
<table class="form">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["import_options"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["file_with_data"]?></td><td class="field"><input type="file" class="text" name="file" /></tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr><td class="submit" colspan="2"><input type="button" class="submit" value="<?php print $CNCAT["lang"]["do_import"]?>" onclick="if(confirm('<?php print $CNCAT["lang"]["really_import"]?>')){this.form.submit();}" /></tr>
</table>
</form>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
