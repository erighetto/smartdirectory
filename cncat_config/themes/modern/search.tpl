{TEMPLATE search}
    {INCLUDE top}
    {INCLUDE brokenscript}
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
                {$CNCAT[lang][menu_search]}
            </div>
            <div id="catitems">
                    <div class="sort">
                    {DISPLAY SORT}
                    </div>
                    <div class="navbar">
                    {DISPLAY PAGES}
                    </div>
                    <div class="items">
                    {IF $CNCAT[banner][items_top]}
                        <center>{$CNCAT[banner][items_top]}</center>
                    {ENDIF}
    	            {DISPLAY ITEMS}
                    {IF $CNCAT[banner][items_bottom]}
                        <center>{$CNCAT[banner][items_bottom]}</center>
                    {ENDIF}
                    {IF $CNCAT[page][cat_item_count]>0}
                        {$CNCAT[lang][links_find_count]}: <strong>{$CNCAT[page][cat_item_count]}</strong>
                    {ELSE}
                        {$CNCAT[lang][links_not_found]}
                    {ENDIF}
                    </div>
                    <div class="navbar">
                    {DISPLAY PAGES}
                    </div>
                </div>
            </div>
        </div>
    {IF $CNCAT[banner][page_right]}
            <div class="banner">{$CNCAT[banner][page_right]}</div>
    {ENDIF}
     </div>
    {INCLUDE bottom}
{/TEMPLATE}
