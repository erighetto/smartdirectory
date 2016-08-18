{TEMPLATE search}
    {INCLUDE top}
    {INCLUDE brokenscript}
    {INCLUDE menu}
    {INCLUDE searchform}

    {DISPLAY SORT}
    {DISPLAY PAGES}
	{DISPLAY ITEMS}
    {IF $CNCAT[page][cat_item_count]>0}
        {$CNCAT[lang][links_find_count]}: <strong>{$CNCAT[page][cat_item_count]}</strong>
    {ELSE}
        {$CNCAT[lang][links_not_found]}
    {ENDIF}
    {DISPLAY PAGES}

    {INCLUDE bottom}
{/TEMPLATE}
