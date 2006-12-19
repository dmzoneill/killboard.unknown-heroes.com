CREATE TABLE `kb3_item_types` (
  `itt_id` int(11) NOT NULL default '0',
  `itt_name` varchar(120) NOT NULL default '',
  `itt_slot` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`itt_id`)
) TYPE=MyISAM;