{TEMPLATE top}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">


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
    <div id="top">
        <div id="cnlogo" class="cnleft"><img alt="" src="{$CNCAT[abs]}cncat_image.php?cat=1" />{cn_str($CNCAT[config][catalog_title])}</div>
        
        {IF $CNCAT[user][id]}
        <div id="online" class="cnright">
            {$CNCAT[lang][you_login_as]} <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=logout">{$CNCAT[lang][logout]}</a>
        </div>
        {ENDIF}
    </div>
    {IF $CNCAT[banner][page_top]}
        <div class="banner">{$CNCAT[banner][page_top]}</div>
    {ENDIF}
{/TEMPLATE}

{TEMPLATE bottom}
      <div id="bottom" class="cnleft">
        {IF $CNCAT[banner][page_bottom]}
            <div class="banner">{$CNCAT[banner][page_bottom]}</div>
        {ENDIF}
        <div class="copyright">
            {cn_copyright(1)}
        </div>
      </div>
    </body>
</html>  
{/TEMPLATE}

<!-- Main menu -->
{TEMPLATE menu}
    {INCLUDE searchform}
    <div id="main_menu">
    <ul>
    {IF $CNCAT[config][show_link_cat]}<li><a href="{$CNCAT[config][cncat_url]}">{$CNCAT[lang][menu_main]}</a></li>{ENDIF}
    {IF !$CNCAT[config][add_disable]}<li><a href="{$CNCAT[page][add_url]}"><strong>{$CNCAT[lang][menu_add_link]}</strong></a></li>{ENDIF}
    {IF $CNCAT[config][add_article_enable]}<li><a href="{$CNCAT[page][add_article_url]}"><strong>{$CNCAT[lang][menu_add_article]}</strong></a></li>{ENDIF}
    {IF $CNCAT[config][show_link_admin]}<li><a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}">{$CNCAT[lang][menu_admin]}</a></li>{ENDIF}
    {IF $CNCAT[config][show_link_map]}<li><a href="{$CNCAT[page][map_url]}">{$CNCAT[lang][menu_map]}</a></li>{ENDIF}
    </ul>
    {$CNCAT[page][static_page_links]}
    </div>
    
{/TEMPLATE}

<!-- Static page link -->
{TEMPLATE static_page_link}
    <a href="{$CNCAT[config][cncat_url]}page/{$CNCAT[page][static_page][name]}">{$CNCAT[page][static_page][title]}</a>
{/TEMPLATE}

<!-- Search form -->
{TEMPLATE searchform}
            <div id="search_form">
            <form action="{$CNCAT[page][search_form_url]}" method="get" id="cn_search">
            <div>
				<input type="submit" style="display: none" onclick="submitSearchForm(0);return false;">
                <input id="cn_query" type="text" name="cn_query" class="text cnleft" value="{cn_str($CNCAT[page][search_query])}" />
                <a class="button" href="#" onclick="submitSearchForm({$CNCAT[config][url_style]});return false;"><span>{$CNCAT[lang][search_submit]}</span></a>       
            </div>
            </form>
            </div>
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
    <div class="pagenav">
    <ul>
{/TEMPLATE}

{TEMPLATE pagenav_curpage}
        <li class="cnleft"><input name="p" type="text" size="2" value="{$CNCAT[pagenav][curpage]}" /></li>
{/TEMPLATE}

{TEMPLATE pagenav_pageitem}
        <li class="page cnleft"><a href="{$CNCAT[pagenav][url]}">{$CNCAT[pagenav][curpage]}</a></li>
{/TEMPLATE}

{TEMPLATE pagenav_delim1}<li class="cnleft">&nbsp;</li>{/TEMPLATE}

{TEMPLATE pagenav_delim2}<li class="cnleft">&nbsp;&nbsp;...&nbsp;&nbsp;</li>{/TEMPLATE}

{TEMPLATE pagenav_bottom}
    </ul>
    </div>
    </form>
{/TEMPLATE}   

<!-- Filters -->


{TEMPLATE filters_top}
    <div id="filters_form">
    <form action="?" id="filters">
    {IF $CNCAT[page][filter_used]}
    <div class="cnleft">
        <a href="#" onclick="for(i = 0; i < document.getElementById('filters').length; i++) { document.getElementById('filters').elements[i].checked = 0; }; submitFilterForm({$CNCAT[config][url_style]});" style="color: gray; text-decoration: none; padding: 1px 4px">x</a>
    </div>
    {ENDIF}
    <ul>
{/TEMPLATE}

{TEMPLATE filter (FILTER[title])}{/TEMPLATE}

{TEMPLATE filter_top}
        <li><span class="filter">{$CNCAT[filter][title]}</span>
{/TEMPLATE}

{TEMPLATE filtval (FILTVAL[id,title])}
    <input type="checkbox" name="f{$CNCAT[filtval][id]}" value="1" id="f{$CNCAT[filtval][id]}" 
    onclick="submitFilterForm({$CNCAT[config][url_style]})" {if $CNCAT[filtval][_checked]}checked="checked"{endif} /> 
    <label for="f{$CNCAT[filtval][id]}">{$CNCAT[filtval][title]}</label>
{/TEMPLATE}

{TEMPLATE filtval_delim}{/TEMPLATE}

{TEMPLATE filter_bottom}
              </li>
            
{/TEMPLATE}

{TEMPLATE filters_delim}
{/TEMPLATE}


{TEMPLATE filters_bottom}
            
    </ul>
    {IF $CNCAT[config][url_style]==0 || !$CNCAT[page][cat][path]}
        <div id="filter_param">
        <input type="hidden" name="c" value="{$CNCAT[page][cid]}" />
        <input type="hidden" name="s" value="{$CNCAT[page][sort_order]}" />
        <input type="hidden" name="p" value="{$CNCAT[page][page_num]}" />
        {IF $CNCAT[page][item_type] >= 0}
            <input type="hidden" name="t" value="{$CNCAT[page][item_type]}" />
        {ENDIF}
        </div>
    {ENDIF}
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
    </form>
    </div>
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
            <div id="stats">
                <div class="title">
                    {$CNCAT[lang][stats]}
                </div>
                <div class="items">
                <ul>
                    {IF $CNCAT[page][cat][id] == $CNCAT[root_cat_id]}
                        <li>
                          <div class="info">{$CNCAT[lang][stats_cats_count]}: {$CNCAT[page][cats_count_full]}</div>
                        </li>
                        <li>
                            <div class="info">{$CNCAT[lang][stats_links_count_full]}: {$CNCAT[page][links_count_full]}</div>
                        </li>
                        <li>
                            <div class="info">{$CNCAT[lang][stats_articles_count_full]}: {$CNCAT[page][articles_count_full]}</div>
                        </li>
                        <li>
                            <div class="info">{$CNCAT[lang][stats_update]}: {cn_date($CNCAT[page][last_update])}</div>
                        </li>
                    {ELSE}
                        <li>
                            <div class="info">{$CNCAT[lang][stats_links_count_cat]}: {$CNCAT[page][cat][link_count_full]}</div>
                        </li>
                        <li>
                            <div class="info">{$CNCAT[lang][stats_articles_count_cat]}: {$CNCAT[page][cat][article_count_full]}</div>
                        </li>
                    {ENDIF}
                </ul>
               </div>
               </div>
{/TEMPLATE}
