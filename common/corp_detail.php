<?php
require_once("db.php");
require_once("class.page.php");
require_once("class.corp.php");
require_once("class.alliance.php");
require_once("class.killlist.php");
require_once("class.killlisttable.php");
require_once("class.killsummarytable.php");
require_once("class.box.php");
require_once("class.toplist.php");

if (!$crp_id = intval($_GET['crp_id']))
{
    echo 'no valid corp id specified<br/>';
    exit;
}
$corp = new Corporation($crp_id);
$alliance = $corp->getAlliance();

$kill_summary = new KillSummaryTable();
$kill_summary->addInvolvedCorp($corp);
$kill_summary->setBreak(6);
$summary_html = $kill_summary->generate();

$page = new Page("Corporation details - " . $corp->getName());
$html .= "<table class=kb-table width=\"100%\" border=\"0\" cellspacing=1><tr class=kb-table-row-even><td rowspan=8 width=128 align=center bgcolor=black>";

if (file_exists("img/corps/".$corp->getID().".jpg"))
{
    $html .= "<img src=\"".$corp->getPortraitURL(128)."\" border=\"0\"></td>";
}
else
{
    $html .= "<img src=\"" . IMG_URL . "/campaign-big.gif\" border=\"0\"></td>";
}

$html .= "<td class=kb-table-cell width=180><b>Alliance:</b></td><td class=kb-table-cell>";
if ($alliance->getName() == "Unknown" || $alliance->getName() == "None")
{
    $html .= "<b>".$alliance->getName()."</b>";
}
else
{
    $html .= "<a href=\"?a=alliance_detail&all_id=".$alliance->getID()."\">".$alliance->getName()."</a>";
}
$html .= "</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Kills:</b></td><td class=kl-kill>".$kill_summary->getTotalKills()."</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Losses:</b></td><td class=kl-loss>".$kill_summary->getTotalLosses()."</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage done (ISK):</b></td><td class=kl-kill>".round($kill_summary->getTotalKillISK()/1000000, 2)."M</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage received (ISK):</b></td><td class=kl-loss>".round($kill_summary->getTotalLossISK()/1000000, 2)."M</td></tr>";
if ($kill_summary->getTotalKillISK())
{
    $efficiency = round($kill_summary->getTotalKillISK() / ($kill_summary->getTotalKillISK() + $kill_summary->getTotalLossISK()) * 100, 2);
}
else
{
    $efficiency = 0;
}

$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Efficiency:</b></td><td class=kb-table-cell><b>" . $efficiency . "%</b></td></tr>";
$html .= "</table>";
$html .= "<br/>";

if ($_GET['view'] == "" || $_GET['view'] == "kills" || $_GET['view'] == "losses")
{
    $html .= $summary_html;
}

switch ($_GET['view'])
{
    case "":
        $html .= "<div class=kb-kills-header>10 Most recent kills</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->setLimit(10);
        $list->setPodsNoobships(true);
        $list->addInvolvedCorp($corp);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));

        $ktab = new KillListTable($list);
        $ktab->setLimit(10);
        $ktab->setDayBreak(false);
        $html .= $ktab->generate();

        $html .= "<div class=kb-losses-header>10 Most recent losses</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->setLimit(10);
        $list->setPodsNoobships(true);
        $list->addVictimCorp($corp);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));

        $ltab = new KillListTable($list);
        $ltab->setLimit(10);
        $ltab->setDayBreak(false);
        $html .= $ltab->generate();

        break;
    case "kills":
        $html .= "<div class=kb-kills-header>All kills</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->addInvolvedCorp($corp);
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
        $list->addVictimCorp($corp);
        if ($_GET['scl_id'])
            $list->addVictimShipClass(new ShipClass($_GET['scl_id']));
        $pagesplitter = new PageSplitter($list->getCount(), 30);
        $list->setPageSplitter($pagesplitter);

        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();

        break;
    case "pilot_kills":
        $html .= "<div class=block-header2>Top killers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopKillsList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(false);
        $list->setMonth(date("m"));
        $list->setYear(date("Y"));
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopKillsList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(false);
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_scores":
        $html .= "<div class=block-header2>Top scorers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopScoreList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(true);
        $list->setMonth(date("m"));
        $list->setYear(date("Y"));
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopScoreList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(true);
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_solo":
        $html .= "<div class=block-header2>Top solokillers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopSoloKillerList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(false);
        $list->setMonth(date("m"));
        $list->setYear(date("Y"));
        $table = new TopPilotTable($list, "Solokills");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopSoloKillerList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(false);
        $table = new TopPilotTable($list, "Solokills");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;

    case "pilot_damage":
        $html .= "<div class=block-header2>Top damagedealers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopDamageDealerList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(false);
        $list->setMonth(date("m"));
        $list->setYear(date("Y"));
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopDamageDealerList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(false);
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;

    case "pilot_griefer":
        $html .= "<div class=block-header2>Top griefers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopGrieferList();
        $list->addInvolvedCorp($corp);
        $list->setMonth(date("m"));
        $list->setYear(date("Y"));
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopGrieferList();
        $list->addInvolvedCorp($corp);
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;

    case "pilot_losses":
        $html .= "<div class=block-header2>Top losers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>This month</div>";

        $list = new TopLossesList();
        $list->addVictimCorp($corp);
        $list->setPodsNoobShips(false);
        $list->setMonth(date("m"));
        $list->setYear(date("Y"));
        $table = new TopPilotTable($list, "Losses");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopLossesList();
        $list->addVictimCorp($corp);
        $list->setPodsNoobShips(false);
        $table = new TopPilotTable($list, "Losses");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "ships_weapons":
        $html .= "<div class=block-header2>Ships & weapons used</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=400>";
        $shiplist = new TopShipList();
        $shiplist->addInvolvedCorp($corp);
        $shiplisttable = new TopShipListTable($shiplist);
        $html .= $shiplisttable->generate();
        $html .= "</td><td valign=top align=right width=400>";

        $weaponlist = new TopWeaponList();
        $weaponlist->addInvolvedCorp($corp);
        $weaponlisttable = new TopWeaponListTable($weaponlist);
        $html .= $weaponlisttable->generate();
        $html .= "</td></tr></table>";

        break;
    case 'violent_systems':
        $html .= "<div class=block-header2>Most violent systems</div>";
        $html .= "<table width=\"99%\"><tr><td align=center valign=top>";

        $html .= "<div class=block-header>This month</div>";
        $html .= "<table class=kb-table>";
        $html .= "<tr class=kb-table-header><td>#</td><td width=180>System</td><td width=40 align=center >Kills</td></tr>";

        $sql = "select sys.sys_name, sys.sys_sec, sys.sys_id, count(distinct kll.kll_id) as kills
                    from kb3_systems sys, kb3_kills kll, kb3_inv_detail inv
                    where kll.kll_system_id = sys.sys_id
                    and inv.ind_kll_id = kll.kll_id";

        if (CORP_ID)
            $sql .= " and inv.ind_crp_id in (".CORP_ID.")";
        if (ALLIANCE_ID)
            $sql .= " and inv.ind_all_id = ".ALLIANCE_ID;

        $sql .= "   and date_format( kll.kll_timestamp, \"%c\" ) = ".date("m")."
                    and date_format( kll.kll_timestamp, \"%Y\" ) = ".date("Y")."
                    group by sys.sys_name
                    order by kills desc
                    limit 25";

        $qry = new DBQuery();
        $qry->execute($sql);
        $odd = false;
        $counter = 1;
        while ($row = $qry->getRow())
        {
            if (!$odd)
            {
                $odd = true;
                $rowclass = 'kb-table-row-odd';
            }
            else
            {
                $odd = false;
                $rowclass = 'kb-table-row-even';
            }

            $html .= "<tr class=".$rowclass."><td><b>".$counter.".</b></td><td class=kb-table-cell width=180><b><a href=\"?a=system_detail&amp;sys_id=".$row['sys_id']."\">".$row['sys_name']."</a></b> (".roundsec($row['sys_sec']).")</td><td align=center>".$row['kills']."</td></tr>";
            $counter++;
        }

        $html .= "</table>";

        $html .= "</td><td align=center valign=top>";
        $html .= "<div class=block-header>All-Time</div>";
        $html .= "<table class=kb-table>";
        $html .= "<tr class=kb-table-header><td>#</td><td width=180>System</td><td width=40 align=center>Kills</td></tr>";

        $sql = "select sys.sys_name, sys.sys_id, sys.sys_sec, count(distinct kll.kll_id) as kills
                    from kb3_systems sys, kb3_kills kll, kb3_inv_detail inv
                    where kll.kll_system_id = sys.sys_id
                    and inv.ind_kll_id = kll.kll_id";

        if (CORP_ID)
            $sql .= " and inv.ind_crp_id in (".CORP_ID.")";
        if (ALLIANCE_ID)
            $sql .= " and inv.ind_all_id = ".ALLIANCE_ID;

        $sql .= " group by sys.sys_name
                    order by kills desc
                    limit 25";

        $qry = new DBQuery();
        $qry->execute($sql);
        $odd = false;
        $counter = 1;
        while ($row = $qry->getRow())
        {
            if (!$odd)
            {
                $odd = true;
                $rowclass = 'kb-table-row-odd';
            }
            else
            {
                $odd = false;
                $rowclass = 'kb-table-row-even';
            }

            $html .= "<tr class=".$rowclass."><td><b>".$counter.".</b></td><td class=kb-table-cell><b><a href=\"?a=system_detail&amp;sys_id=".$row['sys_id']."\">".$row['sys_name']."</a></b> (".roundsec($row['sys_sec']).")</td><td align=center>".$row['kills']."</td></tr>";
            $counter++;
        }
        $html .= "</table>";
        $html .= "</td></tr></table>";
    break;
}

$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Kills & losses");
$menubox->addOption("link","Recent activity", "?a=corp_detail&crp_id=" . $corp->getID());
$menubox->addOption("link","Kills", "?a=corp_detail&crp_id=" . $corp->getID() . "&view=kills");
$menubox->addOption("link","Losses", "?a=corp_detail&crp_id=" . $corp->getID() . "&view=losses");
$menubox->addOption("caption","Pilot statistics");
$menubox->addOption("link","Top killers", "?a=corp_detail&crp_id=" . $corp->getID() . "&view=pilot_kills");

$killboard = $page->killboard_;
$config = $killboard->getConfig();
if ($config->getKillPoints())
    $menubox->addOption("link","Top scorers", "?a=corp_detail&crp_id=" . $corp->getID() . "&view=pilot_scores");
$menubox->addOption("link","Top solokillers", "?a=corp_detail&crp_id=" . $corp->getID() . "&view=pilot_solo");
$menubox->addOption("link","Top damagedealers", "?a=corp_detail&crp_id=" . $corp->getID() . "&view=pilot_damage");
$menubox->addOption("link","Top griefers", "?a=corp_detail&crp_id=" . $corp->getID() . "&view=pilot_griefer");
$menubox->addOption("link","Top losers", "?a=corp_detail&crp_id=" . $corp->getID() . "&view=pilot_losses");
$menubox->addOption("caption","Global statistics");
$menubox->addOption("link","Ships & weapons", "?a=corp_detail&crp_id=" . $corp->getID() . "&view=ships_weapons");
$menubox->addOption("link","Most violent systems", "?a=corp_detail&crp_id=" . $corp->getID() . "&view=violent_systems");
$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();
?>