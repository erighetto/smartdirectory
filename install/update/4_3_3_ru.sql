ALTER TABLE `%prefix%items` ADD `last_check` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
INSERT INTO `%prefix%config` (`name`, `value`) VALUES('mail_notify_isolation', '1');
INSERT INTO `%prefix%mail` (`name`, `from`, `reply_to`, `subject`, `body`) VALUES('isolation', '{$CNCAT[config][admin_email]}', '{$CNCAT[config][admin_email]}', '{$CNCAT[lang][mail_isolation_subject]}', '{$CNCAT[lang][mail_isolation_body]}\r\n\r\n{$CNCAT[lang][site_title]}: {$CNCAT[item][item_title]}\r\n{$CNCAT[lang][link_url]}: {$CNCAT[item][link_url]}');
