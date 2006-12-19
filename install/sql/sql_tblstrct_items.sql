CREATE TABLE `kb3_items` (
  `itm_id` int(11) NOT NULL auto_increment,
  `itm_name` varchar(128) NOT NULL default '',
  `itm_volume` double NOT NULL default '0',
  `itm_type` int(11) NOT NULL default '1',
  `itm_externalid` int(11) NOT NULL default '0',
  `itm_techlevel` tinyint(4) NOT NULL default '1',
  `itm_icon` varchar(56) NOT NULL,
  `itm_value` bigint(4) NOT NULL default '0',
  PRIMARY KEY  (`itm_id`),
  UNIQUE KEY `itm_name` (`itm_name`),
  KEY `itm_type` (`itm_type`)
) TYPE=MyISAM;