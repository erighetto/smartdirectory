{TEMPLATE add}
    {INCLUDE top}
    <div id="cncontent">
    <div id="side_pane" class="cnleft">
    {INCLUDE menu}
    {IF $CNCAT[banner][page_left]}
        <div class="banner">{$CNCAT[banner][page_left]}</div>
        {ENDIF}
    {INCLUDE js_calendar}
    <script type="text/javascript" src="{$CNCAT[abs]}{$CNCAT[system][dir_engine_scripts]}tinymce/tiny_mce.js"></script>
    <script type="text/javascript" src="{$CNCAT[abs]}{$CNCAT[system][dir_engine_scripts]}jquery/jquery.js"></script> 
    </div>
    <div id="cnmain">
        <div id="add_table">
            <div class="title">
                {$CNCAT[lang][menu_add_item]}
            </div>
            <div id="add_page">
            	  {DISPLAY FORM}
            </div>
            {IF $CNCAT[banner][page_right]}
            <div class="banner">{$CNCAT[banner][page_right]}</div>
            {ENDIF}
        </div>
    </div>
    <script type="text/javascript">
    function prev(att_name) {
        var addform = document.getElementById('add_form');
        var prev = document.createElement('input');
        prev.setAttribute('type', 'hidden');
        prev.setAttribute('name', att_name);
        prev.setAttribute('value', '1');
        addform.appendChild(prev);
        addform.submit();
        return false;
    }
    </script>
    </div>
    {INCLUDE bottom}
{/TEMPLATE}

{TEMPLATE final}
	<div class="text"><p><b>{$CNCAT[lang][thanks_for_add]}</b>
        {IF $CNCAT[config][display_ext]}
            {$CNCAT[lang][ext_after_submit]}
        {ENDIF}</p>
	    <p style="margin-top: 10px;"><a href="{$CNCAT[config][cncat_url]}">{$CNCAT[lang][return_to_main]}</a></p>
  </div>
	{IF $CNCAT[config][add_mode] == 0}{DISPLAY BACKLINKS}{ENDIF}
{/TEMPLATE}

{TEMPLATE form}
    {IF $CNCAT[config][add_show_message] == 1}<div class="text">{$CNCAT[config][add_message]}</div>{ENDIF}
    {DISPLAY ERRORS}
    {IF $CNCAT[config][add_accept_to_add] == 1}<div class="text">{$CNCAT[lang][accept_work]}</div>{ENDIF}
    {IF $CNCAT[config][add_accept_to_add] == 2}<div id="work_back" class="text">{$CNCAT[lang][accept_work_with_back]}</div>{ENDIF}
    {IF $CNCAT[config][add_check_link]}<div id="first_check" class="text">{$CNCAT[lang][first_check_link]}</div>{ENDIF}
    {DISPLAY BACKLINKS}
    <div id="addform">
        <form action="{$CNCAT[page][add_url]}" method="post" enctype="multipart/form-data" id="add_form"> 
        <table>
            {INCLUDE field_link_url}
            {INCLUDE field_back_link}
            {INCLUDE field_site_title}
            {INCLUDE field_cats}
            {INCLUDE field_email}
            {INCLUDE field_author}
            {INCLUDE field_descr}
            {INCLUDE field_descr_full}
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

{TEMPLATE step1}
    {IF $CNCAT[config][add_show_message] == 1}<div class="text">{$CNCAT[config][add_message]}</div>{ENDIF}
    {DISPLAY ERRORS}
    {IF $CNCAT[config][add_accept_to_add] == 1}<div class="text">{$CNCAT[lang][accept_work]}</div>{ENDIF}
    {IF $CNCAT[config][add_accept_to_add] == 2}<div id="work_back" class="text">{$CNCAT[lang][accept_work_with_back]}</div>{ENDIF}
    {IF $CNCAT[config][add_check_link]}<div id="first_check" class="text">{$CNCAT[lang][first_check_link]}</div>{ENDIF}
    <div id="addform">
        <form action="{$CNCAT[page][add_url]}" method="post" id="add_form">
        <table>
            {INCLUDE field_site_title}
            {INCLUDE field_link_url}
            {$CNCAT[page][captcha]}
            <tr><td colspan="2" class="submit">
            <a class="button cnright" href="#" onclick="return prev('next');"><span>{$CNCAT[lang][do_next]}</span></a>
            </td></tr>
        </table>
        </form>
    </div>
{/TEMPLATE}

{TEMPLATE step2}
    {DISPLAY ERRORS}
	  <div class="text">
    {IF $CNCAT[config][add_accept_to_add] > 1}
        {$CNCAT[lang][first_check_back_link]}
    {ELSE}
        {$CNCAT[lang][back_link_not_required]}
    {ENDIF}
    </div>
    {DISPLAY BACKLINKS}
    <div id="addform">
    <form action="{$CNCAT[page][add_url]}" method="post" id="add_form" enctype="multipart/form-data">
    <table>
        {INCLUDE field_link_url}
        {IF $CNCAT[config][add_use_back_link]}{INCLUDE field_back_link}{ENDIF}
        <tr><td colspan="2" class="submit">
        <a class="button cnright" href="#" onclick="return prev('prev');"><span>{$CNCAT[lang][do_back]}</span></a>
        <a class="button cnright" href="#" onclick="return prev('next');"><span>{$CNCAT[lang][do_next]}</span></a>    
        </td></tr>
    </table>
    </form>
    </div>
{/TEMPLATE}

{TEMPLATE step3}
    {DISPLAY ERRORS}
    <div id="addform">
    <form action="{$CNCAT[page][add_url]}" method="post" id="add_form" enctype="multipart/form-data">
    <table>
        {INCLUDE field_link_url}
        {IF $CNCAT[config][add_use_back_link]}{INCLUDE field_back_link}{ENDIF}
        {INCLUDE field_site_title}
        {INCLUDE field_cats}
        {INCLUDE field_email}
        {INCLUDE field_author}
        {INCLUDE field_descr}
        {INCLUDE field_descr_full}
        {INCLUDE field_meta_keywords}
        {INCLUDE field_meta_descr}
    		{DISPLAY FILTERS}
    		{DISPLAY EXTFIELDS}
        <tr><td colspan="2" class="submit">
        <a class="button cnright" href="#" onclick="return prev('prev');"><span>{$CNCAT[lang][do_back]}</span></a>
        <a class="button cnright" href="#" onclick="return prev('next');"><span>{$CNCAT[lang][do_next]}</span></a>  
        </td></tr>
    </table>
    </form>
    </div>
{/TEMPLATE}

{TEMPLATE field_link_url}
        <tr>
            <td class="name">{$CNCAT[lang][field_link_url]}:</td>
            <td class="field">
                <input type="text" class="{IF $CNCAT[add][readonly_link_url]}read{ELSE}text{ENDIF}" name="link_url" value="{cn_str($CNCAT[add][link_url])}" {IF $CNCAT[add][readonly_link_url]}readonly="readonly"{ENDIF} />
            </td>
        </tr>
{/TEMPLATE}

{TEMPLATE field_back_link}
        {IF $CNCAT[config][add_use_back_link]}
        <tr>
            <td class="name">{$CNCAT[lang][field_back_link]}:</td>
            <td class="field">
                <input type="text" class="{IF $CNCAT[add][readonly_back_link]}read{ELSE}text{ENDIF}" name="back_link" value="{cn_str($CNCAT[add][back_link])}" {IF $CNCAT[add][readonly_back_link]}readonly="readonly"{ENDIF} />
            </td>
        </tr>
        {ENDIF} 
{/TEMPLATE}

{TEMPLATE field_site_title}
        <tr>
            <td class="name">{$CNCAT[lang][field_site_title]}:</td>
            <td class="field"><input type="text" class="text" name="site_title" value="{cn_str($CNCAT[add][site_title])}" /></td>
        </tr>
{/TEMPLATE}

{TEMPLATE field_cats}
        <tr><td colspan="2" class="name">
        <script type="text/javascript">
        //<![CDATA[
            var count = 0;

            function checkCats(c) {
                if (c.checked) {
                    count++;

                    if (count > {$CNCAT[config][add_max_cats]}) {
                        c.checked = false;
                        count = {$CNCAT[config][add_max_cats]};
                        alert("{$CNCAT[lang][cats_to_long]}");
                    }
                } else {
                    count--;
                }
            }

            {IF $CNCAT[config][add_cat_view]}
                $(document).ready(function() {
                    $('#categories li').each(function (index, li) {
                        var listItem = $(li);
                        var toggle = $('> .toggle', listItem);
                        var childList = $('+ li > ul:first', listItem);
    
                        if (!childList.size()) {
                            childList = $('> ul:first', listItem);
                        }
    
                        if (!childList.size()) {
                            return;
                        }
    
                        toggle.toggle(
                            function () {
                                toggle.attr('src', '{$THEMEURL}images/expand.gif');
                                childList.each(function (index, ul) {
                                    $(ul).css('display', '');
                                });
                            },
                            function () {
                                toggle.attr('src', '{$THEMEURL}images/collapse.gif');
                                childList.each(function (index, ul) {
                                    $(ul).css('display', 'none');
                                });
                            }
                        );
                    });
                });
            {ENDIF}
        //]]>
        </script>
        {$CNCAT[lang][field_cats]}:</td></tr>
        <tr><td colspan="2" class="text">
            <div class="categories" id="categories">{DISPLAY CATEGORIES}</div>
        </td></tr> 
{/TEMPLATE}

{TEMPLATE field_email}
        <tr>
            <td class="name">{$CNCAT[lang][field_email]}:</td>
            <td class="field"><input type="text" class="text" name="email" value="{cn_str($CNCAT[add][email])}" /></td>
        </tr>
{/TEMPLATE}

{TEMPLATE field_author}
    {IF $CNCAT[config][add_use_author]}
        <tr>
            <td class="name">{$CNCAT[lang][field_author]}:</td>
            <td class="field">
                <input type="text" class="text" name="author" value="{cn_str($CNCAT[add][author])}" />
            </td>
        </tr>
    {ENDIF}
{/TEMPLATE}

{TEMPLATE field_descr}
        <tr><td colspan="2" class="name">{$CNCAT[lang][field_descr]}:</td></tr>
        <tr>
            <td colspan="2" class="text">
                {DISPLAY SHORT_EDITOR}
            </td>
        </tr>
{/TEMPLATE}

{TEMPLATE field_descr_full}
        {IF $CNCAT[config][add_use_descr_full]}
        <tr><td colspan="2" class="name">{$CNCAT[lang][field_descr_full]}:</td></tr>
        <tr><td colspan="2" class="name">
                {DISPLAY EDITOR}
        </td></tr>
        {ENDIF}
{/TEMPLATE}

{TEMPLATE field_meta_keywords}
        {IF $CNCAT[config][add_use_meta_keywords]}
        <tr>
            <td class="name">{$CNCAT[lang][field_meta_keywords]}:</td>
            <td class="field"><input type="text" class="text" name="meta_keywords" value="{cn_str($CNCAT[add][meta_keywords])}" /></td>
        </tr>
        {ENDIF}
{/TEMPLATE}

{TEMPLATE field_meta_descr}
        {IF $CNCAT[config][add_use_meta_descr]}
        <tr>
            <td class="name">{$CNCAT[lang][field_meta_descr]}:</td>
            <td class="field"><input type="text" class="text" name="meta_descr" value="{cn_str($CNCAT[add][meta_descr])}" /></td>
        </tr>
        {ENDIF}
{/TEMPLATE}

{TEMPLATE field_captcha}
        <tr><td colspan="2" class="name">{$CNCAT[lang][input_image_code]}:</td></tr>
        <tr>
            <td class="name"><img src="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}code.php?{$CNCAT[add][rand]}" alt="" onclick="this.src='{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}code.php?'+Math.random();" /></td>
            <td class="field"><input type="text" class="text" name="captcha_code" value="" /></td>
        </tr>
{/TEMPLATE}
{TEMPLATE field_recaptcha}
        <tr>
            <td colspan="2" class="name">
            {$CNCAT[page][recaptcha]}
            </td>
        </tr>
{/TEMPLATE}
{TEMPLATE field_keycaptcha}
        <tr>
            <td colspan="2" class="name">
            <input type="hidden" id="capcode" name="capcode" />
            {$CNCAT[page][keycaptcha]}
            </td>
        </tr>
{/TEMPLATE}

{TEMPLATE field_submit}
        <tr>
            <td class="submit" colspan="2">
              <a class="button cnright" href="#" onclick="return prev('next');"><span>{$CNCAT[lang][do_next]}</span></a>
            </td>
        </tr>
{/TEMPLATE}

{TEMPLATE add_filter_top}
        <tr>
			<td class="name">{cn_str($CNCAT[filter][title])}:</td>
			<td class="field">
{/TEMPLATE}
{TEMPLATE add_filtval}
                <input type="checkbox" name="filters[{$CNCAT[filter][id]}][]" value="{cn_str($CNCAT[filtval][id])}" id="filtval_{$CNCAT[filtval][id]}"  {IF $CNCAT[filtval][_checked]}checked="checked"{ENDIF} /> <label for="filtval_{$CNCAT[filtval][id]}">{cn_str($CNCAT[filtval][title])}</label><br />
{/TEMPLATE}
{TEMPLATE add_filter_bottom}
			</td>
		</tr>
{/TEMPLATE}

{TEMPLATE add_backlinks_top}
        <div id="templ_backlinks">
        <div class="text">{$CNCAT[lang][backlinks_list]}:</div>
        <div class="text">
            <table width="100%" class="backlinks">
{/TEMPLATE}

{TEMPLATE add_backlink}
                <tr><td class="code_backlink">
                    <div class="user_code">{cn_str($CNCAT[backlink][user_code])}</div>
                </td><td class="preview_backlink">
                    {$CNCAT[backlink][user_code]}
                </td></tr>
{/TEMPLATE}

{TEMPLATE add_backlinks_bottom}
            </table>
        </div>
        </div>
{/TEMPLATE}

{TEMPLATE add_errors_top}
    <div id="errors" class="text">
        <div class="text">
            <strong>{$CNCAT[lang][add_errors_occurred]}</strong><br />
            <div>
            <ul class="errors">
{/TEMPLATE}

{TEMPLATE add_error}
                <li>{$CNCAT[error][text]}</li>
{/TEMPLATE}

{TEMPLATE add_errors_bottom}
            </ul>
            </div>
        </div>
    </div>
{/TEMPLATE}

{TEMPLATE add_cat_next_level}
    {IF  $CNCAT[config][add_cat_view] && $CNCAT[cat][tree_level] > 1}<li>{ENDIF}
    <ul class="catmap_level{$CNCAT[cat][tree_level]}" {IF  $CNCAT[config][add_cat_view] && $CNCAT[cat][tree_level] > 1}style="display: none;"{ENDIF}>
{/TEMPLATE}

{TEMPLATE add_cat
    (CAT[id, title, disable_add])
}
    <li style="color: {IF $CNCAT[cat][disable_add]}gray{ELSE}black{ENDIF};">
        {IF $CNCAT[config][add_cat_view]}
            {IF $CNCAT[cat][child_id_list]}
                <img class="toggle" src="{$THEMEURL}images/collapse.gif" alt=""/>
            {ELSE}
                <img class="toggle" src="{$THEMEURL}images/notoggle.gif" alt=""/>
            {ENDIF}
        {ENDIF}
        {IF !$CNCAT[cat][disable_add]}
            <input type="checkbox" name="cats[]" value="{$CNCAT[cat][id]}" id="cat_{$CNCAT[cat][id]}" {IF $CNCAT[cat][_checked]}checked="checked"{ENDIF} onclick="checkCats(this)" /> <label for="cat_{$CNCAT[cat][id]}">{cn_str($CNCAT[cat][title])}</label>
        {ELSE}
            {cn_str($CNCAT[cat][title])}
        {ENDIF}
    </li>    
{/TEMPLATE}

{TEMPLATE add_cat_prev_level}
    </ul>
    {IF  $CNCAT[config][add_cat_view] && $CNCAT[cat][tree_level] > 0}</li>{ENDIF}    
{/TEMPLATE}

{TEMPLATE add_extfield}
    {IF $CNCAT[extfield][type] == 1}
        <tr>
            <td class="name">{$CNCAT[extfield][title]}{IF $CNCAT[extfield][required]}<sup>*</sup>{ENDIF}:</td>
            <td class="field"><input type="text" class="text" name="{$CNCAT[extfield][name]}" value="{cn_str($CNCAT[extfield][value])}" /></td>
        </tr>
    {ENDIF}
    {IF $CNCAT[extfield][type] == 2}
        <tr>
            <td class="name">{$CNCAT[extfield][title]}{IF $CNCAT[extfield][required]}<sup>*</sup>{ENDIF}:</td>
            <td class="field"><input type="text" class="text" name="{$CNCAT[extfield][name]}" value="{cn_str($CNCAT[extfield][value])}" /></td>
        </tr>
    {ENDIF}
    {IF $CNCAT[extfield][type] == 3}
        <tr>
            <td class="name">{$CNCAT[extfield][title]}{IF $CNCAT[extfield][required]}<sup>*</sup>{ENDIF}:</td>
            <td class="field"><input type="text" class="text" name="{$CNCAT[extfield][name]}" value="{cn_str($CNCAT[extfield][value])}" /></td>
        </tr>
    {ENDIF}
    {IF $CNCAT[extfield][type] == 4}
        <tr>
            <td class="name">{$CNCAT[extfield][title]}{IF $CNCAT[extfield][required]}<sup>*</sup>{ENDIF}:</td>
            <td class="field">
                <div id="{$CNCAT[extfield][name]}_calendar" class="calendar"></div>
                <input type="text" class="text" style="width: 70%;" id="{$CNCAT[extfield][name]}" name="{$CNCAT[extfield][name]}" value="{cn_str($CNCAT[extfield][value])}" readonly="readonly" />
                <a href="#" onclick="ShowCalendarE('{$CNCAT[extfield][name]}_calendar', '{$CNCAT[extfield][name]}', 0); return false;"><img src="{$THEMEURL}images/calendar.gif" alt="" /></a>
                <a href="#" onclick="document.getElementById('{$CNCAT[extfield][name]}').value=''; return false;"><img src="{$THEMEURL}images/delete.gif" alt="" /></a>
            </td>
        </tr>
    {ENDIF}
    {IF $CNCAT[extfield][type] == 5}
        <tr>
            <td class="name">{$CNCAT[extfield][title]}{IF $CNCAT[extfield][required]}<sup>*</sup>{ENDIF}:</td>
            <td class="field"><textarea name="{$CNCAT[extfield][name]}" col="" rows="">{cn_str($CNCAT[extfield][value])}</textarea></td>
        </tr>
    {ENDIF}
    {IF $CNCAT[extfield][type] == 6}
        <tr>
            <td class="name">{$CNCAT[extfield][title]}{IF $CNCAT[extfield][required]}<sup>*</sup>{ENDIF}:</td>
            <td class="field"><input type="file" name="{$CNCAT[extfield][name]}" /></td>
        </tr>
    {ENDIF}
{/TEMPLATE}
