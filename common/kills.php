<?php
require_once("class.page.php");
require_once("class.box.php");
require_once("class.corp.php");
require_once("class.alliance.php");
require_once("class.killlist.php");
require_once("class.ship.php");
require_once("class.killlisttable.php");
require_once("class.killsummarytable.php");
require_once("class.toplist.php");

$week = $_GET['w'];
$year = $_GET['y'];

if ($week == "")
    $week = date("W");

if ($year == "")
    $year = date("Y");

if ($week == 52)
{
    $nweek = 1;
    $nyear = $year + 1;
    $pyear = $year - 1;
}
else
{
    $nweek = $week + 1;
    $nyear = $year;
}
if ($week == 1)
{
    $pweek = 52;
    $pyear = $year - 1;
}
else
{
    $pweek = $week - 1;
    $pyear = $year;
}

$page = new Page("Kills - Week ".$week);

$kslist = new KillList();
$kslist->setWeek($week);
$kslist->setYear($year);
if (CORP_ID)
    $kslist->addInvolvedCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $kslist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));

$llist = new KillList();
$llist->setWeek($week);
$llist->setYear($year);
if (CORP_ID)
    $llist->addVictimCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $llist->addVictimAlliance(new Alliance(ALLIANCE_ID));

$summarytable = new KillSummaryTable($kslist, $llist);
$summarytable->setBreak(6);
$html .= $summarytable->generate();

$klist = new KillList();
$klist->setOrdered(true);
$klist->setWeek($week);
$klist->setYear($year);
if (CORP_ID)
    $klist->addInvolvedCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $klist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));
if ($_GET['scl_id'])
    $klist->addVictimShipClass(new ShipClass($_GET['scl_id']));
else
    $klist->setPodsNoobShips(false);

$table = new KillListTable($klist);
$html .= $table->generate();

$page->setContent($html);
$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption", "Navigation");
$menubox->addOption("link", "Previous week ", "?a=kills&w=".$pweek."&y=".$pyear);
if ($week != date("W"))
{
    $menubox->addOption('link', "Next week", "?a=kills&w=".$nweek."&y=".$nyear);
}
$page->addContext($menubox->generate());

$tklist = new TopKillsList();
$tklist->setWeek($week);
$tklist->setYear($year);
if (CORP_ID)
    $tklist->addInvolvedCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $tklist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top killers", "kills in week ".$week, "kills", "eagle");
$page->addContext($tkbox->generate());

$killboard = $page->killboard_;
$config = $killboard->getConfig();
if ($config->getKillPoints())
{
    $tklist = new TopScoreList();
    $tklist->setWeek($week);
    $tklist->setYear($year);
    if (CORP_ID)
        $tklist->addInvolvedCorp(new Corporation(CORP_ID));
    if (ALLIANCE_ID)
        $tklist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));

    $tklist->generate();
    $tkbox = new AwardBox($tklist, "Top scorers", "points in week ".$week, "points", "redcross");
    $page->addContext($tkbox->generate());
}

$page->generate();
?>