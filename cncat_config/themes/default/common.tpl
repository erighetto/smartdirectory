{TEMPLATE top}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
    <head>
        <title>{IF $CNCAT[page][cid] > 0 && $CNCAT[page][cid] != $CNCAT[root_cat_id]}{cn_str($CNCAT[page][cat][title])} - {ENDIF}{cn_str($CNCAT[page][title])}</title>
        <meta name="Content-Type" content="text/html; charset={$CNCAT[lang][charset]}" />
        <link rel="stylesheet" type="text/css" media="all" href="{$THEMEURL}style.css" />
        {IF $CNCAT[page][keywords]}<meta name="keywords" content="{cn_str($CNCAT[page][keywords])}" />{ENDIF}
        {IF $CNCAT[page][description]}<meta name="description" content="{cn_str($CNCAT[page][description])}" />{ENDIF}
        {IF $CNCAT[page][cid] > 0}
            {IF $CNCAT[page][show_rss]}<link rel="alternate" type="application/rss+xml" title="{cn_str($CNCAT[config][rss_title])}" href="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}rss.php?c={$CNCAT[page][cid]}" />{ENDIF}
        {ENDIF}
    </head>
    <body>
{/TEMPLATE}

{TEMPLATE bottom}
    <table id="bottom" width="100%">
        <tr><td class="copyright">
            {cn_copyright(1)}
        </td>
        {IF $CNCAT[banner][page_bottom]}
            <td align="right" style="padding: 0 20px 20px 0;">{$CNCAT[banner][page_bottom]}</td>
        {ENDIF}
        </tr>
    </table>
    </body>
</html>  
{/TEMPLATE}

<!-- Main menu -->
{TEMPLATE menu}
    <table id="top" width="100%">
        <tr><td id="title">
            {cn_str($CNCAT[config][catalog_title])}
        </td><td id="control">
        <table align="right">
            <tr><td>
                    {IF $CNCAT[config][show_link_cat]}<a href="{$CNCAT[config][cncat_url]}">{$CNCAT[lang][menu_main]}</a> &nbsp; {ENDIF}
                    {IF !$CNCAT[config][add_disable]}<a href="{$CNCAT[page][add_url]}"><strong>{$CNCAT[lang][menu_add_link]}</strong></a> &nbsp; {ENDIF}
                    {IF $CNCAT[config][add_article_enable]}<a href="{$CNCAT[page][add_article_url]}"><strong>{$CNCAT[lang][menu_add_article]}</strong></a> &nbsp; {ENDIF}
                    {IF $CNCAT[config][show_link_admin]}<a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}">{$CNCAT[lang][menu_admin]}</a> &nbsp; {ENDIF}
                    {IF $CNCAT[config][show_link_map]}<a href="{$CNCAT[page][map_url]}">{$CNCAT[lang][menu_map]}</a> &nbsp; {ENDIF}
                    <br />{$CNCAT[page][static_page_links]}&nbsp;
            </td><td>
                    {INCLUDE searchform}

                    {IF $CNCAT[user][id]}
                        <br />{$CNCAT[lang][you_login_as]} <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=logout">{$CNCAT[lang][logout]}</a>
                    {ENDIF}
            </td></tr>
        </table>
        </td></tr>
    </table>
    {IF $CNCAT[banner][page_top]}
        <center>{$CNCAT[banner][page_top]}</center>
    {ENDIF}
{/TEMPLATE}

<!-- Static page link -->
{TEMPLATE static_page_link}
    <a href="{$CNCAT[config][cncat_url]}page/{$CNCAT[page][static_page][name]}">{$CNCAT[page][static_page][title]}</a>
{/TEMPLATE}

<!-- Search form -->
{TEMPLATE searchform}
            <form action="{$CNCAT[page][search_form_url]}" method="get" id="cn_search">
                <input type="text" name="cn_query" size="20" class="text" value="{cn_str($CNCAT[page][search_query])}" />
                <input type="submit" value="{$CNCAT[lang][search_submit]}" class="submit" onclick="submitSearchForm({$CNCAT[config][url_style]})" />
            </form>
            <script type="text/javascript">
            //<![CDATA[
              function submitSearchForm(furl)
              {     
                  var search_form = document.getElementById('cn_search');
                  var cn_query_value  = search_form.elements[0].value.replace(/[<>\\/&%+]+/g, "");  
                  if(furl > 0)
                  { 
                      search_form.action += cn_query_value;
                      search_form.method = "post";
                      search_form.submit();
                  } 
                  else
                  {
                      search_form.action += "&cn_query=" + cn_query_value;
                      search_form.method = "post";
                      search_form.submit();
                  }
              }
              //]]>
            </script> 
{/TEMPLATE}

<!-- Sorting controls -->
{TEMPLATE sort_top}
                    {$CNCAT[lang][sort_by]}: 
{/TEMPLATE}

{TEMPLATE sort}
                    {IF $CNCAT[page][sort_order]==$CNCAT[sort][id]}
                        <strong>{cn_str($CNCAT[sort][title])}</strong>
                    {ELSE}
                        <a href="{cn_str($CNCAT[sort][url])}">{cn_str($CNCAT[sort][title])}</a>
                    {ENDIF}
{/TEMPLATE}

{TEMPLATE sort_delim} | {/TEMPLATE}

{TEMPLATE sort_bottom}
{/TEMPLATE}

<!-- Page navigation controls   -->

{TEMPLATE pagenav_top}
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
    <table class="pagenav">
    <tr>
{/TEMPLATE}

{TEMPLATE pagenav_curpage}
        <td><input name="p" type="text" size="2" value="{$CNCAT[pagenav][curpage]}" /></td>
{/TEMPLATE}

{TEMPLATE pagenav_pageitem}
        <td><a href="{$CNCAT[pagenav][url]}">{$CNCAT[pagenav][curpage]}</a></td>
{/TEMPLATE}

{TEMPLATE pagenav_delim1}<td>&nbsp;</td>{/TEMPLATE}

{TEMPLATE pagenav_delim2}<td>&nbsp;&nbsp;...&nbsp;&nbsp;</td>{/TEMPLATE}

{TEMPLATE pagenav_bottom}
    </tr>
    </table>
    </form>
{/TEMPLATE}   

<!-- Filters -->


{TEMPLATE filters_top}
    <form action="?" id="filters">
    <table>
        <tr>
{/TEMPLATE}

{TEMPLATE filter (FILTER[title])}{/TEMPLATE}

{TEMPLATE filter_top}
        <td class="filter">
            <table>
                <tr><td class="title" nowrap="nowrap">
                    {$CNCAT[filter][title]}
                </td><td class="clear"></td></tr>
                <tr><td colspan="2" class="values">
{/TEMPLATE}

{TEMPLATE filtval (FILTVAL[id,title])}
    <input type="checkbox" name="f{$CNCAT[filtval][id]}" value="1" id="f{$CNCAT[filtval][id]}" onclick="submitFilterForm({$CNCAT[config][url_style]})" {if $CNCAT[filtval][_checked]}checked="checked"{endif} /> <label for="f{$CNCAT[filtval][id]}">{$CNCAT[filtval][title]}</label>
{/TEMPLATE}

{TEMPLATE filtval_delim}{/TEMPLATE}

{TEMPLATE filter_bottom}
              </td></tr>
            </table>
            <script type="text/javascript">
            //<![CDATA[
              function submitFilterForm(furl)
              {   
                  var url = window.location.href;
                  var filters_form = document.getElementById('filters');
                  if(furl > 0)
                  {
                      for(i = 0; i < filters_form.length; i++) 
                      { 
                          var field = filters_form.elements[i];
                          url = url.replace('/' + field.name + '/', '/');
                          if (field.checked == true)
                          {
                              var html_doc = /([^//]+\.html)/.exec(url);
                              if (html_doc == null || html_doc[1] == null)
                                url += field.name + '/';
                              else
                                url = url.replace('/' + html_doc[1], '/' + field.name + '/' + html_doc[1]);
                          }
                      };
                      filters_form.action = url;
                      filters_form.method = "post";
                  } 
                  filters_form.submit();
              }
              //]]>
            </script> 
        </td>
{/TEMPLATE}

{TEMPLATE filters_delim}
{/TEMPLATE}


{TEMPLATE filters_bottom}
            {IF $CNCAT[page][filter_used]}
            <td style="padding: 0 10px; vertical-align: bottom;">
                <a href="#" onclick="for(i = 0; i < document.getElementById('filters').length; i++) { document.getElementById('filters').elements[i].checked = 0; }; document.getElementById('filters').submit();" style="color: gray; text-decoration: none; padding: 1px 4px">x</a>
            </td>
            {ENDIF}
            <td class="free"></td>
        </tr>
    </table>

    {IF $CNCAT[config][url_style]==0 || !$CNCAT[page][cat][path]}
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

{TEMPLATE statistics}
            <table id="stats">
                <tr><td nowrap="nowrap" class="title">
                    {$CNCAT[lang][stats]}
                </td><td class="clear"></td></tr>
                <tr><td colspan="2" class="items">
                    <table width="100%">
                    {IF $CNCAT[page][cat][id] == $CNCAT[root_cat_id]}
                        <tr>
                            <td class="image"><img src="{$THEMEURL}images/newlink.gif" alt="" /></td>
                            <td class="info">{$CNCAT[lang][stats_cats_count]}: {$CNCAT[page][cats_count_full]}</td>
                        </tr>
                        <!--
                        <tr>
                            <td class="image"><img src="{$THEMEURL}images/newlink.gif" alt="" /></td>
                            <td class="info">{$CNCAT[lang][stats_item_count_full]}: {$CNCAT[page][cat][item_count_full]}</td>
                        </tr>
                        -->
                        <tr>
                            <td class="image"><img src="{$THEMEURL}images/newlink.gif" alt="" /></td>
                            <td class="info">{$CNCAT[lang][stats_links_count_full]}: {$CNCAT[page][links_count_full]}</td>
                        </tr>
                        <tr>
                            <td class="image"><img src="{$THEMEURL}images/newlink.gif" alt="" /></td>
                            <td class="info">{$CNCAT[lang][stats_articles_count_full]}: {$CNCAT[page][articles_count_full]}</td>
                        </tr>
                        <tr>
                            <td class="image"><img src="{$THEMEURL}images/newlink.gif" alt="" /></td>
                            <td class="info">{$CNCAT[lang][stats_update]}: {cn_date($CNCAT[page][last_update])}</td>
                        </tr>
                    {ELSE}
                        <!--
                        <tr>
                            <td class="image"><img src="{$THEMEURL}images/newlink.gif" alt="" /></td>
                            <td class="info">{$CNCAT[lang][stats_item_count_cat]}: {$CNCAT[page][cat][item_count_full]}</td>
                        </tr>
                        -->
                        <tr>
                            <td class="image"><img src="{$THEMEURL}images/newlink.gif" alt="" /></td>
                            <td class="info">{$CNCAT[lang][stats_links_count_cat]}: {$CNCAT[page][cat][link_count_full]}</td>
                        </tr>
                        <tr>
                            <td class="image"><img src="{$THEMEURL}images/newlink.gif" alt="" /></td>
                            <td class="info">{$CNCAT[lang][stats_articles_count_cat]}: {$CNCAT[page][cat][article_count_full]}</td>
                        </tr>
                    {ENDIF}
                    </table>
                </td></tr>
            </table>    
            <br />
{/TEMPLATE}
