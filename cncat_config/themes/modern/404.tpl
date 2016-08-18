{TEMPLATE 404}
    {INCLUDE top}
    <div id="cncontent">
    <div id="cnmain">
    <div id="page404">
        <div class="title">
            {$CNCAT[lang][error_404]}
        </div>
    </div>
    <table id="error404" width="100%">
        <tr><td class="text">
            <div class="msg">
                {$CNCAT[lang][request_page_not_found]}
                <a href="{$CNCAT[config][cncat_url]}">{$CNCAT[lang][return_to_main]}</a>
            </div>
        </td></tr>
    </table>
    </div>
    </div>
    {INCLUDE bottom}
{/TEMPLATE}
