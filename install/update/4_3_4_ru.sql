CREATE TABLE `%prefix%linkact_comm` (  `item_id` int(11) NOT NULL,  `action_comm` text NOT NULL,  `act_date` datetime NOT NULL default '0000-00-00 00:00:00');
ALTER TABLE `%prefix%banners` CHANGE `code` `bcode` TEXT NOT NULL; 
ALTER TABLE `%prefix%banners` CHANGE `page` `bpage` TINYINT( 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `%prefix%banners` CHANGE `comment` `bcomment` TEXT NOT NULL; 
ALTER TABLE `%prefix%banners` CHANGE `condition` `bcondition` VARCHAR( 255 ) NOT NULL; 