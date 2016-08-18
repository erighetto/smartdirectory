{TEMPLATE add_article_form}
    {IF $CNCAT[config][add_article_show_message] == 1}<div class="text">{$CNCAT[config][add_article_message]}</div>{ENDIF}
    {DISPLAY ERRORS}
    {IF $CNCAT[config][add_article_accept_to_add] == 1}<div class="text">{$CNCAT[lang][accept_work]}</div>{ENDIF}
    {IF $CNCAT[config][add_article_accept_to_add] == 2}<div class="text">{$CNCAT[lang][accept_work_with_back]}</div>{ENDIF}
    {IF $CNCAT[config][add_article_check_link]}<div class="text">{$CNCAT[lang][first_check_link]}</div>{ENDIF}
    {DISPLAY BACKLINKS}
    <div id="addform">
    <form action="{$CNCAT[page][add_article_url]}" method="post" enctype="multipart/form-data" id="add_form">
    <table>
        {INCLUDE field_site_title}
        {INCLUDE field_descr}
        {INCLUDE field_descr_full}
        {INCLUDE field_cats}
        {INCLUDE field_email}
        {INCLUDE field_author}
        {IF $CNCAT[config][add_article_use_link_url]}
            {INCLUDE field_link_url}
        {ENDIF}
        {INCLUDE field_back_link}
        {INCLUDE field_meta_keywords}
        {INCLUDE field_meta_descr}
    		{DISPLAY FILTERS}
    		{DISPLAY EXTFIELDS}
        {$CNCAT[page][captcha]}
        {INCLUDE field_submit}
    </table>
    </form>
    </div>
{/TEMPLATE}
