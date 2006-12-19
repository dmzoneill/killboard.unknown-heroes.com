CREATE TABLE `kb3_config` (
  `cfg_site` varchar(16) NOT NULL default '',
  `cfg_key` varchar(32) NOT NULL default '',
  `cfg_value` text NOT NULL,
  PRIMARY KEY  (`cfg_site`,`cfg_key`)
) TYPE=MyISAM;