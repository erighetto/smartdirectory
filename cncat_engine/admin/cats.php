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
require_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "sync_all.php";

$_mode = isset($_GET["mode"]) ? $_GET["mode"] : "";
$_is_link = isset($_GET["type"]) && $_GET["type"] == "link";

if ($_mode == "add") {
/*******************************************************************************
 * ADD BEGIN
 ******************************************************************************/
    $_parent_id = (int)$_GET["id"];
    $_cat = array();

    // if moder can't edit category
    if (!catCanDo("add", $_parent_id)) {
        accessDenied();
    }

    if (isset($_POST["doPost"])) {
        if (catExists("`id`=" . $_parent_id . " AND `is_link`=0")) {
            $_cat["parent_id"] = $_parent_id;
            $_cat["title"] = cn_trim($_POST["title"]);
            $_cat["display"] = !empty($_POST["display"]) ? 0 : 1;
            $_cat["sort_order"] = intval($_POST["sort_order"]);
            $_cat["descr"] = cn_trim($_POST["descr"]);
            $_cat["items_sort_order"] = intval($_POST["items_sort_order"]);

            if ($_is_link) {
                $_cat["is_link"] = 1;
                $_cat["id_real"] = (int)$_POST["id_real"];

                if (catExists("`id`=" . $_cat["id_real"] . " AND `is_link`=0")) {
                    list($link) = catSelect("`path_full`", "`id`=" . $_cat["id_real"]);
                    $_cat["path"] = $link["path_full"];
                } else {
                    $_error[] = $CNCAT["lang"]["cat_not_exists"];
                }
            } else {
                $_cat["path"] = cn_trim($_POST["path"]);
                $_cat["meta_keywords"] = cn_trim($_POST["meta_keywords"]);
                $_cat["meta_descr"] = cn_trim($_POST["meta_descr"]);
                $_cat["disable_add"] = !empty($_POST["disable_add"]) ? 1 : 0;
                $_cat["disable_child_add"] = !empty($_POST["disable_child_add"]) ? 1 : 0;
            }

            // Check fields
            $_errors = array();

            if (empty($_cat["title"])) {
                $_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["title"], $CNCAT["lang"]["field_empty"]);
            } elseif (cn_strlen($_cat["title"]) > 255) {
                $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["title"], 255), $CNCAT["lang"]["field_to_long"]);
            }

            if (!$_is_link) {
                if (!empty($_cat["path"])) {
                    if (cn_strlen($_cat["path"]) > 255) {
                        $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["path"], 255), $CNCAT["lang"]["field_to_long"]);
                    } elseif (/*!preg_match("/^[a-z0-9_\-~]*$/i", $_cat["path"])*/false) {
                        $_errors[] = $CNCAT["lang"]["cat_invalid_path"];
                    } else {
                        list($row) = catSelect("`path_full`", "`id`=" . $_parent_id . " AND `is_link`=0");
                        $_cat["path_full"] = !empty($row["path_full"]) ? $row["path_full"] . "/" . $_cat["path"] : $_cat["path"];
    
                        if (catExists("`path_full`='" . mysql_escape_string($_cat["path_full"]) . "'")) {
                            $_errors[] = $CNCAT["lang"]["cat_path_exists"];
                        }
                    }
                }

                if (cn_strlen($_cat["meta_keywords"]) > 2048) {
                    $_errors[] = $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["meta_keywords"], 2048), $CNCAT["lang"]["field_to_long"]);
                }

                if (cn_strlen($_cat["meta_descr"]) > 2048) {
                    $_errors[] = $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["meta_descr"], 2048), $CNCAT["lang"]["field_to_long"]);
                }
            }

            if (cn_strlen($_cat["descr"]) > 4096) {
                $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["descr"], 4096), $CNCAT["lang"]["field_to_long"]);
            }

            $_cat["image_mime"] = "";
            $_cat["image"] = "";

            // upload image
            if ($_FILES["image"]["error"] == UPLOAD_ERR_OK) {
                if ($_FILES["image"]["size"] > 64 * 1024) {
                    $_errors[] = cn_str_replace("%SIZE%", 64, $CNCAT["lang"]["cat_big_image"]);
                } else {
                    $_cat["image_mime"] = $_FILES["image"]["type"];
                    $_cat["image"] = @file_get_contents($_FILES["image"]["tmp_name"]);
                }
            }

            // insert new category
            if (!$_errors) {
                $_cat["title_full"] = "";
                $_cat["path_full"] = "";
                $_cat["child_id_list"] = "";
                catInsert($_cat);

                cn_syncAll();
                header("Location: index.php?act=cats");
                exit;
            }
        }
    }
/*******************************************************************************
 * ADD END
 ******************************************************************************/
} elseif ($_mode == "edit") {
/*******************************************************************************
 * EDIT BEGIN
 ******************************************************************************/
    $_cat_id = (int)$_GET["id"];
    $_is_link = false;
    $_is_root = false;

    if (!catCanDo("edit", $_cat_id)) {
        accessDenied();
    }

    if (isset($_POST["doPost"])) {
        if (catExists("`id`=" . $_cat_id)) {
            list($cat) = catSelect("`is_link`, `parent_id`", "`id`=" . $_cat_id);

            if ($cat["is_link"]) {
                $_is_link = true;
            } elseif ($cat["parent_id"] == -1) {
                $_is_root = true;
            }

            $_delete = false;
            $_delete_child = false;
            $_move = false;
            $_move_child = false;
            $_move_to = false;

            $_cat = array();
            $_cat["title"] = cn_trim($_POST["title"]);
            $_cat["display"] = !empty($_POST["not_display"]) ? 0 : 1;
            $_cat["items_sort_order"] = intval($_POST["items_sort_order"]);

            if (!$_is_root) {
                $_cat["sort_order"] = (int)$_POST["sort_order"];
            }

            if (!$_is_link) {
                if (!$_is_root) {
                    $_cat["path"] = cn_trim($_POST["path"]);
                    $_cat["descr"] = cn_trim($_POST["descr"]);
                    $_cat["disable_add"] = !empty($_POST["disable_add"]) ? 1 : 0;
                    $_cat["disable_child_add"] = !empty($_POST["disable_child_add"]) ? 1 : 0;

                    // group actions
                    $_delete = !empty($_POST["delete"]);
                    $_delete_child = !empty($_POST["delete_child"]);
                    $_move = !empty($_POST["move"]);
                    $_move_child = !empty($_POST["move_child"]);
                    $_move_to = (int)$_POST["move_to"];
                }

                $_cat["meta_keywords"] = cn_trim($_POST["meta_keywords"]);
                $_cat["meta_descr"] = cn_trim($_POST["meta_descr"]);
            }

            $_delete_image = !empty($_POST["delete_image"]);

            // Check fields
            $_errors = array();
        
            if (empty($_cat["title"])) {
                $_errors[] = cn_str_replace("%FIELD%", $CNCAT["lang"]["title"], $CNCAT["lang"]["field_empty"]);
            } elseif (cn_strlen($_cat["title"]) > 255) {
                $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["title"], 255), $CNCAT["lang"]["field_to_long"]);
            }

            if (!$_is_root) {
                $_parent_change = !empty($_POST["parent_change"]);
    
                if ($_parent_change) {
                    $_cat["parent_id"] = (int)$_POST["parent_id"];
    
                    if ($_cat_id == $_cat["parent_id"]) {
                        $_errors[] = $CNCAT["lang"]["cat_not_move_itself"];
                    } elseif (!catExists("`id`=" . $_cat["parent_id"] . " AND `is_link`=0")) {
                        $_errors[] = $CNCAT["lang"]["parent_cat_not_exists"];
                    } elseif (catIsChildFor($_cat["parent_id"], $_cat_id)) {
                        $_errors[] = $CNCAT["lang"]["cat_not_move_inchild"];
                    }
                }
            }

            if (!$_is_link) {
                if (!$_is_root) {
                    if (empty($_cat["path"])) {
                        cn_str_replace("%FIELD%", $CNCAT["lang"]["path"], $CNCAT["lang"]["field_empty"]);
                    } elseif (cn_strlen($_cat["path"]) > 255) {
                        $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["path"], 255), $CNCAT["lang"]["field_to_long"]);
                    } elseif (/*!preg_match("/^[a-z0-9_\-~]*$/i", $_cat["path"])*/false) {
                        $_errors[] = $CNCAT["lang"]["cat_invalid_path"];
                    } else {
                        list($cat) = catSelect("`parent_id`", "`id`=" . $_cat_id);
                        list($row) = catSelect("`path_full`", "`id`=" . ($_parent_change ? $_cat["parent_id"] : $cat["parent_id"]) . " AND `is_link`=0");
                        $_path_full = !empty($row["path_full"]) ? $row["path_full"] . "/" . $_cat["path"] : $_cat["path"];
    
                        if (catExists("`path_full`='" . mysql_escape_string($_path_full) . "' AND `id`!=" . $_cat_id . " AND `is_link`=0")) {
                            $_errors[] = $_errors[] = $CNCAT["lang"]["cat_path_exists"];
                        }
                    }

                    if (cn_strlen($_cat["descr"]) > 4096) {
                        $_errors[] = $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["descr"], 4096), $CNCAT["lang"]["field_to_long"]);
                    }
        
                    if ($_move) {
                        if (!catExists("`id`=" . $_move_to)) {
                            $_errors[] = $CNCAT["lang"]["cat_move_not_exists"];
                        }
                    }
                }

                if (cn_strlen($_cat["meta_keywords"]) > 2048) {
                    $_errors[] = $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["meta_keywords"], 2048), $CNCAT["lang"]["field_to_long"]);
                }

                if (cn_strlen($_cat["meta_descr"]) > 2048) {
                    $_errors[] = $_errors[] = cn_str_replace(array("%FIELD%", "%LEN%"), array($CNCAT["lang"]["meta_descr"], 2048), $CNCAT["lang"]["field_to_long"]);
                }
            }

            if ($_delete_image) {
                $_cat["image_mime"] = "";
                $_cat["image"] = "";
            }

            // upload image
            if ($_FILES["image"]["error"] == UPLOAD_ERR_OK) {
                if ($_FILES["image"]["size"] > 64 * 1024) {
                    $_errors[] = cn_str_replace("%SIZE%", 64, $CNCAT["lang"]["big_image"]);
                } elseif (!in_array($_FILES["image"]["type"], array("image/jpeg", "image/pjpeg", "image/png", "image/gif", "image/x-icon"))) {
                    $_errors[] = cn_str_replace("%MIME%", "image/jpeg, image/pjpeg, image/png, image/gif, image/x-icon", $CNCAT["lang"]["invalid_mime"]);
                } else {
                    $_cat["image_mime"] = $_FILES["image"]["type"];
                    $_cat["image"] = @file_get_contents($_FILES["image"]["tmp_name"]);
                }
            }

            if (!$_errors) {
                catUpdate($_cat, "`id`=" . $_cat_id);

                if (isAdmin() && !$_is_link && !$_is_parent) {
                    if ($_delete) {
                        $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat` WHERE `cat_id`=" . $_cat_id;
                        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                    }

                    if ($_delete_child) {
                        list($row) = catSelect("`child_id_list`", "`id`=" . $_cat_id);

                        if (!empty($row["child_id_list"])) {
                            $query = "DELETE FROM `" . $CNCAT["config"]["db"]["prefix"] . "itemcat` WHERE `cat_id` IN(" . $row["child_id_list"] . ")";
                            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                        }
                    }

                    if ($_move) {
                        $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "itemcat` SET `cat_id`=" . $_move_to . " WHERE `cat_id`=" . $_cat_id;
                        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                    }

                    if ($_move_child) {
                        list($row) = catSelect("`child_id_list`", "`id`=" . $_cat_id);

                        if (!empty($row["child_id_list"])) {
                            $query = "UPDATE `" . $CNCAT["config"]["db"]["prefix"] . "itemcat` SET `cat_id`=" . $_move_to . " WHERE `cat_id` IN(" . $row["child_id_list"] . ")";
                            $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
                        }
                    }
                }

                cn_syncAll();
                header("Location: index.php?act=cats");
                exit;
            }
        }
    }
/*******************************************************************************
 * EDIT END
 ******************************************************************************/
} elseif ($_mode == "del") {
/*******************************************************************************
 * DELETE BEGIN
 ******************************************************************************/
    $_cat_id = (int)$_GET["id"];

    if (catCanDo("del", $_cat_id) && catExists("`id`=" . $_cat_id . " AND `parent_id`!=-1")) {
        list($cat) = catSelect("`child_id_list`, `item_count_full`, `is_link`", "`id`=" . $_cat_id);

        if ($cat["is_link"] || (empty($cat["child_id_list"]) && (int)$cat["item_count_full"] == 0)) {
            catDelete("`id`=" . $_cat_id);
            cn_syncAll();
        }
    }

    header("Location: index.php?act=cats");
    exit;
/*******************************************************************************
 * DELETE END
 ******************************************************************************/
} elseif ($_mode == "settings" && isset($_POST["doPost"])) {
    $config = array();
    $config["cat_admin_view"] = abs((int)$_POST["cat_admin_view"]);

    if ($config["cat_admin_view"] > 1) {
        $config["cat_admin_view"] = 1;
    }
    
    $query = "
        REPLACE `" . $CNCAT["config"]["db"]["prefix"] . "config`
        SET `value`=" . $config["cat_admin_view"] . ", `name`='cat_admin_view'
    ";
    $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());

    header("Location: index.php?act=cats");
    exit;
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";

/*******************************************************************************
 * ADD BEGIN
 ******************************************************************************/
if ($_mode == "add") {
?>
<h1><?php print $CNCAT["lang"]["categories"]?> / <?php if (!$_is_link) {?><?php print $CNCAT["lang"]["cat_addition"]?><?php } else {?><?php print $CNCAT["lang"]["cat_addition_link"]?><?php }?></h1>
<a href="index.php?act=cats"><?php print $CNCAT["lang"]["cats_list"]?></a>
<div class="deline"></div>
<?php
    $_parent_id = (int)$_GET["id"];
    $_cat = array();

    // check and get information of parent category
    if (catExists("`id`=" . $_parent_id)) {
        list($_cat) = catSelect("`title_full`", "`id`=" . $_parent_id . " AND `is_link`=0");
    }

    // if category exists
    if ($_cat) {
        if ($_errors) {
            print "<ul class=\"errors\">\n";

            foreach ($_errors as $error) {
                print "<li>" . $error . "</li>\n";
            }

            print "</ul>\n";
        }

        if (!isset($_POST["doPost"])) {
            $_POST["sort_order"] = 1000;
        }
?>
<table class="form">
    <tr><td class="title" colspan="2"><?php print cn_str_replace("%NAME%", htmlspecialchars($_cat["title_full"]), $CNCAT["lang"]["in_cat"])?></td></tr>
<form action="index.php?act=cats&mode=add<?php print $_is_link ? "&type=link" : ""?>&id=<?php print $_parent_id?>" method="post" enctype="multipart/form-data">
    <tr><td class="name"><?php print $CNCAT["lang"]["title"]?></td><td class="field"><input type="text" name="title" value="<?php print htmlspecialchars($_POST["title"])?>" class="text" /></td></tr>
    <tr><td class="deline" colspan="2"></td></tr>

    <?php if (!$_is_link) {?>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["path"]?></td>
            <td class="field"><input type="text" name="path" value="<?php print htmlspecialchars($_POST["path"])?>" class="text" /></td>
        </tr>
        <tr><td class="deline" colspan="2"></td></tr>
    <?php }?>

    <tr><td class="name"><?php print $CNCAT["lang"]["sort_order"]?></td><td class="field"><input type="text" name="sort_order" value="<?php print htmlspecialchars($_POST["sort_order"])?>" class="text" /></td></tr>
    <tr><td class="deline" colspan="2"></td></tr>

    <?php if ($_is_link) {?>
        <tr><td class="name" colspan="2"><?php print $CNCAT["lang"]["link_to_cat"]?></td></tr>
        <tr><td class="submit" colspan="2">
            <select name="id_real">
<?php
    // get and print categories tree
    foreach (catSelect(
            "`id`, `title`, `tree_level`",
            "`parent_id`!=-1",
            "ORDER BY `sort_order_global`"
        ) as $cat
    ) {
        print "<option value=\"" . $cat["id"] . "\" class=\"level" . $cat["tree_level"] . "\"";
        print ($cat["id"] == $_POST["id_real"] ? "selected=\"selected\"" : "") .">"; 
        print str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", ($cat["tree_level"] - 1));
        print htmlspecialchars($cat["title"]);
        print "</option>\n";
    }
?>
            </select>
        </td></tr>
        <tr><td class="deline" colspan="2"></td></tr>
    <?php }?>

    <tr>
        <td class="name"><?php print $CNCAT["lang"]["descr"]?></td>
        <td class="field"><textarea name="descr"><?php print htmlspecialchars($_POST["descr"])?></textarea></td>
    </tr>

    <?php if (!$_is_link) {?>
        <tr><td class="deline" colspan="2"></td></tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["meta_keywords"]?></td>
            <td class="field"><input type="text" name="meta_keywords" value="<?php print htmlspecialchars($_POST["meta_keywords"])?>" class="text" /></td>
        </tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["meta_descr"]?></td>
            <td class="field"><input type="text" name="meta_descr" value="<?php print htmlspecialchars($_POST["meta_descr"])?>" class="text" /></td>
        </tr>
    <?php }?>

    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["image"]?></td>
        <td class="field"><input type="file" name="image" class="text" /></td>
     </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["cat_items_sort"]?></td>
        <td class="field">
            <select name="items_sort_order">
                <option value="-1"><?php print $CNCAT["lang"]["deafult_as_in_settings"]?></option>
<?php
foreach ($CNCAT["sql"]["itemorder"] as $sortId => $sort) {
    if ($sort[4] == 1) continue;
?>
                <option value="<?php print $sortId?>"><?php print $CNCAT["lang"]["by"]?> &laquo;<?php print $CNCAT["lang"]["sort_by_" . $sortId]?>&raquo;</option>
<?php
    }
?>
            </select>
        </td>
    </tr>

    <?php if (!$_is_link) {?>
        <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["operations"]?></td></tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["cat_disable_add"]?></td>
            <td class="field"><label><input type="checkbox" name="disable_add" <?php print $_POST["add_disable"] ? "checked=\"checked\"" : ""?>/> <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/lock.gif" alt="" style="vertical-align: middle" /></label></td>
        </tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["cat_disable_child_add"]?></td>
            <td class="field"><label><input type="checkbox" name="disable_child_add" <?php print $_POST["add_child_disable"] ? "checked=\"checked\"" : ""?>/> <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/lock.gif" alt="" style="vertical-align: middle" /></label></td>
        </tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["not_display_in_catlist"]?></td>
            <td class="field"><input type="checkbox" name="not_display" <?php print $_POST["not_display"] ? "checked=\"checked\"" : ""?> /></td>
        </tr>
        <tr><td class="deline" colspan="2"></td></tr>
    <?php }?>

    <tr><td colspan="2" class="submit"><input type="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_submit"]?>" class="submit" /></td></tr>
</form>
</table>
<?php
    } else {
        print "<p class=\"not_found\">" . $CNCAT["lang"]["cat_not_exists"] . "</p>\n";
    }
/*******************************************************************************
 * ADD END
 ******************************************************************************/
} elseif ($_mode == "edit") {
/*******************************************************************************
 * EDIT BEGIN
 ******************************************************************************/
?>
<h1><?php print $CNCAT["lang"]["categories"]?> / <?php print $CNCAT["lang"]["editing"]?></h1>
<a href="index.php?act=cats"><?php print $CNCAT["lang"]["cats_list"]?></a>
<div class="deline"></div>
<?php
    $_cat_id = (int)$_GET["id"];
    $_cat = array();
    $_is_root = false;
    $_is_link = false;

    if (catExists("`id`=" . $_cat_id)) {
        list($_cat) = catSelect("*", "`id`=" . $_cat_id);
    }

    if ($_cat) {
        if ($_cat["is_link"]) {
            $_is_link = true;
        } elseif ((int)$_cat["parent_id"] == -1) {
            $_is_root = true;
        }

        if ($_errors) {
            print "<ul class=\"errors\">\n";

            foreach ($_errors as $error) {
                print "<li>" . $error . "</li>\n";
            }

            print "</ul>\n";
        }

        if (!isset($_POST["doPost"])) {
            $_POST = $_cat;
            $_POST["not_display"] = $_cat["display"] ? 0 : 1;
        }
?>
<table class="form">
    <tr><td class="title" colspan="2"><?php print cn_str_replace("%NAME%", htmlspecialchars($_cat["title_full"]), $CNCAT["lang"]["cat_title"])?></td></tr>
<form action="index.php?act=cats&mode=edit&id=<?php print $_cat["id"]?>" method="post" enctype="multipart/form-data">
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["title"]?></td>
        <td class="field"><input type="text" name="title" value="<?php print htmlspecialchars($_POST["title"])?>" class="text" /></td>
    </tr>
    <?php if (!$_is_link && !$_is_root) {?>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["path"]?></td>
            <td class="field"><input type="text" name="path" value="<?php print htmlspecialchars($_POST["path"])?>" class="text" /></td>
        </tr>
    <?php }?>

    <?php if (!$_is_root) {?>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["sort_order"]?></td>
        <td class="field"><input type="text" name="sort_order" value="<?php print htmlspecialchars($_POST["sort_order"])?>" class="text" /></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["descr"]?></td>
        <td class="field"><textarea name="descr"><?php print htmlspecialchars($_POST["descr"])?></textarea></td>
    </tr>
    <?php }?>
    <?php if (!$_is_link) {?>
        <tr><td class="deline" colspan="2"></td></tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["meta_keywords"]?></td>
            <td class="field"><input type="text" name="meta_keywords" value="<?php print htmlspecialchars($_POST["meta_keywords"])?>" class="text" /></td>
        </tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["meta_descr"]?></td>
            <td class="field"><input type="text" name="meta_descr" value="<?php print htmlspecialchars($_POST["meta_descr"])?>" class="text" /></td>
        </tr>
    <?php }?>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name">
            <?php if ($_cat["image_mime"]) {?>
                <?php print $CNCAT["lang"]["delete_image"]?>
                <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_prefix"] . "image.php?cat=" . $_cat_id ?>" alt="" style="vertical-align: middle;" />
            <?php }?>
        </td>
        <td class="field"><?php if ($_cat["image_mime"]) {?><input type="checkbox" name="delete_image" /><?php }?></td>
    </tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["image"]?></td>
        <td class="field"><input type="file" name="image" class="text" /></td>
    </tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr>
        <td class="name"><?php print $CNCAT["lang"]["cat_items_sort"]?></td>
        <td class="field">
            <select name="items_sort_order">
                <option value="-1" <?php print $_POST["items_sort_order"] == -1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["deafult_as_in_settings"]?></option>
<?php
foreach ($CNCAT["sql"]["itemorder"] as $sortId => $sort) {
    if ($sort[4] == 1) continue;
?>
                <option value="<?php print $sortId?>" <?php print $_POST["items_sort_order"] == $sortId ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["by"]?> &laquo;<?php print $CNCAT["lang"]["sort_by_" . $sortId]?>&raquo;</option>
<?php
    }
?>
            </select>
        </td>
    </tr>

    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["operations"]?></td></tr>
    <?php if (!$_is_root) {?>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["change_parent_cat"]?></td>
            <td class="field"><input type="checkbox" name="parent_change" <?php print $_POST["parent_change"] ? "checked=\"checked\"" : ""?> /></td>
        </tr>
        <tr>
            <td class="submit" colspan="2">
                <select name="parent_id">
<?php
    // print root category
    list($cat) = catSelect("`id`, `title`", "`parent_id`=-1");

    print "<option value=\"" . $cat["id"] . "\" class=\"level" . $cat["tree_level"] . "\" " . ($_POST["parent_id"] == $cat["id"] ? "selected=\"selected\"" : "") . ">";
    print htmlspecialchars($cat["title"]) . ($_cat["parent_id"] == $cat["id"] ? " *" : "");
    print "</option>\n";

    // get child id list
    list($row) = catSelect("`child_id_list`", "`id`=" . $_cat_id);
    $cats = array();

    if (!empty($row["child_id_list"])) {
        $cats = explode(",", $row["child_id_list"]);
    }

    $cats[] = $_cat_id;

    // get and print categories tree
    foreach (catSelect(
            "`id`, `title`, `parent_id`, `tree_level`",
            "`is_link`=0 AND `id` NOT IN(" . join(",", $cats) . ") AND `parent_id`!=-1",
            "ORDER BY `sort_order_global`"
        ) as $cat
    ) {
        print "<option value=\"" . $cat["id"] . "\" class=\"level" . $cat["tree_level"] . "\" " . ($_POST["parent_id"] == $cat["id"] ? "selected=\"selected\"" : "") . ">"; 
        print str_repeat("&nbsp;-&nbsp;&nbsp;&nbsp;", $cat["tree_level"]);
        print htmlspecialchars($cat["title"]) . ($_cat["parent_id"] == $cat["id"] ? " *" : "");
        print "</option>\n";
    }
?>
                </select>
            </td>
        </tr>
        <?php if (!$_is_link) {?>
        <tr><td class="deline" colspan="2"></td></tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["cat_disable_add"]?></td>
            <td class="field"><label><input type="checkbox" name="disable_add" <?php print $_POST["disable_add"] ? "checked=\"checked\"" : ""?> /> <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>lock.gif" alt="" style="vertical-align: middle;" /></label></td>
        </tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["cat_disable_child_add"]?></td>
            <td class="field"><label><input type="checkbox" name="disable_child_add" <?php print $_POST["disable_child_add"] ? "checked=\"checked\"" : ""?> /> <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>lock.gif" alt="" style="vertical-align: middle;" /></label></td>
        </tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["not_display_in_catlist"]?></td>
            <td class="field"><input type="checkbox" name="not_display" <?php print $_POST["not_display"] ? "checked=\"checked\"" : ""?> /></td>
        </tr>
        <?php }?>
        <tr><td class="deline" colspan="2"></td></tr>
    <?php }?>

    <?php if (isAdmin() && !$_is_link && !$_is_root) {?>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["delete_all_items"]?>&nbsp;(<?php print $_cat["item_count"]?>)</td>
            <td class="field"><input type="checkbox" name="delete" /></td>
        </tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["delete_child_items"]?>&nbsp;(<?php print ($_cat["item_count_full"] - $_cat["item_count"])?>)</td>
            <td class="field"><input type="checkbox" name="delete_child" /></td>
        </tr>
        <tr><td class="deline" colspan="2"></td></tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["move_all_items"]?>&nbsp;(<?php print $_cat["item_count"]?>)</td>
            <td class="field"><input type="checkbox" name="move" /></td>
        </tr>
        <tr>
            <td class="name"><?php print $CNCAT["lang"]["move_child_items"]?>&nbsp;(<?php print ($_cat["item_count_full"] - $_cat["item_count"])?>)</td>
            <td class="field"><input type="checkbox" name="move_child" /></td>
        </tr>
        <tr><td class="submit" colspan="2">
            <select name="move_to">
<?php
    // get and print categories tree
    foreach (catSelect(
            "`id`, `title`, `tree_level`",
            "`parent_id`!=-1 AND `is_link`=0",
            "ORDER BY `sort_order_global`"
        ) as $cat
    ) {
        print "<option value=\"" . $cat["id"] . "\" class=\"level" . $cat["tree_level"] . "\" " . ($_POST["id"] == $cat["id"] ? "selected=\"selected\"" : "") . ">"; 
        print str_repeat("&nbsp;-&nbsp;&nbsp;&nbsp;", $cat["tree_level"]);
        print htmlspecialchars($cat["title"]) . ($_cat["id"] == $cat["id"] ? " *" : "");
        print "</option>\n";
    }
?>
            </select>
        </td></tr>
    <?php }?>

    <tr><td class="deline" colspan="2"></td></tr>
    <tr><td colspan="2" class="submit"><input type="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_save"]?>" class="submit" /></td></tr>
</form>
</table>
<?php
    } else {
        print "<p class=\"not_found\">" . $CNCAT["lang"]["cat_not_exists"] . "</p>\n";
    }
/*******************************************************************************
 * EDIT END
 ******************************************************************************/
} else {
/*******************************************************************************
 * INDEX BEGIN
 ******************************************************************************/

    $_open_cat_list = explode(',', isset($_COOKIE['admin_open_cat_list']) ? $_COOKIE['admin_open_cat_list'] : '');
?>
<script type="text/javascript">
<!--
var is_edit = false;
var intv;

function fastEdit(id) {
    clearInterval(intv);

    if (is_edit) {
        title = document.getElementById('cat_' + id + '_title');
        text = document.getElementById('cat_' + id + '_title_r').innerHTML;
        title.innerHTML = '<input type="text" style="height: 20px; width: 150px; border: 1px solid silver;" id="cat_' + id + '_input" onkeydown="if (event.keyCode == 13) fastSave(' + id + ', this.value);" value="' + text + '" />';
        document.getElementById('cat_' + id + '_input').focus();
    }
}

function fastSave(id, text) {
    title = document.getElementById('cat_' + id + '_title');
    title.innerHTML = '<a href="">' + text + '</a>';

    title_r = document.getElementById('cat_' + id + '_title_r');
    title_r.innerHTML = text;
}

var cats_bc = new Array();
var catsChk = new Array();

function selectCat(id, cat) {
    if (!cats_bc[id]) {
        cats_bc[id] = cat.style.backgroundColor;
    }

    catsChk[id] = !catsChk[id];

    if (catsChk[id]) {
        bgcolor = '#fefece';
    } else {
        bgcolor = cats_bc[id];
    }

    cat.style.backgroundColor = bgcolor;
}
//-->
</script>
<h1 style="float: left; padding-right: 10px;"><?php print $CNCAT["lang"]["cats_list"]?></h1>

<form action="index.php?act=cats&mode=settings" method="post">
    <input type="hidden" name="doPost" value="1" />
    <p>
        <select name="cat_admin_view" onchange="this.form.submit()">
            <option value="0" <?php print $CNCAT["config"]["cat_admin_view"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["table"]?></option>
            <option value="1" <?php print $CNCAT["config"]["cat_admin_view"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["tree"]?></option>
        </select>
    </p>
</form>
<div class="deline"></div>
<?php
    $query = "SELECT c2.id, c1.title_full FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats` c1, `" . $CNCAT["config"]["db"]["prefix"] . "cats` c2
        WHERE c2.is_link=1 AND c1.id=c2.id_real";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());   
    $reals = array();
    
    while ($row = mysql_fetch_assoc($res)) {
        $reals[$row["id"]] = $row["title_full"];
    }
    
    $query = "SELECT `id`, `parent_id`, `id_real`, `id_full`, `is_link`, `title`, `title_full`, `path_full`, `image_mime`, `sort_order`, `item_count`, `item_count_full`, `tree_level`, `child_id_list`
        FROM `" . $CNCAT["config"]["db"]["prefix"] . "cats` WHERE `parent_id`!=-1 ORDER BY `sort_order_global` ASC";
    $res = $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    //$num = 0;
    $cats = array();

    while ($row = mysql_fetch_assoc($res)) {
        $cats[] = $row;
    }

    if ($CNCAT["config"]["cat_admin_view"] == 1) {
?>
<script type="text/javascript">
$(document).ready(function () {
    var openCatList = [];

    if ($.cookie('admin_open_cat_list')) {
        openCatList = $.cookie('admin_open_cat_list').split(',');
    }

    $('#categories li').each(function () {
        var listItem = $(this);
        var listItemId = $('>>> .id:first', listItem).text();

        $('>> .toggler', listItem).click(function () {
            var toggler = $(this);
            if (toggler.attr('alt') == 'x') return;
            var childList = $('> ul', listItem);

            if (childList.hasClass('invisible')) {
                toggler.attr('src', '<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>expand.gif');
                childList.attr('class', '');

                // save in cookie
                openCatList.push(listItemId);
                var saveValues = [];

                for (var k in openCatList) {
                    if (openCatList[k] == '') continue;
                    saveValues.push(openCatList[k]);
                }

                $.cookie('admin_open_cat_list', saveValues.join(','), { expire: 365 });
            } else {
                toggler.attr('src', '<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>collapse.gif');
                childList.attr('class', 'invisible');

                // remove from cookie
                var saveValues = [];

                for (var k in openCatList) {
                    if (openCatList[k] == '') continue;
                    if (openCatList[k] == listItemId) {
                        delete openCatList[k];
                        continue;
                    }

                    saveValues.push(openCatList[k]);
                }

                $.cookie('admin_open_cat_list', saveValues.join(','), { expire: 365 });
            }
        });
    });
});
</script>

<ul id="categories">
<?php
list($cat) = catSelect("`title`, `id`, `image_mime`", "`parent_id`=-1");
?>
    <li>
        <div class="info level0">
            <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>notoggle.gif" alt="x" class="toggler" />
            <a href="<?php print $CNCAT_ENGINE->url->createUrlCat($cat["id"])?>"><span class="title"><?php print htmlspecialchars($cat["title"])?></span></a>
            &mdash;
            <!-- Controls begin -->
                <?php if (catCanDo("edit", $cat["id"])):?>
                    <a href="index.php?act=cats&mode=edit&id=<?php print $cat["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit.gif" alt="" title="<?php print $CNCAT["lang"]["do_edit"]?>" /></a>
                <?php else:?>
                    <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit_dis.gif" alt="" />
                <?php endif?>
                <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete_dis.gif" alt="" />
                <?php if (catCanDo("add", $cat["id"]) && !$cat["is_link"]):?>
                    <a href="index.php?act=cats&mode=add&id=<?php print $cat["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/add.gif" title="<?php print $CNCAT["lang"]["cat_add"]?>" /></a>
                <?php else:?>
                    <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>add_dis.gif" alt="" />
                <?php endif?>
    
                <?php if (catCanDo("add", $cat["id"]) && !$cat["is_link"]):?>
                    <a href="index.php?act=cats&mode=add&type=link&id=<?php print $cat["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/addl.gif" title="<?php print $CNCAT["lang"]["cat_add_link"]?>" /></a>
                <?php else:?>
                    <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>addl_dis.gif" alt="" />
                <?php endif?>
            <!-- Controls end -->
        </div>
    </li><ul>
    <?php $ul_level = 0; foreach ($cats as $k => $cat):?>
        <li>
            <div class="info level<?php print $cat["tree_level"]?>">
                <?php if (!empty($cat["child_id_list"])):?>
                    <?php if (!in_array($cat["id"], $_open_cat_list)):?>
                        <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>collapse.gif" alt="+" class="toggler" />
                    <?php else:?>
                        <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>expand.gif" alt="-" class="toggler" />
                    <?php endif?>
                <?php else:?>
                    <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>notoggle.gif" alt="x" class="toggler" />
                <?php endif?>
                <a href="<?php print $CNCAT_ENGINE->url->createUrlCat($cat["id"], $cat["path_full"])?>"><span class="title"><?php if ($cat["is_link"]) {?><em><?php print htmlspecialchars($cat["title"])?></em><?php } else {?><?php print htmlspecialchars($cat["title"])?><?php }?></span></a>
                <?php if ($cat["image_mime"]) {?>
                    <img style="vertical-align: middle; margin: 0 5px;" src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_prefix"] . "image.php?cat=" . $cat["id"]?>" alt="" />
                <?php }?>

                &mdash; (<?php print $cat["item_count"] . '/' . $cat["item_count_full"]?>)
                <span style="color: silver;">
                    <?php print !empty($cat["path_full"]) ? ('| ' . htmlspecialchars($cat["path_full"])) : ''?>
                    | <span class="id"><?php print $cat["id"]?></span> |
                </span>
                <!-- Controls begin -->
                    <?php if (catCanDo("edit", $cat["id"])):?>
                        <a href="index.php?act=cats&mode=edit&id=<?php print $cat["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit.gif" alt="" title="<?php print $CNCAT["lang"]["do_edit"]?>" /></a>
                    <?php else:?>
                        <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit_dis.gif" alt="" />
                    <?php endif?>
                    <?php if (catCanDo("del", $cat["id"])):?>
                        <?php if (($cat["item_count_full"] || !empty($cat["child_id_list"])) && !$cat["is_link"]):?>
                            <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete_dis.gif" alt="" />
                        <?php else:?>
                            <a href="index.php?act=cats&mode=del&id=<?php print $cat["id"]?>" onclick="return confirm('<?php print cn_str_replace("%NAME%", htmlspecialchars(cn_str_replace("'", "\'", $cat["title_full"])), $CNCAT["lang"]["really_delete"])?>')"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete.gif" alt="" title="<?php print $CNCAT["lang"]["do_delete"]?>" /></a>
                        <?php endif?>
                    <?php else:?>
                        <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete_dis.gif" alt="" />
                    <?php endif?>
                    <?php if (catCanDo("add", $cat["id"]) && !$cat["is_link"]):?>
                        <a href="index.php?act=cats&mode=add&id=<?php print $cat["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/add.gif" title="<?php print $CNCAT["lang"]["cat_add"]?>" /></a>
                    <?php else:?>
                        <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>add_dis.gif" alt="" />
                    <?php endif?>
        
                    <?php if (catCanDo("add", $cat["id"]) && !$cat["is_link"]):?>
                        <a href="index.php?act=cats&mode=add&type=link&id=<?php print $cat["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/addl.gif" title="<?php print $CNCAT["lang"]["cat_add_link"]?>" /></a>
                    <?php else:?>
                        <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>addl_dis.gif" alt="" />
                    <?php endif?>
                <!-- Controls end -->
            </div>
            <?php if ($cats[$k+1]["tree_level"] > $cat["tree_level"]) { $ul_level++; ?><ul<?php if (!in_array($cat["id"], $_open_cat_list)) print " class=\"invisible\"";?>><?php } else {?></li><?php }?>
            <?php
            if ($cats[$k+1]["tree_level"] < $cat["tree_level"]) {
                for ($i = 0; $i < ($cat["tree_level"] - $cats[$k+1]["tree_level"]); $i++) {
                    $ul_level--;
                    print '</ul></li>';
                }
            }
            ?>
    <?php endforeach?>
    <?php
        if ($ul_level) {
            for ($i = 0; $i < $ul_level; $i++) {
                print "</ul></li>";
            }
        }
    ?>
    </ul></ul>
<?php
    } else {
?>
<table class="list" id="categories">
    <tr>
        <td class="title">&nbsp;</td>
        <td class="title"><?php print $CNCAT["lang"]["category"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["path"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["sort"]?></td>
        <td class="title"><?php print $CNCAT["lang"]["id"]?></td>
        <td class="title">&nbsp;</td>
        <td class="title">&nbsp;</td>
        <td class="title">&nbsp;</td>
        <td class="title">&nbsp;</td>
    </tr>
<?php
        list($cat) = catSelect("`title`, `id`, `image_mime`", "`parent_id`=-1");
?>
    <tr style="cursor: default;" onmouseover="this.style.backgroundColor='#fefede';" onmouseout="this.style.backgroundColor = '#fff';">
        <td class="item" style="height: 30px;">
        <?php if ($cat["image_mime"]) {?>
            <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_prefix"] . "image.php?cat=" . $cat["id"]?>" alt="" />
        <?php }?>
        </td>
        <!--<td class="item" style="white-space: nowrap;" onmousedown="is_edit = true; intv = setInterval('fastEdit(<?php print $cat["id"]?>)', 1000);" onmouseup="is_edit = false; clearInterval(intv);">-->
        <td class="item" style="white-space: nowrap;">
        <?php if (isAdmin()) {?>
            <span id="cat_<?php print $cat["id"]?>_title">
                <a href="<?php print $CNCAT_ENGINE->url->createUrlCat($cat["id"])?>"><?php print htmlspecialchars($cat["title"])?></a>
            </span>
        <?php } else {?>
            <span id="cat_<?php print $cat["id"]?>_title">
                <?php print htmlspecialchars($cat["title"])?>
            </span>
        <?php }?>
            <span style="display: none;" id="cat_<?php print $cat["id"]?>_title_r"><?php print htmlspecialchars($cat["title"])?></span>
        </td>
        <td class="item">/</td>
        <td class="item">0</td>
        <td style="display: none;"></td>
        <td class="item"><?php print $cat["id"]?></td>
        <td class="item">
        <?php if (isAdmin()) {?>
            <a href="index.php?act=cats&mode=edit&id=<?php print $cat["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit.gif" alt="" title="<?php print $CNCAT["lang"]["do_edit"]?>" /></a>
        <?php } else {?>
            <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit_dis.gif" alt="" />
        <?php }?>
        </td>
        <td class="item"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete_dis.gif" alt="" /></td>
        <td class="item">
        <?php if (isAdmin()) {?>
            <a href="index.php?act=cats&mode=add&id=<?php print $cat["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>add.gif" alt="" title="<?php print $CNCAT["lang"]["cat_add"]?>" /></a>
        <?php } else {?>
            <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>add_dis.gif" alt="" />
        <?php }?>
        </td>
        <td class="item">
        <?php if (isAdmin()) {?>
            <a href="index.php?act=cats&mode=add&type=link&id=<?php print $cat["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>addl.gif" alt="" title="<?php print $CNCAT["lang"]["cat_add_link"]?>" /></a>
        <?php } else {?>
            <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>addl_dis.gif" alt="" />
        <?php }?>
        </td>
    </tr>
<?php
        foreach ($cats as $num => $row) {
            $num++;
?>
        <tr style="cursor: default; background: <?php print $num % 2 ? "#f8f8f8" : "#fff"?>;" onmouseover="this.style.backgroundColor='#fefede';" onmouseout="this.style.backgroundColor = '<?php print $num % 2 ? "#f8f8f8" : "#fff"?>';">
            <td class="item" style="height: 30px;">
            <?php if ($row["image_mime"]) {?>
                <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_prefix"] . "image.php?cat=" . $row["id"]?>" alt="" />
            <?php }?>
            </td>
            <td class="item" style="white-space: nowrap;">
                <?php print str_repeat("&nbsp;-&nbsp;&nbsp;", $row["tree_level"])?>
                <?php if (catCanDo("edit", $row["id"])) {?>
                    <span class="tree_level<?php print $row["tree_level"]?>" id="cat_<?php print $row["id"]?>_title">
                        <a href="<?php print $CNCAT_ENGINE->url->createUrlCat($row["id"], $row["path_full"])?>"><?php print htmlspecialchars($row["title"])?></a>
                    </span>
                    </a> (<?php print $row["item_count"] . '/' . $row["item_count_full"]?>)
                <?php } else {?>
                    <span class="tree_level<?php print $row["tree_level"]?>" id="cat_<?php print $row["id"]?>_title">
                        <?php print htmlspecialchars($row["title"])?> 
                    </span>
                <?php }?><br />
                <span style="display: none;" id="cat_<?php print $row["id"]?>_title_r"><?php print htmlspecialchars($row["title"])?></span>
                <?php if ($row["is_link"]) {?>
                    <span style="visibility: hidden;"><?php print str_repeat("&nbsp;-&nbsp;&nbsp;", $row["tree_level"])?></span>
                    <span style="color: #777;"><?php print htmlspecialchars($reals[$row["id"]])?></span>
                    <span style="display: none;" id="cat_<?php print $row["id"]?>_title_r"><?php print htmlspecialchars($row["title"])?></span>
                <?php }?>
            </td>
            <td class="item">/<?php print htmlspecialchars($row["path_full"])?></td>
            <td class="item"><?php print htmlspecialchars($row["sort_order"])?></td>
            <td class="item"><?php print $row["id"]?></td>
            <td class="item">
                <?php if (catCanDo("edit", $row["id"])) {?>
                    <a href="index.php?act=cats&mode=edit&id=<?php print $row["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit.gif" alt="" title="<?php print $CNCAT["lang"]["do_edit"]?>" /></a>
                <?php } else {?>
                    <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit_dis.gif" alt="" />
                <?php }?>
            </td>
            <td class="item">
            <?php if (catCanDo("del", $row["id"])) {?>
                <?php if (($row["item_count_full"] || !empty($row["child_id_list"])) && !$row["is_link"]) {?>
                    <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete_dis.gif" alt="" />
                <?php } else {?>
                    <a href="index.php?act=cats&mode=del&id=<?php print $row["id"]?>" onclick="return confirm('<?php print cn_str_replace("%NAME%", htmlspecialchars(cn_str_replace("'", "\'", $row["title_full"])), $CNCAT["lang"]["really_delete"])?>')"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete.gif" alt="" title="<?php print $CNCAT["lang"]["do_delete"]?>" /></a>
                <?php }?>
            <?php } else {?>
                <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete_dis.gif" alt="" />
            <?php }?>
            </td>
            <td class="item">
            <?php if (catCanDo("add", $row["id"]) && !$row["is_link"]) {?>
                <a href="index.php?act=cats&mode=add&id=<?php print $row["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/add.gif" title="<?php print $CNCAT["lang"]["cat_add"]?>" /></a>
            <?php } else {?>
                <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>add_dis.gif" alt="" />
            <?php }?>
            </td>
            <td class="item">
            <?php if (catCanDo("add", $row["id"]) && !$row["is_link"]) {?>
                <a href="index.php?act=cats&mode=add&type=link&id=<?php print $row["id"]?>"><img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>/addl.gif" title="<?php print $CNCAT["lang"]["cat_add_link"]?>" /></a>
            <?php } else {?>
                <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>addl_dis.gif" alt="" />
            <?php }?>
            </td>
        </tr>
<?php
        }
?>
</table>
<?php
    }
?>
<p>
    <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>edit.gif" alt="" style="vertical-align: middle;" /> - <?php print $CNCAT["lang"]["do_edit"]?>;
    <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>delete.gif" alt="" style="vertical-align: middle;" /> - <?php print $CNCAT["lang"]["do_delete"]?>;
    <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>add.gif" alt="" style="vertical-align: middle;" /> - <?php print $CNCAT["lang"]["cat_add"]?>;
    <img src="<?php print $CNCAT["abs"] . $CNCAT["system"]["dir_engine_images"]?>addl.gif" alt="" style="vertical-align: middle;" /> - <?php print $CNCAT["lang"]["cat_add_link"]?>;
</p>
<?php
/*******************************************************************************
 * INDEX END
 ******************************************************************************/
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
