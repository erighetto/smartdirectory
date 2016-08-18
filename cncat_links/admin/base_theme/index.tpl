{TEMPLATE index}
    {INCLUDE top}
    {INCLUDE brokenscript}
    {INCLUDE menu}
    {INCLUDE searchform}

    {IF $CNCAT[page][show_cat_path]}
        {DISPLAY CAT_PATH}
    {ENDIF}

    <br>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">    
    <tr>
        <td valign="top">
            {DISPLAY CATEGORIES}
        </td>
        {IF $CNCAT[page][show_new_items] && !$CNCAT[page][show_items]}
            <td width="200" valign="top">
                {DISPLAY NEW_ITEMS}
            </td>
        {ENDIF}
    </tr>
    </table>

    {IF $CNCAT[page][show_items]}
        <hr>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">    
        <tr>
            {IF $CNCAT[banner][page_left]}
                <td valign="top" width="1" style="padding-right:5px"><center>{$CNCAT[banner][page_left]}</center><br></td>
            {ENDIF}
            
            <td width="*" valign="top" style="padding-left:5px; padding-right:10px">
                {IF $CNCAT[banner][items_top]}
                    <center>{$CNCAT[banner][items_top]}</center><br>
                {ENDIF}
                
                {DISPLAY SORT}
                {DISPLAY PAGES}
	            {DISPLAY ITEMS}
                {INCLUDE itemcount}
                {DISPLAY PAGES}
                
                {IF $CNCAT[banner][items_bottom]}
                    <br><center>{$CNCAT[banner][items_bottom]}</center>
                {ENDIF}
            </td>
            
            {IF $CNCAT[page][show_new_items]}
                <td width="250" valign="top" align="center">
                    {DISPLAY NEW_ITEMS}
                </td>
            {ENDIF}

                
            {IF $CNCAT[banner][page_right]}
                <td width="1" valign="top" align="center">
                    {$CNCAT[banner][page_right]}
                </td>
            {ENDIF}
            
            
        </tr>
        </table>
    {ENDIF}

    
    {INCLUDE bottom}
{/TEMPLATE}
