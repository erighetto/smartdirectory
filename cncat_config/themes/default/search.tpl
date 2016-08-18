{TEMPLATE search}
    {INCLUDE top}
    {INCLUDE brokenscript}
    {INCLUDE menu}

    <table width="100%">
    <tr><td width="1" style="padding: 0 20px;">
        {IF $CNCAT[banner][page_left]}<table style="margin: 0 auto;"><tr><td>{$CNCAT[banner][page_left]}</td></tr></table>{ENDIF}
    </td><td width="100%">
        <table class="pagetitle">
            <tr><td class="title">
                {$CNCAT[lang][menu_search]}
            </td><td class="clear"></td></tr>
        </table>
        <table id="main" width="100%">
            <tr><td class="left">
                <table id="catitems">
                    <tr><td class="sort">
                    {DISPLAY SORT}
                    </td></tr>
                    <tr><td class="navbar">
                    {DISPLAY PAGES}
                    </td></tr>
                    <tr><td class="items">
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
                    </td></tr>
                    <tr><td class="navbar">
                    {DISPLAY PAGES}
                    </td></tr>
                </table>
            </td></tr>
        </table>
    </td><td width="1" style="padding: 0 20px;">
        {IF $CNCAT[banner][page_right]}<table style="margin: 0 auto;"><tr><td>{$CNCAT[banner][page_right]}</td></tr></table>{ENDIF}
    </td></table>

    {INCLUDE bottom}
{/TEMPLATE}
