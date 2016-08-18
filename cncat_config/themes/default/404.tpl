{TEMPLATE 404}
    {INCLUDE top}
    {INCLUDE menu}

    <table class="pagetitle">
        <tr><td class="title">
            {$CNCAT[lang][error_404]}
        </td><td class="clear"></td></tr>
    </table>
    <table id="error404" width="100%">
        <tr><td class="text">
            <div class="msg">
                {$CNCAT[lang][request_page_not_found]}
                <a href="{$CNCAT[config][cncat_url]}">{$CNCAT[lang][return_to_main]}</a>
            </div>
        </td></tr>
    </table>

    {INCLUDE bottom}
{/TEMPLATE}
