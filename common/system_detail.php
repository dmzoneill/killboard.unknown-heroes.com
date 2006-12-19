<?php
require_once("db.php");
require_once("class.page.php");
require_once('class.system.php');
require_once('class.killlist.php');
require_once('class.killlisttable.php');
require_once('class.killsummarytable.php');
require_once('class.box.php');

$sys_id = intval($_GET['sys_id']);

if (!$sys_id)
{
    echo 'no valid id supplied<br/>';
    exit;
}
$system = new SolarSystem($sys_id);

$page = new Page('System details - '.$system->getName());

$html .= "<table border=\"0\" class=\"kb-table\"><tr class=\"kb-table-header\"><td colspan=\"3\">Graphical Overview</td></tr><tr>";
$html .= "<td><img src=\"?a=mapview&sys_id=".$sys_id."&mode=map&size=250\" border=\"0\" width=\"250\" height=\"250\"></td>";
$html .= "<td><img src=\"?a=mapview&sys_id=".$sys_id."&mode=region&size=250\" border=\"0\" width=\"250\" height=\"250\"></td>";
$html .= "<td><img src=\"?a=mapview&sys_id=".$sys_id."&mode=cons&size=250\" border=\"0\" width=\"250\" height=\"250\"></td>";
$html .= "</tr></table><br/>";

$kslist = new KillList();
if (CORP_ID)
    $kslist->addInvolvedCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $kslist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));
$kslist->addSystem($system);

$lslist = new KillList();
if (CORP_ID)
    $lslist->addVictimCorp(new Corporation(CORP_ID));
if (ALLIANCE_ID)
    $lslist->addVictimAlliance(new Alliance(ALLIANCE_ID));
$lslist->addSystem($system);

$summarytable = new KillSummaryTable($kslist, $lslist);
$summarytable->setBreak(6);
$html .= $summarytable->generate();

$klist = new KillList();
$klist->setOrdered(true);
if ($_GET['view'] == 'losses')
{
    if (CORP_ID)
        $klist->addVictimCorp(new Corporation(CORP_ID));
    if (ALLIANCE_ID)
        $klist->addVictimAlliance(new Alliance(ALLIANCE_ID));
}
else
{
    if (CORP_ID)
        $klist->addInvolvedCorp(new Corporation(CORP_ID));
    if (ALLIANCE_ID)
        $klist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));
}
$klist->addSystem($system);
if ($_GET['scl_id'])
    $klist->addVictimShipClass(new ShipClass($_GET['scl_id']));
else
    $klist->setPodsNoobShips(false);

if ($_GET['view'] == 'recent' || !isset($_GET['view']))
{
    $html .= "<div class=kb-kills-header>20 most recent kills</div>";
    $klist->setLimit(20);
}
else
{
    if ($_GET['view'] == 'losses')
    {
        $html .= "<div class=kb-kills-header>All losses</div>";
    }
    else
    {
        $html .= "<div class=kb-kills-header>All kills</div>";
    }
    $pagesplitter = new PageSplitter($klist->getCount(), 20);
    $klist->setPageSplitter($pagesplitter);
}

$table = new KillListTable($klist);
$html .= $table->generate();
if (is_object($pagesplitter))
{
    $html .= $pagesplitter->generate();
}

$page->setContent($html);
$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Navigation");
$menubox->addOption("link","All kills", "?a=system_detail&amp;sys_id=".$sys_id."&amp;view=kills");
$menubox->addOption("link","All losses", "?a=system_detail&amp;sys_id=".$sys_id."&amp;view=losses");
$menubox->addOption("link","Recent Activity", "?a=system_detail&amp;sys_id=".$sys_id."&amp;view=recent");
$page->addContext($menubox->generate());

$page->generate();
?>