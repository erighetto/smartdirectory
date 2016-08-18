{TEMPLATE top}
%TOP%
{/TEMPLATE}

{TEMPLATE bottom}
%BOTTOM%
{/TEMPLATE}

{TEMPLATE menu}
%MENU%
{/TEMPLATE}

{TEMPLATE searchform}
%SEARCHFORM%
{/TEMPLATE}

{TEMPLATE sort_top}
<p align="right">{$CNCAT[lang][sort_by]}:
{/TEMPLATE}

{TEMPLATE sort}
    {IF $CNCAT[page][sort_order]==$CNCAT[sort][id]}
        <strong>{cn_str($CNCAT[sort][title])}</strong>
    {ELSE}
        <a href="{cn_str($CNCAT[sort][url])}">{cn_str($CNCAT[sort][title])}</a>
    {ENDIF}
{/TEMPLATE}

{TEMPLATE sort_delim}
|
{/TEMPLATE}

{TEMPLATE sort_bottom}
</p>
{/TEMPLATE}

{TEMPLATE pagenav_top}
    <center>
	<form action="" onsubmit="
        if ((this.elements[0].value-1) &gt; {$CNCAT[pagenav][pagecount]} || this.elements[0].value &lt; 1) 
        {
            alert('{$CNCAT[lang][wrong_page_number]}');
            return false;
        } 
        url='{$CNCAT[pagenav][urltemplate]}'; 
        location.href = url.replace(new RegExp('{PAGE}', ''), this.elements[0].value-1);
        return false;
    " method="get">
    <table cellspacing="5" cellpadding="0" border="0"><tr>
{/TEMPLATE}

{TEMPLATE pagenav_curpage}
    <td><input name="start" type="text" size="2" value="{$CNCAT[pagenav][curpage]}" class="leftright"></td>
{/TEMPLATE}

{TEMPLATE pagenav_pageitem}
    <td class="leftright"><a href="{$CNCAT[pagenav][url]}">{$CNCAT[pagenav][curpage]}</a></td>
{/TEMPLATE}

{TEMPLATE pagenav_delim1}<td>&nbsp;</td>{/TEMPLATE}

{TEMPLATE pagenav_delim2}<td>&nbsp;&nbsp;...&nbsp;&nbsp;</td>{/TEMPLATE}

{TEMPLATE pagenav_bottom}
    </tr>
    </table>
    </center>
{/TEMPLATE}

{TEMPLATE filters_top}
    <form action="?">
    <table width="100%">
        <tr>
{/TEMPLATE}

{TEMPLATE filter (FILTER[title])}{/TEMPLATE}

{TEMPLATE filter_top}
        <td>
            <table>
                <tr><td>{$CNCAT[filter][title]}</td></tr>
                <tr><td>
{/TEMPLATE}

{TEMPLATE filtval (FILTVAL[id,title])}
                    <input type="checkbox" name="f{$CNCAT[filtval][id]}" value="1" id="f{$CNCAT[filtval][id]}" onclick="form.submit()" {if $CNCAT[filtval][_checked]}checked="checked"{endif} /> <label for="f{$CNCAT[filtval][id]}">{$CNCAT[filtval][title]}</label>
{/TEMPLATE}

{TEMPLATE filtval_delim}{/TEMPLATE}

{TEMPLATE filter_bottom}
                </td></tr>
            </table>
        </td>
{/TEMPLATE}

{TEMPLATE filters_delim}
{/TEMPLATE}


{TEMPLATE filters_bottom}
        </tr>
    </table>
    {IF $CNCAT[config][url_style]==0}
        <input type="hidden" name="c" value="{$CNCAT[page][cid]}" />
        <input type="hidden" name="s" value="{$CNCAT[page][sort_order]}" />
        <input type="hidden" name="p" value="{$CNCAT[page][page_num]}" />
        {IF $CNCAT[page][item_type] >= 0}
            <input type="hidden" name="t" value="{$CNCAT[page][item_type]}" />
        {ENDIF}
    {ENDIF}
    </form>
{/TEMPLATE}

{TEMPLATE itemtypes_top}
	{$CNCAT[lang][itemtypes_show]}:
{/TEMPLATE}

{TEMPLATE itemtype}
        {IF $CNCAT[itemtype][id] == $CNCAT[page][item_type]}
            <strong>{cn_str($CNCAT[itemtype][title])}</strong>
        {ELSE}
            <a href="{cn_str($CNCAT[itemtype][url])}">{cn_str($CNCAT[itemtype][title])}</a>
        {ENDIF}
{/TEMPLATE}

{TEMPLATE itemtypes_delim} | {/TEMPLATE}

{TEMPLATE itemtypes_bottom}
{/TEMPLATE}
