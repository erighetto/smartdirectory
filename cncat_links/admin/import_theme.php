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

if (!isAdmin()) accessDenied();

$_errors = array();

if (isset($_POST["doPost"])) {
    $_dir = isset($_POST["dir"]) ? basename($_POST["dir"]) : "";
    $_db = isset($_POST["db"]) ? $_POST["db"] : "";
    $_prefix = isset($_POST["prefix"]) ? $_POST["prefix"] : "";
    $_theme_name = isset($_POST["theme_name"]) ? $_POST["theme_name"] : "";

    if (empty($_theme_name)) {
        $_errors[] = $CNCAT["lang"]["theme_name_is_empty"];
    }

    $dir = $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_config_themes"] . $_dir;

    if (empty($_dir)) {
        $_errors[] = $CNCAT["lang"]["theme_folder_is_empty"];
    }

    $CNCAT_ENGINE->db->query("USE " . $_db);

    if (mysql_errno() != 0) {
        $_errors[] = $CNCAT["lang"]["specified_db_not_exists"];
    }

    if (!$_errors) {
        if (!is_dir($dir)) {
            @mkdir($dir);
        }

        if (!empty($_dir) && !is_writable($dir)) {
            $_errors[] = $CNCAT["lang"]["theme_folder_not_exists"];
        }
    }

    if (!$_errors) {
        $query = "SELECT * FROM `" . mysql_escape_string($_prefix) . "templates`";
        $res = $CNCAT_ENGINE->db->query($query) or die($CNCAT_ENGINE->displayErrorDB(mysql_error()));
        $old_tpl = array();
        
        while ($row = mysql_fetch_assoc($res)) {
            $old_tpl[$row["name"]] = str_replace(array("%ABS/cat/", "%ABS/", "%ABS"), array("{\$THEMEURL}images/", "{\$CNCAT[abs]}", "{\$CNCAT[abs]}"), $row);
        }
        
        $res = array(
            "404.tpl" => array(),
            "add.tpl" => array(),
            "add_article.tpl" => array(),
            "admin.tpl" => array(),
            "article.tpl" => array(),
            "category.tpl" => array(),
            "common.tpl" => array(),
            "extended.tpl" => array(),
            "extended_article.tpl" => array(),
            "index.tpl" => array(),
            "item.tpl" => array(),
            "js.tpl" => array(),
            "mail.tpl" => array(),
            "map.tpl" => array(),
            "search.tpl" => array(),
            "styles.css" => array(),
            "theme.txt" => array()
        );
        
        $res["common.tpl"]["top"] = str_replace(
            array(
                "%TITLE",
                "%CHARSET",
                "%KEYWORDS"
            ),
            array(
                "{IF \$CNCAT[page][cid] > 0}{cn_str(\$CNCAT[page][cat][title])} / {ENDIF}{cn_str(\$CNCAT[page][title])}",
                "{\$CNCAT[lang][charset]}",
                "{IF \$CNCAT[page][keywords]}<meta name=\"keywords\" content=\"{cn_str(\$CNCAT[page][keywords])}\" />{ENDIF}"
            ),
            $old_tpl["top"]["html"]
        );
        
        $res["common.tpl"]["bottom"] = str_replace(
            array(
                "%TITLE",
                "%COPYRIGHT",
            ),
            array(
                "{IF \$CNCAT[page][cid] > 0}{cn_str(\$CNCAT[page][cat][title])} / {ENDIF}{cn_str(\$CNCAT[page][title])}",
                "{cn_copyright(1)}"
            ),
            $old_tpl["bottom"]["html"]
        );
        
        $res["common.tpl"]["menu"] = str_replace(
            array(
                "%MAINTEXT",
                "%ADDLINKTEXT",
                "%MODERATORSTEXT",
                "add.php",
                "search.php",
                "admin/"
            ),
            array(
                "{\$CNCAT[lang][menu_main]}",
                "{\$CNCAT[lang][menu_add_item]}",
                "{\$CNCAT[lang][menu_admin]}",
                "{$CNCAT["system"]["dir_prefix"]}add.php",
                "{$CNCAT["system"]["dir_prefix"]}search.php",
                "{\$CNCAT[system][dir_admin]}/"
            ),
            $old_tpl["bmenu"]["html"]
        );
        
        $res["common.tpl"]["searchform"] = str_replace(
            array(
                "%QUERYTEXT",
                "%SEARCHTEXT"
            ),
            array(
                "{cn_str(\$CNCAT[page][search_query])}",
                "{cn_str(\$CNCAT[lang][search_submit])}"
            ),
            $old_tpl["searchform"]["html"]
        );
        
        $res["common.tpl"]["pagenav_top"] = "";
        $res["common.tpl"]["pagenav_curpage"] = "";
        $res["common.tpl"]["pagenav_pageitem"] = "";
        $res["common.tpl"]["pagenav_delim1"] = "";
        $res["common.tpl"]["pagenav_delim2"] = "";
        $res["common.tpl"]["pagenav_bottom"] = "";
        
        $res["common.tpl"]["filters_top"] = "";
        $res["common.tpl"]["filter"] = "";
        $res["common.tpl"]["filter_top"] = "";
        $res["common.tpl"]["filtval"] = "";
        $res["common.tpl"]["filtval_delim"] = "";
        $res["common.tpl"]["filter_bottom"] = "";
        $res["common.tpl"]["filters_delim"] = "";
        $res["common.tpl"]["filters_bottom"] = "";
        
        $res["category.tpl"]["cats_top"] = $old_tpl["partstop"]["html"];
        $res["category.tpl"]["cats_column_top"] = str_replace(
            array(
                "%WIDTH"
            ),
            array(
                "{\$CNCAT[page][cat_col_width]}%"
            ),
            $old_tpl["partsdelimtop"]["html"]
        );
        $res["category.tpl"]["cat"] = str_replace(
            array(
                "%CTITLE",
                "%URL",
                "%CCOUNT",
                "%SUBPARTS"
            ),
            array(
                "{cn_str(\$CNCAT[cat][title])}",
                "{cn_str(\$CNCAT[cat][url])}",
                "{\$CNCAT[cat][item_count_full]}",
                "{DISPLAY SUBCATS}"
            ),
            $old_tpl["partsbit"]["html"]
        );
        $res["category.tpl"]["cats_column_bottom"] = $old_tpl["partsdelimbottom"]["html"];
        $res["category.tpl"]["cats_bottom"] = $old_tpl["partsbottom"]["html"];
        $res["category.tpl"]["subcats_top"] = $old_tpl["subpartstop"]["html"];
        $res["category.tpl"]["subcat"] = str_replace(
            array(
                "%CTITLE",
                "%URL"
            ),
            array(
                "{cn_str(\$CNCAT[subcat][title])}",
                "{cn_str(\$CNCAT[subcat][url])}",
            ),
            $old_tpl["subpartsbit"]["html"]
        );
        $res["category.tpl"]["subcats_delim"] = $old_tpl["subpartsdelim"]["html"];
        $res["category.tpl"]["subcats_more"] = $old_tpl["subpartsmore"]["html"];
        $res["category.tpl"]["subcats_bottom"] = $old_tpl["subpartsbottom"]["html"];
        
        $old_tpl["catname"]["html"] = str_replace("%ADMINIFACE", /*"{IF \$CNCAT[cat][_canedit]}(<a href=\"{\$CNCAT[abs]}{\$CNCAT[system][dir_admin]}index.php?act=cats&mode=add&id={\$CNCAT[cat][id]}\"><small>{\$CNCAT[lang][do_submit]}</small></a> <a href=\"{\$CNCAT[abs]}{\$CNCAT[system][dir_admin]}index.php?act=cats&mode=edit&id={\$CNCAT[cat][id]}\"><small>{\$CNCAT[lang][do_edit]}</small></a>){ENDIF}"*/"", $old_tpl["catname"]["html"]);
        list($top, $bottom) = explode("%OTHERTEXT", $old_tpl["catname"]["html"]);
        
        $res["category.tpl"]["catpath_top"] = str_replace("%MAINTEXT", "{\$CNCAT[config][catalog_title]}", $top);
        $res["category.tpl"]["catpath_bottom"] = $bottom;
        
        $res["item.tpl"]["items_top"] = $old_tpl["linkstop"]["html"] . str_replace("%NUM", "{\$CNCAT[page][start_item_num]}", $old_tpl["linksmiddle"]["html"]);
        
        /*
        %ID - идентификатор ссылки
        %TITLE - название сайта
        %GIN - количество переходов с сайта
        %GOUT - количество переходов с каталога на сайт
        %MODERVOTE - оценка администратора
        %RATINGPR - Google PR
        %RATINGCY - Yandex CY
        %BEST - звездочка для избранных ссылок (отображается, если оценка модератора = 10)
        %DESC - описание
        %FULLDESC - полное описание
        %KEYWORDS - ключевые слова
        %RESFIELD1 - дополнительное поле 1
        %RESFIELD2 - дополнительное поле 2
        %RESFIELD3 - дополнительное поле 3
        %EXTINFOLINK - заменяется на шаблон extinfolink, если включены ссылки на расширенное описание ресурсов
        %EXTENDEDINFOURL - ссылка на расширенное описание ресурса
        %URL - URL сайта
        %BROKENTEXT - буква "x" в ссылке [x]
        %ADMINIFACE - элементы управления ссылкой для администратора
        */
        
        $res["item.tpl"]["item"] = str_replace(
            array(
                "%ID",
                "%TITLE",
                "%GIN",
                "%GOUT",
                "%MODERVOTE",
                "%RATINGPR",
                "%RATINGCY",
                "%BEST",
                "%DESC",
                "%FULLDESC",
                "%KEYWORDS",
                "%RESFIELD1",
                "%RESFIELD2",
                "%RESFIELD3",
                "%EXTINFOLINK",
                "%EXTENDEDINFOURL",
                "%URL",
                "%BROKENTEXT",
                "%ADMINIFACE"
            ),
            array(
                "{cn_str(\$CNCAT[item][item_id])}",
                "{cn_str(\$CNCAT[item][item_title])}",
                "{cn_str(\$CNCAT[item][link_jumps_from])}",
                "{cn_str(\$CNCAT[item][link_jumps_to])}",
                "{cn_str(\$CNCAT[item][item_rating_moder])}",
                "{cn_str(\$CNCAT[item][link_rating_pr])}",
                "{cn_str(\$CNCAT[item][link_rating_cy])}",
                "{IF \$CNCAT[item][item_rating_moder] == 10}<img src=\"{\$THEMEURL}images/favitem.gif\" width=\"15\" height=\"16\" alt=\"*\">{ENDIF}",
                "{cn_str(\$CNCAT[item][item_descr])}",
                "{cn_str(\$CNCAT[item][item_descr_full])}",
                "{cn_str(\$CNCAT[item][item_meta_keywords])}",
                "{cn_str(\$CNCAT[item][ext_text1])}",
                "{cn_str(\$CNCAT[item][ext_text2])}",
                "{cn_str(\$CNCAT[item][ext_text3])}",
                "{IF \$CNCAT[item][item_display_ext] == 1}<a href=\"{\$CNCAT[item][_ext_url]}\">[+]</a>{ENDIF}",
                "<a href=\"{\$CNCAT[item][_ext_url]}\">[+]</a>",
                "{cn_str(\$CNCAT[item][link_url])}",
                "{IF \$CNCAT[config][links_broken_notify]}<span style=\"color:#707070\">[<a href=\"javascript:badlink('{\$CNCAT[item][item_id]}')\" title=\"{\$CNCAT[lang][broken_notify]}\" style=\"color:red; text-decoration:none\">x</a>]</span>{ENDIF}",
                ""
            ),
            $old_tpl["linksbit"]["html"]
        );
        $res["item.tpl"]["items_bottom"] = $old_tpl["linksbottom"]["html"];
        $res["item.tpl"]["admin_rating_top"] = "";
        $res["item.tpl"]["admin_rating_num"] = "";
        $res["item.tpl"]["admin_rating_num_active"] = "";
        $res["item.tpl"]["admin_rating_bottom"] = "";
        $res["item.tpl"]["brokenscript"] = str_replace(
            array(
                "%BROKENSURETEXT",
                "%YESTEXT",
                "%NOTEXT"
            ),
            array(
                "{\$CNCAT[lang][broken_sure]}",
                "{\$CNCAT[lang][yes]}",
                "{\$CNCAT[lang][no]}"
            ),
            $old_tpl["brokenscript"]["html"]
        );
        $res["item.tpl"]["newitems_top"] = str_replace("%NEWLINKSTEXT", "{\$CNCAT[lang][new_items]}", $old_tpl["newlinkstop"]["html"]);
        $res["item.tpl"]["newitem"] = str_replace(
            array(
                "%ID",
                "%EXTENDEDINFOURL",
                "%TITLE",
                "%GIN",
                "%GOUT",
                "%MODERVOTE",
                "%DESC75",
                "%DESC",
                "%URL",
                "%RESFIELD1",
                "%RESFIELD2",
                "%RESFIELD3"
            ),
            array(
                "{cn_str(\$CNCAT[item][item_id])}",
                "<a href=\"{\$CNCAT[item][_ext_url]}\">[+]</a>",
                "{cn_str(\$CNCAT[item][item_title])}",
                "{cn_str(\$CNCAT[item][link_jumps_from])}",
                "{cn_str(\$CNCAT[item][link_jumps_to])}",
                "{cn_str(\$CNCAT[item][item_rating_moder])}",
                "{cn_str(\$CNCAT[item][item_descr])}",
                "{cn_str(\$CNCAT[item][item_descr])}",
                "{cn_str(\$CNCAT[item][link_url])}",
                "{cn_str(\$CNCAT[item][ext_text1])}",
                "{cn_str(\$CNCAT[item][ext_text2])}",
                "{cn_str(\$CNCAT[item][ext_text3])}"
            ),
            $old_tpl["newlinkstbit"]["html"]
        );
        $res["item.tpl"]["newitems_bottom"] = $old_tpl["newlinkstbottom"]["html"];
        $res["theme.txt"] = array("name" => $_theme_name, "image" => "screenshot.png", "descr" => "");

        foreach ($res as $filename => $templates) {
            $result = file_get_contents(dirname(__FILE__) . "/base_theme/" . $filename);
        
            foreach ($templates as $name => $text) {
                $result = str_replace("%" . cn_strtoupper($name) . "%", $text, $result);
            }
        
            $filename = $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_config_themes"] . $_dir . "/" . $filename;
            
            if ($f = @fopen($filename, "w+b")) {
                fwrite($f, $result);
                fclose($f);
            } else {
                $_errors[] = cn_str_replace("%FILE%", $filename, $CNCAT["lang"]["file_create_error"]);
                break;
            }
        }
    }

    if (!$_errors) {
        header("Location: index.php?act=import_theme&mode=final");
        exit;
    }
}

include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["import_theme_cncat_2x_3x"]?></h1>
<a href="index.php?act=import"><?php print $CNCAT["lang"]["import"]?></a>
<div class="deline"></div>
<div class="error_box"><?php print $CNCAT["lang"]["import_theme_notify"]?></div>
<div class="deline"></div>
<?php
if (!empty($_errors)) {
    print "<ul class=\"errors\"><li>";
    print implode("</li><li>", $_errors);
    print "</li></ul>";
}

if (isset($_GET["mode"]) && $_GET["mode"] == "final") {
    print "<p><strong>" . $CNCAT["lang"]["import_finish"] . "</strong></p>";
}
?>
<form action="index.php?act=import_theme" method="post">
<table class="form">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["import_theme_from_db"]?></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["theme_name"]?></td><td class="field"><input type="text" class="text" name="theme_name" value="<?php print cn_str($_POST["theme_name"])?>" /></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["theme_folder"]?></td><td class="field"><input type="text" class="text" name="dir" value="<?php print cn_str($_POST["dir"])?>" /></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["database_name"]?></td><td class="field"><input type="text" class="text" name="db" value="<?php print cn_str($_POST["db"])?>" /></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["tables_prefix"]?></td><td class="field"><input type="text" class="text" name="prefix"  value="<?php print isset($_POST["prefix"]) ? cn_str($_POST["prefix"]) : "cncat_";?>" /></tr>
    <tr><td class="deline" colspan="2"></td></tr>
    <tr><td class="submit" colspan="2"><input type="submit" class="submit" name="doPost" value="<?php print $CNCAT["lang"]["do_import"]?>" /></tr>
</table>
</form>
<?php
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
