{TEMPLATE admin_items_begin}
<script type="text/javascript">
<!--
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
//-->
</script>
<table width="100%">
    <tr>
        <td width="100%">
            <label><input type="checkbox" onclick="selectItems(this.checked)" /> {$CNCAT[lang][select_all_links]}</label>
        </td>
        <td class="title" style="text-align:right">
            {$CNCAT[lang][in]}
        </td>
        <td class="title" style="text-align:right">
            {$CNCAT[lang][out]}
        </td>
        <td class="title" style="text-align:right">
            {$CNCAT[lang][pr]}
        </td>
        <td class="title" style="text-align:right">
            {$CNCAT[lang][cy]}
        </td>
        <td class="title" style="text-align:right">
            {$CNCAT[lang][id]}
        </td>
    </tr>
{/TEMPLATE}

{TEMPLATE admin_item(
    ITEM[item_id, item_title, item_status, item_favour, item_insert_date, item_submit_date, item_descr, item_rating_moder, link_url, link_jumps_to, link_jumps_from, link_rating_cy, link_rating_pr, link_chk_work_date, link_chk_work_res, link_chk_back_date, link_chk_back_res, link_favicon_mime, link_favicon_url, link_broken_warning]
)}
    <tr>
        <td class="title" style="white-space: normal;">
            {IF $CNCAT[item][_favicon_url]}
               	<div style="float:left;border:0;width:16px;height:16px;margin:0 2px 0 0;background-image:url({cn_str($CNCAT[item][_favicon_url])})"></div>
            {ENDIF}
            {$CNCAT[item][item_title]}
            <span style="font-weight: normal;">
            {IF $CNCAT[item][item_favour] > 0}
                <img src="{$THEMEURL}images/favitem.gif" alt="" /> x {$CNCAT[item][item_favour]}
            {ENDIF}
            {IF cn_date($CNCAT[item][item_insert_date])}
                / <span title="{$CNCAT[lang][add_date]}">{cn_date($CNCAT[item][item_insert_date])}</span>
            {ENDIF}
            {IF cn_date($CNCAT[item][item_submit_date])}
                / <span title="{$CNCAT[lang][approve_date]}"><strong>{cn_date($CNCAT[item][item_submit_date])}</strong></span>
            {ENDIF}
            </span>
        </td>
        <td class="title" width="35" style="font-weight: normal; text-align:right" title="{$CNCAT[lang][in]}">
            {$CNCAT[item][link_jumps_to]}
        </td>
        <td class="title" width="35" style="font-weight: normal; text-align:right" title="{$CNCAT[lang][out]}">
            {$CNCAT[item][link_jumps_from]}
        </td>
        <td class="title" width="35" style="font-weight: normal; text-align:right" title="{$CNCAT[lang][pr]}">
            {IF $CNCAT[item][link_rating_pr] > 0}{$CNCAT[item][link_rating_pr]}{ELSE}-{ENDIF}
        </td>
        <td class="title" width="35" style="font-weight: normal; text-align:right" title="{$CNCAT[lang][cy]}">
            {IF $CNCAT[item][link_rating_cy] > 0}{$CNCAT[item][link_rating_cy]}{ELSE}-{ENDIF}
        </td>
        <td class="title" width="35" style="font-weight: normal; text-align:right" title="{$CNCAT[lang][id]}">
            {$CNCAT[item][item_id]}
        </td>
    </tr>
    <tr onclick="selectItem({$CNCAT[item][item_id]})" ondblclick="location.href='index.php?act=links_add&mode=forms&edit=1&id={$CNCAT[item][item_id]}';" id="item_{$CNCAT[item][item_id]}_descr" style="cursor: default; background-color: {IF $CNCAT[item][item_status] == 0}#f2f2ff{ENDIF}{IF $CNCAT[item][item_status] == 1}#f2fff2{ENDIF}{IF $CNCAT[item][item_status] == 2}#fff3f3{ENDIF}">
        <td style="padding: 10px;">
            <a href="{cn_str($CNCAT[item][link_url])}" target="_blank"><strong>{cn_str($CNCAT[item][link_url])}</strong></a><br />
            <table><td style="padding: 10px 0;"><strong>{$CNCAT[lang][categories]}:</strong></td><td style="padding: 10px 5px;">{DISPLAY CATS}</td></tr></table>
            <strong>{$CNCAT[lang][descr]}:</strong> {cn_str($CNCAT[item][item_descr])}
        </td>
        <td style="padding: 5px;" nowrap="nowrap" width="220" colspan="5">
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
        </td>
    </tr>
    <tr><td class="deline" colspan="6"></td></tr>
    <tr id="item_{$CNCAT[item][item_id]}_ctrl" style="background-color: {IF $CNCAT[item][item_status] == 0}#f2f2ff{ENDIF}{IF $CNCAT[item][item_status] == 1}#f2fff2{ENDIF}{IF $CNCAT[item][item_status] == 2}#fff3f3{ENDIF}">
    <td colspan="6">
        <table width="100%"><tr><td id="item_{$CNCAT[item][item_id]}_cb_cell" style="padding: 5px;">
            <input type="checkbox" style="padding: 5px;" name="id[]" value="{$CNCAT[item][item_id]}" id="item_{$CNCAT[item][item_id]}_cb" onclick="selectItem({$CNCAT[item][item_id]}, this.checked)" /></td>
        </td><td width="100%" style="padding: 5px; vertical-align: middle;">
        {IF $CNCAT[item][item_status] == 4}
            <nobr><a href="index.php?act=links&mode=destroy&id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_destroy]}</a></nobr>
        {ELSE}
            {IF $CNCAT[item][item_status] == 2}
                <nobr><a href="index.php?act=links&mode=destroy&id={$CNCAT[item][item_id]}" onclick="return confirm('{$CNCAT[lang][really_destroy_link]}');">{$CNCAT[lang][do_destroy]}</a></nobr> &nbsp;&bull;&nbsp;
            {ELSE}
                <nobr><a href="index.php?act=links&mode=delete&id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_delete]}</a></nobr> &nbsp;&bull;&nbsp;
            {ENDIF}
            <nobr>{IF $CNCAT[item][item_status] == 0}{$CNCAT[lang][do_asnew]}{ELSE}<a href="index.php?act=links&mode=asnew&id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_asnew]}</a>{ENDIF}</nobr> &nbsp;&bull;&nbsp;
            <nobr>{IF $CNCAT[item][item_status] == 1}{$CNCAT[lang][do_approve]}{ELSE}<a href="index.php?act=links&mode=approve&id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_approve]}</a>{ENDIF}</nobr> &nbsp;&bull;&nbsp;
            <nobr><a href="index.php?act=links_add&mode=forms&edit=1&id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_edit]}</a></nobr> &nbsp;&bull;&nbsp;
            <nobr>{DISPLAY RATING}</nobr>&nbsp;&bull;&nbsp;
            <nobr><a href="index.php?act=check&mode=checkone&id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_check]}</a></nobr>
            {IF $CNCAT[item][item_check]}
                &nbsp;&bull;&nbsp;<nobr><a href="index.php?act=links&mode=checkdel&id={$CNCAT[item][item_id]}">{$CNCAT[lang][cancel_check]}</a></nobr>
            {ELSE}
                &nbsp;&bull;&nbsp;<nobr><a href="index.php?act=links&mode=checkadd&id={$CNCAT[item][item_id]}">{$CNCAT[lang][into_check_turn]}</a></nobr>
            {ENDIF}
            {IF $CNCAT[item][link_broken_warning]}
                &nbsp;&bull;&nbsp;<nobr><a href="index.php?act=links&mode=nobroken&id={$CNCAT[item][item_id]}">{$CNCAT[lang][reset_broken]}</a></nobr>
            {ENDIF}
        {ENDIF}
        </td></tr></table>
    </td></tr>
    <tr colspan="6"><td>&nbsp;</td></tr>
{/TEMPLATE}

{TEMPLATE admin_items_end}
</table>
{/TEMPLATE}

{TEMPLATE admin_item_cat}<a href="{cn_str($CNCAT[cat][_url])}">{cn_str($CNCAT[cat][title_full])}</a>{/TEMPLATE}
{TEMPLATE admin_item_cat_delim}<br />{/TEMPLATE}

{TEMPLATE admin_item_rating}
{IF $CNCAT[item][item_rating_moder] == $CNCAT[admin][item_rating_value]}
    <strong>{$CNCAT[admin][item_rating_value]}</strong>
{ELSE}
    <a href="index.php?act=links&mode=rating&value={$CNCAT[admin][item_rating_value]}&id={$CNCAT[item][item_id]}">{$CNCAT[admin][item_rating_value]}</a>
{ENDIF}
{/TEMPLATE}
