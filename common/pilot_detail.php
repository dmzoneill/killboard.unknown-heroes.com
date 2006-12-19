<?php
require_once("class.page.php");
require_once("class.pilot.php");
require_once("class.corp.php");
require_once("class.alliance.php");
require_once("class.kill.php");
require_once("class.killlist.php");
require_once("class.killlisttable.php");
require_once("class.killsummarytable.php");
require_once("class.box.php");
require_once("class.toplist.php");

$pilot = new Pilot($_GET['plt_id']);
$corp = $pilot->getCorp();
$alliance = $corp->getAlliance();
$page = new Page("Pilot details - " . $pilot->getName());

if (!$pilot->exists())
{
    $html = "That pilot doesn't exist.";
    $page->generate($html);
    exit;
}

$klist = new KillList();
$tklist = new KillList();
$llist = new KillList();
$klist->addInvolvedPilot($pilot);
$tklist->addInvolvedPilot($pilot);
$llist->addVictimPilot($pilot);
$klist->getAllKills();
$llist->getAllKills();
$tklist->setPodsNoobShips(false);

$html .= "<table class=kb-table cellspacing=1 width=\"100%\">";

$html .= "<tr class=kb-table-row-even>";
$html .= "<td rowspan=8 width=128><img src=\"" . $pilot->getPortraitURL(128) . "\" border=\"0\" width=\"128\" heigth=\"128\"></td>";

$html .= "<td class=kb-table-cell width=160><b>Corporation:</b></td><td class=kb-table-cell><a href=\"?a=corp_detail&crp_id=" . $corp->getID() . "\">" . $corp->getName() . "</a></td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Alliance:</b></td><td class=kb-table-cell>";
if ($alliance->getName() == "Unknown" || $alliance->getName() == "None")
    $html .= "<b>" . $alliance->getName() . "</b>";
else
    $html .= "<a href=\"?a=alliance_detail&all_id=" . $alliance->getID() . "\">" . $alliance->getName() . "</a>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Kills:</b></td><td class=kl-kill>" . $klist->getCount() . "</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Real kills:</b></td><td class=kl-kill>" . $tklist->getCount() . "</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Losses:</b></td><td class=kl-loss>" . $llist->getCount() . "</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage done (ISK):</b></td><td class=kl-kill>" . $klist->getISK() . "M</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage received (ISK):</b></td><td class=kl-loss>" . $llist->getISK() . "M</td></tr>";

$html .= "</td></tr>";
$html .= "</table>";

$html .= "<br/>";

$points = $klist->getPoints();
$summary = new KillSummaryTable($klist, $llist);
$summary->setBreak(6);
if ($_GET['view'] == "ships_weapons")
{
    $summary->setFilter(false);
}
$html .= $summary->generate();

switch ($_GET['view'])
{
    case "kills":
        $html .= "<div class=kb-kills-header>All kills</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->addInvolvedPilot($pilot);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));
        $pagesplitter = new PageSplitter($list->getCount(), 30);
        $list->setPageSplitter($pagesplitter);
        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();

        break;
    case "losses":
        $html .= "<div class=kb-losses-header>All losses</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->setPodsNoobships(true);
        $list->addVictimPilot($pilot);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));
        $pagesplitter = new PageSplitter($list->getCount(), 30);
        $list->setPageSplitter($pagesplitter);

        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();
        break;
    case "ships_weapons":
        $html .= "<div class=block-header2>Ships & weapons used</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=400>";
        $shiplist = new TopShipList();
        $shiplist->addInvolvedPilot($pilot);
        $shiplisttable = new TopShipListTable($shiplist);
        $html .= $shiplisttable->generate();
        $html .= "</td><td valign=top align=right width=400>";

        $weaponlist = new TopWeaponList();
        $weaponlist->addInvolvedPilot($pilot);
        $weaponlisttable = new TopWeaponListTable($weaponlist);
        $html .= $weaponlisttable->generate();
        $html .= "</td></tr></table>";

        break;
    default:
        $html .= "<div class=kb-kills-header>10 Most recent kills</div>";
        $list = new KillList();
        $list->setOrdered(true);
        $list->setLimit(10);
        $list->setPodsNoobships(true);
        $list->addInvolvedPilot($pilot);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));

        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $html .= $table->generate();

        $html .= "<div class=kb-losses-header>10 Most recent losses</div>";
        $list = new KillList();
        $list->setOrdered(true);
        $list->setLimit(10);
        $list->setPodsNoobships(true);
        $list->addVictimPilot($pilot);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));

        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $table->setDayBreak(false);
        $html .= $table->generate();
        break;
}

$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Kills & losses");
$menubox->addOption("link","Recent activity", "?a=pilot_detail&plt_id=" . $pilot->getID() . "&view=recent");
$menubox->addOption("link","Kills", "?a=pilot_detail&plt_id=" . $pilot->getID() . "&view=kills");
$menubox->addOption("link","Losses", "?a=pilot_detail&plt_id=" . $pilot->getID() . "&view=losses");
$menubox->addOption("caption","Statistics");
$menubox->addOption("link","Ships & weapons", "?a=pilot_detail&plt_id=" . $pilot->getID() . "&view=ships_weapons");
if (strstr($config->getConfig("mods_active"), 'signature_generator'))
{
    $menubox->addOption("caption","Signature");
    $menubox->addOption("link","Link", "?a=sig_list&i=" . $pilot->getID());
}
$page->addContext($menubox->generate());

$killboard = $page->killboard_;
$config = $killboard->getConfig();
if ($config->getKillPoints())
{
    $scorebox = new Box("Kill points");
    $scorebox->addOption("points",$points);
    $page->addContext($scorebox->generate());
}

$page->setContent($html);

$page->generate();
?>