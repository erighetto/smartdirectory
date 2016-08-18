{TEMPLATE items_top}
%ITEMS_TOP%
{/TEMPLATE}

{TEMPLATE item
    (ITEM  [item_id, item_title, item_rating_moder,
            item_favour, item_descr, 
            item_meta_keywords, item_meta_descr,
            item_submit_date,
            item_display_ext,
            link_url, link_target, link_jumps_from, link_jumps_to, link_rating_pr, link_rating_cy,
            ext_text1, ext_text2, ext_text3]    
    )
}
%ITEM%
{/TEMPLATE}

{TEMPLATE items_bottom}
%ITEMS_BOTTOM%
{/TEMPLATE}

{TEMPLATE admin_rating_top}
{/TEMPLATE}

{TEMPLATE admin_rating_num}
{/TEMPLATE}

{TEMPLATE admin_rating_num_active}
{/TEMPLATE}

{TEMPLATE admin_rating_bottom}
{/TEMPLATE}

{TEMPLATE itemcount}
    {IF $CNCAT[page][cat_item_count]>0}
        {$CNCAT[lang][items_in_category]}: <strong>{$CNCAT[page][cat_item_count]}</strong>
    {ELSE}
        {$CNCAT[lang][no_items_in_category]}
    {ENDIF}
{/TEMPLATE}

{TEMPLATE brokenscript}
%BROKENSCRIPT%
{/TEMPLATE}

{TEMPLATE newitems_top}
%NEWITEMS_TOP%
{/TEMPLATE}

{TEMPLATE newitem
    (ITEM[item_id, item_title, item_descr, link_url])
}
%NEWITEM%
{/TEMPLATE}

{TEMPLATE newitems_bottom}
%NEWITEMS_BOTTOM%
{/TEMPLATE}

{TEMPLATE itemtypes_top}
	{$CNCAT[lang][itemtypes_show]}:
{/TEMPLATE}

{TEMPLATE itemtype}
        {IF $CNCAT[itemtype][id] == $CNCAT[page][item_type]}
            <strong>{cn_str($CNCAT[itemtype][title])}</strong>
        {ELSE}
            <a href="{cn_str($CNCAT[itemtype][url])}">{cn_str($CNCAT[itemtype][title])}</a>
        {ENDIF}
{/TEMPLATE}

{TEMPLATE itemtypes_delim} | {/TEMPLATE}

{TEMPLATE itemtypes_bottom}
{/TEMPLATE}
