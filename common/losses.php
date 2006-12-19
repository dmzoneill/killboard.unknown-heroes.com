<?php
require_once("class.page.php");
require_once("class.box.php");
require_once("class.corp.php");
require_once("class.alliance.php");
require_once("class.killlist.php");
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
}
else
{
    $nweek = $week + 1;
    $nyear = $year;
}
if ($week == "1")
{
    $pweek = 52;
    $pyear = $year - 1;
}
else
{
    $pweek = $week - 1;
    $pyear = $year;
}

$page = new Page("Losses - Week ".$week);

$klist = new KillList();
$klist->setWeek($week);
$klist->setYear($year);
if (CORP_ID)
    $klist->addInvolvedCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $klist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));

$lslist = new KillList();
$lslist->setWeek($week);
$lslist->setYear($year);
if (CORP_ID)
    $lslist->addVictimCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $lslist->addVictimAlliance(new Alliance(ALLIANCE_ID));

$summarytable = new KillSummaryTable($klist, $lslist);
$summarytable->setBreak(6);
$html .= $summarytable->generate();
// $html .= "<table width=\"99%\" align=center><tr><td class=weeknav align=left>";
// if ( $week != date( "W" ) )
// $html .= "[<a href=\"?a=losses&w=".$nweek."&y=".$nyear."\"><<</a>]";
// $html .= "</td><td class=weeknav align=right>[<a href=\"?a=losses&w=".$pweek."&y=".$pyear."\">>></a>]</td></tr></table>";
$llist = new KillList();
$llist->setOrdered(true);
$llist->setWeek($week);
$llist->setYear($year);
if (CORP_ID)
    $llist->addVictimCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $llist->addVictimAlliance(new Alliance(ALLIANCE_ID));
if ($_GET['scl_id'])
    $llist->addVictimShipClass(new ShipClass($_GET['scl_id']));
else
    $llist->setPodsNoobShips(false);

$table = new KillListTable($llist);
$html .= $table->generate();

$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption", "Navigation");
$menubox->addOption("link", "Previous week", "?a=losses&w=".$pweek."&y=".$pyear);
if ($week != date("W"))
{
    $menubox->addOption("link", "Next week", "?a=losses&w=".$nweek."&y=".$nyear);
}
$page->addContext($menubox->generate());

$tllist = new TopLossesList();
$tllist->setWeek($week);
$tllist->setYear($year);
if (CORP_ID)
    $tllist->addVictimCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $tllist->addVictimAlliance(new Alliance(ALLIANCE_ID));

$tllist->generate();
$tlbox = new AwardBox($tllist, "Top losers", "losses in week ".$week, "losses", "moon");
$page->addContext($tlbox->generate());

$page->setContent($html);
$page->generate();
?>