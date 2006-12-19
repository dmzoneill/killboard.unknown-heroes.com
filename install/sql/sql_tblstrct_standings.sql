CREATE TABLE `kb3_standings` (
  `sta_from` int(11) NOT NULL default '0',
  `sta_to` int(11) NOT NULL default '0',
  `sta_from_type` enum('a','c') NOT NULL default 'a',
  `sta_to_type` enum('a','c') NOT NULL default 'a',
  `sta_value` float NOT NULL default '0',
  `sta_comment` varchar(200) NOT NULL,
  KEY `sta_from` (`sta_from`)
) TYPE=MyISAM;