<?php
/*******************************************************************************
 * CNCat 4.4 
 * Copyright (c) "CN-Software" Ltd. 
 * http://www.cn-software.com/cncat/
 * ----------------------------------------------------------------------------
 * Please do not modify this header!
 *
 * If you change the original code, we do not guarantee the correct functioning
 * of the program and correct updates.
 * See full text of license agreement in cncat-license.txt file located at the 
 * root folder of the web directory.
*******************************************************************************/

if (!defined("CNCAT_ENGINE")) die();

/**
*   Common render functions
*/
class CNCatRender {
    var $CNCAT;

    function CNCatRender() {
        $this->CNCAT =& $GLOBALS['CNCAT'];
    }

    /**
    *   Renders page navigation code
    *   @param url url template, where {PAGE} will be replaced with page no. For example: /cat/s1p{PAGE}.html
    *   @param pagecount count of pages
    *   @param curpage current page number
    */
    function renderPageNavigation($url, $pagecount, $curpage, $max_pages = 8)
    {
        GLOBAL $CNCAT, $CNCAT_ENGINE;

        $CNCAT["pagenav"]["urltemplate"] = $url;
        $CNCAT["pagenav"]["pagecount"] = $pagecount;
        $max_pages = $max_pages < 8? 8: $max_pages;
        $result = "";
        $result .= $CNCAT_ENGINE->tpl->renderTemplate ("pagenav_top");

	    /* If <=8 records */
	    if ($pagecount <= $max_pages)
	    {
		    for ($i=0;$i<$pagecount;$i++)
            {
                $CNCAT["pagenav"]["curpage"] = $i+1;
                $CNCAT["pagenav"]["url"] = cn_str_replace ("{PAGE}", $i, $url);
                $result .= ($i>0) ? $CNCAT_ENGINE->tpl->renderTemplate ("pagenav_delim1") : "";
                $result .= $CNCAT_ENGINE->tpl->renderTemplate ($curpage==$i ? "pagenav_curpage" : "pagenav_pageitem");
            }
	    }
	    /* If >8 records */
	    else
	    {
		    if ($curpage<4) // 1 2 [3] 4 5 ... 99 100
		    {
			    for ($i=0;$i<$pagecount;$i++)
                {
                    $CNCAT["pagenav"]["curpage"] = $i+1;
                    $CNCAT["pagenav"]["url"] = cn_str_replace ("{PAGE}", $i, $url);
                    $result .= ($i>0 && ($i<=5 || $i>=$pagecount-1)) ? $CNCAT_ENGINE->tpl->renderTemplate ("pagenav_delim1") : "";
                    $result .= $CNCAT_ENGINE->tpl->renderTemplate ($curpage==$i ? "pagenav_curpage" : "pagenav_pageitem");
                    if ($i==4)
                    {
                        $i = $pagecount-3;
			            $result .= $CNCAT_ENGINE->tpl->renderTemplate ("pagenav_delim2");
                    }
                }
		    }
		    elseif ($curpage>=$pagecount-4) // 1 2 ... 96 97 98 [99] 100
		    {
			    for ($i=0;$i<$pagecount;$i++)
                {
                    $CNCAT["pagenav"]["curpage"] = $i+1;
                    $CNCAT["pagenav"]["url"] = cn_str_replace ("{PAGE}", $i, $url);
                    $result .= ($i>0 && ($i<=1 || $i>=$pagecount-4)) ? $CNCAT_ENGINE->tpl->renderTemplate ("pagenav_delim1") : "";
                    $result .= $CNCAT_ENGINE->tpl->renderTemplate ($curpage==$i ? "pagenav_curpage" : "pagenav_pageitem");
                    if ($i==1)
                    {
                        $i = $pagecount-6;
			            $result .= $CNCAT_ENGINE->tpl->renderTemplate ("pagenav_delim2");
                    }
                }
		    }
		    else // 1 2 ... 50 [51] 52 ... 99 100
		    {
			    for ($i=0;$i<$pagecount;$i++)
                {
                    $CNCAT["pagenav"]["curpage"] = $i+1;
                    $CNCAT["pagenav"]["url"] = cn_str_replace ("{PAGE}", $i, $url);
                    $result .= ($i>0 && ($i<=1 || $i>=$pagecount-1 || ($i>=$curpage && $i<=$curpage+1))) ? $CNCAT_ENGINE->tpl->renderTemplate ("pagenav_delim1") : "";
                    $result .= $CNCAT_ENGINE->tpl->renderTemplate ($curpage==$i ? "pagenav_curpage" : "pagenav_pageitem");
                    if ($i==1)
                    {
                        $i = $curpage-2;
			            $result .= $CNCAT_ENGINE->tpl->renderTemplate ("pagenav_delim2");
                    }
                    if ($i== $curpage+1)
                    {
                        $i = $pagecount-3;
			            $result .= $CNCAT_ENGINE->tpl->renderTemplate ("pagenav_delim2");
                    }
                }
		    }
	    }

        $result .= $CNCAT_ENGINE->tpl->renderTemplate ("pagenav_bottom");
        
	    return $result;
    }
    
    /**
    * Prepares item to display
    * @param item - item description
    */
    function prepareItemToDisplay(&$item)
    {
    }
  
    /**
    * Prepares category to display
    * @param cat - category description
    */
    function prepareCategoryToDisplay(&$cat)
    {
        GLOBAL $CNCAT, $CNCAT_ENGINE;
        
        $cat["url"] = $CNCAT_ENGINE->url->createUrlCat ($cat["is_link"] ? $cat["id_real"] : $cat["id"], $cat["path_full"]);       
    }
    /**
     * Render progress bar
     * @param $min
     * @param $max
     * @param $value
     * @return string 
     */
    function renderProgressBar($min, $max, $value) {
        if ($value > $max) {
            $value = $max;
        } elseif ($value < $min) {
            $value = $min;
        }

        if ($max == $min) {
            $pcs = 100;
        } else {
            $pcs = ceil((100 / ($max - $min)) * ($value - $min));
        }

        $result = "<div style=\"padding: 1px; margin: 5px 0; border: 1px solid silver;\">";
        $result .= "<div id=\"progress_bar\" style=\"width: " . $pcs . "%; height: 20px; background: #eee;\"></div>";
        $result .= "</div>";
        $result .= "<div style=\"text-align: center; margin-top: -25px; height: 30px;\"><strong id=\"progress_per\">" . $pcs . "%</strong></div>";
    
        return $result;
    }
    
    function renderTextEditor($name, $text = '') {
        global $CNCAT_ENGINE, $CNCAT;
    
        /*$result = "
            <style type=\"text/css\">
                .editor table td {
                    text-align: left;
                }
            </style>
        ";*/
        $result = "";
        $result .= "\n<script type=\"text/javascript\">\n";
        $result .= "tinyMCE.init({\n";

        if (!empty($CNCAT["config"]["tinymce"])) {
            $result .= $CNCAT["config"]["tinymce"];
        } else {
            $result .= "
                language : '" . strtok($CNCAT["config"]["language"], "_") . "',
                mode : 'exact',
                theme : 'advanced',
                convert_urls: false,
                relative_urls: false,
                plugins : 'fullscreen,preview,paste',
                theme_advanced_toolbar_location : 'top',
                theme_advanced_buttons1 : 'bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,formatselect',
                theme_advanced_buttons2 : 'cut,copy,paste,pasteword,separator,bullist,numlist,separator,outdent,indent,separator,undo,redo,separator,link,unlink,anchor,image,separator,fullscreen,preview,code',
                theme_advanced_buttons3 : 'hr,removeformat,visualaid,separator,sub,sup,separator,charmap',
                skin : 'default'
            ";
        }
        
        if ($CNCAT_ENGINE->misc->isAdmin() || ( $_SESSION["moder_imgbr_allow"] && $CNCAT_ENGINE->misc->isModer() )) {
            $result .= ", file_browser_callback : 'myCustomFileBrowser'";
        }

        $result .= ", elements : '" . $name . "'";
        $result .= ", content_css : '" . $CNCAT_ENGINE->tpl->getThemeUrl() . "editor.css'";
        $result .= "});\n";

        if ($CNCAT_ENGINE->misc->isAdmin() || $_SESSION["moder_imgbr_allow"]) {
            $result .= "
var nenergo_win;
var nenergo_field_name;

function myCustomFileBrowser(field_name, url, type, win) {
     nenergo_win=win;
     nenergo_field_name=field_name;

     mywnd=window.open('" . $CNCAT["abs"] . $CNCAT["system"]["dir_admin"] . "image_browser.php?type='+type,'browser','scrollbars=no,width=670,height=590,top='+((screen.height-550)/2)+',left='+((screen.width-790)/2));
     if (!mywnd) alert('Error');
     else {
          url='';
          }
     }

function mySetImage(file) {
     nenergo_win.document.forms[0].elements[nenergo_field_name].value = file;
     nenergo_win.document.forms[0].elements[nenergo_field_name].onchange();
}
";
        }

        $result .= "</script>\n";
        $result .= "<div class=\"editor\"><textarea id=\"" . $name . "\" name=\"" . $name . "\" cols=\"1\" rows=\"1\" style=\"height: 350px; width: 100%;\">" . htmlspecialchars($text) . "</textarea></div>";

        return $result;
    }

    function includeScript($src, $type = 'text/javascript') {
        if ($src[0] != '/' && substr($src, 0, 7) != 'http://') {
            $src = $this->CNCAT['abs'] . $this->CNCAT['system']['dir_engine_scripts'] . $src;
        }

        return '<script src="' . $src . '" type="' . $type . '"></script>' . "\n";
    }

    function includeStyle($href, $type = 'text/css') {
        if ($href[0] != '/' && substr($href, 0, 7) != 'http://') {
            $href = $this->CNCAT['abs'] . $this->CNCAT['system']['dir_engine_styles'] . $href;
        }

        return '<link rel="stylesheet" href="' . $href . '" type="' . $type . '" media="all" />' . "\n";
    }
}
?>
