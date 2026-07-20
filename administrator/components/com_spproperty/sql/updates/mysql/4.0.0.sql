ALTER TABLE `#__spproperty_agents` ADD COLUMN `instagram` varchar(255) DEFAULT '' AFTER `facebook`;
ALTER TABLE `#__spproperty_properties` MODIFY `price` float(8,2) NOT NULL DEFAULT '0.00';