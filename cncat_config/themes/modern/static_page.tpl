{TEMPLATE static_page}
    {INCLUDE top}
    <div id="cncontent">
    <div id="side_pane" class="cnleft">
    {INCLUDE menu}
    </div>
    <div id="cnmain">
    <div style="padding: 10px; margin: 0 20px; background: white;">
        <h3>{$CNCAT[static_page][title]}</h3>
        {$CNCAT[static_page][contents]}
    </div>
    </div>
    </div>

    {INCLUDE bottom}
{/TEMPLATE}
