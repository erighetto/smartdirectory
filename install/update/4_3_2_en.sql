CREATE TABLE `%prefix%actlog` (`item_id` int(11) NOT NULL default '0', `user_id` int(11) NOT NULL default '0', `date` datetime NOT NULL default '0000-00-00 00:00:00', `code` tinyint(4) NOT NULL default '0', `comment` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `%prefix%cats` ADD `items_sort_order` TINYINT NOT NULL DEFAULT '-1';
ALTER TABLE `%prefix%items` ADD `item_token` VARCHAR( 15 ) NULL AFTER `item_type`;
