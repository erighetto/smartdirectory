<!-- Admin -->
{TEMPLATE mail_admin_to}{$CNCAT[config][admin_email]}{/TEMPLATE}
{TEMPLATE mail_admin_from}{$CNCAT[config][admin_email]}{/TEMPLATE}
{TEMPLATE mail_admin_subject}{$CNCAT[lang][mail_admin_subject]}{/TEMPLATE}

{TEMPLATE mail_admin_body
    (ITEM[link_url, item_title, item_descr])
}
{$CNCAT[lang][mail_admin_body]}

{$CNCAT[lang][site_title]}: {$CNCAT[item][item_title]}
{$CNCAT[lang][link_url]}: {$CNCAT[item][link_url]}
{$CNCAT[lang][descr]}: {$CNCAT[item][item_descr]}
{/TEMPLATE}

<!-- Add -->
{TEMPLATE mail_add_from}{$CNCAT[config][admin_email]}{/TEMPLATE}
{TEMPLATE mail_add_reply_to}{$CNCAT[config][admin_email]}{/TEMPLATE}
{TEMPLATE mail_add_subject}{$CNCAT[lang][mail_add_subject]}{/TEMPLATE}

{TEMPLATE mail_add_body
    (ITEM[link_url, item_title])
}
{$CNCAT[lang][mail_add_body]}

{$CNCAT[lang][site_title]}: {$CNCAT[item][item_title]}
{$CNCAT[lang][link_url]}: {$CNCAT[item][link_url]}

{$CNCAT[lang][backlinks]}:
{DISPLAY BACKLINKS}
{/TEMPLATE}

<!-- Approve -->
{TEMPLATE mail_approve_from}{$CNCAT[config][admin_email]}{/TEMPLATE}
{TEMPLATE mail_approve_reply_to}{$CNCAT[config][admin_email]}{/TEMPLATE}
{TEMPLATE mail_approve_subject}{$CNCAT[lang][mail_approve_subject]}{/TEMPLATE}

{TEMPLATE mail_approve_body
    (ITEM[link_url, item_title, item_descr])
}
{$CNCAT[lang][mail_approve_body]}

{$CNCAT[lang][site_title]}: {$CNCAT[item][item_title]}
{$CNCAT[lang][link_url]}: {$CNCAT[item][link_url]}
{/TEMPLATE}

<!-- Decline -->
{TEMPLATE mail_decline_from}{$CNCAT[config][admin_email]}{/TEMPLATE}
{TEMPLATE mail_decline_reply_to}{$CNCAT[config][admin_email]}{/TEMPLATE}
{TEMPLATE mail_decline_subject}{$CNCAT[lang][mail_decline_subject]}{/TEMPLATE}

{TEMPLATE mail_decline_body
    (ITEM[link_url, item_title, item_descr])
}
{$CNCAT[lang][mail_decline_body]}

{$CNCAT[lang][site_title]}: {$CNCAT[item][item_title]}
{$CNCAT[lang][link_url]}: {$CNCAT[item][link_url]}
{/TEMPLATE}
