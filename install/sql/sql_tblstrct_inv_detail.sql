CREATE TABLE `kb3_inv_detail` (
  `ind_kll_id` int(6) NOT NULL default '0',
  `ind_plt_id` int(6) NOT NULL default '0',
  `ind_sec_status` varchar(5) default NULL,
  `ind_all_id` int(3) NOT NULL default '0',
  `ind_crp_id` int(5) NOT NULL default '0',
  `ind_shp_id` int(3) NOT NULL default '0',
  `ind_wep_id` int(5) NOT NULL default '0',
  `ind_order` int(2) NOT NULL default '0',
  KEY `ind_shp_id` (`ind_shp_id`),
  KEY `ind_kll_id` (`ind_kll_id`),
  KEY `ind_plt_id` (`ind_plt_id`)
) TYPE=MyISAM;