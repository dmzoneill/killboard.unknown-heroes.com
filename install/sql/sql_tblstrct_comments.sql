CREATE TABLE `kb3_comments` (
  `id` int(11) NOT NULL auto_increment,
  `kll_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `name` tinytext NOT NULL,
  `posttime` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;