{TEMPLATE admin_items_begin}
<script type="text/javascript" src="{$CNCAT[abs]}{$CNCAT[system][dir_engine]}scripts/jquery/jquery.js"></script>
<script type="text/javascript">
<!--
var act_d ='{$CNCAT[lang][confirm_delete]}';
var act_i ='{$CNCAT[lang][confirm_isolation]}';
var act_s ='{$CNCAT[lang][confirm_spam]}';
function itemAction(action, item_id, replace) {
    var actions = {
        'destroy':  { 'act': 'links', 'mode': 'destroy', 'id': item_id },
        'delete':   { 'act': 'links', 'mode': 'delete', 'id': item_id, 'comment':$('#_comment_'+item_id).val() },
        'black':    { 'act': 'links', 'mode': 'delete', 'id': item_id, 'black': 1, 'comment':$('#_comment_'+item_id).val() },
        'asnew':    { 'act': 'links', 'mode': 'asnew', 'id': item_id },
        'approve':  { 'act': 'links', 'mode': 'approve', 'id': item_id },
        'check':    { 'act': 'check', 'mode': 'checkone', 'id': item_id },
        'checkadd': { 'act': 'links', 'mode': 'checkadd', 'id': item_id },
        'checkdel': { 'act': 'links', 'mode': 'checkdel', 'id': item_id },
        'nobroken': { 'act': 'links', 'mode': 'nobroken', 'id': item_id },
        'isolation': { 'act': 'links', 'mode': 'isolation', 'id': item_id, 'comment':$('#_comment_'+item_id).val() }
    };

    $('#item_' + item_id + ' .control_locker').css('visibility', 'visible');

    if (replace) {
        items_browsers[item_id] = '';
    
        $('#browser_' + item_id).remove();
        $('#loader_' + action + '_' + item_id).css('display', 'inline');

        $.get('index.php', actions[action], function () {
            $.getJSON(location.href, function (data) {
                $('#loader_' + action + '_' + item_id).css('display', 'none');

                // Hide old item
                $('#item_' + item_id).slideUp(500);

                if (data.length) {
                    // Display new item
                    var table = $(data[0]);
                    $('#last').before(table.css('display', 'none'));
                    table.slideDown(500);

                    // Update pagebar
                    $('table.pagenav').replaceWith(data[1]);
                }
                    
                // Update counters
                updateCounters();
                updateBrowser(item_id);
            });
        });
    } else {
        var loader = $('<img src="{$CNCAT[abs]}{$CNCAT[system][dir_engine]}images/load_medium.gif" style="position: absolute; margin-top: -100px; margin-left: 40%;" />');
        $('#item_' + item_id).fadeTo(500, 0.3).after(loader);

        $.get('index.php', actions[action], function (data) {
            loader.remove();
            $('#item_' + item_id).replaceWith($(data));
            updateCounters();
            updateBrowser(item_id);
        });
    }
}
function actionConfirm(action,item_id, replace)
{
    var act_text = act_d;
     
    if (action == 'isolation')
      act_text = act_i; 
    else if (action == 'black')
      act_text = act_s;
       
    if ($('#action_confirm_'+item_id).attr('id')) {
        $('#action_confirm_'+item_id).remove();
    }
    $('<div id="action_confirm_' + item_id + '" style="padding: 0pt 10px 10px;">' + act_text + ': </div>').insertAfter('#item_' + item_id + '_tab').slideDown(500);
    $('<p><textarea style="height: 100px;width:60%;" id="_comment_'+item_id+'" /></p>').appendTo($('#action_confirm_'+item_id));
    $('<input type="submit" value="{$CNCAT[lang][do_continue]}"/>').click(function(){itemAction(action, item_id, replace);return false;}).appendTo($('#action_confirm_'+item_id));
}

function updateCounters() {
    $.getJSON('index.php?act=links&mode=counters', function (counter) {
        $('#links0_count').text(counter.links0);
        $('#links1_count').text(counter.links1);
        $('#links2_count').text(counter.links2);
        $('#links4_count').text(counter.links4);
        $('#links5_count').text(counter.links5);
        $('#broken_count').text(counter.broken);
        $('#check_count').text(counter.check);
    });
}

function itemRate(item_id, val) {
    $('#item_' + item_id + ' .control_locker').css('visibility', 'visible');

    var loader = $('<img src="{$CNCAT[abs]}{$CNCAT[system][dir_engine]}images/load_medium.gif" style="position: absolute; margin-top: -100px; margin-left: 40%;" />');
    $('#item_' + item_id).fadeTo(500, 0.3).after(loader);

    $.get('index.php', { act: 'links', mode: 'rating', id: item_id, value: val }, function (data) {
        loader.remove();
        $('#item_' + item_id).replaceWith($(data));
    });
}

var items_bc = new Array();

function selectItem(id, checked) {
    cb = document.getElementById('item_' + id + '_cb');
    descr = document.getElementById('item_' + id + '_descr');

    if (!items_bc[id]) {
        items_bc[id] = descr.style.backgroundColor;
    }

    if (checked == undefined) {
        cb.checked = !cb.checked;
    } else {
        cb.checked = checked;
    }

    if (cb.checked) {
        bgcolor = '#fefece';
    } else {
        bgcolor = items_bc[id];
    }

    descr.style.backgroundColor = bgcolor;
    document.getElementById('item_' + id + '_cb_cell').style.backgroundColor = bgcolor;
}

function selectItems(checked) {
    var a = document.getElementsByTagName("input");

    for (i = 0; i < a.length; i++) {
	    var obj = a.item(i);
	    var oid = obj.id;

	    if (oid.substr(0, 5) == 'item_') {
	        id = oid.substr(5);
	        id = id.substr(0, id.length - 3);
	        obj.checked = checked;

            descr = document.getElementById('item_' + id + '_descr');

            if (!items_bc[id]) {
                items_bc[id] = descr.style.backgroundColor;
            }

            if (checked) {
                bgcolor = '#fefece';
            } else {
                bgcolor = items_bc[id];
            }
        
            descr.style.backgroundColor = bgcolor;
            document.getElementById('item_' + id + '_cb_cell').style.backgroundColor = bgcolor;
        }
	}
}

function openBrowser(url, item_id) {
    if ($('#browser_' + item_id).attr('id')) {
        $('#browser_' + item_id).remove();
    } else {
        $('<iframe src="' + url + '" class="browser" id="browser_' + item_id + '"></iframe>').insertAfter('#item_' + item_id).slideDown(500);
        
    }
}

function updateBrowser(item_id) {
    $('#browser_' + item_id).attr('src', $('#browser_' + item_id).attr('src'));
}

var items_browsers = {};

function openAllBrowsers() {
    for (var item_id in items_browsers) {
        if (items_browsers[item_id] != '') {
            openBrowser(items_browsers[item_id], item_id);
        }
    };
};
//-->
</script>
<style>
/*table td {
    border: 1px solid red;
}*/
</style>
<table width="100%" id="items">
    <tr><td colspan="6"><table width="100%">
        <tr><td>
            <input type="checkbox" id="select_all_top" onclick="selectItems(this.checked); document.getElementById('select_all_bottom').checked=this.checked;" /> <label for="select_all_top">{$CNCAT[lang][select_all_links]}</label>
            &nbsp; <input type="button" onclick="openAllBrowsers()" value="{$CNCAT[lang][browse_all_items]}" />
        </td>
        <td class="title" style="text-align:right; width: 40px; border-bottom: 0;">
            {$CNCAT[lang][in]}
        </td>
        <td class="title" style="text-align:right; width: 40px; border-bottom: 0;">
            {$CNCAT[lang][out]}
        </td>
        <td class="title" style="text-align:right; width: 40px; border-bottom: 0;">
            {$CNCAT[lang][pr]}
        </td>
        <td class="title" style="text-align:right; width: 40px; border-bottom: 0;">
            {$CNCAT[lang][cy]}
        </td>
        <td class="title" style="text-align:right; width: 40px; border-bottom: 0;">
            {$CNCAT[lang][id]}
        </td></tr>
    </table></td></tr>
    <tr><td colspan="6">
{/TEMPLATE}

{TEMPLATE admin_item(
    ITEM[item_id, item_type, item_title_translite, item_title, item_status, item_favour, item_insert_date, item_submit_date, item_descr, item_rating_moder, link_url, link_jumps_to, link_jumps_from, link_rating_cy, link_rating_pr, link_chk_work_date, link_chk_work_res, link_chk_back_date, link_chk_back_res, link_favicon_mime, link_favicon_url, link_broken_warning, item_com_count, item_insert_type, item_submit_type]
)}
{IF $CNCAT[item][item_type] == 0}<script type="text/javascript">items_browsers[{$CNCAT[item][item_id]}] = "{cn_str($CNCAT[item][link_url])}";</script>{ENDIF}
<div id="item_{$CNCAT[item][item_id]}">
    <table style="width: 100%;" >
    <tr class="item_top">
        <td class="{IF $CNCAT[item][item_type] == 0}title{ELSE}title_article{ENDIF}">
            {IF $CNCAT[item][_favicon_url]}
               	<div style="float: left; border: 0; width: 16px; height: 16px; margin-right: 4px; background-image: url({cn_str($CNCAT[item][_favicon_url])})"></div>
            {ENDIF}
            <a href="{cn_str($CNCAT[item][_ext_url])}#comments">{cn_str($CNCAT[item][item_title])}</a>
            <span style="font-weight: normal;">
            {IF $CNCAT[item][item_favour] > 0}
                <img src="{$THEMEURL}images/favitem.gif" alt="" /> x {$CNCAT[item][item_favour]}
            {ENDIF}
            {IF cn_date($CNCAT[item][item_insert_date])}
                / <span title="{$CNCAT[lang][add_date]}">{cn_date($CNCAT[item][item_insert_date])}</span>
            {ENDIF}
            {IF cn_date($CNCAT[item][item_submit_date])}
                / <span title="{$CNCAT[lang][approve_date]}">{cn_date($CNCAT[item][item_submit_date])}</span>
            {ENDIF}
            </span>
        </td>
        <td style="width: 40px;" class="{IF $CNCAT[item][item_type] == 0}title{ELSE}title_article{ENDIF}" title="{$CNCAT[lang][in]}">
            {$CNCAT[item][link_jumps_to]}
        </td>
        <td style="width: 40px;" class="{IF $CNCAT[item][item_type] == 0}title{ELSE}title_article{ENDIF}" title="{$CNCAT[lang][out]}">
            {$CNCAT[item][link_jumps_from]}
        </td>
        <td style="width: 40px;" class="{IF $CNCAT[item][item_type] == 0}title{ELSE}title_article{ENDIF}" title="{$CNCAT[lang][pr]}">
            {IF $CNCAT[item][link_rating_pr] > 0}{$CNCAT[item][link_rating_pr]}{ELSE}-{ENDIF}
        </td>
        <td style="width: 40px;" class="{IF $CNCAT[item][item_type] == 0}title{ELSE}title_article{ENDIF}" title="{$CNCAT[lang][cy]}">
            {IF $CNCAT[item][link_rating_cy] > 0}{$CNCAT[item][link_rating_cy]}{ELSE}-{ENDIF}
        </td>
        <td style="width: 40px;" class="{IF $CNCAT[item][item_type] == 0}title{ELSE}title_article{ENDIF}" itle="{$CNCAT[lang][id]}">
            {$CNCAT[item][item_id]}
        </td>
    </tr>

    <tr onclick="selectItem({$CNCAT[item][item_id]})" ondblclick="location.href='index.php?act=links_add&mode=forms&edit=1&id={$CNCAT[item][item_id]}';" id="item_{$CNCAT[item][item_id]}_descr" style="cursor: default; background-color: {IF $CNCAT[item][item_status] == 0}#f2f2ff{ENDIF}{IF $CNCAT[item][item_status] == 1}#f2fff2{ENDIF}{IF $CNCAT[item][item_status] == 2}#fff3f3{ENDIF}{IF $CNCAT[item][item_status] == 5}#F5F5DC{ENDIF}">
        <td style="padding: 0 10px 10px 10px;" style="width: 100%;">
            {IF $CNCAT[item][link_url]}
                <div style="padding-top: 10px;"><a href="{cn_str($CNCAT[item][link_url])}" target="_blank"><strong>{cn_str($CNCAT[item][link_url])}</strong></a></div>
            {ENDIF}
            <table><tr><td style="padding: 10px 0;"><strong>{$CNCAT[lang][categories]}:</strong></td><td style="padding: 10px 5px;">{DISPLAY CATS}</td></tr></table>
            <div><strong>{$CNCAT[lang][descr]}:</strong> {cn_str($CNCAT[item][item_descr])}</div>
            {IF $CNCAT[item][action_comm]}<div><strong>{$CNCAT[lang][comment]}:</strong> {cn_str($CNCAT[item][action_comm])}</div>{ENDIF}
            <p><a href="{cn_str($CNCAT[item][_ext_url])}#comments">{$CNCAT[lang][comments]}: {$CNCAT[item][item_com_count]}</a></p>
        </td>
        <td style="padding: 5px;" nowrap="nowrap" colspan="5">
            <div style="width: 235px;"></div>
            <table width="100%">
                <tr><td width="50%">
                   {IF $CNCAT[item][link_chk_work_date] != '0000-00-00 00:00:00'}
                        <img src="{$CNCAT[abs]}{$CNCAT[system][dir_engine_images]}{IF !$CNCAT[item][link_chk_work_res]}not{ENDIF}workl.gif" alt="" title="{$CNCAT[lang][link_work]}: {IF $CNCAT[item][link_chk_work_res]}{$CNCAT[lang][yes]}{ELSE}{$CNCAT[lang][no]}{ENDIF}" style="vertical-align: middle;" />
                        <strong>{cn_date($CNCAT[item][link_chk_work_date])}</strong>
                   {ENDIF}
                </td><td width="50%">
                   {IF $CNCAT[item][link_chk_back_date] != '0000-00-00 00:00:00'}
                        <img src="{$CNCAT[abs]}{$CNCAT[system][dir_engine_images]}{IF !$CNCAT[item][link_chk_back_res]}not{ENDIF}backl.gif" alt="" title="{$CNCAT[lang][link_back_exists]}: {IF $CNCAT[item][link_chk_back_res]}{$CNCAT[lang][yes]}{ELSE}{$CNCAT[lang][no]}{ENDIF}" style="vertical-align: middle;" />
                        <strong>{cn_date($CNCAT[item][link_chk_back_date])}</strong>
                   {ENDIF}
                </td></tr>
            </table>
            <p>
                <strong>{$CNCAT[lang][insert_by]}:</strong>
                {IF $CNCAT[item][item_insert_type] == 1}{$CNCAT[lang][by_user]}{ENDIF}
                {IF $CNCAT[item][item_insert_type] == 2}{$CNCAT[lang][by_admin]}{ENDIF}
                {IF $CNCAT[item][item_insert_type] == 3}{$CNCAT[lang][by_robot]}{ENDIF}
                {IF $CNCAT[item][item_status] == 1}
                    <br /><strong>{$CNCAT[lang][submit_by]}:</strong>
                    {IF $CNCAT[item][item_submit_type] == 0}?{ENDIF}
                    {IF $CNCAT[item][item_submit_type] == 1}{$CNCAT[lang][by_admin]}{ENDIF}
                    {IF $CNCAT[item][item_submit_type] == 2}{$CNCAT[lang][at_insert]}{ENDIF}
                    {IF $CNCAT[item][item_submit_type] == 3}{$CNCAT[lang][at_check]}{ENDIF}
                {ENDIF}
            </p>
            <p><a href="index.php?act=actlog&item_id={$CNCAT[item][item_id]}" onclick="openBrowser('index.php?act=actlog&item_id={$CNCAT[item][item_id]}&compact=1', {$CNCAT[item][item_id]}); return false;">{$CNCAT[lang][action_log]}</a></p>
        </td>
    </tr>

    <tr><td class="deline" colspan="6"></td></tr>

    <tr id="item_{$CNCAT[item][item_id]}_ctrl" style="background-color: {IF $CNCAT[item][item_status] == 0}#f2f2ff{ENDIF}{IF $CNCAT[item][item_status] == 1}#f2fff2{ENDIF}{IF $CNCAT[item][item_status] == 2}#fff3f3{ENDIF}{IF $CNCAT[item][item_status] == 5}#F5F5DC{ENDIF}">
    <td colspan="6" style="oberflow: hidden;">
        <table width="100%" id="item_{$CNCAT[item][item_id]}_tab"><tr><td id="item_{$CNCAT[item][item_id]}_cb_cell" style="padding: 5px;">
            <input type="checkbox" style="padding: 5px;" name="id[]" value="{$CNCAT[item][item_id]}" id="item_{$CNCAT[item][item_id]}_cb" onclick="selectItem({$CNCAT[item][item_id]}, this.checked)" />
        </td><td width="100%" style="padding: 5px 10px; vertical-align: middle;" class="control" id="item_control_{$CNCAT[item][item_id]}">
        {IF $CNCAT[item][item_status] == 4}
            <nobr><a href="" onclick="if (confirm('{$CNCAT[lang][really_destroy_link]}')) { itemAction('destroy', {$CNCAT[item][item_id]}, 1); };  return false;">{$CNCAT[lang][do_destroy]}</a> 
                <img src="{$CNCAT[abs]}{$CNCAT[system][dir_engine]}images/load_small.gif" class="loader_small" id="loader_destroy_{$CNCAT[item][item_id]}" /></nobr>
        {ELSE}
            {IF $CNCAT[item][item_status] == 2}
                <nobr><a href="" onclick="if (confirm('{$CNCAT[lang][really_destroy_link]}')) { itemAction('destroy', {$CNCAT[item][item_id]}, 1); };  return false;">{$CNCAT[lang][do_destroy]}</a> 
                    <img src="{$CNCAT[abs]}{$CNCAT[system][dir_engine]}images/load_small.gif" class="loader_small" id="loader_destroy_{$CNCAT[item][item_id]}" /></nobr> &nbsp;&bull;&nbsp;
            {ELSE}
                <nobr><a href="" onclick="actionConfirm('delete', {$CNCAT[item][item_id]}, {IF $CNCAT[admin][act] == 'links'}1{ELSE}0{ENDIF}); return false;">{$CNCAT[lang][do_delete]}</a> 
                    <img src="{$CNCAT[abs]}{$CNCAT[system][dir_engine]}images/load_small.gif" class="loader_small" id="loader_delete_{$CNCAT[item][item_id]}" /></nobr> &nbsp;&bull;&nbsp;
            {ENDIF}
       {IF $CNCAT[item][item_status] != 5}
          <nobr><a href="" onclick="actionConfirm('isolation', {$CNCAT[item][item_id]}, {IF $CNCAT[admin][act] == 'links'}1{ELSE}0{ENDIF}); return false;">{$CNCAT[lang][do_isolation]}</a>
          <img src="{$CNCAT[abs]}{$CNCAT[system][dir_engine]}images/load_small.gif" class="loader_small" id="loader_isolation_{$CNCAT[item][item_id]}" /></nobr> &nbsp;&bull;&nbsp;
       {ENDIF}
            <nobr>{IF $CNCAT[item][item_status] == 0}{$CNCAT[lang][do_asnew]}{ELSE}<a href="" onclick="itemAction('asnew', {$CNCAT[item][item_id]}, {IF $CNCAT[admin][act] == 'links'}1{ELSE}0{ENDIF}); return false;">{$CNCAT[lang][do_asnew]}</a> <img src="{$CNCAT[abs]}{$CNCAT[system][dir_engine]}images/load_small.gif" alt="" style="vertical-align: middle; display: none;" id="loader_asnew_{$CNCAT[item][item_id]}" />{ENDIF}</nobr> &nbsp;&bull;&nbsp;
            <nobr>{IF $CNCAT[item][item_status] == 1}{$CNCAT[lang][do_approve]}{ELSE}<a href="" onclick="itemAction('approve', {$CNCAT[item][item_id]}, {IF $CNCAT[admin][act] == 'links'}1{ELSE}0{ENDIF}); return false;">{$CNCAT[lang][do_approve]}</a>{ENDIF}</nobr> <img src="{$CNCAT[abs]}{$CNCAT[system][dir_engine]}images/load_small.gif" alt="" style="vertical-align: middle; display: none;" id="loader_approve_{$CNCAT[item][item_id]}" />&nbsp;&bull;&nbsp;
            <nobr><a href="index.php?act={IF $CNCAT[item][item_type] == 1}articles{ELSE}links{ENDIF}_add&mode=forms&edit=1&id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_edit]}</a></nobr> &nbsp;&bull;&nbsp;
            <nobr>{DISPLAY RATING}</nobr> &nbsp;&bull;&nbsp;
            <nobr><a href="" onclick="itemAction('check', {$CNCAT[item][item_id]}, 0); return false;">{$CNCAT[lang][do_check]}</a></nobr>
            {IF $CNCAT[item][item_check]}
                &nbsp;&bull;&nbsp; <nobr><a href="" onclick="itemAction('checkdel', {$CNCAT[item][item_id]}, {IF $CNCAT[admin][act] == 'check'}1{ELSE}0{ENDIF}); return false;">{$CNCAT[lang][cancel_check]}</a>
                    <img src="{$CNCAT[abs]}{$CNCAT[system][dir_engine]}images/load_small.gif" class="loader_small" id="loader_checkdel_{$CNCAT[item][item_id]}" /></nobr>
            {ELSE}
                &nbsp;&bull;&nbsp; <nobr><a href="" onclick="itemAction('checkadd', {$CNCAT[item][item_id]}, 0); return false;">{$CNCAT[lang][into_check_turn]}</a></nobr>
            {ENDIF}
            {IF $CNCAT[item][link_broken_warning]}
                &nbsp;&bull;&nbsp; <nobr><a href="" onclick="itemAction('nobroken', {$CNCAT[item][item_id]}, {IF $CNCAT[admin][act] == 'broken'}1{ELSE}0{ENDIF}); return false;">{$CNCAT[lang][reset_broken]}</a></nobr>
            {ENDIF}
        {ENDIF}
            {IF $CNCAT[item][item_type] == 0}&nbsp;&bull;&nbsp; <a href="javascript: openBrowser('{$CNCAT[item][link_url]}', {$CNCAT[item][item_id]})">{$CNCAT[lang][item_browse]}</a>{ENDIF}
            &nbsp;&bull;&nbsp; <a href="" onclick="actionConfirm('black', {$CNCAT[item][item_id]}, {IF $CNCAT[admin][act] == 'links'}1{ELSE}0{ENDIF}); return false;">{$CNCAT[lang][to_spam]}</a>
        </td></tr>
        <tr><td colspan="2"><div class="control_locker"></div></td></tr></table>
    </td></tr>
    <tr><td colspan="6">&nbsp;</td></tr>
    </table>
</div>
{/TEMPLATE}

{TEMPLATE admin_items_end}
    <div id="last"></div>
    </td></tr><tr>
        <td colspan="6">
            <input type="checkbox" id="select_all_bottom" onclick="selectItems(this.checked);document.getElementById('select_all_top').checked=this.checked;" /> <label for="select_all_bottom">{$CNCAT[lang][select_all_links]}</label>
        </td>
    </tr>
</table>
{/TEMPLATE}

{TEMPLATE admin_item_cat}<a href="{cn_str($CNCAT[cat][_url])}">{cn_str($CNCAT[cat][title_full])}</a>{/TEMPLATE}
{TEMPLATE admin_item_cat_delim}<br />{/TEMPLATE}

{TEMPLATE admin_item_rating}
{IF $CNCAT[item][item_rating_moder] == $CNCAT[admin][item_rating_value]}
    <strong>{$CNCAT[admin][item_rating_value]}</strong>
{ELSE}
    <a href="" onclick="itemRate({$CNCAT[item][item_id]}, {$CNCAT[admin][item_rating_value]}); return false;">{$CNCAT[admin][item_rating_value]}</a>
{ENDIF}
{/TEMPLATE}

{TEMPLATE admin_table_items_bottom}
<div id="item_table_bottom" class="deline"></div>
<script type="text/javascript">
function formSubmit(_this, mode, confirm)
{
    _this.form.action='index.php?act=links&mode=' + mode; 
    
    if ($('#form_confirm').attr('id')) {
        $('#form_confirm').remove();
    }
    var act_text = act_d;
     
    if (mode == 'isolation')
      act_text = act_i; 
    else if (mode == 'black')
      act_text = act_s;
      
    if (confirm == 1)  
    {
        $('<div id="form_confirm" style="padding: 0pt 10px 10px;">'+act_text+': </div>').insertAfter('#item_table_bottom').slideDown(500);
        $('<p><textarea style="height: 100px;width:60%;" id="comment" name="comment"/></p>').appendTo($('#form_confirm'));
        $('<input type="submit" value="{$CNCAT[lang][do_continue]}"/>').click(function(){_this.form.submit();return false;}).appendTo($('#form_confirm'));
    }
    else
        _this.form.submit();
}
</script>
<p>
    <label><input type="radio" name="global" value="0" checked="checked" />{$CNCAT[lang][with_all_select]}</label>
    <label><input type="radio" name="global" value="1" />{$CNCAT[lang][with_all_find]}</label>
</p>
<p>

    {IF $CNCAT[page][type] == 2 || $CNCAT[page][type] == 4}
        <input type="button" value="{$CNCAT[lang][do_destroy]}" class="submit" onclick="if (confirm('{$CNCAT[lang][really_destroy_links]}')) { formSubmit(this,'destroy', 0); }" />
    {ELSE}
        <input type="button" value="{$CNCAT[lang][do_delete]}" class="submit" onclick="formSubmit(this,'delete', 1)" />
    {ENDIF}

    {IF $CNCAT[page][type] != 4}
        <input type="button" value="{$CNCAT[lang][do_asnew]}" class="submit" onclick="formSubmit(this,'asnew', 0)" />
        <input type="button" value="{$CNCAT[lang][do_approve]}" class="submit" onclick="formSubmit(this,'approve', 0)" />
        <input type="button" value="{$CNCAT[lang][into_check_turn]}" class="submit" onclick="formSubmit(this,'checkadd', 0)" />{ENDIF}
    {IF $CNCAT[page][type] != 5}
        <input type="button" value="{$CNCAT[lang][do_isolation]}" class="submit" onclick="formSubmit(this,'isolation', 1)" />
    {ENDIF}
    {IF $_GET[act] == 'broken'}
        <input type="button" value="{$CNCAT[lang][reset_broken]}" class="submit" onclick="formSubmit(this,'nobroken', 0)" />
    {ENDIF}
</p>
</form>
{/TEMPLATE}
{TEMPLATE rebuild_redirect}
<div class="ok_box">
  {$CNCAT[page][rebuild_redirect_mess]} <br />
  <a href="{$CNCAT[page][rebuild_redirect_url]}">
    {$CNCAT[page][rebuild_redirect_url]}</a>
</div>
{/TEMPLATE}
