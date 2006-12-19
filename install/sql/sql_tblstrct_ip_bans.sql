CREATE TABLE `kb3_ip_bans` (
  `ipb_ip` varchar(32) NOT NULL default '',
  `ipb_comment` varchar(128) NOT NULL default '',
  UNIQUE KEY `ipb_ip` (`ipb_ip`)
) TYPE=MyISAM;