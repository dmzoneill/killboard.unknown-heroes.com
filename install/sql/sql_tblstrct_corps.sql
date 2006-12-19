CREATE TABLE `kb3_corps` (
  `crp_id` int(11) NOT NULL auto_increment,
  `crp_name` varchar(64) NOT NULL default '',
  `crp_all_id` int(11) NOT NULL default '0',
  `crp_trial` tinyint(4) NOT NULL default '0',
  `crp_updated` datetime default NULL,
  PRIMARY KEY  (`crp_id`)
) TYPE=MyISAM;