{TEMPLATE ext
    (ITEM[item_title, item_descr, item_descr_full, link_url, link_target, link_jumps_to, link_jumps_from, item_rating_moder, item_image_mime])
}
    {INCLUDE top}
    {INCLUDE menu}

    <strong>{cn_str($CNCAT[ext][item_title])}</strong>

    <hr />

    <style type="text/css">
        #ext {
            width: 600px;
            border-collapse: collapse;
            border: 0;
        }

        #ext td {
            padding: 2px 0;
        }

        #ext td.header {
            font-weight: bold;
            vertical-align: top;
            width: 40%;
        }
    </style>

    <table id="ext">
    {IF $CNCAT[item][item_status] == 1}
        <tr><td class="header">{$CNCAT[lang][title]}:</td><td><strong>{cn_str($CNCAT[item][item_title])}</strong></td></tr>
        <tr><td class="header">{$CNCAT[lang][link_url]}:</td><td>
            {IF $CNCAT[item][link_target] == 1}
                <a target="_blank" href="{cn_str($CNCAT[item][link_url])}"><strong>{cn_str($CNCAT[item][item_title])}</strong></a>
            {ENDIF}
            {IF $CNCAT[item][link_target] == 2}
                <a target="_blank" href="{$CNCAT[config][cncat_url]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}"><strong>{cn_str($CNCAT[item][item_title])}</strong></a>
            {ENDIF}
            {IF $CNCAT[item][link_target] == 3}
                <a target="_blank" href="{cn_str($CNCAT[item][link_url])}" onclick="location.href='{$CNCAT[config][cncat_url]}{$CNCAT[system][dir_prefix]}jump.php?{$CNCAT[item][item_id]}'; return false;"><strong>{cn_str($CNCAT[item][item_title])}</strong></a>
            {ENDIF}
        </td></tr>
        <tr>
            <td class="header">{$CNCAT[lang][categories]}:</td>
            <td>
                {DISPLAY CATEGORIES}
            </td>
        </tr>
        <tr><td class="header">{$CNCAT[lang][descr]}:</td><td>{cn_str($CNCAT[item][item_descr])}</td></tr>
        {IF $CNCAT[item][item_descr_full]}
            <tr><td class="header">{$CNCAT[lang][descr_full]}:</td><td>{cn_str($CNCAT[item][item_descr_full])}</td></tr>
        {ENDIF}
        {DISPLAY FILTERS}
        <tr>
            <td class="header">{$CNCAT[lang][moder_rating]}:</td>
            <td>
                {IF $CNCAT[item][item_rating_moder]}
                    {cn_str($CNCAT[item][item_rating_moder])}
                {ELSE}
                    {$CNCAT[lang][no_moder_rating]}
                {ENDIF}
            </td>
        </tr>
        <tr><td class="header">{$CNCAT[lang][jumps_to]}:</td><td>{cn_str($CNCAT[item][link_jumps_to])}</td></tr>
        <tr><td class="header">{$CNCAT[lang][jumps_from]}:</td><td>{cn_str($CNCAT[item][link_jumps_from])}</td></tr>
    {ELSE}
            <tr><td>{$CNCAT[lang][link_not_approved]}</td></tr>
    {ENDIF}
    </table>

    {INCLUDE bottom}
{/TEMPLATE}

{TEMPLATE ext_cats_top}
    <ul>
{/TEMPLATE}

{TEMPLATE ext_cat
    (CAT[title_full])
}
            <li><a href="{cn_str($CNCAT[cat][_url])}">{cn_str($CNCAT[cat][title_full])}</a></li>
{/TEMPLATE}

{TEMPLATE ext_cat_delim}{/TEMPLATE}

{TEMPLATE ext_cats_bottom}
    </ul>
{/TEMPLATE}

{TEMPLATE ext_filter_top}
        <tr><td class="header">{cn_str($CNCAT[filter][title])}:</td><td>
{/TEMPLATE}

{TEMPLATE ext_filtval}{cn_str($CNCAT[filtval][title])}{/TEMPLATE}

{TEMPLATE ext_filtval_delim}, {/TEMPLATE}

{TEMPLATE ext_filter_bottom}
        </td></tr>
{/TEMPLATE}
