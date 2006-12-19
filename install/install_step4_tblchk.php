<?php
function check_commenttable()
{
    $query = 'select count(*) from kb3_comments';
    $result = mysql_query($query);
    if ($result)
    {
    	check_commenttablerow();
        return;
    }
    $query = 'CREATE TABLE `kb3_comments` (
`ID` INT NOT NULL AUTO_INCREMENT ,
`kll_id` INT NOT NULL ,
`comment` TEXT NOT NULL ,
`name` TINYTEXT NOT NULL ,
`posttime` TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' NOT NULL,
PRIMARY KEY ( `ID` )
) TYPE = MYISAM';
    mysql_query($query);
}

function check_commenttablerow()
{
    $query = 'select posttime from kb3_comments limit 1';
    $result = mysql_query($query);
    if ($result)
    {
        $query = 'ALTER TABLE `kb3_comments` CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT';
        mysql_query($query);
        return;
    }
    $query = 'ALTER TABLE `kb3_comments` ADD `posttime` TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' NOT NULL';
    mysql_query($query);
}

function check_shipvaltable()
{
    $query = 'select count(*) from kb3_ships_values';
    $result = mysql_query($query);
    if ($result)
    {
        return;
    }
    $query = 'CREATE TABLE `kb3_ships_values` (
`shp_id` INT( 11 ) NOT NULL ,
`shp_value` BIGINT( 4 ) NOT NULL ,
PRIMARY KEY ( `shp_id` )
) TYPE = MYISAM ;';
    mysql_query($query);
    mysql_query('UPDATE kb3_ships set shp_class = 8 WHERE shp_id=257 limit 1');
    mysql_query('UPDATE kb3_ships set shp_class = 8 WHERE shp_id=252 limit 1');
    mysql_query('UPDATE kb3_ships set shp_class = 4 WHERE shp_id=266 limit 1');
    mysql_query('UPDATE kb3_ships set shp_class = 4 WHERE shp_id=275 limit 1');
    mysql_query('UPDATE kb3_ships set shp_class = 4 WHERE shp_id=272 limit 1');
    mysql_query('UPDATE kb3_ships set shp_class = 4 WHERE shp_id=246 limit 1');
    mysql_query('UPDATE kb3_ships set shp_class = 4 WHERE shp_id=249 limit 1');
    mysql_query('UPDATE kb3_ships set shp_techlevel = 2 where shp_class = 22');
}

function check_invdetail()
{
    $query = 'select ind_sec_status from kb3_inv_detail limit 1';
    $result = mysql_query($query);
    $len = mysql_field_len($result, 0);
    if ($len == 4)
    {
        $query = 'ALTER TABLE `kb3_inv_detail` CHANGE `ind_sec_status` `ind_sec_status` VARCHAR(5)';
        mysql_query($query);
    }
}

function check_pilots()
{
    $query = 'select plt_name from kb3_pilots limit 1';
    $result = mysql_query($query);
    $len = mysql_field_len($result,0);
    if ($len == 32)
    {
        $query = 'ALTER TABLE `kb3_pilots` CHANGE `plt_name` `plt_name` VARCHAR(64) NOT NULL';
        mysql_query($query);
    }
}

function check_contracts()
{
    $query = 'select ctd_sys_id from kb3_contract_details limit 1';
    $result = mysql_query($query);
    if ($result)
    {
        return;
    }
    mysql_query('ALTER TABLE `kb3_contract_details` ADD `ctd_sys_id` INT(11) NOT NULL DEFAULT \'0\'');

    $result = mysql_query('SHOW columns from `kb3_contract_details` like \'ctd_ctr_id\'');
    $arr = mysql_fetch_array($result);
    if ($arr['Key'] == 'PRI')
    {
        return;
    }
    mysql_query('ALTER TABLE `kb3_contract_details` ADD INDEX (`ctd_ctr_id`) ');
}
function check_index()
{
    $result = mysql_query('SHOW columns from kb3_item_types like \'itt_id\'');
    $arr = mysql_fetch_array($result);
    if ($arr['Key'] == 'PRI')
    {
        return;
    }
    mysql_query('ALTER TABLE `kb3_item_types` ADD PRIMARY KEY ( `itt_id` ) ');
}
function check_tblstrct1()
{
    $query = 'select shp_description from kb3_ships limit 1';
    $result = mysql_query($query);
    if (!$result)
    {
        return;
    }
    $query = 'ALTER TABLE `kb3_ships` DROP `shp_description`';
    mysql_query($query);
}
function check_tblstrct2()
{
    $query = 'select itm_description from kb3_items limit 1';
    $result = mysql_query($query);
    if (!$result)
    {
        return;
    }
    $query = 'ALTER TABLE `kb3_items` DROP `itm_description`';
    mysql_query($query);
}
function check_tblstrct3()
{
    $query = 'select Value from kb3_items limit 1';
    $result = mysql_query($query);
    if ($result)
    {
        $query = 'ALTER TABLE `kb3_items` CHANGE `Value` `itm_value` INT( 11 ) NOT NULL DEFAULT \'0\'';
        mysql_query($query);
    }
}
function check_tblstrct4()
{
    $query = 'select itm_value from kb3_items limit 1';
    $result = mysql_query($query);
    if ($result)
    {
        return;
    }
    $query = 'ALTER TABLE `kb3_items` ADD `itm_value` INT( 11 ) NOT NULL DEFAULT \'0\'';
    mysql_query($query);
    mysql_query('ALTER TABLE `kb3_items` CHANGE `itm_externalid` `itm_externalid` INT( 11 ) NOT NULL DEFAULT \'0\'');
}

function check_tblstrct5()
{
    $query = 'select count(*) from kb3_standings';
    $result = mysql_query($query);
    if ($result)
    {
        $query = 'select count(*) from kb3_standings where sta_from=1 and sta_to=1 and sta_from_type=\'a\' and
                  sta_to_type=\'c\'';
        $result = mysql_query($query);
        if ($result)
        {
            return;
        }
        mysql_query('drop table kb3_standings');
    }
$query = 'CREATE TABLE `kb3_standings` (
  `sta_from` int(11) NOT NULL default \'0\',
  `sta_to` int(11) NOT NULL default \'0\',
  `sta_from_type` enum(\'a\',\'c\') NOT NULL default \'a\',
  `sta_to_type` enum(\'a\',\'c\') NOT NULL default \'a\',
  `sta_value` float NOT NULL default \'0\',
  `sta_comment` varchar(200) NOT NULL,
  KEY `sta_from` (`sta_from`)
) TYPE=MyISAM;';
    mysql_query($query);
}

check_commenttable();
check_commenttablerow();
check_shipvaltable();
check_invdetail();
check_pilots();
check_contracts();
check_index();
check_tblstrct1();
check_tblstrct2();
check_tblstrct3();
check_tblstrct4();
check_tblstrct5();
?>