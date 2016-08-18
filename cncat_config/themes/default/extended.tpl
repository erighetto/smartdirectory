{TEMPLATE ext
    (ITEM[item_status, item_title, item_title_translite, item_descr, item_descr_full, link_url, link_target, link_jumps_to, link_jumps_from, item_rating_moder, item_image_mime, item_author_name])
}
    {INCLUDE top}
    {INCLUDE menu}

    <table width="100%">
    <tr><td width="50%" style="padding: 0 20px;">
        {IF $CNCAT[banner][page_left]}<table style="margin: 0 auto;"><tr><td>{$CNCAT[banner][page_left]}</td></tr></table>{ENDIF}
    </td><td width="600">
        <table class="pagetitle" style="margin: 10px auto 0 auto;" width="600">
            <tr><td class="title">
                {$CNCAT[lang][site_descr]}
            </td><td class="clear"></td></tr>
        </table>
        <table id="ext">
            <tr><td class="name" colspan="2"></td></tr>
        {IF $CNCAT[item][item_status] == 1}
            <tr><td class="text" colspan="2"><h3>{cn_str($CNCAT[item][item_title])}</h3></td></tr>
            <tr><td class="name">{$CNCAT[lang][link_url]}:</td><td class="text">
            <div style="overflow: hidden;">
                {IF $CNCAT[item][link_target] == 1}
                    <a target="_blank" href="{cn_str($CNCAT[item][link_url])}"><strong>{cn_str($CNCAT[item][link_url])}</strong></a>
                {ENDIF}
                {IF $CNCAT[item][link_target] == 2}
                    <a target="_blank" href="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}"><strong>{cn_str($CNCAT[item][link_url])}</strong></a>
                {ENDIF}
                {IF $CNCAT[item][link_target] == 3}
                    <a target="_blank" href="{cn_str($CNCAT[item][link_url])}" onclick="location.href='{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}'; return false;"><strong>{cn_str($CNCAT[item][link_url])}</strong></a>
                {ENDIF}
            </div>
            </td></tr>
            <tr><td class="name" colspan="2">{$CNCAT[lang][categories]}:</td></tr>
            <tr><td class="text" colspan="2">
                    {DISPLAY CATEGORIES}
            </td></tr>
            <tr><td class="name" colspan="2">{$CNCAT[lang][descr]}:</td></tr>
            <tr><td class="text" colspan="2">{cn_str($CNCAT[item][item_descr])}</td></tr>
            {IF $CNCAT[item][item_descr_full]}
                <tr><td class="name" colspan="2">{$CNCAT[lang][descr_full]}:</td></tr>
                <tr><td class="text" colspan="2">{cn_str($CNCAT[item][item_descr_full])}</td></tr>
            {ENDIF}
            {DISPLAY FILTERS}
            <tr>
                <td class="name">{$CNCAT[lang][moder_rating]}:</td>
                <td class="text">
                    {IF $CNCAT[item][item_rating_moder]}
                        {cn_str($CNCAT[item][item_rating_moder])}
                    {ELSE}
                        {$CNCAT[lang][no_moder_rating]}
                    {ENDIF}
                </td>
            </tr>
            {IF $CNCAT[config][rating_enable]}
            <tr>
                <td class="name">{$CNCAT[lang][users_rating]}:</td>
                <td class="text">
                    {IF $CNCAT[item][item_rating_users] > 0}
                        {cn_str($CNCAT[item][item_rating_users])} ({$CNCAT[lang][votes_count]}: {$CNCAT[item][item_votes_count]})
                    {ELSE}
                        {$CNCAT[lang][no]}
                    {ENDIF}
                </td>
            </tr>
            {ENDIF}
            <tr><td class="name">{$CNCAT[lang][jumps_to]}:</td><td class="text">{cn_str($CNCAT[item][link_jumps_to])}</td></tr>
            <tr><td class="name">{$CNCAT[lang][jumps_from]}:</td><td class="text">{cn_str($CNCAT[item][link_jumps_from])}</td></tr>
            {DISPLAY EXTFIELDS}
            {DISPLAY COMMENTS}
        {ELSE}
            <tr><td class="text">{$CNCAT[lang][link_not_approved]}</td></tr>
        {ENDIF}
            {IF $CNCAT[item][_control_bar]}
            <tr><td class="text" colspan="2">
                <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=delete&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_delete]}</a>&nbsp;&bull;&nbsp;
                <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=isolation&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_isolation]}</a>&nbsp;&bull;&nbsp;
                {IF $CNCAT[item][item_status] == 1}
                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=asnew&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_asnew]}</a>&nbsp;&bull;&nbsp;
                {ELSE}
                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;mode=approve&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_approve]}</a>&nbsp;&bull;&nbsp;
                {ENDIF}
                <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links_add&amp;mode=forms&amp;edit=1&amp;id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_edit]}</a>&nbsp;&bull;&nbsp;
                <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=links&amp;type=1&amp;checkone_id={$CNCAT[item][item_id]}">{$CNCAT[lang][do_check]}</a>&nbsp;&bull;&nbsp;
                {DISPLAY ADMIN_RATING}
            </td></tr>
            {ENDIF}
            <tr><td class="name" colspan="2"></td></tr>
        </table>
        <table style="margin: 0 auto;" width="600">
            <tr><td>
                {cn_copyright(1)}
            </td></tr>
        </table>
    </td><td width="50%" style="padding: 0 20px;">
        {IF $CNCAT[banner][page_right]}<table style="margin: 0 auto;"><tr><td>{$CNCAT[banner][page_right]}</td></tr></table>{ENDIF}
    </td></tr></table>

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
        <tr><td class="name">{cn_str($CNCAT[filter][title])}:</td><td class="text">
{/TEMPLATE}
{TEMPLATE ext_filtval}{cn_str($CNCAT[filtval][title])}{/TEMPLATE}
{TEMPLATE ext_filtval_delim}, {/TEMPLATE}
{TEMPLATE ext_filter_bottom}
        </td></tr>
{/TEMPLATE}

{TEMPLATE ext_ext_field}
    {IF $CNCAT[extfield][type] == 1 || $CNCAT[extfield][type] == 2}
        {IF is_numeric($CNCAT[extfield][value])}
            <tr><td class="name">{cn_str($CNCAT[extfield][title])}:</td><td class="text">{cn_str($CNCAT[extfield][value])}</td></tr>
        {ENDIF}
    {ELSE}
        {IF $CNCAT[extfield][value]}
            {IF $CNCAT[extfield][type] == 3}
                <tr><td class="name">{cn_str($CNCAT[extfield][title])}:</td><td class="text">{cn_str($CNCAT[extfield][value])}</td></tr>
            {ENDIF}
            {IF $CNCAT[extfield][type] == 4}
                <tr><td class="name">{cn_str($CNCAT[extfield][title])}:</td><td class="text">{cn_date($CNCAT[extfield][value])}</td></tr>
            {ENDIF}
            {IF $CNCAT[extfield][type] == 5}
                <tr><td class="name">{cn_str($CNCAT[extfield][title])}:</td><td class="text">{cn_str($CNCAT[extfield][value])}</td></tr>
            {ENDIF}
        {ENDIF}
        {IF $CNCAT[extfield][type] == 6}
            {IF $CNCAT[extfield][value]}
                <tr><td class="name">{cn_str($CNCAT[extfield][title])}:</td><td class="text">
                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}image.php?item={$CNCAT[item][item_id]}&amp;field={$CNCAT[extfield][name]}"><img src="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}image.php?item={$CNCAT[item][item_id]}&amp;thumb=1&amp;field={$CNCAT[extfield][name]}" /></a>
                </td></tr>
            {ENDIF}
        {ENDIF}
    {ENDIF}
{/TEMPLATE}

{TEMPLATE ext_comments_top}
    {IF $CNCAT[config][comments_links_enable]}
        <tr><td class="name" colspan="2"><a name="comments"></a>{$CNCAT[lang][comments]}:</td></tr>
        {IF !$CNCAT[item][item_com_count]}<tr><td class="text" colspan="2"><em>{$CNCAT[lang][no_comments]}</em></td></tr>{ENDIF}
    {ENDIF}
    {IF $CNCAT[comments][post_result]}
        <tr><td colspan="2" class="text" style="background-color: #f2fff2;">{$CNCAT[comments][post_result]}</td></tr>
    {ENDIF}
    <tr><td colspan="2" class="text">
{/TEMPLATE}

{TEMPLATE ext_comment}
        <div style="padding: 10px;{IF !$CNCAT[comment][display]}background-color: #f2f2f2;{ENDIF}">
            <a name="comment_{$CNCAT[comment][id]}"></a>
            {cn_date($CNCAT[comment][date_insert])}{IF $CNCAT[comment][author_name]}: <strong>{$CNCAT[comment][author_name]}</strong>{ENDIF}
            <p>{cn_str_nl2br($CNCAT[comment][text])}</p>
            {IF $CNCAT[item][_control_bar]}
                {IF $CNCAT[comment][display]}
                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=comments&amp;action=hide&amp;id={$CNCAT[comment][id]}">{$CNCAT[lang][do_hide]}</a> |
                {ELSE}
                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=comments&amp;action=show&amp;id={$CNCAT[comment][id]}">{$CNCAT[lang][do_show]}</a> |
                {ENDIF}
                <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=comments&amp;action=delete&amp;id={$CNCAT[comment][id]}">{$CNCAT[lang][do_delete]}</a>
            {ENDIF}
        </div>
        <hr size="1" />
{/TEMPLATE}

{TEMPLATE ext_comments_bottom}
    </td></tr>

    <tr><td colspan="2" class="text">
        <a name="form"></a>
        {DISPLAY ERRORS}

        <form action="#form" method="post">
        {IF $CNCAT[config][comments_links_enable] && $CNCAT[config][comments_add_enable]}
            <p><strong>{$CNCAT[lang][add_your_comment]}:</strong></p>
            {$CNCAT[lang][name]}:<br /><input type="text" class="text" name="author_name" size="30" value="{cn_str($CNCAT[page][form_data][author_name])}" /><br />
            {$CNCAT[lang][email]}:<br /><input type="text" class="text" name="author_email" size="30" value="{cn_str($CNCAT[page][form_data][author_email])}" />
            <p>{$CNCAT[lang][comment]}:<br /><textarea name="text" rows="4" cols="50">{cn_str($CNCAT[page][form_data][text])}</textarea></p>
        {ENDIF}
        {IF $CNCAT[config][rating_enable]}
            <a name="vote"></a>
            <p>{$CNCAT[lang][your_vote]}:
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
            </p>
        {ENDIF}
        <table border="0">
        {$CNCAT[page][captcha]}
        </table>
        {IF $CNCAT[config][rating_enable] || $CNCAT[config][comments_add_enable]}
            <input type="submit" name="doPost" value="{$CNCAT[lang][do_send]}" class="submit" />
        {ENDIF}
        </form>
    </td></tr>
{/TEMPLATE}

{TEMPLATE ext_comments_errors_top}
<ul class="errors">
{/TEMPLATE}

{TEMPLATE ext_comments_error}
    <li>{$CNCAT[comments][form_error]}</li>
{/TEMPLATE}

{TEMPLATE ext_comments_errors_bottom}
</ul>
{/TEMPLATE}
