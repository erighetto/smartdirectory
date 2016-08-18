{TEMPLATE add_article_form}
    {IF $CNCAT[config][add_article_show_message] == 1}<tr><td colspan="2" class="text">{$CNCAT[config][add_article_message]}</td></tr>{ENDIF}
    <tr>
        <td colspan="2" class="text">{DISPLAY ERRORS}</td>
    </tr>
    {IF $CNCAT[config][add_article_accept_to_add] == 1}<tr><td colspan="2" class="text">{$CNCAT[lang][accept_work]}</td></tr>{ENDIF}
    {IF $CNCAT[config][add_article_accept_to_add] == 2}<tr><td colspan="2" class="text">{$CNCAT[lang][accept_work_with_back]}</td></tr>{ENDIF}
    {IF $CNCAT[config][add_article_check_link]}<tr><td colspan="2" class="text">{$CNCAT[lang][first_check_link]}</td></tr>{ENDIF}
    {DISPLAY BACKLINKS}
    <tr><td>
    <form action="{$CNCAT[page][add_article_url]}" method="post" enctype="multipart/form-data">
    <input type="hidden" value="1" name="submit" />
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
    </td></tr>
{/TEMPLATE}
