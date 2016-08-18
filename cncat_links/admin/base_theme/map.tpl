{TEMPLATE map}
    {INCLUDE top}
    {INCLUDE menu}
    {INCLUDE searchform}

    {DISPLAY CATEGORIES}
    

    
    {INCLUDE bottom}
{/TEMPLATE}

{TEMPLATE mapcat_next_level}
    
{/TEMPLATE}

{TEMPLATE map_cat_next_level}
    <ul class="catmap_level{$CNCAT[cat][tree_level]}">
{/TEMPLATE}

{TEMPLATE map_cat
    (CAT[title, item_count, item_count_full])
}
        <li><a href="{cn_str($CNCAT[cat][url])}">{cn_str($CNCAT[cat][title])}</a> {$CNCAT[cat][item_count_full]}<br>
{/TEMPLATE}

{TEMPLATE map_cat_prev_level}
    </ul>
{/TEMPLATE}
