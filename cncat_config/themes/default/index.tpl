{TEMPLATE index}
    {INCLUDE top}
    {INCLUDE brokenscript}
    {INCLUDE menu}

    <table width="100%">
    <tr>
    {IF $CNCAT[banner][page_left]}
        <td style="padding-left: 20px; padding-top: 19px;">{$CNCAT[banner][page_left]}</td>
    {ENDIF}
    
    <td width="100%">
    {DISPLAY FILTERS}
    <table width="100%">
        <tr><td id="catlist">
            {DISPLAY CATEGORIES}
        </td>
        {IF !$CNCAT[page][show_items] && $CNCAT[config][show_stats]}
            <td>
                {DISPLAY STATISTICS}
            </td>
        {ENDIF}
        </tr>
    </table>
    <table width="100%">
        <tr><td id="catpath">
        </td></tr>
    </table>
    {IF $CNCAT[page][show_items]}
    <table id="main" width="100%">
        <tr>
        <td class="left">
            <table id="catitems">
                <tr><td>
                    <table width="100%" border="0">
                        <tr>
	                      	<td class="caticon">                        	
            	            	{IF $CNCAT[page][cat][image_mime]}
		   	                        <img src="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}image.php?cat={$CNCAT[page][cat][id]}" alt="{cn_str($CNCAT[page][title_full])}" style="vertical-align: middle;" />
		                        {ELSE}
									<img src="{$THEMEURL}images/category-big.gif" alt="" />
	                        	{ENDIF}
	                       	</td>
                        	<td>
                        		<table width="100%" border="0">
                        		<tr>
	        	                	<td class="catpath">
							            {IF $CNCAT[page][show_cat_path]}
							                {DISPLAY CAT_PATH}
						    	        {ENDIF}
						    	    </td>
	    	                    	<td class="itemtype" rowspan="2">{DISPLAY ITEM_TYPES}</td>
						    	</tr>
						    	<tr>
						    	    <td class="cattitle">
		        	                	{$CNCAT[page][cat][title]}
						                {IF $CNCAT[page][show_rss]}<a href="{$CNCAT[abs]}{$CNCAT[system][dir_prefix]}rss.php?c={$CNCAT[page][cid]}"><img src="{$THEMEURL}images/rss.gif" alt="RSS" style="border:0" /></a>{ENDIF}
						                <div class="catdescr">{$CNCAT[cat][descr]}</div>
                                        {IF $CNCAT[cat][_canedit]}
						                    <span class="edit">
						                     	<a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=cats&amp;mode=add&amp;id={$CNCAT[cat][id]}">{$CNCAT[lang][do_submit]}</a> | 
							                    <a href="{$CNCAT[abs]}{$CNCAT[system][dir_admin]}index.php?act=cats&amp;mode=edit&amp;id={$CNCAT[cat][id]}">{$CNCAT[lang][do_edit]}</a>
							                </span>
						                {ENDIF}
		        	                </td>
	    	                    </tr>
	    	                    <tr>
			                        <td class="sort" colspan="2" height="100%">
		    	                        {DISPLAY SORT}
		    	                    </td>
			                    </tr>
			                    </table>
                        	</td>
                        </tr>
                    </table>
                </td></tr>
                <tr><td class="navbar">
                {DISPLAY PAGES}
                </td></tr>
                <tr><td class="items">
                {IF $CNCAT[banner][items_top]}
                    <center>{$CNCAT[banner][items_top]}</center>
                {ENDIF}
	            {DISPLAY ITEMS}
	            {INCLUDE itemcount}
                {IF $CNCAT[banner][items_bottom]}
                    <center>{$CNCAT[banner][items_bottom]}</center>
                {ENDIF}
                </td></tr>
                <tr><td class="navbar">
                {DISPLAY PAGES}
                </td></tr>
            </table>
        </td>
        {IF $CNCAT[page][show_new_items] || $CNCAT[config][show_stats]}
        <td class="right">
            {IF $CNCAT[page][show_new_items]}
                {DISPLAY NEW_ITEMS}
                {DISPLAY NEW_ARTICLES}
            {ENDIF}
            {IF $CNCAT[page][show_stats]}
                {DISPLAY STATISTICS}
            {ENDIF}
        </td>
        {ENDIF}
        </tr>
    </table>
    {ENDIF}
    {IF $CNCAT[banner][page_right]}
        <td style="padding-right: 20px; padding-top: 19px;">{$CNCAT[banner][page_right]}</td>
    {ENDIF}
    </td></tr></table>

    {INCLUDE bottom}
{/TEMPLATE}
