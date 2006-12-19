CREATE TABLE `kb3_contract_details` (
  `ctd_ctr_id` int(11) NOT NULL default '0',
  `ctd_crp_id` int(11) NOT NULL default '0',
  `ctd_all_id` int(11) NOT NULL default '0',
  `ctd_reg_id` int(11) NOT NULL default '0',
  `ctd_sys_id` int(11) NOT NULL default '0',
  KEY `ctd_ctr_id` (`ctd_ctr_id`)
) TYPE=MyISAM;