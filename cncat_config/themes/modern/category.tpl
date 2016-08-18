{TEMPLATE cats_top}
            <div id="categories">
                
{/TEMPLATE}
{TEMPLATE cats_column_top}
                <div class="catcol cnleft" style="width:{$CNCAT[page][cat_col_width]}%">
                    <table>
{/TEMPLATE}
{TEMPLATE cat 
    ( CAT[id, title, item_count, item_count_full, is_link, image_mime, meta_keywords, meta_descr, descr])
}
                        <tr><td class="image" rowspan="2">
                            {IF $CNCAT[cat][image_mime]}
                            	<img src="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}image.php?cat={$CNCAT[cat][id]}" alt=""/>
                            {ELSE}
								<img src="{$THEMEURL}images/category.gif" alt="" />
                            {ENDIF}
                        </td><td class="title">
                            {IF $CNCAT[cat][is_link]}@{ENDIF}<a href="{cn_str($CNCAT[cat][url])}">{cn_str($CNCAT[cat][title])}</a>
                            <span class="counter">&nbsp;{$CNCAT[cat][item_count_full]}</span>
                        </td></tr>
                        <tr><td class="subcats">
                            {DISPLAY SUBCATS}
                        </td></tr>
{/TEMPLATE}    
{TEMPLATE cats_column_bottom}
                    </table>
                </div>
{/TEMPLATE}
{TEMPLATE cats_bottom}
                </div>
{/TEMPLATE}

{TEMPLATE subcats_top}{/TEMPLATE}
{TEMPLATE subcat (SUBCAT[title, item_count_full, is_link])}
  {IF $CNCAT[subcat][is_link]}&nbsp;@{ENDIF}
  <a href="{$CNCAT[subcat][url]}">{cn_str($CNCAT[subcat][title])}</a>
  &nbsp;{$CNCAT[subcat][item_count_full]}
{/TEMPLATE}
{TEMPLATE subcats_delim}, {/TEMPLATE}
{TEMPLATE subcats_more}... {/TEMPLATE}
{TEMPLATE subcats_bottom}{/TEMPLATE}

{TEMPLATE catpath_top}
            <div id="path">
                <div class="title">
{/TEMPLATE}
{TEMPLATE catpath_rootcat}
                    <a href="{$CNCAT[cat][url]}">{cn_str($CNCAT[cat][title])}</a> 
{/TEMPLATE}
{TEMPLATE catpath_parentcat_delim} &raquo; {/TEMPLATE}
{TEMPLATE catpath_parentcat}
                    <a href="{$CNCAT[cat][url]}">{cn_str($CNCAT[cat][title])}</a>
{/TEMPLATE}
{TEMPLATE catpath_currentcat_delim} 
{/TEMPLATE}
{TEMPLATE catpath_currentcat}
{/TEMPLATE}
{TEMPLATE catpath_bottom}
                </div>
            </div>
{/TEMPLATE}


