{TEMPLATE index}
    {INCLUDE top}
    {INCLUDE brokenscript}
    <div id="cncontent">
    <div id="side_pane" class="cnleft">
        {INCLUDE menu}
        {IF $CNCAT[banner][page_left]}
        <div class="banner">{$CNCAT[banner][page_left]}</div>
        {ENDIF}
        {IF $CNCAT[page][show_new_items]}
            {DISPLAY NEW_ITEMS}
            {DISPLAY NEW_ARTICLES}
        {ENDIF}
        {IF $CNCAT[page][show_stats]}
            {DISPLAY STATISTICS}
        {ENDIF}
    </div>
    <div id="cnmain">
        {DISPLAY FILTERS}
        {DISPLAY CATEGORIES}
        {IF $CNCAT[banner][page_right]}
        <div class="banner cnright">{$CNCAT[banner][page_right]}</div>
        {ENDIF}
        {IF $CNCAT[page][show_items]} 
        <div id="catitems">
           	<div class="caticon cnleft">                        	
	            	{IF $CNCAT[page][cat][image_mime]}
                    <img src="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}image.php?cat={$CNCAT[page][cat][id]}" 
                    alt="{cn_str($CNCAT[page][title_full])}" />
                {ELSE}
			              <img src="{$THEMEURL}images/category-big.gif" alt="" />
              	{ENDIF}
            </div>
            <div class="cat_menu">
                {IF $CNCAT[page][show_cat_path]}
		                {DISPLAY CAT_PATH}
	    	        {ENDIF}
                <div class="cattitle">{$CNCAT[page][cat][title]}
		                {IF $CNCAT[page][show_rss]}<a href="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}rss.php?c={$CNCAT[page][cid]}"><img src="{$THEMEURL}images/rss.gif" alt="RSS" /></a>
                    {ENDIF}
		            </div>
		            <div class="itemtype">{DISPLAY ITEM_TYPES}</div>
                <div class="catdescr">{$CNCAT[cat][descr]}</div>
                {IF $CNCAT[cat][_canedit]}
		            <div class="edit">
                   	<a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=cats&amp;mode=add&amp;id={$CNCAT[cat][id]}">
                    {$CNCAT[lang][do_submit]}</a> | 
	                  <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=cats&amp;mode=edit&amp;id={$CNCAT[cat][id]}">
                    {$CNCAT[lang][do_edit]}</a>
	              </div>
                {ENDIF}
		        	  <div class="sort">
		    	          {DISPLAY SORT}
		    	      </div>
              </div>
              <div class="navbar">
                {DISPLAY PAGES}
              </div>
              <div class="items">
                  {IF $CNCAT[banner][items_top]}
                      <div class="banner">{$CNCAT[banner][items_top]}</div>
                  {ENDIF}
    	            {DISPLAY ITEMS}
    	            {INCLUDE itemcount}
                  {IF $CNCAT[banner][items_bottom]}
                      <div class="banner">{$CNCAT[banner][items_bottom]}</div>
                  {ENDIF}
              </div>
              <div class="navbar">
                {DISPLAY PAGES}
              </div>
        </div>
    {ENDIF}
    </div>
    </div>
    {INCLUDE bottom}
{/TEMPLATE}
