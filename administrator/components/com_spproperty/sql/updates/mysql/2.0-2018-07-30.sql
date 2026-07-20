-- SP Property - by JoomShaper.com
-- author    JoomShaper http://www.joomshaper.com
-- Copyright (C) 2010 - 2018 JoomShaper.com. All Rights Reserved.
-- License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
-- Websites: http://www.joomshaper.com

-- update on version 2.0

--change #__spproperty_properties table's columns
ALTER TABLE `#__spproperty_properties` CHANGE `spproperty_property_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__spproperty_properties` CHANGE `slug` `alias` VARCHAR(55) NOT NULL DEFAULT '';
ALTER TABLE `#__spproperty_properties` CHANGE `spproperty_category_id` `category_id` BIGINT(20) NOT NULL;
ALTER TABLE `#__spproperty_properties` CHANGE `spproperty_agent_id` `agent_id` BIGINT(20) DEFAULT NULL;
ALTER TABLE `#__spproperty_properties` CHANGE `enabled` `published` TINYINT(3) NOT NULL DEFAULT '1';
ALTER TABLE `#__spproperty_properties` CHANGE `created_on` `created` datetime NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_properties` CHANGE `modified_on` `modified` datetime NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_properties` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__spproperty_properties` CHANGE `locked_on` `checked_out_time` datetime NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_properties` ADD COLUMN `rent_period` VARCHAR(50) DEFAULT NULL AFTER `property_status`;
ALTER TABLE `#__spproperty_properties` ADD COLUMN `price_request` VARCHAR(50) DEFAULT NULL AFTER `price`;
ALTER TABLE `#__spproperty_properties` ADD COLUMN `property_id` VARCHAR(255) DEFAULT NULL AFTER `id`;

--change #__spproperty_agents table's columns
ALTER TABLE `#__spproperty_agents` CHANGE `spproperty_agent_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__spproperty_agents` CHANGE `slug` `alias` VARCHAR(55) NOT NULL DEFAULT '';
ALTER TABLE `#__spproperty_agents` CHANGE `enabled` `published` TINYINT(3) NOT NULL DEFAULT '0';
ALTER TABLE `#__spproperty_agents` CHANGE `created_on` `created` datetime  NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_agents` CHANGE `modified_on` `modified` datetime  NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_agents` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__spproperty_agents` CHANGE `locked_on` `checked_out_time` datetime  NULL DEFAULT NULL;


--change #__spproperty_categories table's columns
ALTER TABLE `#__spproperty_categories` CHANGE `spproperty_category_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__spproperty_categories` CHANGE `slug` `alias` VARCHAR(55) NOT NULL DEFAULT '';
ALTER TABLE `#__spproperty_categories` CHANGE `enabled` `published` TINYINT(3) NOT NULL DEFAULT '1';
ALTER TABLE `#__spproperty_categories` CHANGE `created_on` `created` datetime  NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_categories` CHANGE `modified_on` `modified` datetime  NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_categories` CHANGE `locked_by` `checked_out` bigint(20)  NOT NULL DEFAULT '0';
ALTER TABLE `#__spproperty_categories` CHANGE `locked_on` `checked_out_time` datetime  NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_categories` ADD COLUMN `desc` TEXT AFTER `icon`;


--change #__spproperty_propertyfeatures table's colums
ALTER TABLE `#__spproperty_propertyfeatures` CHANGE `spproperty_propertyfeature_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__spproperty_propertyfeatures` CHANGE `slug` `alias` VARCHAR(55) NOT NULL DEFAULT '';
ALTER TABLE `#__spproperty_propertyfeatures` CHANGE `enabled` `published` TINYINT(3) NOT NULL DEFAULT '1';
ALTER TABLE `#__spproperty_propertyfeatures` CHANGE `created_on` `created` datetime  NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_propertyfeatures` CHANGE `modified_on` `modified` datetime  NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_propertyfeatures` CHANGE `locked_by` `checked_out` bigint(20)  NOT NULL DEFAULT '0';
ALTER TABLE `#__spproperty_propertyfeatures` CHANGE `locked_on` `checked_out_time` datetime  NULL DEFAULT NULL;

--change #__spproperty_visitrequests table's colums
ALTER TABLE `#__spproperty_visitrequests` CHANGE `spproperty_visitrequest_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__spproperty_visitrequests` CHANGE `spproperty_property_id` `property_id` bigint(20) NOT NULL;
ALTER TABLE `#__spproperty_visitrequests` CHANGE `enabled` `published` TINYINT(3) NOT NULL DEFAULT '1';
ALTER TABLE `#__spproperty_visitrequests` CHANGE `created_on` `created` datetime  NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_visitrequests` CHANGE `modified_on` `modified` datetime  NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_visitrequests` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__spproperty_visitrequests` CHANGE `locked_on` `checked_out_time` datetime  NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_visitrequests` ADD COLUMN `type` VARCHAR(50) DEFAULT 'visit' AFTER `userid`;