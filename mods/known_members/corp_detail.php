<?php
require_once("common/db.php");
require_once("common/class.page.php");
require_once("common/class.corp.php");
require_once("common/class.alliance.php");
require_once("common/class.killlist.php");
require_once("common/class.killlisttable.php");
require_once("common/class.killsummarytable.php");
require_once("common/class.box.php");
require_once("common/class.toplist.php");
require_once("common/class.pilot.php");


$corp = new Corporation(intval($_GET['crp_id']));
$alliance = $corp->getAlliance();

$klist = new KillList();
$tklist = new KillList();
$llist = new KillList();
$klist->addInvolvedCorp($corp);
$tklist->addInvolvedCorp($corp);
$tklist->setPodsNoobShips(false);
$llist->addVictimCorp($corp);
$klist->getAllKills();
$llist->getAllKills();

$page = new Page("Corporation details - " . $corp->getName());
$html .= "<table class=kb-table width=\"100%\" border=\"0\" cellspacing=1><tr class=kb-table-row-even><td rowspan=8 width=128 align=center>";

if (file_exists("img/corps/".$corp->getID().".jpg"))
{
    $html .= "<img src=\"".$corp->getPortraitURL(128)."\" border=\"0\"></td>";
}
else
{
    $html .= "<img src=\"" . IMG_URL . "/campaign-big.gif\" border=\"0\"></td>";
}

// $html .= "</tr>";
$html .= "<td class=kb-table-cell width=180><b>Alliance:</b></td><td class=kb-table-cell>";
if ($alliance->getName() == "Unknown" || $alliance->getName() == "None")
    $html .= "<b>" . $alliance->getName() . "</b>";
else
    $html .= "<a href=\"?a=alliance_detail&all_id=" . $alliance->getID() . "\">" . $alliance->getName() . "</a>";
$html .= "</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Kills:</b></td><td class=kl-kill>" . $klist->getCount() . "</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Real kills:</b></td><td class=kl-kill>" . $tklist->getCount() . "</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Losses:</b></td><td class=kl-loss>" . $llist->getCount() . "</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage done (ISK):</b></td><td class=kl-kill>" . $klist->getISK() . "M</td></tr>";
$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage received (ISK):</b></td><td class=kl-loss>" . $llist->getISK() . "M</td></tr>";
if ($klist->getISK())
    $efficiency = round($klist->getISK() / ($klist->getISK() + $llist->getISK()) * 100, 2);
else
    $efficiency = 0;

$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Efficiency:</b></td><td class=kb-table-cell><b>" . $efficiency . "%</b></td></tr>";

$html .= "</table>";

$html .= "<br/>";

if ($_GET['view'] == "" || $_GET['view'] == "kills" || $_GET['view'] == "losses")
{
    $summarytable = new KillSummaryTable($klist, $llist);
    $summarytable->setBreak(6);

    $html .= $summarytable->generate();
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
        $list->setPodsNoobShips(false);
        $list->setMonth(date("m"));
        $list->setYear(date("Y"));
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopScoreList();
        $list->addInvolvedCorp($corp);
        $list->setPodsNoobShips(false);
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
		
		
   case "known_members":
       		if($config->getConfig('known_members_own'))
			{
				$alliance->getID();
				if (ALLIANCE_ID && $alliance->getID() == ALLIANCE_ID)
				{
					$can_view = 1;
				}
				elseif (CORP_ID && $corp->getID() == CORP_ID)
				{
					$can_view = 1;
				}

			}
			
			
			
		if($can_view == 1)
		{
		$html .= "Cannot View this corps Member List";
		}
		else
		{	
   			$query = "SELECT * FROM `kb3_pilots`  WHERE plt_crp_id =".intval($_GET['crp_id'])." ORDER BY `plt_name` ASC";
			$qry = new DBQuery();
			$qry->execute($query);
			$cnt = $qry->recordCount();
   			$clmn = $config->getConfig('known_members_clmn');
			
		$html .= "<div class=block-header2>Known Pilots (".$cnt.")</div>";
		$html .= "<table class=kb-table align=center>";
		$html .= '<tr class=kb-table-header>';
		if (strpos($clmn,"img"))
		{
		$html .= '<td class=kb-table-header align="center"></td>';
		}
		$html .= '<td class=kb-table-header align="center">Pilot</td>';
		if (strpos($clmn,"kll_pnts"))
		{
		$html .= '<td class=kb-table-header align="center">Kill Points</td>';
		}
		if (strpos($clmn,"dmg_dn"))
		{		
		$html .= '<td class=kb-table-header align="center">Dmg Done (isk)</td>';
		}
		if (strpos($clmn,"dmg_rcd"))
		{
		$html .= '<td class=kb-table-header align="center">Dmg Recived (isk)</td>';
		}
		if (strpos($clmn,"eff"))
		{
		$html .= '<td class=kb-table-header align="center">Efficiency</td>';
		}
		if ($page->isAdmin())
		{
		$html .= '<td class=kb-table-header align="center">Admin - Move</td>';
		}
		$html .= '</tr>';
			while ($data = $qry->getRow())
			{
				$pilot = new Pilot( $data['plt_id'] );
				$plist = new KillList();
				$plist->addInvolvedPilot($pilot);
				$plist->getAllKills();
				$points = $plist->getPoints();
				
				$pllist = new KillList();
				$pllist->addVictimPilot($pilot);
				$pllist->getAllKills();
				
				$plistisk = $plist->getISK();
				$pllistisk = $pllist->getISK();
				if ($plistisk == 0) { $plistisk = 1; } //Remove divide by 0
				if ($pllistisk == 0) { $pllistisk = 1; } //Remove divide by 0
				$efficiency = round($plistisk / ($plistisk + $pllistisk) * 100, 2); 
				
					if (!$odd)
					{
						$odd = true;
						$class = 'kb-table-row-odd';
					}
					else
					{ 									 
						$odd = false;
						$class = 'kb-table-row-even';
					}

					$html .= "<tr class=".$class." style=\"height: 32px;\">"; 
					if (strpos($clmn,"img"))
					{					
					$html .= '<td width="64" align="center"><img src='.$pilot->getPortraitURL( 32 ).'></td>';
					}
					$html .= '<td align="center"><a href=?a=pilot_detail&plt_id='.$pilot->getID().'>'.$pilot->getName().'</a></td>'; 
					if (strpos($clmn,"kll_pnts"))
					{
					$html .= '<td align="center">'.$points.'</td>';
					}
					if (strpos($clmn,"dmg_dn"))
					{
					$html .= '<td align="center">'.round($plist->getISK(),2).'M</td>';
					}
					if (strpos($clmn,"dmg_rcd"))
					{					
					$html .= '<td align="center">'.round($pllist->getISK(),2).'M</td>';
					}
					if (strpos($clmn,"eff"))
					{
					$html .= '<td align="center">'.$efficiency.'%</td>';
					}
					if ($page->isAdmin())
					{
					$html .= "<td align=center><a href=\"javascript:openWindow('?a=move_pilot&plt_id=".$data['plt_id']."', null, 500, 500, '' )\">Move</a></td>";
					}
					$html .= '</tr>';
			}

		$html .='</table>';
		}
        break;
}

$menubox = new Box("Menu");
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
$menubox->addOption("link","Known Members", "?a=corp_detail&crp_id=" . $corp->getID() . "&view=known_members");
$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();
?>