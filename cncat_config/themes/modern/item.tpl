{TEMPLATE items_top}
                <div> 
{/TEMPLATE}
{TEMPLATE item 
    (ITEM  [item_id, item_title, item_rating_moder, item_rating_users,
            item_favour, item_descr, 
            item_meta_keywords, item_meta_descr,
            item_submit_date,
            item_display_ext,
            link_url, link_target, link_jumps_from, link_jumps_to, link_rating_pr, link_rating_cy,
            ext_text1, ext_text2, ext_text3, item_title_translite]    
    )
}
                        <div class="number">
	                        
				                {IF $CNCAT[item][_favicon_url]}
				                	  <div class="favicon cnleft" style="background-image:url({cn_str($CNCAT[item][_favicon_url])})"></div>
				                {ENDIF}
	    	                    <div style="padding-top:3px" class="cnleft">{cn_str($CNCAT[item][_number] + $CNCAT[page][start_item_num])}.</div>
                        </div>
                        <div class="item">
                            <div class="link">
                                {IF $CNCAT[config][use_ext_links]}
                                    <a {IF $CNCAT[config][link_new_window]}onclick="window.open(this.href);return false;"{ENDIF} href="{cn_str($CNCAT[item][_ext_url])}"><strong>{cn_str($CNCAT[item][item_title])}</strong></a>
                                {ELSE}
                                    {IF $CNCAT[item][link_target] == 1}
                                        <a {IF $CNCAT[config][link_new_window]}onclick="window.open(this.href);return false;"{ENDIF} href="{$CNCAT[item][link_url]}"><strong>{cn_str($CNCAT[item][item_title])}</strong></a>
                                    {ENDIF}
                                    {IF $CNCAT[item][link_target] == 2}
                                        <a {IF $CNCAT[config][link_new_window]}onclick="window.open(this.href);return false;"{ENDIF} href="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}"><strong>{cn_str($CNCAT[item][item_title])}</strong></a>
                                    {ENDIF}
                                    {IF $CNCAT[item][link_target] == 3}
                                        <a href="{$CNCAT[item][link_url]}" onclick="{IF $CNCAT[config][link_new_window]}window.open('{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}');{ELSE}location.href = '{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}';{ENDIF} return false;"><strong>{cn_str($CNCAT[item][item_title])}</strong></a>
                                    {ENDIF}
                                {ENDIF}
                                {IF $CNCAT[item][item_favour] > 0}<img src="{$THEMEURL}images/faviteml.gif" alt="" />{ENDIF}{IF $CNCAT[item][item_favour] > 1}<img src="{$THEMEURL}images/favitemr.gif" alt="" />{ENDIF}
                                {IF $CNCAT[item][item_favour] > 2}<img src="{$THEMEURL}images/faviteml.gif" alt="" />{ENDIF}{IF $CNCAT[item][item_favour] > 3}<img src="{$THEMEURL}images/favitemr.gif" alt="" />{ENDIF}
                                {IF $CNCAT[item][item_favour] > 4}<img src="{$THEMEURL}images/faviteml.gif" alt="" />{ENDIF}{IF $CNCAT[item][item_favour] > 5}<img src="{$THEMEURL}images/favitemr.gif" alt="" />{ENDIF}
                                {IF $CNCAT[item][item_favour] > 6}<img src="{$THEMEURL}images/faviteml.gif" alt="" />{ENDIF}{IF $CNCAT[item][item_favour] > 7}<img src="{$THEMEURL}images/favitemr.gif" alt="" />{ENDIF}
                                {IF $CNCAT[item][item_favour] > 8}<img src="{$THEMEURL}images/faviteml.gif" alt="" />{ENDIF}{IF $CNCAT[item][item_favour] > 9}<img src="{$THEMEURL}images/favitemr.gif" alt="" />{ENDIF}
                            </div>
                            <div class="info">
                                ({$CNCAT[item][link_jumps_from]}/{$CNCAT[item][link_jumps_to]})
    
                                {IF $CNCAT[item][link_rating_pr]>=0} 
                                     {$CNCAT[lang][pr]}: {$CNCAT[item][link_rating_pr]}    
                                {ENDIF}
    
                                {IF $CNCAT[item][link_rating_cy]>=0} 
                                     {$CNCAT[lang][cy]}: {$CNCAT[item][link_rating_cy]}    
                                {ENDIF}
    
                                {IF $CNCAT[item][item_rating_moder]>0 || $CNCAT[item][item_rating_users]>0} 
                                    {$CNCAT[lang][rating]}: <span title="{$CNCAT[lang][administrator]}">{IF $CNCAT[item][item_rating_moder]>0}{$CNCAT[item][item_rating_moder]}{ELSE}{$CNCAT[lang][no]}{ENDIF}</span>/<span title="{$CNCAT[lang][users]}">{IF $CNCAT[item][item_rating_users]>0}{$CNCAT[item][item_rating_users]}{ELSE}{$CNCAT[lang][no]}{ENDIF}</span>
                                {ENDIF}
    
                                {IF $CNCAT[config][rating_enable]}
                                    | <a href="{cn_str($CNCAT[item][_ext_url])}#vote">{$CNCAT[lang][appreciate]}</a>
                                {ENDIF}
    
                                {IF $CNCAT[config][comments_links_enable]}
                                    | <a href="{cn_str($CNCAT[item][_ext_url])}#comments">{$CNCAT[lang][comments]} {IF $CNCAT[item][item_com_count]}(<strong>{$CNCAT[item][item_com_count]}</strong>){ENDIF}</a>
                                {ENDIF}
                            </div>
                            <div class="descr">
                                {cn_str($CNCAT[item][item_descr])} 
                                {IF $CNCAT[item][item_display_ext] == 1}
                                    <a href="{cn_str($CNCAT[item][_ext_url])}"><img src="{$THEMEURL}images/extlink.gif" alt="" title="{$CNCAT[lang][site_descr]}" style="vertical-align: middle;" /></a>
                                {ENDIF}
                                <div class="url">
                                {IF $CNCAT[config][links_broken_notify]}
                                    {cn_str($CNCAT[item][link_url])} <a href="javascript:baditem('{$CNCAT[item][item_id]}')" title="{$CNCAT[lang][broken_notify]}"><img src="{$THEMEURL}images/badlink.gif" alt="{$CNCAT[lang][broken_notify]}" /></a>
                                {ENDIF}
                                </div>
                                {DISPLAY EXTFIELDS}
                                {IF $CNCAT[item][_control_bar]}
                                <div class="control">
                                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=delete&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_delete]}</a>&nbsp;&bull;&nbsp;
                                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=isolation&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_isolation]}</a>&nbsp;&bull;&nbsp;
                                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=asnew&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_asnew]}</a>&nbsp;&bull;&nbsp;
                                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links_add&amp;mode=forms&amp;edit=1&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_edit]}</a>&nbsp;&bull;&nbsp;
                                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;type=1&amp;checkone_id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_check]}</a>&nbsp;&bull;&nbsp;
                                    {DISPLAY ADMIN_RATING}
                                </div>
                                {ENDIF}
                            </div>
                            
                        </div>
                        {IF $CNCAT[banner][items_middle] && $CNCAT[item][_number]==intval($CNCAT[item][_count]/2-1)}
                        <div class="banner">
                            {$CNCAT[banner][items_middle]}
                        </div>
                        {ENDIF}
{/TEMPLATE}
{TEMPLATE items_bottom}
                    </div>
{/TEMPLATE}

<!--
1 - int,
2 - double,
3 - varchar(255),
4 - datetime,
5 - text,
6 - image  
-->

{TEMPLATE item_ext_field}
    {IF $CNCAT[extfield][type] == 1 || $CNCAT[extfield][type] == 2}
        {IF is_numeric($CNCAT[extfield][value])}
            <div>{cn_str($CNCAT[extfield][title])}: {cn_str($CNCAT[extfield][value])}</div>
        {ENDIF}
    {ELSE}
        {IF $CNCAT[extfield][value]}
            {IF $CNCAT[extfield][type] == 3}
                <div>{cn_str($CNCAT[extfield][title])}: {cn_str($CNCAT[extfield][value])}</div>
            {ENDIF}
            {IF $CNCAT[extfield][type] == 4}
                <div>{cn_str($CNCAT[extfield][title])}: {cn_date($CNCAT[extfield][value])}</div>
            {ENDIF}
            {IF $CNCAT[extfield][type] == 5}
                <div>{cn_str($CNCAT[extfield][title])}: {cn_str($CNCAT[extfield][value])}</div>
            {ENDIF}
            {IF $CNCAT[extfield][type] == 6}
                <div class="name">{cn_str($CNCAT[extfield][title])}:<div class="text">
                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}image.php?item={$CNCAT[item][item_id]}&amp;field={$CNCAT[extfield][name]}"><img src="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}image.php?item={$CNCAT[item][item_id]}&amp;thumb=1&amp;field={$CNCAT[extfield][name]}" /></a>
                </div></div>
            {ENDIF}
        {ENDIF}
    {ENDIF}
{/TEMPLATE}

{TEMPLATE admin_rating_top}{/TEMPLATE}
{TEMPLATE admin_rating_num}<a href="{$CNCAT[config][cncat_url]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=rating&amp;value={$CNCAT[admin][item_rating_value]}&amp;id={$CNCAT[item][item_id]}">{$CNCAT[admin][item_rating_value]}</a>{/TEMPLATE}
{TEMPLATE admin_rating_num_active}<strong>{$CNCAT[admin][item_rating_value]}</strong>{/TEMPLATE}
{TEMPLATE admin_rating_delim} {/TEMPLATE}
{TEMPLATE admin_rating_bottom}{/TEMPLATE}

{TEMPLATE itemcount}
    {IF $CNCAT[page][cat_item_count]>0}
        {$CNCAT[lang][items_in_category]}: <strong>{$CNCAT[page][cat_item_count]}</strong>
    {ELSE}
        {$CNCAT[lang][no_items_in_category]}
    {ENDIF}
{/TEMPLATE}

{TEMPLATE favitems_top}
    <ul>
{/TEMPLATE}
{TEMPLATE favitems_bottom}
    </ul>
{/TEMPLATE}

{TEMPLATE brokenscript}
    {if $CNCAT[config][links_broken_notify]}
        <script type="text/javascript">
        //<![CDATA[
            function baditem(l) 
            {
                t=screen.height/2-50;
                w=screen.width/2-50;
                wnd=window.open("","baditem"+l,"width=200,height=100,top="+t+",left="+w);
                wnd.document.write("<!DOCTYPE html PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'><HTML><HEAD><TITLE>{$CNCAT[lang][broken_warning]}<"+"/TITLE><META http-equiv='Content-Type' content='text/html; charset={$CNCAT[lang][charset]}'><"+"/HEAD><BODY style='font-family:verdana;font-size:11px;color:gray;'><center><B>{$CNCAT[lang][broken_sure]}<"+"/B><br /><br />");
                wnd.document.write("<a href={$CNCAT[abs]}{$CNCAT[system][dir_admin]}baditem.php?bad="+l+">{$CNCAT[lang][yes]}<"+"/a> &nbsp;|&nbsp; <a href='javascript:window.close();'>{$CNCAT[lang][no]}<"+"/a><"+"/center><"+"/BODY><"+"/HTML>");
                wnd.document.close();
            }
        //]]>
        </script>
    {endif}
{/TEMPLATE}

{TEMPLATE newitems_top}
            <div id="newitems">
                <div class="title">
                    {$CNCAT[lang][new_items]}
                </div>
                <div class="items">
                <ul>
{/TEMPLATE}
{TEMPLATE newitem
    (ITEM[item_id, item_title, item_descr, link_url, link_target])
}
                        <li><div class="link">
                            {IF $CNCAT[item][link_target] == 1}
                                <a {IF $CNCAT[config][link_new_window]} onclick="window.open(this.href);return false;" {ENDIF} href="{$CNCAT[item][link_url]}">
                                <strong>{cn_str($CNCAT[item][item_title])}</strong></a>
                            {ENDIF}
                            {IF $CNCAT[item][link_target] == 2}
                                <a {IF $CNCAT[config][link_new_window]} onclick="window.open(this.href);return false;" {ENDIF} href="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}">
                                <strong>{cn_str($CNCAT[item][item_title])}</strong></a>
                            {ENDIF}
                            {IF $CNCAT[item][link_target] == 3}
                                <a href="{$CNCAT[item][link_url]}" onclick="{IF $CNCAT[config][link_new_window]}window.open('{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}');{ELSE}location.href = '{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}';{ENDIF} return false;">
                                <strong>{cn_str($CNCAT[item][item_title])}</strong></a>
                            {ENDIF}
                            </div><div class="descr">{cn_str($CNCAT[item][item_descr])}</div>
                        </li>
{/TEMPLATE}
{TEMPLATE newitems_bottom}
                </ul>
                </div>
          </div>
{/TEMPLATE}
