CREATE TABLE `%prefix%actlog` (
  `item_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `code` tinyint(4) NOT NULL default '0',
  `comment` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%backlinks` (
  `id` int(11) NOT NULL auto_increment,
  `user_code` text NOT NULL,
  `check_code` text,
  `check_url` text,
  `check_preg` text,
  `check_method` enum('code','url','preg') NOT NULL default 'code',
  `sort_order` int(11) NOT NULL default '1000',
  `disabled` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%banners` (
  `id` int(11) NOT NULL auto_increment,
  `bcode` text NOT NULL,
  `bcondition` varchar(255) NOT NULL default '',
  `bcomment` text NOT NULL,
  `bpage` tinyint(4) NOT NULL default '0',
  `cat_id` int(11) NOT NULL default '0',
  `child_cats` tinyint(4) NOT NULL default '0',
  `on_cat_main` tinyint(4) NOT NULL default '0',
  `pattern` varchar(255) NOT NULL default '',
  `item_type` varchar(255) NOT NULL default '',
  `position` enum('page_top','page_left','page_right','page_bottom','items_top','items_middle','items_bottom') NOT NULL default 'page_top',
  `enable_php` tinyint(4) NOT NULL default '0',
  `sort_order` int(11) NOT NULL default '0',
  `disabled` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `disabled` (`disabled`,`sort_order`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%bwlist` (
  `id` int(11) NOT NULL auto_increment,
  `type` enum('black','white') NOT NULL default 'black',
  `check_method` enum('substr','regexp') NOT NULL default 'substr',
  `check_str` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%cats` (
  `id` int(11) NOT NULL auto_increment,
  `id_full` varchar(255) NOT NULL default '',
  `is_link` tinyint(4) NOT NULL default '0',
  `id_real` int(20) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `title_full` text NOT NULL,
  `path` varchar(255) NOT NULL default '',
  `path_full` text NOT NULL,
  `parent_id` int(11) NOT NULL default '0',
  `child_id_list` text NOT NULL,
  `tree_level` int(11) NOT NULL default '0',
  `item_count` int(11) NOT NULL default '0',
  `item_count_full` int(11) NOT NULL default '0',
  `link_count` int(11) NOT NULL default '0',
  `link_count_full` int(11) NOT NULL default '0',
  `article_count` int(11) NOT NULL default '0',
  `article_count_full` int(11) NOT NULL default '0',
  `last_update` datetime NOT NULL default '0000-00-00 00:00:00',
  `sort_order` int(11) NOT NULL default '1000',
  `sort_order_global` int(11) NOT NULL default '0',
  `meta_keywords` text,
  `meta_descr` text,
  `image` mediumblob,
  `image_mime` varchar(20) default NULL,
  `descr` text,
  `theme_id` int(11) NOT NULL default '0',
  `disable_add` tinyint(4) NOT NULL default '0',
  `disable_child_add` tinyint(4) NOT NULL default '0',
  `display` tinyint(4) NOT NULL default '1',
  `items_sort_order` tinyint(4) NOT NULL default '-1',
  PRIMARY KEY  (`id`),
  KEY `sort_order` (`parent_id`,`sort_order`,`title`),
  KEY `path_full` (`path_full`(255)),
  FULLTEXT KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%checklog` (
  `id` int(11) NOT NULL auto_increment,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `text` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%comments` (
  `id` int(11) NOT NULL auto_increment,
  `item_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `author_name` varchar(255) NOT NULL default '',
  `author_email` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `vote` tinyint(4) NOT NULL default '0',
  `date_insert` datetime NOT NULL default '0000-00-00 00:00:00',
  `active` tinyint(4) NOT NULL default '0',
  `display` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%config` (
  `name` varchar(255) NOT NULL default '',
  `value` text,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%fields` (
  `name` varchar(50) NOT NULL default '',
  `type` tinyint(4) NOT NULL default '0',
  `active` tinyint(4) NOT NULL default '0',
  `required` tinyint(4) NOT NULL default '0',
  `item_type` tinyint(4) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `search` tinyint(4) NOT NULL default '0',
  `display` varchar(255) NOT NULL default '0',
  `sort_order` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%filters` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `required` tinyint(4) NOT NULL default '0',
  `sort_order` int(11) NOT NULL default '1000',
  PRIMARY KEY  (`id`),
  KEY `sort_order` (`sort_order`,`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%filtvals` (
  `id` int(11) NOT NULL auto_increment,
  `filter_id` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `sort_order` int(11) NOT NULL default '1000',
  PRIMARY KEY  (`id`),
  KEY `filter_id` (`filter_id`),
  KEY `sort_order` (`sort_order`,`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%images` (
  `img_id` int(11) NOT NULL auto_increment,
  `img_cid` int(11) NOT NULL default '0',
  `img_title` varchar(50) NOT NULL default '',
  `img_mime` varchar(20) NOT NULL default '',
  `img_data` mediumblob NOT NULL,
  `thumb_data` mediumblob NOT NULL,
  PRIMARY KEY  (`img_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%img_cats` (
  `cat_id` int(11) NOT NULL auto_increment,
  `cat_title` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`cat_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%itemcat` (
  `item_id` int(11) NOT NULL default '0',
  `cat_id` int(11) NOT NULL default '0',
  `priority` smallint(6) NOT NULL default '0',
  `item_status` tinyint(4) NOT NULL default '0',
  `item_type` tinyint(4) NOT NULL default '0',
  KEY `item_id` (`item_id`),
  KEY `item_status` (`item_status`,`cat_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%itemfilt` (
  `item_id` int(11) NOT NULL default '0',
  `filtval_id` int(11) NOT NULL default '0',
  KEY `link_id` (`item_id`,`filtval_id`),
  KEY `filtval_id` (`filtval_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%items` (
  `item_id` int(11) NOT NULL auto_increment,
  `item_status` tinyint(4) NOT NULL default '0',
  `item_type` tinyint(4) NOT NULL default '0',
  `item_token` varchar(15) default NULL,
  `item_title` varchar(255) default NULL,
  `item_title_translite` varchar(510) NOT NULL,
  `item_descr` text,
  `item_descr_full` text,
  `item_meta_keywords` text,
  `item_meta_descr` text,
  `item_image` mediumblob,
  `item_image_mime` varchar(20) default NULL,
  `item_display_ext` tinyint(4) NOT NULL default '0',
  `item_insert_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `item_insert_type` tinyint(4) NOT NULL default '1',
  `item_submit_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `item_submit_type` tinyint(4) NOT NULL default '0',
  `item_favour` tinyint(4) NOT NULL default '0',
  `item_favour_neg` tinyint(4) NOT NULL default '0',
  `item_rating_moder` tinyint(4) NOT NULL default '0',
  `item_rating_moder_neg` int(11) NOT NULL default '0',
  `item_rating_users` decimal(6,1) NOT NULL default '0.0',
  `item_rating_users_neg` decimal(6,1) NOT NULL default '0.0',
  `item_votes_count` int(11) NOT NULL default '0',
  `item_author_ip` varchar(20) default NULL,
  `item_author_email` varchar(255) default NULL,
  `item_author_name` varchar(255) default NULL,
  `item_mail_sended` tinyint(4) NOT NULL default '0',
  `item_com_count` int(11) NOT NULL default '0',
  `link_url` text,
  `link_back_link_url` text,
  `link_favicon` blob,
  `link_favicon_url` varchar(255) default NULL,
  `link_favicon_mime` varchar(20) default NULL,
  `link_region_id` int(11) NOT NULL default '0',
  `link_broken_warning` tinyint(4) default '0',
  `link_target` tinyint(4) NOT NULL default '0',
  `link_jumps_from` int(11) NOT NULL default '0',
  `link_jumps_from_neg` int(11) NOT NULL default '0',
  `link_jumps_to` int(11) NOT NULL default '0',
  `link_jumps_to_neg` int(11) NOT NULL default '0',
  `link_rating_pr` int(11) NOT NULL default '-1',
  `link_rating_pr_neg` int(11) NOT NULL default '1',
  `link_rating_cy` int(11) NOT NULL default '-1',
  `link_rating_cy_neg` int(11) NOT NULL default '1',
  `link_pr_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `link_cy_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `link_chk_flag` tinyint(4) NOT NULL default '0',
  `link_chk_work_res` tinyint(4) NOT NULL default '0',
  `link_chk_work_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `link_chk_back_res` tinyint(4) NOT NULL default '0',
  `link_chk_back_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_check` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_chk_comment` text,
  `ext_text1` text,
  `ext_text2` text,
  `ext_text3` text,
  `ext_varchar1` varchar(255) default NULL,
  `ext_varchar2` varchar(255) default NULL,
  `ext_varchar3` varchar(255) default NULL,
  `ext_int1` int(11) default NULL,
  `ext_int2` int(11) default NULL,
  `ext_int3` int(11) default NULL,
  `ext_double1` double default NULL,
  `ext_double2` double default NULL,
  `ext_double3` double default NULL,
  `ext_datetime1` datetime default NULL,
  `ext_datetime2` datetime default NULL,
  `ext_datetime3` datetime default NULL,
  `ext_image1` mediumblob,
  `ext_image1_thumb` mediumblob,
  `ext_image1_mime` varchar(30) default NULL,
  `ext_image2` mediumblob,
  `ext_image2_thumb` mediumblob,
  `ext_image2_mime` varchar(30) default NULL,
  `ext_image3` mediumblob,
  `ext_image3_thumb` mediumblob,
  `ext_image3_mime` varchar(30) default NULL,
  PRIMARY KEY  (`item_id`),
  KEY `rating_users` (`item_status`,`item_favour_neg`,`item_rating_users_neg`,`item_title`),
  KEY `rating_pr` (`item_status`,`item_favour_neg`,`link_rating_pr_neg`,`item_title`),
  KEY `rating_moder` (`item_status`,`item_favour_neg`,`item_rating_moder_neg`,`item_title`),
  KEY `rating_cy` (`item_status`,`item_favour_neg`,`link_rating_cy_neg`,`item_title`),
  KEY `title` (`item_status`,`item_favour_neg`,`item_title`),
  KEY `insert_date` (`item_status`,`item_favour`,`item_insert_date`),
  KEY `submit_date` (`item_status`,`item_favour`,`item_submit_date`,`item_insert_date`),
  KEY `new_items` (`item_status`,`item_submit_date`,`item_insert_date`),
  KEY `popularity` (`item_favour_neg`,`link_jumps_from_neg`,`link_jumps_to_neg`,`item_title`),
  KEY `status` (`item_status`),
  FULLTEXT KEY `fulltext` (`item_title`,`item_descr`,`item_descr_full`,`item_meta_keywords`,`item_meta_descr`,`link_url`,`ext_text1`,`ext_text2`,`ext_text3`,`ext_varchar1`,`ext_varchar2`,`ext_varchar3`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%jumps` (
  `item_id` int(11) NOT NULL default '0',
  `ip` int(10) unsigned NOT NULL default '0',
  `type` enum('to','from','vote') NOT NULL default 'to',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  KEY `item_id` (`item_id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%linkcheck` (
  `moder_id` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  `check_flag` tinyint(4) NOT NULL default '0',
  KEY `moder_id` (`moder_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%mail` (
  `name` varchar(50) NOT NULL default '',
  `from` varchar(255) NOT NULL default '',
  `reply_to` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%modercat` (
  `mid` int(11) default '0',
  `cid` int(11) default '0',
  KEY `mid` (`mid`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%moders` (
  `id` int(11) NOT NULL auto_increment,
  `login` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `pass` varchar(32) NOT NULL default '',
  `cat_edit` tinyint(4) NOT NULL default '0',
  `imgbr_allow` tinyint(1) NOT NULL default '0',
  `cats` text NOT NULL,
  `cats_child` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%prefix%linkact_comm` (  
  `item_id` int(11) NOT NULL,  
  `action_comm` text NOT NULL,  
  `act_date` datetime NOT NULL default '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

