-- v4.0.3

ALTER TABLE `#__spproperty_properties` CHANGE `created` `created` datetime NULL DEFAULT NULL;
ALTER TABLE `#__spproperty_properties` CHANGE `modified` `modified` datetime NULL DEFAULT NULL; 
ALTER TABLE `#__spproperty_properties` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;

ALTER TABLE `#__spproperty_properties` ADD `gallery_folder` TINYINT(1) NULL DEFAULT NULL AFTER `image`;
ALTER TABLE `#__spproperty_properties` ADD `gallery_folder_name` VARCHAR(50) NULL DEFAULT NULL AFTER `gallery_folder`;
