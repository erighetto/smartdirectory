<!-- Links -->
{TEMPLATE articles_top}
    <table class="items" width="100%">
{/TEMPLATE}

<!--    Template for item. 
        You need to specify all attributes of ITEM variable that used in the template. 
        Only these fields will be fetched from DB for sure. -->
{TEMPLATE article
    (ITEM  [item_id, item_title, item_rating_moder,
            item_favour, item_descr, 
            item_meta_keywords, item_meta_descr,
            item_submit_date,
            item_display_ext,
            link_url, link_target, link_jumps_from, link_jumps_to, link_rating_pr, link_rating_cy,
            ext_text1, ext_text2, ext_text3]    
    )
}
    <tr>
        <td valign="top" width="1">
            {cn_str($CNCAT[item][_number] + $CNCAT[page][start_item_num])}.<br>
            {IF $CNCAT[item][_favicon_url]}
               	<div style="border:0;width:16px;height:16px;margin:2px 2px 0 0;background-image:url({cn_str($CNCAT[item][_favicon_url])})"></div>
            {ENDIF}
        </td>
        <td>
        </td>
        <td valign="top" width="*">
            <a href="{$CNCAT[item][_ext_url]}" class="article"><strong>{cn_str($CNCAT[item][item_title])}</strong></a>

            {if $CNCAT[item][item_favour]>0}<img src="{$THEMEURL}images/favitem.gif" width="15" height="16" alt="*">{endif}
            {if $CNCAT[item][item_favour]>1}<img src="{$THEMEURL}images/favitem.gif" width="15" height="16" alt="*">{endif}

            {cn_date($CNCAT[item][item_submit_date])}
            
            <br>
            
            {cn_str($CNCAT[item][item_descr])} 
            
            {if $CNCAT[item][ext_text1]}
                <br>{$CNCAT[lang][extfield_items_field1]}: {cn_str($CNCAT[item][ext_field1])}
            {endif}
            
            {if $CNCAT[item][ext_text2]}
                <br>{$CNCAT[lang][extfield_items_field2]}: {cn_str($CNCAT[item][ext_field2])}
            {endif}

            {if $CNCAT[item][item_display_ext] == 1}
                <a href="{$CNCAT[item][_ext_url]}">[+]</a>
            {endif}
            <br>
            
            {if $CNCAT[item][link_url]}
                <font color=gray>{cn_url($CNCAT[item][link_url])}</font> 
                {if $CNCAT[config][links_broken_notify]}
                    <span style="color:#707070">[<a href="javascript:baditem('{$CNCAT[item][item_id]}')" title="{$CNCAT[lang][broken_notify]}" style="color:red; text-decoration:none">x</a>]</span>
                {endif}
                <br>
            {endif}
            {IF $CNCAT[item][_control_bar]}
                <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&mode=delete&id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_delete]}</a> |
                <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&mode=asnew&id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_asnew]}</a> |
                <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links_add&mode=forms&edit=1&id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_edit]}</a> |
                <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=check&mode=checkone&id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_check]}</a> |
                {DISPLAY ADMIN_RATING}
            {ENDIF}
        </td>    
    </tr>
    <tr><td colspan="3"></td></tr>
    {IF $CNCAT[banner][items_middle] && $CNCAT[item][_number]==intval($CNCAT[item][_count]/2-1)}
        <tr>
            <td colspan="3" align="center">{$CNCAT[banner][items_middle]}</td>            
        </tr>
    {ENDIF}
{/TEMPLATE}

{TEMPLATE articles_bottom}
    </table>
    {IF $CNCAT[page][cat_item_count]==0 && $CNCAT[banner][items_middle]}<br><center>{$CNCAT[banner][items_middle]}</center>{ENDIF}
{/TEMPLATE}

<!-- Moder rating -->
{TEMPLATE articles_rating_top}{/TEMPLATE}
{TEMPLATE articles_rating_num}<a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&mode=rating&value={$CNCAT[admin][item_rating_value]}&id={$CNCAT[item][item_id]}">{$CNCAT[admin][item_rating_value]}</a>{/TEMPLATE}
{TEMPLATE articles_rating_num_active}<strong>{$CNCAT[admin][item_rating_value]}</strong>{/TEMPLATE}
{TEMPLATE articles_rating_delim} {/TEMPLATE}
{TEMPLATE articles_rating_bottom}{/TEMPLATE}

<!-- New items -->
{TEMPLATE newarticles_top}
    <table class="newitems" width="100%">
    <tr><th>{$CNCAT[lang][new_articles]}</th></tr>
{/TEMPLATE}


<!--    Template for new item. 
        You need to specify all attributes of ITEM variable (see the "item" template). 
-->
{TEMPLATE newarticle
    (ITEM  [item_id, item_title, item_descr, link_url])
}
    <tr>
        <td>
            <b><a target=_blank href="{$CNCAT[item][link]}" class="article">{cn_str($CNCAT[item][item_title])}</a></b><br>
            <small>{cn_str($CNCAT[item][item_descr])}</small>
        </td>
    </tr>
{/TEMPLATE}

{TEMPLATE newarticles_bottom}
    </table>    
{/TEMPLATE}-
