<?php
require_once("class.page.php");
require_once("class.killsummarytable.php");
require_once("class.box.php");
require_once("class.corp.php");
require_once("class.alliance.php");
require_once("class.killlist.php");
require_once("class.killlisttable.php");
require_once("class.contract.php");
require_once("class.graph.php");
require_once("class.toplist.php");

$week = date("W");
$year = date("Y");

$page = new Page("Week " . $week);

$kslist = new KillList();
if (CORP_ID)
    $kslist->addInvolvedCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $kslist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));

$kslist->setWeek($week);
$kslist->setYear($year);

$llist = new KillList();
if (CORP_ID)
    $llist->addVictimCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $llist->addVictimAlliance(new Alliance(ALLIANCE_ID));

$llist->setWeek($week);
$llist->setYear($year);

$summarytable = new KillSummaryTable($kslist, $llist);
$summarytable->setBreak(6);
$html .= $summarytable->generate();

if ($week == 1)
{
    $pyear = date("Y") - 1;
    $pweek = 52;
}
else
{
    $pyear = date("Y");
    $pweek = $week - 1;
}

if ($page->killboard_->hasCampaigns(true))
{
    $html .= "<div class=kb-campaigns-header>Active campaigns</div>";
    $list = new ContractList();
    $list->setActive("yes");
    $list->setCampaigns(true);
    $table = new ContractListTable($list);
    $html .= $table->generate();
}

if ($page->killboard_->hasContracts(true))
{
    $html .= "<div class=kb-campaigns-header>Active contracts</div>";
    $list = new ContractList();
    $list->setActive("yes");
    $list->setCampaigns(false);
    $table = new ContractListTable($list);
    $html .= $table->generate();
}

// bad hax0ring, we really need mod callback stuff
if (strpos($config->getConfig('mods_active'), 'rss_feed') !== false)
{
    $html .= "<div class=kb-kills-header><a href=\"?a=rss\"><img src=\"mods/rss_feed/rss_icon.png\" alt=\"RSS-Feed\" border=\"0\"></a>&nbsp;20 most recent kills</div>";
}
else
{
    $html .= "<div class=kb-kills-header>20 most recent kills</div>";
}

$klist = new KillList();
$klist->setOrdered(true);
if (CORP_ID)
    $klist->addInvolvedCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $klist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));

// boards with low killcount could not display 20 kills with those limits
//$klist->setStartWeek($week - 1);
//$klist->setYear($year);
$klist->setLimit(20);

if ($_GET['scl_id'])
    $klist->addVictimShipClass(new ShipClass($_GET['scl_id']));
else
    $klist->setPodsNoobShips(false);

$table = new KillListTable($klist);
$table->setLimit(20);
$html .= $table->generate();

$page->setContent($html);
$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Navigation");
$menubox->addOption("link","Previous week", "?a=kills&w=" . $pweek . "&y=" . $pyear);
$page->addContext($menubox->generate());

$tklist = new TopKillsList();
$tklist->setWeek($week);
$tklist->setYear($year);
if (CORP_ID)
    $tklist->addInvolvedCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $tklist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));

$tklist->generate();
$tkbox = new AwardBox($tklist, "Top killers", "kills in week " . $week, "kills", "eagle");
$page->addContext($tkbox->generate());

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
    $tkbox = new AwardBox($tklist, "Top scorers", "points in week " . $week, "points", "redcross");
    $page->addContext($tkbox->generate());
}

$page->generate();
?>