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
if (isset($_POST["doPost"])) {
    $config = array();
    $config["url_style"]    = abs((int)$_POST["url_style"]);
    if ($config["url_style"] > 3) {
        $config["url_style"] = 3;
    }
    $config["cncat_url"]    = cn_trim($_POST["cncat_url"]);
    $config["use_translit"] = empty($_POST["use_translit"]) ? 0: 1;
    $config["use_ext_links"] = empty($_POST["use_ext_links"]) ? 0: 1;
    
    foreach ($config as $name => $value) {
        $query = "REPLACE `" . $CNCAT["config"]["db"]["prefix"] . "config` SET `name`='" . $name . "', `value`='" . mysql_escape_string($value) . "'";
        $CNCAT_ENGINE->db->query($query) or $CNCAT_ENGINE->displayErrorDB(mysql_error());
    }

    header("Location: index.php?act=urls_opt");
    exit;
} 

if (isset($_GET["doRebuild"])) {
    $step = abs((int)$_GET["rebuild_step"]);
    $begin = abs((int)$_GET["rebuild_from"]);
    $count = abs((int)$_GET["rebuild_count"]);
    $cur = abs((int)$_GET["rebuild_cur"]);  
    if (!isset($_GET["rebuild_count"]))
    {
        $query = "SELECT count(item_id) as count FROM {$CNCAT["config"]["db"]["prefix"]}items";
        $res = $CNCAT_ENGINE->db->query ($query, "Translit rebuild count") or $CNCAT_ENGINE->displayErrorDB (mysql_error());
        $row = mysql_fetch_assoc($res);
        $count = $row['count'];
        $count = $count - $begin > 0? $count - $begin: 0;
    }
    $query = "SELECT item_id, item_title FROM {$CNCAT["config"]["db"]["prefix"]}items ORDER BY item_id LIMIT {$begin}, {$step}";
    $res = $CNCAT_ENGINE->db->query ($query, "Translit rebuild") or $CNCAT_ENGINE->displayErrorDB (mysql_error());
    $message = "";
    if (mysql_num_rows($res)>0)
    {
        $CNCAT['page']['rebuild_redirect_mess'] = sprintf($CNCAT['lang']['rebuild_redirect_mess'], $cur, $count);
        $CNCAT['page']['rebuild_redirect_url']  = "index.php?act=urls_opt&doRebuild=1&rebuild_step=". $step 
                                                . "&rebuild_count=" . $count . "&rebuild_cur=" . ($cur + $step) 
                                                . "&rebuild_from=" . ($begin+$step);

        $message = $CNCAT_ENGINE->tpl->renderTemplate("rebuild_redirect"); 
        while ($item = mysql_fetch_assoc($res))
        {
            $query = "UPDATE ".$CNCAT["config"]["db"]["prefix"]."items SET 
                item_title_translite='" . mysql_escape_string(cn_translitEncode($item['item_title'])) . "' where item_id={$item['item_id']}";
            $CNCAT_ENGINE->db->query ($query) or $CNCAT_ENGINE->displayErrorDB (mysql_error());
        }
        if ($count < $cur) {
            header("Location: index.php?act=urls_opt&rebuild_done=1");
            exit;
        }
        else
            header("Refresh: 1; {$CNCAT['page']['rebuild_redirect_url']}");
    }
    else {
        header("Location: index.php?act=urls_opt&rebuild_done=1");
        exit;
    }
    include_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
    echo $message;   
        
}else{
include_once $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "top.php";
?>
<h1><?php print $CNCAT["lang"]["settings"]?> / <?php print $CNCAT["lang"]["settings_urls"]?></h1>
<div class="ok_box"><?php print $CNCAT["lang"]["url_config_note"]?></div>
<form action="index.php?act=urls_opt" method="post">
<table class="form">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["setting_header_urls"]?></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["cncat_url"]?></td>
        <td class="field">
          <input type="text" class="text" name="cncat_url" value="<?php print htmlspecialchars($CNCAT["config"]["cncat_url"])?>" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["use_ext_links"]?></td>
        <td class="field">
          <input type="checkbox" name="use_ext_links" <?php print !empty($CNCAT["config"]["use_ext_links"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["url_style"]?></td>
        <td class="field">
            <select name="url_style">
                <option value="0" <?php print $CNCAT["config"]["url_style"] == 0 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["url_style_default"]?></option>
                <option value="1" <?php print $CNCAT["config"]["url_style"] == 1 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["url_style_old"]?></option>
                <option value="2" <?php print $CNCAT["config"]["url_style"] == 2 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["url_style_new"]?></option>
                <option value="3" <?php print $CNCAT["config"]["url_style"] == 3 ? "selected=\"selected\"" : ""?>><?php print $CNCAT["lang"]["url_style_furl"]?></option>
            </select>
        </td>
    </tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["use_translit"]?></td>
        <td class="field">
          <input type="checkbox" name="use_translit" <?php print !empty($CNCAT["config"]["use_translit"]) ? "checked=\"checked\"" : ""?> /></td></tr>
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="submit" colspan="2"><input type="submit" name="doPost" class="submit" value="<?php print $CNCAT["lang"]["do_save"]?>" /></td></tr>
</table>
</form>

<?php if ($_GET["rebuild_done"])
      print "<div class=\"ok_box\">{$CNCAT["lang"]["rebuild_done"]}</div>";
?>

<form action="index.php" method="get">
<input type="hidden" name="doRebuild" value="1" />
<input type="hidden" name="act" value="urls_opt" />
<table class="form">
    <tr><td class="title" colspan="2"><?php print $CNCAT["lang"]["settings_rebuild"]?></td>
    <tr><td class="name"><?php print $CNCAT["lang"]["rebuild_step"]?></td>
        <td class="field">
          <input type="text" class="text" name="rebuild_step" value="200" /></td></tr>
    <tr><td class="name"><?php print $CNCAT["lang"]["rebuild_from"]?></td>
        <td class="field">
          <input type="text" class="text" name="rebuild_from" value="0" /></td></tr>    
    <tr><td class="deline" colspan="2"></td>
    <tr><td class="submit" colspan="2"><input type="submit" class="submit" value="<?php print $CNCAT["lang"]["rebuild_begin"]?>" /></td></tr>
</table>
</form>
<?php
}
include $CNCAT["system"]["dir_root"] . $CNCAT["system"]["dir_admin"] . "bottom.php";
?>
