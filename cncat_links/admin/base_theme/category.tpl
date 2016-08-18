{TEMPLATE cats_top}
%CATS_TOP%
{/TEMPLATE}

{TEMPLATE cats_column_top}
%CATS_COLUMN_TOP%
{/TEMPLATE}

{TEMPLATE cat 
    (CAT[id, title, item_count_full, is_link, image_mime])
}  
%CAT%
{/TEMPLATE}

{TEMPLATE cats_column_bottom}
%CATS_COLUMN_BOTTOM%
{/TEMPLATE}

{TEMPLATE cats_bottom}
%CATS_BOTTOM%
{/TEMPLATE}

{TEMPLATE subcats_top}
%SUBCATS_TOP%
{/TEMPLATE}

{TEMPLATE subcat
    (SUBCAT[title, item_count_full, is_link])
}
%SUBCAT%
{/TEMPLATE}

{TEMPLATE subcats_delim}
%SUBCATS_DELIM%
{/TEMPLATE}

{TEMPLATE subcats_more}
%SUBCATS_MORE%
{/TEMPLATE}

{TEMPLATE subcats_bottom}
%SUBCATS_BOTTOM%
{/TEMPLATE}

{TEMPLATE catpath_top}
%CATPATH_TOP%
{/TEMPLATE}

{TEMPLATE catpath_rootcat}
    <a href="{$CNCAT[cat][url]}">{cn_str($CNCAT[cat][title])}</a> 
{/TEMPLATE}

{TEMPLATE catpath_parentcat_delim} &raquo; {/TEMPLATE}

{TEMPLATE catpath_parentcat}
    <a href="{$CNCAT[cat][url]}">{cn_str($CNCAT[cat][title])}</a>
{/TEMPLATE}

{TEMPLATE catpath_currentcat_delim} 
    &raquo;
{/TEMPLATE}

{TEMPLATE catpath_currentcat}
    {cn_str($CNCAT[cat][title])}
{/TEMPLATE}

{TEMPLATE catpath_bottom}
%CATPATH_BOTTOM%
{/TEMPLATE}
