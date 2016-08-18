{TEMPLATE map}
    {INCLUDE top}
    {INCLUDE menu}

    <table width="100%">
    <tr><td width="1" style="padding: 0 20px;">
        {IF $CNCAT[banner][page_left]}<table style="margin: 0 auto;"><tr><td>{$CNCAT[banner][page_left]}</td></tr></table>{ENDIF}
    </td><td width="100%">
        <table class="pagetitle">
            <tr><td class="title">
                {$CNCAT[lang][menu_map]}
            </td><td class="clear"></td></tr>
        </table>
        <table id="map" width="100%">
            <tr><td class="mapcat">
                <table width="100%">
                    <tr><td class="categories">
                        {DISPLAY CATEGORIES}
                    </td></tr>
                </table>
            </td></tr>
        </table>
    </td><td width="1" style="padding: 0 20px;">
        {IF $CNCAT[banner][page_right]}<table style="margin: 0 auto;"><tr><td>{$CNCAT[banner][page_right]}</td></tr></table>{ENDIF}
    </td></tr></table>

    {INCLUDE bottom}
{/TEMPLATE}

{TEMPLATE map_cat_next_level}
    <ul class="level{$CNCAT[cat][tree_level]}">
{/TEMPLATE}

{TEMPLATE map_cat
    (CAT[title, item_count, item_count_full])
}
    <li><a href="{$CNCAT[cat][url]}">{$CNCAT[cat][title]}</a> {$CNCAT[cat][item_count_full]}<br />
{/TEMPLATE}

{TEMPLATE map_cat_prev_level}
    </ul>    

{/TEMPLATE}
