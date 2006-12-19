CREATE TABLE `kb3_pilots` (
  `plt_id` int(11) NOT NULL auto_increment,
  `plt_name` varchar(64) NOT NULL,
  `plt_crp_id` int(11) NOT NULL default '0',
  `plt_externalid` int(11) NOT NULL default '0',
  `plt_killpoints` int(11) NOT NULL default '0',
  `plt_losspoints` int(11) NOT NULL default '0',
  `plt_updated` datetime default NULL,
  PRIMARY KEY  (`plt_id`),
  UNIQUE KEY `plt_name` (`plt_name`)
) TYPE=MyISAM;