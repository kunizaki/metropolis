ALTER TABLE `#__spproperty_properties` ADD COLUMN `fixed_price` TINYINT(3) NOT NULL DEFAULT '0' AFTER `price`;
ALTER TABLE `#__spproperty_properties` ADD COLUMN `currency` varchar(50) DEFAULT '' AFTER `price`;
ALTER TABLE `#__spproperty_properties` ADD COLUMN `currency_position` varchar(20) DEFAULT '' AFTER `price`;
ALTER TABLE `#__spproperty_properties` ADD COLUMN `currency_format` varchar(20) DEFAULT '' AFTER `price`;