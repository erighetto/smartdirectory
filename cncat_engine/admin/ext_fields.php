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

if (isset($_POST["doPost"])) {
    foreach ($_POST["field"] as $name => $field) {
        $query = "
            DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "fields`
            WHERE
                `name` = '" . mysql_escape_string(cn_substr($name, 0, 255)) . "'
                AND `type` = " . intval($field["item_type"]) . "
        ";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error()); 

        $query = "
            INSERT INTO `" . $CNCAT["config"]["db"]["prefix"] . "fields`
            SET
                `type` = " . intval($field["type"]) . ",
                `active` = " . (!empty($field["active"]) ? 1 : 0) . ",
                `required` = " . (!empty($field["required"]) ? 1 : 0) . ",
                `title` = '" . mysql_escape_string(cn_substr($field["title"], 0, 255)) . "',
                `display` = '" . mysql_escape_string(implode(',', (array)$field["display"])) . "',
                `search` = " . (!empty($field["search"]) ? 1 : 0) . ",
                `sort_order` = " . intval($field["sort_order"]) . ",
                `name` = '" . mysql_escape_string(cn_substr($name, 0, 255)) . "',
                `item_type` = " . intval($field["item_type"]) . "
        ";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error()); 
    }

    $form = !empty($_GET["form"]) ? $_GET["form"] : "links";

    header("Location: index.php?act=ext_fields&form=" . $form);
    exit;
}

$default_fields = array (
  0 => 
  array (
    'ext_datetime1' => 
    array (
      'type' => '4',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_datetime2' => 
    array (
      'type' => '4',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_datetime3' => 
    array (
      'type' => '4',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_double1' => 
    array (
      'type' => '2',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_double2' => 
    array (
      'type' => '2',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_double3' => 
    array (
      'type' => '2',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_image1' => 
    array (
      'type' => '6',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_image2' => 
    array (
      'type' => '6',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_image3' => 
    array (
      'type' => '6',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_int1' => 
    array (
      'type' => '1',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_int2' => 
    array (
      'type' => '1',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_int3' => 
    array (
      'type' => '1',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_text1' => 
    array (
      'type' => '5',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_text2' => 
    array (
      'type' => '5',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_text3' => 
    array (
      'type' => '5',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_varchar1' => 
    array (
      'type' => '3',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_varchar2' => 
    array (
      'type' => '3',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_varchar3' => 
    array (
      'type' => '3',
      'active' => '0',
      'item_type' => '0',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
  ),
  1 => 
  array (
    'ext_datetime1' => 
    array (
      'type' => '4',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_datetime2' => 
    array (
      'type' => '4',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_datetime3' => 
    array (
      'type' => '4',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_double1' => 
    array (
      'type' => '2',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_double2' => 
    array (
      'type' => '2',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_double3' => 
    array (
      'type' => '2',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_image1' => 
    array (
      'type' => '6',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_image2' => 
    array (
      'type' => '6',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_image3' => 
    array (
      'type' => '6',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_int1' => 
    array (
      'type' => '1',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_int2' => 
    array (
      'type' => '1',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_int3' => 
    array (
      'type' => '1',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_text1' => 
    array (
      'type' => '5',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_text2' => 
    array (
      'type' => '5',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_text3' => 
    array (
      'type' => '5',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_varchar1' => 
    array (
      'type' => '3',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_varchar2' => 
    array (
      'type' => '3',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
    'ext_varchar3' => 
    array (
      'type' => '3',
      'active' => '0',
      'item_type' => '1',
      'title' => '',
      'display' => 
      array (
        0 => '',
      ),
      'search' => '0',
      'sort_order' => '1000',
      'required' => '0',
    ),
  ),
);

foreach ($default_fields as $type => $fields) {
    foreach ($fields as $name => $field) {
        if (!isset($CNCAT["config"]["extfields"]["items"][$type][$name])) {
            $CNCAT["config"]["extfields"]["items"][$type][$name] = $field;
        }
    }
}

$item_type = 0;

if (!empty($_GET["form"]) && $_GET["form"] == "articles") {
    $item_type = 1;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
    <h1><?php print $CNCAT["lang"]["settings"]?> / <?php print $CNCAT["lang"]["extended_fields"]?></h1>
    <form action="" method="post">
    <div>
        <?php if ($_GET['form'] == 'articles') {?>
        <div style="float: left; background: white; border-style: solid; border-color: #c2cdd1; border-width: 1px 1px 0 1px;  padding: 5px 9px;"><a href="index.php?act=ext_fields" style="padding: 5px;"><?php print $CNCAT["lang"]["links"]?></a></div>
        <div style="float: left; background: #fefece; border-style: solid; border-color: #c2cdd1; border-width: 1px 1px 0 0; padding: 5px 14px; font-weight: bold;"><?php print $CNCAT["lang"]["articles"]?></div>
        <?php } else {?>
        <div style="float: left; background: #fefece; border-style: solid; border-color: #c2cdd1; border-width: 1px 1px 0 1px; padding: 5px 14px; font-weight: bold;"><?php print $CNCAT["lang"]["links"]?></div>
        <div style="float: left; background: white; border-style: solid; border-color: #c2cdd1; border-width: 1px 1px 0 0;  padding: 5px 9px;"><a href="index.php?act=ext_fields&form=articles" style="padding: 5px;"><?php print $CNCAT["lang"]["articles"]?></a></div>
        <?php }?>
    </div>
    <br style="clear: both;" />
    <table class="items">
        <tr>
            <td class="title"><?php print $CNCAT["lang"]["field_name"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["field_type"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["field_title"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["field_active"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["field_display"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["field_required"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["field_search"]?></td>
            <td class="title"><?php print $CNCAT["lang"]["sort_order"]?></td>
        </tr>
<?php
foreach ((array)$CNCAT["config"]["extfields"]["items"][$item_type] as $name => $field) {
?>
    <input type="hidden" name="field[<?php print $name?>][item_type]" value="<?php print $item_type?>">
    <input type="hidden" name="field[<?php print $name?>][type]" value="<?php print $field["type"]?>">
    <tr>
        <td class="item"><?php print $name?></td>
        <td class="item">
        <?php
        $type = "";
        
        switch ($field["type"]) {
            case 1: $type = $CNCAT["lang"]["field_type_int"]; break;
            case 2: $type = $CNCAT["lang"]["field_type_double"]; break;
            case 3: $type = $CNCAT["lang"]["field_type_string"]; break;
            case 4: $type = $CNCAT["lang"]["field_type_date"]; break;
            case 5: $type = $CNCAT["lang"]["field_type_text"]; break;
            case 6: $type = $CNCAT["lang"]["field_type_image"]; break;
        }
        
        print $type;
        ?>
        </td>
        <td class="item">
            <input type="text" class="text" name="field[<?php print $name?>][title]" value="<?php print htmlspecialchars($field["title"])?>" size="20" />
        </td>
        <td class="item">
            <select name="field[<?php print $name?>][active]">
                <option value="1"<?php print $field["active"] == 1 ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["yes"]?></option>
                <option value="0"<?php print $field["active"] == 0 ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["no"]?></option>
            </select>
        </td>
        <td class="item">
            <input type="checkbox" name="field[<?php print $name?>][display][]" value="1"<?php print in_array(1, $field["display"]) ? " checked=\"checked\"" : ""?>> <?php print $CNCAT["lang"]["ext_field_in_add"]?><br />
            <input type="checkbox" name="field[<?php print $name?>][display][]" value="2"<?php print in_array(2, $field["display"]) ? " checked=\"checked\"" : ""?>> <?php print $CNCAT["lang"]["ext_field_in_items"]?><br />
            <input type="checkbox" name="field[<?php print $name?>][display][]" value="3"<?php print in_array(3, $field["display"]) ? " checked=\"checked\"" : ""?>> <?php print $CNCAT["lang"]["ext_field_in_extended"]?><br />
        </td>
        <td class="item">
            <select name="field[<?php print $name?>][required]">
                <option value="1"<?php print $field["required"] == 1 ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["yes"]?></option>
                <option value="0"<?php print $field["required"] == 0 ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["no"]?></option>
            </select>
        </td>
        <td class="item">
        <?php if ($field["type"] != 6) {?>
            <select name="field[<?php print $name?>][search]">
                <option value="1"<?php print $field["search"] == 1 ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["yes"]?></option>
                <option value="0"<?php print $field["search"] == 0 ? " selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["no"]?></option>
            </select>
        <?php }?>
        </td>
        <td class="item">
            <input type="text" class="text" name="field[<?php print $name?>][sort_order]" value="<?php print $field["sort_order"]?>"  />
        </td>
    </tr>
<?php
}
?>
    </table>
    <div class="deline"></div>
    <input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_save"]?>" />
    </form>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
