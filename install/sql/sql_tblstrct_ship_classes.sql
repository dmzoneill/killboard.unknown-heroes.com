CREATE TABLE `kb3_ship_classes` (
  `scl_id` int(11) NOT NULL auto_increment,
  `scl_class` varchar(32) NOT NULL default '',
  `scl_value` bigint(4) NOT NULL default '0',
  `scl_points` int(11) NOT NULL default '0',
  PRIMARY KEY  (`scl_id`)
) TYPE=MyISAM;