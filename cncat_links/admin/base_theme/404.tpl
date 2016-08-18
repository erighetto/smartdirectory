{TEMPLATE 404}
    {INCLUDE top}
    {INCLUDE menu}

    <h3>{$CNCAT[lang][error_404]}</h3>
    {$CNCAT[lang][request_page_not_found]}
    <a href="{$CNCAT[config][cncat_url]}">{$CNCAT[lang][return_to_main]}</a>

    {INCLUDE bottom}
{/TEMPLATE}
