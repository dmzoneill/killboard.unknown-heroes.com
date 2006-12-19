CREATE TABLE `kb3_items_destroyed` (
  `itd_kll_id` int(11) NOT NULL default '0',
  `itd_itm_id` int(11) NOT NULL default '0',
  `itd_quantity` int(11) NOT NULL default '1',
  `itd_itl_id` smallint(11) NOT NULL default '0',
  KEY `itd_kll_id` (`itd_kll_id`)
) TYPE=MyISAM;