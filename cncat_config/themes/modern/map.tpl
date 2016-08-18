{TEMPLATE map}
    {INCLUDE top}
    <div id="cncontent">
    <div id="side_pane" class="cnleft">
    {INCLUDE menu}
    {IF $CNCAT[banner][page_left]}
        <div class="banner">{$CNCAT[banner][page_left]}</div>
        {ENDIF}
    </div>
    <div id="cnmain">
        <div id="page">
            <div class="title">
                {$CNCAT[lang][menu_map]}
            </div>
            <div id="map">
                <div class="mapcat">
                    <div class="categories">
                            {DISPLAY CATEGORIES}
                    </div>
                </div>
            </div>
        </div>
        {IF $CNCAT[banner][page_left]}
          <div class="banner">{$CNCAT[banner][page_right]}</div>
        {ENDIF}
    </div>
    </div>
    {INCLUDE bottom}
{/TEMPLATE}

{TEMPLATE map_cat_next_level}
    {IF  $CNCAT[cat][tree_level] > 1}<li>{ENDIF}
    <ul class="level{$CNCAT[cat][tree_level]}">
{/TEMPLATE}

{TEMPLATE map_cat
    (CAT[title, item_count, item_count_full])
}
    <li><a href="{$CNCAT[cat][url]}">{$CNCAT[cat][title]}</a> {$CNCAT[cat][item_count_full]}<br /></li>
{/TEMPLATE}

{TEMPLATE map_cat_prev_level}
    </ul>    
    {IF  $CNCAT[cat][tree_level] > 0}</li>{ENDIF}
{/TEMPLATE}
