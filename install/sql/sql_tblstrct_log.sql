CREATE TABLE `kb3_log` (
  `log_kll_id` int(11) NOT NULL default '0',
  `log_site` varchar(20) NOT NULL default '',
  `log_ip_address` varchar(20) NOT NULL default '',
  `log_timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `log_kll_id` (`log_kll_id`)
) TYPE=MyISAM;