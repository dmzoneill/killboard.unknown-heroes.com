<?php
require_once("db.php");
function check_commenttable()
{
    $qry = new DBQuery();
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
    $qry->execute($query);
}

function check_commenttablerow()
{
    $qry = new DBQuery();
    $query = 'select posttime from kb3_comments limit 1';
    $result = mysql_query($query);
    if ($result)
    {
        $query = 'ALTER TABLE `kb3_comments` CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT';
        $qry->execute($query);
        return;
    }
    $query = 'ALTER TABLE `kb3_comments` ADD `posttime` TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' NOT NULL';
    $qry->execute($query);
}

function check_shipvaltable()
{
    $qry = new DBQuery();
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
    $qry->execute($query);
    $qry->execute('UPDATE kb3_ships set shp_class = 8 WHERE shp_id=257 limit 1');
    $qry->execute('UPDATE kb3_ships set shp_class = 8 WHERE shp_id=252 limit 1');
    $qry->execute('UPDATE kb3_ships set shp_class = 4 WHERE shp_id=266 limit 1');
    $qry->execute('UPDATE kb3_ships set shp_class = 4 WHERE shp_id=275 limit 1');
    $qry->execute('UPDATE kb3_ships set shp_class = 4 WHERE shp_id=272 limit 1');
    $qry->execute('UPDATE kb3_ships set shp_class = 4 WHERE shp_id=246 limit 1');
    $qry->execute('UPDATE kb3_ships set shp_class = 4 WHERE shp_id=249 limit 1');
    $qry->execute('UPDATE kb3_ships set shp_techlevel = 2 where shp_class = 22');
}

function check_invdetail()
{
    $qry = new DBQuery();
    $query = 'select ind_sec_status from kb3_inv_detail limit 1';
    $qry->execute($query);
    $len = mysql_field_len($qry->resid_,0);
    if ($len == 4)
    {
        $query = 'ALTER TABLE `kb3_inv_detail` CHANGE `ind_sec_status` `ind_sec_status` VARCHAR(5)';
        $qry->execute($query);
    }
}

function check_pilots()
{
    $qry = new DBQuery();
    $query = 'select plt_name from kb3_pilots limit 1';
    $qry->execute($query);
    $len = mysql_field_len($qry->resid_,0);
    if ($len == 32)
    {
        $query = 'ALTER TABLE `kb3_pilots` CHANGE `plt_name` `plt_name` VARCHAR(64) NOT NULL';
        $qry->execute($query);
    }
}

function check_contracts()
{
    $qry = new DBQuery();
    $query = 'select ctd_sys_id from kb3_contract_details limit 1';
    $result = mysql_query($query);
    if ($result)
    {
        return;
    }
    $qry->execute('ALTER TABLE `kb3_contract_details` ADD `ctd_sys_id` INT(11) NOT NULL DEFAULT \'0\'');

    $qry->execute('SHOW columns from `kb3_contract_details` like \'ctd_ctr_id\'');
    $arr = $qry->getRow();
    if ($arr['Key'] == 'PRI')
    {
        return;
    }
    $qry->execute('ALTER TABLE `kb3_contract_details` ADD INDEX (`ctd_ctr_id`) ');
}
function check_index()
{
    $qry = new DBQuery();
    $qry->execute('SHOW columns from kb3_item_types like \'itt_id\'');
    $arr = $qry->getRow();
    if ($arr['Key'] == 'PRI')
    {
        return;
    }
    $qry->execute('ALTER TABLE `kb3_item_types` ADD PRIMARY KEY ( `itt_id` ) ');
}
function check_tblstrct1()
{
    $qry = new DBQuery();
    $query = 'select shp_description from kb3_ships limit 1';
    $result = mysql_query($query);
    if (!$result)
    {
        return;
    }
    $query = 'ALTER TABLE `kb3_ships` DROP `shp_description`';
    $qry->execute($query);
}
function check_tblstrct2()
{
    $qry = new DBQuery();
    $query = 'select itm_description from kb3_items limit 1';
    $result = mysql_query($query);
    if (!$result)
    {
        return;
    }
    $query = 'ALTER TABLE `kb3_items` DROP `itm_description`';
    $qry->execute($query);
}
function check_tblstrct3()
{
    $qry = new DBQuery();
    $query = 'select Value from kb3_items limit 1';
    $result = mysql_query($query);
    if ($result)
    {
        $query = 'ALTER TABLE `kb3_items` CHANGE `Value` `itm_value` INT( 11 ) NOT NULL DEFAULT \'0\'';
        $qry->execute($query);
    }
}
function check_tblstrct4()
{
    $qry = new DBQuery();
    $query = 'select itm_value from kb3_items limit 1';
    $result = mysql_query($query);
    if ($result)
    {
        return;
    }
    $query = 'ALTER TABLE `kb3_items` ADD `itm_value` INT( 11 ) NOT NULL DEFAULT \'0\'';
    $qry->execute($query);
    $qry->execute('ALTER TABLE `kb3_items` CHANGE `itm_externalid` `itm_externalid` INT( 11 ) NOT NULL DEFAULT \'0\'');
}

function check_tblstrct5()
{
    $qry = new DBQuery();
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
        $qry->execute('drop table kb3_standings');
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
    $qry->execute($query);
}

function chk_kb3_items()
{
    $qry = new DBQuery();
    $query = 'select count(*) from kb3_item_types where itt_id = 787';
    $result = mysql_query($query);
    $result = mysql_fetch_array($result);
    if ($result['cnt'] == 1)
    {
        return;
    }
    $queries = "
        INSERT IGNORE INTO `kb3_item_types` VALUES (737, 'Gas Cloud Harvester',1);
        INSERT IGNORE INTO `kb3_item_types` VALUES (762, 'Inertia Stabilizer',3);
        INSERT IGNORE INTO `kb3_item_types` VALUES (763, 'Nanofiber Internal Structure',3);
        INSERT IGNORE INTO `kb3_item_types` VALUES (764, 'Overdrive Injector System',3);
        INSERT IGNORE INTO `kb3_item_types` VALUES (765, 'Expanded Cargohold',3);
        INSERT IGNORE INTO `kb3_item_types` VALUES (766, 'Power Diagnostic System',3);
        INSERT IGNORE INTO `kb3_item_types` VALUES (767, 'Capacitor Power Relay',3);
        INSERT IGNORE INTO `kb3_item_types` VALUES (768, 'Capacitor Flux Coil',3);
        INSERT IGNORE INTO `kb3_item_types` VALUES (769, 'Reactor Control Unit',3);
        INSERT IGNORE INTO `kb3_item_types` VALUES (770, 'Shield Flux Coil',3);
        INSERT IGNORE INTO `kb3_item_types` VALUES (771, 'Missile Launcher Heavy Assault',1);
        INSERT IGNORE INTO `kb3_item_types` VALUES (738, 'Cyber Armor', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (740, 'Cyber Electronics', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (741, 'Cyber Engineering', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (742, 'Cyber Gunnery', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (743, 'Cyber Industry', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (744, 'Cyber Leadership', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (745, 'Cyber Learning', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (746, 'Cyber Missile', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (747, 'Cyber Navigation', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (748, 'Cyber Science', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (749, 'Cyber Shields', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (772, 'Assault Missile', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (773, 'Rig Armor', 5);
        INSERT IGNORE INTO `kb3_item_types` VALUES (774, 'Rig Shield', 5);
        INSERT IGNORE INTO `kb3_item_types` VALUES (775, 'Rig Energy Weapon', 5);
        INSERT IGNORE INTO `kb3_item_types` VALUES (776, 'Rig Hybrid Weapon', 5);
        INSERT IGNORE INTO `kb3_item_types` VALUES (777, 'Rig Projectile Weapon', 5);
        INSERT IGNORE INTO `kb3_item_types` VALUES (778, 'Rig Drones', 5);
        INSERT IGNORE INTO `kb3_item_types` VALUES (779, 'Rig Launcher', 5);
        INSERT IGNORE INTO `kb3_item_types` VALUES (780, 'Rig Electronics', 5);
        INSERT IGNORE INTO `kb3_item_types` VALUES (781, 'Rig Energy Grid', 5);
        INSERT IGNORE INTO `kb3_item_types` VALUES (782, 'Rig Astronautic', 5);
        INSERT IGNORE INTO `kb3_item_types` VALUES (783, 'Cyber X Specials', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (785, 'Script', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (786, 'Rig Electronics Superiority', 5);
        INSERT IGNORE INTO `kb3_item_types` VALUES (722, 'Advanced Hybrid Ammo Blueprint', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (723, 'Tractor Beam Blueprint', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (724, 'Implant Blueprints', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (725, 'Advanced Projectile Ammo Blueprint', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (726, 'Advanced Frequency Crystal Blueprint', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (727, 'Mining Crystal Blueprint', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (728, 'Decryptors - Amarr', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (729, 'Decryptors - Minmatar', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (730, 'Decryptors - Gallente', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (731, 'Decryptors - Caldari', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (732, 'Decryptors - Sleepers', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (733, 'Decryptors - Yan Jung', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (734, 'Decryptors - Takmahl', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (735, 'Decryptors - Talocan', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (754, 'Salvaged Materials', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (784, 'Large Collidable Ship', 0);
        INSERT IGNORE INTO `kb3_item_types` VALUES (787, 'Rig Blueprint', 0);
        ";
    $query = explode("\n", $queries);
    foreach ($query as $querystring)
    {
        if ($string = trim(str_replace(');', ')', $querystring)))
        {
            $qry->execute($string);
        }
    }
}
function chk_kb3_items2()
{
    $qry = new DBQuery();
    $query = 'select itm_externalid from kb3_items where itm_name = \'Repair Drone\'';
    $result = mysql_query($query);
    $result = mysql_fetch_array($result);
    if ($result['itm_externalid'] == '9871')
    {
        return;
    }
    $queries = "
            update kb3_items set itm_externalid='10246' where itm_name='Mining Drone I';
            update kb3_items set itm_externalid='10248' where itm_name='Mining Drone - Improved UNUSED';
            update kb3_items set itm_externalid='10250' where itm_name='Mining Drone II';
            update kb3_items set itm_externalid='1187' where itm_name='Mining Drone - Elite';
            update kb3_items set itm_externalid='1201' where itm_name='Proximity Drone';
            update kb3_items set itm_externalid='1202' where itm_name='Wasp I';
            update kb3_items set itm_externalid='15508' where itm_name='Civilian Mining Drone';
            update kb3_items set itm_externalid='15510' where itm_name='Vespa I';
            update kb3_items set itm_externalid='16206' where itm_name='Valkyrie I';
            update kb3_items set itm_externalid='17565' where itm_name='Hellhound I';
            update kb3_items set itm_externalid='21050' where itm_name='Unanchoring Drone';
            update kb3_items set itm_externalid='21638' where itm_name='Survey Drone';
            update kb3_items set itm_externalid='21640' where itm_name='Vespa II';
            update kb3_items set itm_externalid='2173' where itm_name='Valkyrie II';
            update kb3_items set itm_externalid='2175' where itm_name='Infiltrator I';
            update kb3_items set itm_externalid='2183' where itm_name='Infiltrator II';
            update kb3_items set itm_externalid='2185' where itm_name='Hammerhead I';
            update kb3_items set itm_externalid='2193' where itm_name='Hammerhead II';
            update kb3_items set itm_externalid='2195' where itm_name='Praetor I';
            update kb3_items set itm_externalid='2203' where itm_name='Praetor II';
            update kb3_items set itm_externalid='2205' where itm_name='Acolyte I';
            update kb3_items set itm_externalid='2205' where itm_name='Acolyte II';
            update kb3_items set itm_externalid='22572' where itm_name='Praetor EV-900';
            update kb3_items set itm_externalid='22574' where itm_name='Warp Scrambling Drone';
            update kb3_items set itm_externalid='22713' where itm_name='10mn webscramblifying Drone';
            update kb3_items set itm_externalid='22765' where itm_name='Heavy Shield Maintenance Bot I';
            update kb3_items set itm_externalid='22780' where itm_name='Fighter Uno';
            update kb3_items set itm_externalid='23055' where itm_name='Templar';
            update kb3_items set itm_externalid='23057' where itm_name='Dragonfly';
            update kb3_items set itm_externalid='23059' where itm_name='Firbolg';
            update kb3_items set itm_externalid='23061' where itm_name='Einherji';
            update kb3_items set itm_externalid='23473' where itm_name='Wasp EC-900';
            update kb3_items set itm_externalid='23506' where itm_name='Ogre SD-900';
            update kb3_items set itm_externalid='23510' where itm_name='Praetor TD-900';
            update kb3_items set itm_externalid='23512' where itm_name='Berserker TP-900';
            update kb3_items set itm_externalid='23523' where itm_name='Heavy Armor Maintenance Bot I';
            update kb3_items set itm_externalid='23525' where itm_name='Curator I';
            update kb3_items set itm_externalid='23559' where itm_name='Berserker SW-900';
            update kb3_items set itm_externalid='23559' where itm_name='Warden I';
            update kb3_items set itm_externalid='23561' where itm_name='Garde I';
            update kb3_items set itm_externalid='23563' where itm_name='Bouncer I';
            update kb3_items set itm_externalid='23659' where itm_name='Acolyte EV-300';
            update kb3_items set itm_externalid='23702' where itm_name='Infiltrator EV-600';
            update kb3_items set itm_externalid='23705' where itm_name='Vespa EC-600';
            update kb3_items set itm_externalid='23707' where itm_name='Hornet EC-300';
            update kb3_items set itm_externalid='23709' where itm_name='Medium Armor Maintenance Bot I';
            update kb3_items set itm_externalid='23711' where itm_name='Light Armor Maintenance Bot I';
            update kb3_items set itm_externalid='23713' where itm_name='Hammerhead SD-600';
            update kb3_items set itm_externalid='23715' where itm_name='Hobgoblin SD-300';
            update kb3_items set itm_externalid='23717' where itm_name='Medium Shield Maintenance Bot I';
            update kb3_items set itm_externalid='23719' where itm_name='Light Shield Maintenance Bot I';
            update kb3_items set itm_externalid='23721' where itm_name='Valkyrie TP-600';
            update kb3_items set itm_externalid='23723' where itm_name='Warrior TP-300';
            update kb3_items set itm_externalid='23725' where itm_name='Infiltrator TD-600';
            update kb3_items set itm_externalid='23727' where itm_name='Acolyte TD-300';
            update kb3_items set itm_externalid='23729' where itm_name='Valkyrie SW-600';
            update kb3_items set itm_externalid='23731' where itm_name='Warrior SW-300';
            update kb3_items set itm_externalid='23759' where itm_name='FA-14 Templar';
            update kb3_items set itm_externalid='2436' where itm_name='Wasp II';
            update kb3_items set itm_externalid='2444' where itm_name='Ogre I';
            update kb3_items set itm_externalid='2446' where itm_name='Ogre II';
            update kb3_items set itm_externalid='2454' where itm_name='Hobgoblin I';
            update kb3_items set itm_externalid='2456' where itm_name='Hobgoblin II';
            update kb3_items set itm_externalid='24618' where itm_name='horrible tracking drone';
            update kb3_items set itm_externalid='2464' where itm_name='Hornet I';
            update kb3_items set itm_externalid='2466' where itm_name='Hornet II';
            update kb3_items set itm_externalid='2476' where itm_name='Berserker I';
            update kb3_items set itm_externalid='2478' where itm_name='Berserker II';
            update kb3_items set itm_externalid='2486' where itm_name='Warrior I';
            update kb3_items set itm_externalid='2488' where itm_name='Warrior II';
            update kb3_items set itm_externalid='3218' where itm_name='Harvester Mining Drone';
            update kb3_items set itm_externalid='3549' where itm_name='Tutorial Attack Drone';
            update kb3_items set itm_externalid='9871' where itm_name='Repair Drone';
        ";
    $query = explode("\n", $queries);
    foreach ($query as $querystring)
    {
        if ($string = trim(str_replace(');', ')', $querystring)))
        {
            $qry->execute($string);
        }
    }
}
function check_tblstrct6()
{
    $qry = new DBQuery();
    $query = 'select all_img from kb3_alliances limit 1';
    $result = mysql_query($query);
    if (!$result)
    {
        return;
    }
    $query = 'ALTER TABLE `kb3_alliances` DROP `all_img`';
    $qry->execute($query);
}
?>