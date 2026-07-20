
-- SP Property - by JoomShaper.com
-- author    JoomShaper http://www.joomshaper.com
-- Copyright (C) 2010 - 2018 JoomShaper.com. All Rights Reserved.
-- License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
-- Websites: http://www.joomshaper.com

-- Create syntax for TABLE '#__spproperty_agents'
CREATE TABLE IF NOT EXISTS `#__spproperty_agents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(55) NOT NULL DEFAULT '',
  `designation` varchar(100) NOT NULL DEFAULT '',
  `phone` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `skype` varchar(255) DEFAULT '',
  `email` varchar(255) DEFAULT NULL,
  `image` text,
  `description` text,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `gplus` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `language` varchar(255) NOT NULL DEFAULT '*',
  `access` int(5) NOT NULL DEFAULT '1',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime  NULL DEFAULT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime  NULL DEFAULT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__spproperty_visitrequests'
CREATE TABLE IF NOT EXISTS `#__spproperty_visitrequests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL DEFAULT '0',
  `type` varchar(50) DEFAULT 'visit',
  `customer_name` varchar(255) NOT NULL DEFAULT '',
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `customer_comments` varchar(255) DEFAULT NULL,
  `visitor_ip` varchar(50) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `language` varchar(255) NOT NULL DEFAULT '*',
  `access` int(5) NOT NULL DEFAULT '1',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime  NULL DEFAULT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NULL DEFAULT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__spproperty_categories'
CREATE TABLE IF NOT EXISTS `#__spproperty_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(55) NOT NULL DEFAULT '',
  `icon_image` tinyint(1) NOT NULL,
  `image` text,
  `icon` varchar(255) DEFAULT NULL,
  `desc` TEXT,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `language` varchar(255) NOT NULL DEFAULT '*',
  `access` int(5) NOT NULL DEFAULT '1',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime  NULL DEFAULT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NULL DEFAULT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__spproperty_properties'
CREATE TABLE IF NOT EXISTS `#__spproperty_properties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `property_id` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(55) NOT NULL DEFAULT '',
  `category_id` bigint(20) NOT NULL,
  `featured` tinyint(1) DEFAULT NULL,
  `features_text` text,
  `description` text,
  `image` text,
  `gallery_folder` tinyint(1) DEFAULT NULL,
  `gallery_folder_name` varchar(50) DEFAULT NULL,
  `gallery` text,
  `property_status` varchar(50) DEFAULT NULL,
  `rent_period` varchar(50) DEFAULT NULL,
  `price` float(8,2) NOT NULL DEFAULT '0.00',
  `fixed_price` TINYINT(3) NOT NULL DEFAULT '0',
  `currency` varchar(50) DEFAULT '',
  `currency_position` varchar(20) DEFAULT '',
  `currency_format` varchar(20) DEFAULT '',
  `price_request` varchar(50) DEFAULT NULL,
  `agent_id` bigint(20) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `city` varchar(155) DEFAULT NULL,
  `address` text,
  `map` text,
  `zip` varchar(50) DEFAULT '',
  `psize` int(20) DEFAULT NULL,
  `beds` int(5) DEFAULT NULL,
  `baths` int(5) DEFAULT NULL,
  `garages` int(5) DEFAULT NULL,
  `lvl_fltno` varchar(255) DEFAULT NULL,
  `building_year` int(10) DEFAULT NULL,
  `features` text,
  `video_text` text,
  `video` text,
  `fp_text` text,
  `floor_plans` text,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `language` varchar(255) NOT NULL DEFAULT '*',
  `access` int(5) NOT NULL DEFAULT '1',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NULL DEFAULT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NULL DEFAULT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__spproperty_propertyfeatures'
CREATE TABLE IF NOT EXISTS `#__spproperty_propertyfeatures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(55) NOT NULL DEFAULT '',
  `image` TEXT DEFAULT NULL,
  `icon_sp` VARCHAR(100) DEFAULT NULL,
  `icon_fa` VARCHAR(100) DEFAULT NULL,
  `icon` VARCHAR(100) DEFAULT NULL,
  `icon_type` tinyint(1) NOT NULL DEFAULT '0',
  `polls` text NOT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `language` varchar(255) NOT NULL DEFAULT '*',
  `access` int(5) NOT NULL DEFAULT '1',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NULL DEFAULT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NULL DEFAULT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__spproperty_favourites (From v2.2)'
CREATE TABLE IF NOT EXISTS `#__spproperty_favourites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `property_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;