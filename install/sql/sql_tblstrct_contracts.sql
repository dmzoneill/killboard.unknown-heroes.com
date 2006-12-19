CREATE TABLE `kb3_contracts` (
  `ctr_id` int(11) NOT NULL auto_increment,
  `ctr_name` varchar(128) NOT NULL default '',
  `ctr_site` varchar(64) NOT NULL default '',
  `ctr_campaign` smallint(6) NOT NULL default '0',
  `ctr_started` datetime NOT NULL default '0000-00-00 00:00:00',
  `ctr_ended` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ctr_id`)
) TYPE=MyISAM;