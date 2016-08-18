{TEMPLATE ext_article
    (ITEM[item_status, item_title, item_title_translite, item_descr, item_descr_full, link_url, link_target, link_jumps_to, link_jumps_from, item_rating_moder, item_image_mime, item_author_name])
}
    {INCLUDE top}
    <div id="cncontent">
    <div id="side_pane" class="cnleft">
    {INCLUDE menu}
    {IF $CNCAT[banner][page_left]}
        <div class="banner">{$CNCAT[banner][page_left]}</div>
    {ENDIF}
    </div>
    <div id="cnmain">
        <div>
            {IF $CNCAT[item][item_status] == 1}
            <div id="article_info" class="cnright">
            <div id="page">
                <div class="title">
                    {$CNCAT[lang][information]}
                </div>
            </div>
            <div class="info">
                {IF $CNCAT[item][link_url]}
                <div class="name">{$CNCAT[lang][link_url_article]}:</div>
                <div class="text">
                    {IF $CNCAT[item][link_target] == 1}
                        <a onclick="window.open(this.href);return false;" href="{cn_str($CNCAT[item][link_url])}">{cn_str($CNCAT[item][link_url])}</a>
                    {ENDIF}
                    {IF $CNCAT[item][link_target] == 2}
                        <a onclick="window.open(this.href);return false;" href="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}">{cn_str($CNCAT[item][link_url])}</a>
                    {ENDIF}
                    {IF $CNCAT[item][link_target] == 3}
                        <a  href="{cn_str($CNCAT[item][link_url])}" onclick="window.open('{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}');return false;">{cn_str($CNCAT[item][link_url])}</a>
                    {ENDIF}
                </div>
                {ENDIF}
                {IF $CNCAT[item][item_author_name]}
                <div>
                    <div class="name">{$CNCAT[lang][author]}:</div>
                    <div class="text">{cn_str($CNCAT[item][item_author_name])}</div>
                </div>
                {ENDIF}
                <div class="name">{$CNCAT[lang][categories]}:</div>
                <div class="text">
                        {DISPLAY CATEGORIES}
                </div>
                {DISPLAY FILTERS}
                <div>
                    <div class="name">{$CNCAT[lang][moder_rating]}:</div>
                    <div class="text">
                        {IF $CNCAT[item][item_rating_moder]}
                            {cn_str($CNCAT[item][item_rating_moder])}
                        {ELSE}
                            {$CNCAT[lang][no_moder_rating]}
                        {ENDIF}
                    </div>
                </div>
                <div>
                    <div class="name">{$CNCAT[lang][users_rating]}:</div>
                    <div class="text">
                        {IF $CNCAT[item][item_rating_users] > 0}
                            {cn_str($CNCAT[item][item_rating_users])} ({$CNCAT[lang][votes_count]}: {$CNCAT[item][item_votes_count]})
                        {ELSE}
                            {$CNCAT[lang][no]}
                        {ENDIF}
                    </div>
                </div>
                <div class="name">{$CNCAT[lang][jumps_to]}:</div><div class="text">{cn_str($CNCAT[item][link_jumps_to])}</div>
                <div class="name">{$CNCAT[lang][jumps_from]}:</div><div class="text">{cn_str($CNCAT[item][link_jumps_from])}</div>
                {DISPLAY EXTFIELDS}
            </div>
        </div>
            <div id="ext_article">
                <div class="title">{cn_str($CNCAT[item][item_title])}</div>
                <div class="descr">
                {cn_str($CNCAT[item][item_descr_full])}
                {DISPLAY COMMENTS}
                </div>
            {ELSE}
                {$CNCAT[lang][article_not_approved]}
            {ENDIF}
            {IF $CNCAT[item][_control_bar]}
              <div class="control">
                <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=delete&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_delete]}</a>&nbsp;&bull;&nbsp;
                <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=isolation&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_isolation]}</a>&nbsp;&bull;&nbsp;
                {IF $CNCAT[item][item_status] == 1}
                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=asnew&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_asnew]}</a>&nbsp;&bull;&nbsp;
                {ELSE}
                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=approve&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_approve]}</a>&nbsp;&bull;&nbsp;
                {ENDIF}
                <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=articles_add&amp;mode=forms&amp;edit=1&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_edit]}</a>&nbsp;&bull;&nbsp;
                <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;type=1&amp;checkone_id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_check]}</a>&nbsp;&bull;&nbsp;
                {DISPLAY ADMIN_RATING}
              </div>
            {ENDIF}
        {IF $CNCAT[item][item_status] == 1}
          </div>
        </div>
        
        {ENDIF}
        </div>
    {IF $CNCAT[banner][page_right]}
            <div class="banner">{$CNCAT[banner][page_right]}</div>
    {ENDIF}
    </div>
    {INCLUDE bottom}
{/TEMPLATE}

{TEMPLATE ext_cats_top}
<ul>
{/TEMPLATE}
{TEMPLATE ext_cat
    (CAT[title_full])
}<li><a href="{cn_str($CNCAT[cat][_url])}">{cn_str($CNCAT[cat][title_full])}</a></li>{/TEMPLATE}
{TEMPLATE ext_cat_delim}{/TEMPLATE}
{TEMPLATE ext_cats_bottom}
</ul>
{/TEMPLATE}

{TEMPLATE ext_filter_top}
        <div class="name">{cn_str($CNCAT[filter][title])}:</div><div class="text">
{/TEMPLATE}
{TEMPLATE ext_filtval}{cn_str($CNCAT[filtval][title])}{/TEMPLATE}
{TEMPLATE ext_filtval_delim}, {/TEMPLATE}
{TEMPLATE ext_filter_bottom}
        </div>
{/TEMPLATE}

{TEMPLATE ext_ext_field}
    {IF $CNCAT[extfield][type] == 1 || $CNCAT[extfield][type] == 2}
        {IF is_numeric($CNCAT[extfield][value])}
            <div class="name">{cn_str($CNCAT[extfield][title])}:</div><div class="text">{cn_str($CNCAT[extfield][value])}</div>
        {ENDIF}
    {ELSE}
        {IF $CNCAT[extfield][value]}
            {IF $CNCAT[extfield][type] == 3}
                <div class="name">{cn_str($CNCAT[extfield][title])}:</div><div class="text">{cn_str($CNCAT[extfield][value])}</div>
            {ENDIF}
            {IF $CNCAT[extfield][type] == 4}
                <div class="name">{cn_str($CNCAT[extfield][title])}:</div><div class="text">{cn_date($CNCAT[extfield][value])}</div>
            {ENDIF}
            {IF $CNCAT[extfield][type] == 5}
                <div class="name">{cn_str($CNCAT[extfield][title])}:</div><div class="text">{cn_str($CNCAT[extfield][value])}</div>
            {ENDIF}
            {IF $CNCAT[extfield][type] == 6}
                {IF $CNCAT[extfield][value]}
                    <div class="name">{cn_str($CNCAT[extfield][title])}:</div><div class="text">
                        <a href="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}image.php?item={$CNCAT[item][item_id]}&amp;field={$CNCAT[extfield][name]}"><img src="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}image.php?item={$CNCAT[item][item_id]}&amp;thumb=1&amp;field={$CNCAT[extfield][name]}" /></a>
                    </div>
                {ENDIF}
            {ENDIF}
        {ENDIF}
    {ENDIF}
{/TEMPLATE}

{TEMPLATE ext_article_comments_top}
<div>
    {IF $CNCAT[config][comments_articles_enable]}
    <div>
        <a name="comments"></a><strong>{$CNCAT[lang][comments]}:</strong>
        {IF !$CNCAT[item][item_com_count]}<p><em>{$CNCAT[lang][no_comments]}</em></p>{ENDIF}
    </div>
    {ENDIF}
    {IF $CNCAT[comments][post_result]}
        <div class="text" style="background-color: #f2fff2; padding: 5px;">{$CNCAT[comments][post_result]}</div>
    {ENDIF}
    <div class="text">
{/TEMPLATE}

{TEMPLATE ext_article_comments_bottom}
    </div>

    <div class="text">
    {IF $CNCAT[config][rating_enable] || $CNCAT[config][comments_add_enable]}
        <a name="form"></a>
        {DISPLAY ERRORS}
        <form action="#form" method="post">
        {IF $CNCAT[config][comments_articles_enable] && $CNCAT[config][comments_add_enable]}
            <div><strong>{$CNCAT[lang][add_your_comment]}:</strong></div>
            <div>{$CNCAT[lang][name]}:<br /><input type="text" class="text" name="author_name" size="30" value="{cn_str($CNCAT[page][form_data][author_name])}" /><br />
            {$CNCAT[lang][email]}:<br /><input type="text" class="text" name="author_email" size="30" value="{cn_str($CNCAT[page][form_data][author_email])}" /></div>
            <div>{$CNCAT[lang][comment]}:<br /><textarea name="text" rows="4" cols="50">{cn_str($CNCAT[page][form_data][text])}</textarea></div>
        {ENDIF}
        {IF $CNCAT[config][rating_enable]}
            <div><a name="vote"></a>
            {$CNCAT[lang][your_vote]}:
            <select name="vote">
                <option value="0">- {$CNCAT[lang][no]} -</option>
                <option value="1" {IF $CNCAT[page][form_data][vote] == 1}selected="selected"{ENDIF}>1</option>
                <option value="2" {IF $CNCAT[page][form_data][vote] == 2}selected="selected"{ENDIF}>2</option>
                <option value="3" {IF $CNCAT[page][form_data][vote] == 3}selected="selected"{ENDIF}>3</option>
                <option value="4" {IF $CNCAT[page][form_data][vote] == 4}selected="selected"{ENDIF}>4</option>
                <option value="5" {IF $CNCAT[page][form_data][vote] == 5}selected="selected"{ENDIF}>5</option>
                <option value="6" {IF $CNCAT[page][form_data][vote] == 6}selected="selected"{ENDIF}>6</option>
                <option value="7" {IF $CNCAT[page][form_data][vote] == 7}selected="selected"{ENDIF}>7</option>
                <option value="8" {IF $CNCAT[page][form_data][vote] == 8}selected="selected"{ENDIF}>8</option>
                <option value="9" {IF $CNCAT[page][form_data][vote] == 9}selected="selected"{ENDIF}>9</option>
                <option value="10" {IF $CNCAT[page][form_data][vote] == 10}selected="selected"{ENDIF}>10</option>
            </select>
            </div>
        {ENDIF}
         <div>
        {IF $CNCAT[page][captcha]}
        <table border="0">
        {$CNCAT[page][captcha]}
        </table>
        {ENDIF}
        {IF $CNCAT[config][rating_enable] || $CNCAT[config][comments_add_enable]}
        <a class="button" href="#" onclick="doPost()"><span>{$CNCAT[lang][do_send]}</span></a>
        </div>
        {ENDIF}
        </form>
        <script type="text/javascript">
        function doPost() {
            var addform = document.getElementById('form_comm');
            var prev = document.createElement('input');
            prev.setAttribute('type', 'hidden');
            prev.setAttribute('name', 'doPost');
            prev.setAttribute('value', '1');
            addform.appendChild(prev);
            addform.submit();
            return false;
        }
        </script>
        {ENDIF}
    </div>
    </div>
{/TEMPLATE}
