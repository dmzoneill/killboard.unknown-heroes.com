CREATE TABLE `kb3_regions` (
  `reg_id` int(11) NOT NULL default '0',
  `reg_name` varchar(64) NOT NULL default '',
  `reg_x` float NOT NULL default '0',
  `reg_y` float NOT NULL default '0',
  `reg_z` float NOT NULL default '0',
  PRIMARY KEY  (`reg_id`)
) TYPE=MyISAM;