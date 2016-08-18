{TEMPLATE add}
    {INCLUDE top}
    {INCLUDE menu}

    <style type="text/css" media="all">
        #add_table {
            margin: 0 auto;
            border: 0;
            border-collapse: collapse;
            width: 500px;
        }

        #add_table td {
            padding: 5px;
        }

        #add_table td input.text {
            width: 320px;
        }

        #add_table td textarea {
            width: 100%;
            height: 100px;
        }

        #add_table td.submit {
            text-align: right;
        }

        #add_table td.submit input {
            width: 150px;
        }
        
        #add_table ul.errors {
            color: red;
        }

        #cat {
            border-top: 1px solid gray;
            border-left: 1px solid gray;
        }

        #cat li {
            white-space: nowrap;
        }
        
        #cat li input {
            width: auto;
        }
    </style>

    <script type="text/javascript" src="{$CNCAT[abs]}{$CNCAT[system][dir_engine_scripts]}tinymce/tiny_mce.js"></script>
    {INCLUDE js_calendar}

    <hr />
    <table id="add_table" align="center">
        {DISPLAY FORM}
    </table>

    {INCLUDE bottom}
{/TEMPLATE}

{TEMPLATE final}
	<tr>
		<td colspan="2">{$CNCAT[lang][thanks_for_add]}
        {IF $CNCAT[config][display_ext]}
            {$CNCAT[lang][ext_after_submit]}
	    {ELSE}
	        <a href="{$CNCAT[config][cncat_url]}">{$CNCAT[lang][return_to_main]}</a>
        {ENDIF}
    </tr>
	{IF $CNCAT[config][add_mode] == 0}
        {DISPLAY BACKLINKS}
    {ENDIF}
{/TEMPLATE}

{TEMPLATE form}
    {IF $CNCAT[config][add_show_message] == 1}<tr><td colspan="2">{$CNCAT[config][add_message]}</td></tr>{ENDIF}
    <tr>
        <td colspan="2">{DISPLAY ERRORS}</td>
    </tr>
    {IF $CNCAT[config][add_accept_to_add] == 1}<tr><td colspan="2">{$CNCAT[lang][accept_work]}</td></tr>{ENDIF}
    {IF $CNCAT[config][add_accept_to_add] == 2}<tr><td colspan="2">{$CNCAT[lang][accept_work_with_back]}</td></tr>{ENDIF}
    {IF $CNCAT[config][add_check_link]}<tr><td colspan="2">{$CNCAT[lang][first_check_link]}</td></tr>{ENDIF}
    <form action="?submit" method="post">
        {INCLUDE field_link_url}
        {INCLUDE field_back_link}
        {INCLUDE field_site_title}
        {INCLUDE field_cats}
        {INCLUDE field_email}
        {INCLUDE field_descr}
        {INCLUDE field_descr_full}
        {INCLUDE field_meta_keywords}
        {INCLUDE field_meta_descr}
		{DISPLAY FILTERS}
		{DISPLAY EXTFIELDS}
        {INCLUDE field_captcha}
        {INCLUDE field_submit}
    </form>
{/TEMPLATE}

{TEMPLATE step1}
    {IF $CNCAT[config][add_show_message] == 1}<tr><td colspan="2">{$CNCAT[config][add_message]}</td></tr>{ENDIF}
    <tr>
        <td colspan="2">{DISPLAY ERRORS}</td>
    </tr>
    {IF $CNCAT[config][add_accept_to_add] == 1}<tr><td colspan="2">{$CNCAT[lang][accept_work]}</td></tr>{ENDIF}
    {IF $CNCAT[config][add_accept_to_add] == 2}<tr><td colspan="2">{$CNCAT[lang][accept_work_with_back]}</td></tr>{ENDIF}
	{IF $CNCAT[config][add_check_link]}<tr><td colspan="2">{$CNCAT[lang][first_check_link]}</td></tr>{ENDIF}
    <form action="?submit" method="post">
		{INCLUDE field_site_title}
        {INCLUDE field_link_url}
        {INCLUDE field_captcha}
        <tr><td colspan="2" class="submit"><input type="submit" name="next" value="{$CNCAT[lang][do_next]}" /></td></tr>
    </form>
{/TEMPLATE}

{TEMPLATE step2}
    <tr>
        <td colspan="2">{DISPLAY ERRORS}</td>
    </tr>
	<tr><td colspan="2">
    {IF $CNCAT[config][add_accept_to_add] > 1}
        {$CNCAT[lang][first_check_back_link]}
    {ELSE}
        {$CNCAT[lang][back_link_not_required]}
    {ENDIF}
    </td></tr>
    <script type="text/javascript">
    function prev() {
        var addform = document.getElementById('addform');
        var prev = document.createElement('input');
        prev.setAttribute('type', 'hidden');
        prev.setAttribute('name', 'prev');
        prev.setAttribute('value', '1');
        addform.appendChild(prev);
        addform.submit();
    }
    </script>
    <form action="?submit" method="post" id="addform">
        {INCLUDE field_link_url}
        {IF $CNCAT[config][add_use_back_link]}{INCLUDE field_back_link}{ENDIF}
        {DISPLAY BACKLINKS}
        <tr><td colspan="2" class="submit"><input type="button" value="{$CNCAT[lang][do_back]}" onclick="prev()" /> <input type="submit" name="next" value="{$CNCAT[lang][do_next]}" /></td></tr>
    </form>
{/TEMPLATE}

{TEMPLATE step3}
    <tr>
        <td colspan="2">{DISPLAY ERRORS}</td>
    </tr>
    <script type="text/javascript">
    function prev() {
        var addform = document.getElementById('addform');
        var prev = document.createElement('input');
        prev.setAttribute('type', 'hidden');
        prev.setAttribute('name', 'prev');
        prev.setAttribute('value', '1');
        addform.appendChild(prev);
        addform.submit();
    }
    </script>
    <form action="?submit" method="post" id="addform">
        {INCLUDE field_link_url}
        {IF $CNCAT[config][add_use_back_link]}{INCLUDE field_back_link}{ENDIF}
        {INCLUDE field_site_title}
        {INCLUDE field_cats}
        {INCLUDE field_email}
        {INCLUDE field_descr}
        {INCLUDE field_descr_full}
        {INCLUDE field_meta_keywords}
        {INCLUDE field_meta_descr}
		{DISPLAY FILTERS}
		{DISPLAY EXTFIELDS}
        <tr><td colspan="2" class="submit"><input type="button" value="{$CNCAT[lang][do_back]}" onclick="prev()" /> <input type="submit" name="next" value="{$CNCAT[lang][do_next]}" /></td></tr>
    </form>
{/TEMPLATE}

{TEMPLATE field_link_url}
        <tr>
            <td>{$CNCAT[lang][field_link_url]}:</td>
            <td>
                <input type="text" class="text" name="link_url" value="{cn_str($CNCAT[add][link_url])}" {IF $CNCAT[add][readonly_link_url]}readonly="readonly"{ENDIF} />
            </td>
        </tr>
{/TEMPLATE}

{TEMPLATE field_back_link}
        {IF $CNCAT[config][add_use_back_link]}
        <tr>
            <td>{$CNCAT[lang][field_back_link]}:</td>
            <td>
                <input type="text" class="text" name="back_link" value="{cn_str($CNCAT[add][back_link])}" {IF $CNCAT[add][readonly_back_link]}readonly="readonly"{ENDIF} />
            </td>
        </tr>
        {ENDIF} 
{/TEMPLATE}

{TEMPLATE field_site_title}
        <tr>
            <td>{$CNCAT[lang][field_site_title]}:</td>
            <td><input type="text" class="text" name="site_title" value="{cn_str($CNCAT[add][site_title])}" /></td>
        </tr>
{/TEMPLATE}

{TEMPLATE field_cats}
        <script type="text/javascript">
            var count = 0;
    
            function checkCats(c) {
                if (c.checked) {
                    count++;
    
                    if (count > {$CNCAT[config][add_max_cats]}) {
                        c.checked = false;
                        count = {$CNCAT[config][add_max_cats]};
                        alert("{$CNCAT[lang][add_cats_to_long]}");
                    }
                } else {
                    count--;
                }
            }
        </script>

        <tr>
            <td colspan="2">
                {$CNCAT[lang][field_cats]}:<br />
                <div style="overflow: scroll; height: 300px;" id="cat">{DISPLAY CATEGORIES}</div>
            </td>
        <tr> 
{/TEMPLATE}

{TEMPLATE field_email}
        <tr>
            <td>{$CNCAT[lang][field_email]}:</td>
            <td><input type="text" class="text" name="email" value="{cn_str($CNCAT[add][email])}" /></td>
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
        <tr>
            <td colspan="2">
                {$CNCAT[lang][field_descr]}:<br />
                <textarea name="descr">{cn_str($CNCAT[add][descr])}</textarea>
            </td>
        </tr>
{/TEMPLATE}

{TEMPLATE field_descr_full}
        {IF $CNCAT[config][add_use_descr_full]}
        <tr>
            <td colspan="2">
                {$CNCAT[lang][field_descr_full]}:<br />
                {DISPLAY EDITOR}
            </td>
        </tr>
        {ENDIF}
{/TEMPLATE}

{TEMPLATE field_meta_keywords}
        {IF $CNCAT[config][add_use_meta_keywords]}
        <tr>
            <td>{$CNCAT[lang][field_meta_keywords]}:</td>
            <td><input type="text" class="text" name="meta_keywords" value="{cn_str($CNCAT[add][meta_keywords])}" /></td>
        </tr>
        {ENDIF}
{/TEMPLATE}

{TEMPLATE field_meta_descr}
        {IF $CNCAT[config][add_use_meta_descr]}
        <tr>
            <td>{$CNCAT[lang][field_meta_descr]}:</td>
            <td><input type="text" class="text" name="meta_descr" value="{cn_str($CNCAT[add][meta_descr])}" /></td>
        </tr>
        {ENDIF}
{/TEMPLATE}

{TEMPLATE field_captcha}
        {IF $CNCAT[config][add_use_captcha]}
        <tr><td colspan="2">{$CNCAT[lang][input_image_code]}:</td></tr>
        <tr>
            <td><img src="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}code.php?{$CNCAT[add][rand]}" alt="" onclick="this.src='{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}code.php?'+Math.random();"></td>
            <td><input type="text" class="text" name="captcha_code" value="{cn_str($CNCAT[add][captcha_code])}" /></td>
        </tr>
        {ENDIF}
{/TEMPLATE}

{TEMPLATE field_submit}
        <tr>
            <td class="submit" colspan="2"><input type="submit" name="next" value="{$CNCAT[lang][do_submit]}" /></td>
        </tr>
{/TEMPLATE}

{TEMPLATE add_filter_top}
        <tr>
			<td>{$CNCAT[filter][title]}:</td>
			<td>
{/TEMPLATE}
{TEMPLATE add_filtval}
                <input type="checkbox" name="filters[{$CNCAT[filter][id]}][]" value="{cn_str($CNCAT[filtval][id])}" id="filtval{$CNCAT[filtval][id]}" {IF $CNCAT[filtval][_checked]}checked="checked"{ENDIF} /> <label for="filtval{$CNCAT[filtval][id]}">{cn_str($CNCAT[filtval][title])}</label><br />
{/TEMPLATE}
{TEMPLATE add_filter_bottom}
			</td>
		</tr>
{/TEMPLATE}

{TEMPLATE add_backlinks_top}
        <tr><td colspan="2"><p>{$CNCAT[lang][add_set_back_link]}</p></td></tr>
        <tr><td colspan="2">
            <table width="100%" border="1">
{/TEMPLATE}

{TEMPLATE add_backlink}
                <tr><td width="50%">
                    <div style="overflow: scroll; height: 70px; margin: 5px; border: 1px solid gray;">{cn_str($CNCAT[backlink][user_code])}</div>
                </td><td width="50%">
                    {$CNCAT[backlink][user_code]}
                </td></tr>
{/TEMPLATE}

{TEMPLATE add_backlinks_bottom}
            </table>
        </td></tr>
{/TEMPLATE}

{TEMPLATE add_errors_top}
    <tr>
        <td colspan="2">
            <strong>{$CNCAT[lang][add_errors_occurred]}</strong><br />
            <ul class="errors">
{/TEMPLATE}

{TEMPLATE add_error}
                <li>{$CNCAT[error][text]}</li>
{/TEMPLATE}

{TEMPLATE add_errors_bottom}
            <ul>
        </td>
    </tr>
{/TEMPLATE}

{TEMPLATE add_cat_next_level}
    <ul class="catmap_level{$CNCAT[cat][tree_level]}">
{/TEMPLATE}

{TEMPLATE add_cat
    (CAT[id, title, disable_add])
}
    <li style="color: {IF $CNCAT[cat][disable_add]}gray{ELSE}black{ENDIF};">
        {IF !$CNCAT[cat][disable_add]}
            <input type="checkbox" name="cats[]" value="{$CNCAT[cat][id]}" id="cat{$CNCAT[cat][id]}" {IF $CNCAT[cat][_checked]}checked="checked"{ENDIF} onclick="checkCats(this)" /> <label for="cat{$CNCAT[cat][id]}">{cn_str($CNCAT[cat][title])}</label>
        {ELSE}
            {cn_str($CNCAT[cat][title])}
        {ENDIF}
    </li>
{/TEMPLATE}

{TEMPLATE add_cat_prev_level}
    </ul>    
{/TEMPLATE}

{TEMPLATE extfield_ext_int1}
        <tr>
            <td>{$CNCAT[lang][extfield_ext_int1]}:</td>
            <td><input type="text" class="text" name="ext_int1" value="{cn_str($CNCAT[add][ext_int1])}" /></td>
        </tr>
{/TEMPLATE}

{TEMPLATE extfield_ext_double1}
        <tr>
            <td>{$CNCAT[lang][extfield_ext_double1]}:</td>
            <td><input type="text" class="text" name="ext_double1" value="{cn_str($CNCAT[add][ext_double1])}" /></td>
        </tr>
{/TEMPLATE}

{TEMPLATE extfield_ext_varchar1}
        <tr>
            <td>{$CNCAT[lang][extfield_ext_varchar1]}:</td>
            <td><input type="text" class="text" name="ext_varchar1" value="{cn_str($CNCAT[add][ext_varchar1])}" /></td>
        </tr>
{/TEMPLATE}

{TEMPLATE extfield_ext_datetime1}
        <tr>
            <td>{$CNCAT[lang][extfield_ext_datetime1]}:</td>
            <td>
                <input type="text" id="ext_datetime1" name="ext_datetime1" value="{cn_str($CNCAT[add][datetime])}" readonly="readonly" />
                <img src="{$THEMEURL}images/calendar.gif" style="cursor: pointer;" onclick="ShowCalendarE('ext_datetime1_calendar', 'ext_datetime1', 0);" />
                <div id="ext_datetime1_calendar" style="visibility:hidden;background-color:white;z-index:9999;width:10px;border: solid 1px #343E78; position:absolute;"></div>
            </td>
        </tr>
{/TEMPLATE}

{TEMPLATE extfield_ext_text1}
        <tr>
            <td>{$CNCAT[lang][extfield_ext_text1]}:</td>
            <td><textarea name="ext_text1">{cn_str($CNCAT[add][ext_text1])}</textarea></td>
        </tr>
{/TEMPLATE}

{TEMPLATE extfield_ext_text2}
        <tr>
            <td>{$CNCAT[lang][extfield_ext_text2]}:</td>
            <td><textarea name="ext_text2">{cn_str($CNCAT[add][ext_text2])}</textarea></td>
        </tr>
{/TEMPLATE}

{TEMPLATE extfield_ext_text3}
        <tr>
            <td>{$CNCAT[lang][extfield_ext_text3]}:</td>
            <td><textarea name="ext_text3">{cn_str($CNCAT[add][ext_text3])}</textarea></td>
        </tr>
{/TEMPLATE}
