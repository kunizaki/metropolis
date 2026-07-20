-- Create syntax for TABLE '#__authorarchive_bookmarks'
CREATE TABLE IF NOT EXISTS `#__authorarchive_bookmarks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) NOT NULL DEFAULT '0',
  `item_ids` text,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;