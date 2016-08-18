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
    <div class="number">
		    {IF $CNCAT[item][_favicon_url]}
		    <div class="favicon cnleft" style="background-image:url({cn_str($CNCAT[item][_favicon_url])})"></div>
        {ENDIF}
	         <div style="padding-top:3px" class="cnleft">{cn_str($CNCAT[item][_number] + $CNCAT[page][start_item_num])}.</div>
    </div>
    <div class="item">
    <div class="link">
        <a href="{cn_str($CNCAT[item][_ext_url])}" style="color: #859847;"><strong>{cn_str($CNCAT[item][item_title])}</strong></a>

        {IF $CNCAT[item][item_favour] > 0}<img src="{$THEMEURL}images/faviteml.gif" alt="" />{ENDIF}{IF $CNCAT[item][item_favour] > 1}<img src="{$THEMEURL}images/favitemr.gif" alt="" />{ENDIF}
        {IF $CNCAT[item][item_favour] > 2}<img src="{$THEMEURL}images/faviteml.gif" alt="" />{ENDIF}{IF $CNCAT[item][item_favour] > 3}<img src="{$THEMEURL}images/favitemr.gif" alt="" />{ENDIF}
        {IF $CNCAT[item][item_favour] > 4}<img src="{$THEMEURL}images/faviteml.gif" alt="" />{ENDIF}{IF $CNCAT[item][item_favour] > 5}<img src="{$THEMEURL}images/favitemr.gif" alt="" />{ENDIF}
        {IF $CNCAT[item][item_favour] > 6}<img src="{$THEMEURL}images/faviteml.gif" alt="" />{ENDIF}{IF $CNCAT[item][item_favour] > 7}<img src="{$THEMEURL}images/favitemr.gif" alt="" />{ENDIF}
        {IF $CNCAT[item][item_favour] > 8}<img src="{$THEMEURL}images/faviteml.gif" alt="" />{ENDIF}{IF $CNCAT[item][item_favour] > 9}<img src="{$THEMEURL}images/favitemr.gif" alt="" />{ENDIF}
    </div>
    <div class="info">
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

        {IF $CNCAT[config][comments_articles_enable]}
            | <a href="{cn_str($CNCAT[item][_ext_url])}#comments">{$CNCAT[lang][comments]} {IF $CNCAT[item][item_com_count]}(<strong>{$CNCAT[item][item_com_count]}</strong>){ENDIF}</a>
        {ENDIF}
    </div>
                            

    
	    <div class="descr">
	        {IF $CNCAT[item][item_descr]}
          {cn_str($CNCAT[item][item_descr])} 
	        {IF $CNCAT[item][item_display_ext] == 1}
	            <a href="{cn_str($CNCAT[item][_ext_url])}"><img src="{$THEMEURL}images/extlink.gif" alt="" /></a>
	        {ENDIF}
	        {ENDIF}
	        {IF $CNCAT[item][link_url]}
	    <div class="url">
	        {IF $CNCAT[item][link_target] == 1}
	            <a onclick="window.open(this.href);return false;" href="{cn_str($CNCAT[item][link_url])}">{cn_str($CNCAT[item][link_url])}</a>
	        {ENDIF}
	        {IF $CNCAT[item][link_target] == 2}
	            <a onclick="window.open(this.href);return false;" href="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}">{cn_str($CNCAT[item][link_url])}</a>
	        {ENDIF}
	        {IF $CNCAT[item][link_target] == 3}
	            <a href="{$CNCAT[item][link_url]}" onclick="window.open('{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}'); return false;">{cn_str($CNCAT[item][link_url])}</a>
	        {ENDIF}
	        {IF $CNCAT[config][links_broken_notify]}
	            <a href="javascript:baditem('{$CNCAT[item][item_id]}')" title="{$CNCAT[lang][broken_notify]}"><img src="{$THEMEURL}images/badlink.gif" alt="{$CNCAT[lang][broken_notify]}" /></a>
	        {ENDIF}
	    </div>
    	{ENDIF}
    	{DISPLAY EXTFIELDS}
        {IF $CNCAT[item][_control_bar]}
        <div class="control">
            <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=delete&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_delete]}</a>&nbsp;&bull;&nbsp;
            <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=isolation&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_isolation]}</a>&nbsp;&bull;&nbsp;
            <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=asnew&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_asnew]}</a>&nbsp;&bull;&nbsp;
            <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=articles_add&amp;mode=forms&amp;edit=1&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_edit]}</a>&nbsp;&bull;&nbsp;
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

{TEMPLATE newarticles_top}
    <div id="newarticles">
    <div class="title">
        {$CNCAT[lang][new_articles]}
    </div>
    <div class="items">
    <ul>
{/TEMPLATE}

{TEMPLATE newarticle
    (ITEM[item_id, item_title, item_descr, link_url, item_title_translite])
}
	            <li><div class="link">
	                	<a href="{$CNCAT[item][_ext_url]}" style="color: #859847;"><strong>{cn_str($CNCAT[item][item_title])}</strong></a>
		            </div><div class="descr">{cn_str($CNCAT[item][item_descr])}</div>
                        </li>
{/TEMPLATE}

{TEMPLATE newarticles_bottom}
            	</ul>
                </div>
          </div>
{/TEMPLATE}
