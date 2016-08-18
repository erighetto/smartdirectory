ALTER TABLE `%prefix%cats` ADD `display` TINYINT NOT NULL DEFAULT '1';
ALTER TABLE `%prefix%items` ADD `ext_image1` MEDIUMBLOB NULL;
ALTER TABLE `%prefix%items` ADD `ext_image1_thumb` MEDIUMBLOB NULL;
ALTER TABLE `%prefix%items` ADD `ext_image1_mime` VARCHAR(30) NULL;
ALTER TABLE `%prefix%items` ADD `ext_image2` MEDIUMBLOB NULL;
ALTER TABLE `%prefix%items` ADD `ext_image2_thumb` MEDIUMBLOB NULL;
ALTER TABLE `%prefix%items` ADD `ext_image2_mime` VARCHAR(30) NULL;
ALTER TABLE `%prefix%items` ADD `ext_image3` MEDIUMBLOB NULL;
ALTER TABLE `%prefix%items` ADD `ext_image3_thumb` MEDIUMBLOB NULL;
ALTER TABLE `%prefix%items` ADD `ext_image3_mime` VARCHAR(30) NULL;
INSERT INTO `%prefix%fields` (`name`, `type`, `active`, `required`, `item_type`, `title`, `search`, `display`, `sort_order`) VALUES('ext_image1', 6, 0, 0, 0, 'Картинка 1', 0, '', 1000);
INSERT INTO `%prefix%fields` (`name`, `type`, `active`, `required`, `item_type`, `title`, `search`, `display`, `sort_order`) VALUES('ext_image2', 6, 0, 0, 0, 'Картинка 2', 0, '', 1000);
INSERT INTO `%prefix%fields` (`name`, `type`, `active`, `required`, `item_type`, `title`, `search`, `display`, `sort_order`) VALUES('ext_image3', 6, 0, 0, 0, 'Картинка 3', 0, '', 1000);
INSERT INTO `%prefix%fields` (`name`, `type`, `active`, `required`, `item_type`, `title`, `search`, `display`, `sort_order`) VALUES('ext_image1', 6, 0, 0, 1, 'Картинка 1', 0, '', 1000);
INSERT INTO `%prefix%fields` (`name`, `type`, `active`, `required`, `item_type`, `title`, `search`, `display`, `sort_order`) VALUES('ext_image2', 6, 0, 0, 1, 'Картинка 2', 0, '', 1000);
INSERT INTO `%prefix%fields` (`name`, `type`, `active`, `required`, `item_type`, `title`, `search`, `display`, `sort_order`) VALUES('ext_image3', 6, 0, 0, 1, 'Картинка 3', 0, '', 1000);
INSERT INTO `%prefix%config` (`name`, `value`) VALUES('add_short_wysiwyg', '0');
INSERT INTO `%prefix%config` (`name`, `value`) VALUES('add_article_short_wysiwyg', '0');
