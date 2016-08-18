{TEMPLATE static_page}
    {INCLUDE top}
    {INCLUDE menu}

    <div style="padding: 10px; margin: 0 20px; background: white;">
        <h3>{$CNCAT[static_page][title]}</h3>
        {$CNCAT[static_page][contents]}
    </div>

    {INCLUDE bottom}
{/TEMPLATE}
