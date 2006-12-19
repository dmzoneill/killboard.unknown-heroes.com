CREATE TABLE `kb3_system_jumps` (
  `sjp_from` int(11) NOT NULL default '0',
  `sjp_to` int(11) NOT NULL default '0',
  KEY `sjp_from` (`sjp_from`)
) TYPE=MyISAM;