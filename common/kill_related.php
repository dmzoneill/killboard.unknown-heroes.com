<?php
require_once("class.page.php");
require_once("class.corp.php");
require_once("class.alliance.php");
require_once("class.killlist.php");
require_once("class.killlisttable.php");
require_once("class.killsummarytable.php");
require_once("class.box.php");

if (!$kll_id = intval($_GET['kll_id']))
{
    echo 'No valid kill id specified';
    exit;
}

$page = new Page("Related kills & losses");

// this is a fast query to get the system and timestamp
$rqry = new DBQuery();
$rsql = 'SELECT kll_timestamp, kll_system_id from kb3_kills where kll_id = '.$kll_id;
$rqry->execute($rsql);
$rrow = $rqry->getRow();
$system = new SolarSystem($rrow['kll_system_id']);

// now we get all kills in that system for +-12 hours
$query = 'SELECT kll.kll_timestamp AS ts FROM kb3_kills kll WHERE kll.kll_system_id='.$rrow['kll_system_id'].'
            AND kll.kll_timestamp <= date_add( \''.$rrow['kll_timestamp'].'\', INTERVAL \'12\' HOUR )
            AND kll.kll_timestamp >= date_sub( \''.$rrow['kll_timestamp'].'\', INTERVAL \'12\' HOUR )
            ORDER BY kll.kll_timestamp ASC';
$qry = new DBQuery();
$qry->execute($query);
$ts = array();
while ($row = $qry->getRow())
{
    $time = strtotime($row['ts']);
    $ts[intval(date('H', $time))][] = $row['ts'];
}

// this tricky thing looks for gaps of more than 1 hour and creates an intersection
$baseh = date('H', strtotime($rrow['kll_timestamp']));
$maxc = count($ts);
$times = array();
for ($i = 0; $i < $maxc; $i++)
{
    $h = ($baseh+$i) % 24;
    if (!isset($ts[$h]))
    {
        break;
    }
    foreach ($ts[$h] as $timestamp)
    {
        $times[] = $timestamp;
    }
}
for ($i = 0; $i < $maxc; $i++)
{
    $h = ($baseh-$i) % 24;
    if ($h < 0)
    {
        $h += 24;
    }
    if (!isset($ts[$h]))
    {
        break;
    }
    foreach ($ts[$h] as $timestamp)
    {
        $times[] = $timestamp;
    }
}
unset($ts);
asort($times);

// we got 2 resulting timestamps
$firstts = array_shift($times);
$lastts = array_pop($times);

$kslist = new KillList();
$kslist->addSystem($system);
$kslist->setStartDate($firstts);
$kslist->setEndDate($lastts);
if (CORP_ID)
    $kslist->addInvolvedCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $kslist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));

$lslist = new KillList();
$lslist->addSystem($system);
$lslist->setStartDate($firstts);
$lslist->setEndDate($lastts);
if (CORP_ID)
    $lslist->addVictimCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $lslist->addVictimAlliance(new Alliance(ALLIANCE_ID));

$summarytable = new KillSummaryTable($kslist, $lslist);
$summarytable->setBreak(6);
$html .= $summarytable->generate();

$klist = new KillList();
$klist->setOrdered(true);
$klist->addSystem($system);
$klist->setStartDate($firstts);
$klist->setEndDate($lastts);
if (CORP_ID)
    $klist->addInvolvedCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $klist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));

$llist = new KillList();
$llist->setOrdered(true);
$llist->addSystem($system);
$llist->setStartDate($firstts);
$llist->setEndDate($lastts);
if (CORP_ID)
    $llist->addVictimCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $llist->addVictimAlliance(new Alliance(ALLIANCE_ID));

if ($_GET['scl_id'])
{
    $klist->addVictimShipClass(new ShipClass($_GET['scl_id']));
    $llist->addVictimShipClass(new ShipClass($_GET['scl_id']));
}

function handle_involved($kill, $side)
{
    global $pilots;
    $qry = new DBQuery();
    $sql = "select ind_plt_id, ind_crp_id, ind_all_id, ind_sec_status,
            ind_shp_id, ind_wep_id
            from kb3_inv_detail
            where ind_kll_id = ".$kill->getID()."
            order by ind_order";

    $qry->execute($sql);
    while ($row = $qry->getRow())
    {
        $ship = new Ship($row['ind_shp_id']);
        $shipc = $ship->getClass();

        // dont set pods as ships for pilots we already have
        if (isset($pilots[$side][$row['ind_plt_id']]))
        {
            if ($shipc->getID() == 18 || $shipc->getID() == 2)
            {
                continue;
            }
        }
        $weapon = new Item($row['ind_wep_id']);
        $pilot = new Pilot($row['ind_plt_id']);
        $corp = new Corporation($row['ind_crp_id']);
        $alliance = new Alliance($row['ind_all_id']);

        $pilots[$side][$row['ind_plt_id']] = array('name' => $pilot->getName(), 'sid' => $ship->getID(), 'spic' => $ship->getImage(32), 'aid' => $row['ind_all_id'],
                                                  'corp' => $corp->getName(), 'alliance' => $alliance->getName(), 'scl' => $shipc->getPoints(),
                                                  'ship' => $ship->getName(), 'weapon' => $weapon->getName(), 'cid' => $row['ind_crp_id']);
    }
}

function handle_destroyed($kill, $side)
{
    global $destroyed, $pilots;

    $destroyed[$kill->getID()] = $kill->getVictimID();

    $ship = new Ship();
    $ship->lookup($kill->getVictimShipName());
    $shipc = $ship->getClass();

    // mark the pilot as podded
    if ($shipc->getID() == 18 || $shipc->getID() == 2)
    {
        global $pods;
        $pods[$kill->getID()] = $kill->getVictimID();

        // return when we've added him already
        if (isset($pilots[$side][$kill->getVictimId()]))
        {
            return;
        }
    }

    $pilots[$side][$kill->getVictimId()] = array('name' => $kill->getVictimName(), 'spic' => $ship->getImage(32), 'scl' => $shipc->getPoints(),
                                              'corp' => $kill->getVictimCorpName(), 'alliance' => $kill->getVictimAllianceName(), 'aid' => $kill->getVictimAllianceID(),
                                              'ship' => $kill->getVictimShipname(), 'sid' => $ship->getID(), 'cid' => $kill->getVictimCorpID());
}

$destroyed = $pods = array();
$pilots = array('a' => array(), 'e' => array());
$kslist->rewind();
while ($kill = $kslist->getKill())
{
    handle_involved($kill, 'a');
    handle_destroyed($kill, 'e');
}
$lslist->rewind();
while ($kill = $lslist->getKill())
{
    handle_involved($kill, 'e');
    handle_destroyed($kill, 'a');
}
function cmp_func($a, $b)
{
    if ($a['scl'] > $b['scl'])
    {
        return -1;
    }
    // sort after points, shipname, pilotname
    elseif ($a['scl'] == $b['scl'])
    {
        if ($a['ship'] == $b['ship'])
        {
            if ($a['name'] > $b['name'])
            {
                return 1;
            }
            return -1;
        }
        elseif ($a['ship'] > $b['ship'])
        {
            return 1;
        }
        return -1;
    }
    return 1;
}

function is_destroyed($pilot)
{
    global $destroyed;

    if ($result = array_search((string)$pilot, $destroyed))
    {
        global $smarty;

        $smarty->assign('kll_id', $result);
        return true;
    }
    return false;
}

function podded($pilot)
{
    global $pods;

    if ($result = array_search((string)$pilot, $pods))
    {
        global $smarty;

        $smarty->assign('pod_kll_id', $result);
        return true;
    }
    return false;
}

// sort arrays, ships with high points first
uasort($pilots['a'], 'cmp_func');
uasort($pilots['e'], 'cmp_func');
$smarty->assign_by_ref('pilots_a', $pilots['a']);
$smarty->assign_by_ref('pilots_e', $pilots['e']);

$pod = new Ship(6);
$smarty->assign('podpic', $pod->getImage(32));
$smarty->assign('friendlycnt', count($pilots['a']));
$smarty->assign('hostilecnt', count($pilots['e']));
$smarty->assign('system', $system->getName());
$smarty->assign('firstts', $firstts);
$smarty->assign('lastts', $lastts);

$html .= $smarty->fetch(get_tpl('battle_overview'));

$html .= "<div class=kb-kills-header>Related kills</div>";

$ktable = new KillListTable($klist);
$html .= $ktable->generate();

$html .= "<div class=kb-losses-header>Related losses</div>";

$ltable = new KillListTable($llist);
$html .= $ltable->generate();

$menubox = new Box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption", "View");
$menubox->addOption("link", "Kills & losses", "?a=kill_related&kll_id=".$_GET['kll_id']);
$page->addContext($menubox->generate());

$page->setContent($html);
$page->generate();
?>