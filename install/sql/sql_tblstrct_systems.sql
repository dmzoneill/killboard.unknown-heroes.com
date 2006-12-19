CREATE TABLE `kb3_systems` (
  `sys_id` int(11) NOT NULL auto_increment,
  `sys_eve_id` int(11) NOT NULL default '0',
  `sys_con_id` int(11) NOT NULL default '0',
  `sys_name` varchar(128) NOT NULL default '',
  `sys_x` float NOT NULL default '0',
  `sys_y` float NOT NULL default '0',
  `sys_z` float NOT NULL default '0',
  `sys_sec` decimal(21,20) NOT NULL default '0.00000000000000000000',
  PRIMARY KEY  (`sys_id`)
) TYPE=MyISAM;