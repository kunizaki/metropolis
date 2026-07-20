
-- SP Property - by JoomShaper.com
-- author    JoomShaper http://www.joomshaper.com
-- Copyright (C) 2010 - 2018 JoomShaper.com. All Rights Reserved.
-- License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
-- Websites: http://www.joomshaper.com

-- update on version 3.0

-- Create syntax for TABLE '#__spproperty_favourites'
CREATE TABLE IF NOT EXISTS `#__spproperty_favourites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `property_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `#__spproperty_agents` MODIFY `published` TINYINT(3) NOT NULL DEFAULT '0';
ALTER TABLE `#__spproperty_agents` ADD COLUMN `featured` tinyint(1) DEFAULT NULL AFTER `gplus`;

ALTER TABLE `#__spproperty_propertyfeatures` ADD COLUMN `icon_type` tinyint(1) NOT NULL DEFAULT '0' AFTER `alias`;
ALTER TABLE `#__spproperty_propertyfeatures` ADD COLUMN `icon` VARCHAR(100) DEFAULT NULL AFTER `alias`;
ALTER TABLE `#__spproperty_propertyfeatures` ADD COLUMN `icon_fa` VARCHAR(100) DEFAULT NULL AFTER `alias`;
ALTER TABLE `#__spproperty_propertyfeatures` ADD COLUMN `icon_sp` VARCHAR(100) DEFAULT NULL AFTER `alias`;
ALTER TABLE `#__spproperty_propertyfeatures` ADD COLUMN `image` TEXT DEFAULT NULL AFTER `alias`;