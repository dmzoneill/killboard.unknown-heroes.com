CREATE TABLE `kb3_ships` (
  `shp_id` int(11) NOT NULL auto_increment,
  `shp_name` varchar(64) NOT NULL default '',
  `shp_class` int(11) NOT NULL default '18',
  `shp_externalid` int(11) NOT NULL default '0',
  `shp_rce_id` int(11) NOT NULL default '0',
  `shp_baseprice` int(12) NOT NULL default '0',
  `shp_techlevel` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`shp_id`),
  UNIQUE KEY `shp_name` (`shp_name`),
  KEY `shp_class` (`shp_class`)
) TYPE=MyISAM;