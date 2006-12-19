CREATE TABLE `kb3_banned_mails` (
  `bml_timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `bml_ip` varchar(32) NOT NULL default '',
  `bml_mail` text NOT NULL
) TYPE=MyISAM;