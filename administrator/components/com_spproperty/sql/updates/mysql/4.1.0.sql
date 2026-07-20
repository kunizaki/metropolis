-- v4.1.0

--  Alter Structure for #__spproperty_agents

ALTER TABLE `#__spproperty_agents` CHANGE `created` `created` DATETIME  NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_agents` CHANGE `modified` `modified` DATETIME  NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_agents` CHANGE `checked_out_time` `checked_out_time` DATETIME  NULL DEFAULT NULL;

--  Alter Structure for #__spproperty_categories

ALTER TABLE `#__spproperty_categories` CHANGE `created` `created` DATETIME NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_categories` CHANGE `modified` `modified` DATETIME NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_categories` CHANGE `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL;

--  Alter Structure for #__spproperty_propertyfeatures

ALTER TABLE `#__spproperty_propertyfeatures` CHANGE `created` `created` DATETIME NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_propertyfeatures` CHANGE `modified` `modified` DATETIME NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_propertyfeatures` CHANGE `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL;

--  Alter Structure for #__spproperty_visitrequests

ALTER TABLE `#__spproperty_visitrequests` CHANGE `created` `created` DATETIME NULL DEFAULT NULL; 
ALTER TABLE `#__spproperty_visitrequests` CHANGE `modified` `modified` DATETIME NULL DEFAULT NULL; 
ALTER TABLE `#__spproperty_visitrequests` CHANGE `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL;


--  Alter Structure for #__spproperty_properties

ALTER TABLE `#__spproperty_properties` CHANGE `created` `created` DATETIME NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_properties` CHANGE `modified` `modified` DATETIME NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_properties` CHANGE `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL;

--  Update Structure for #__spproperty_agents

UPDATE `#__spproperty_agents` SET `created` = NULL WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__spproperty_agents` SET `modified` = NULL WHERE `modified` = '0000-00-00 00:00:00';
UPDATE `#__spproperty_agents` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

--  Update Structure for #__spproperty_categories

UPDATE `#__spproperty_categories` SET `created` = NULL WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__spproperty_categories` SET `modified` = NULL WHERE `modified` = '0000-00-00 00:00:00';
UPDATE `#__spproperty_categories` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

--  Update Structure for #__spproperty_propertyfeatures

UPDATE `#__spproperty_propertyfeatures` SET `created` = NULL WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__spproperty_propertyfeatures` SET `modified` = NULL WHERE `modified` = '0000-00-00 00:00:00';
UPDATE `#__spproperty_propertyfeatures` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

--  Update Structure for #__spproperty_visitrequests

UPDATE `#__spproperty_visitrequests` SET `created` = NULL WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__spproperty_visitrequests` SET `modified` = NULL WHERE `modified` = '0000-00-00 00:00:00';
UPDATE `#__spproperty_visitrequests` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

--  Update Structure for #__spproperty_properties

UPDATE `#__spproperty_properties` SET `created` = NULL WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__spproperty_properties` SET `modified` = NULL WHERE `modified` = '0000-00-00 00:00:00';
UPDATE `#__spproperty_properties` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';