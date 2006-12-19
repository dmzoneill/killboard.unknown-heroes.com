<?php
require_once("class.kill.php");
require_once("class.page.php");
require_once("class.pilot.php");
require_once("class.corp.php");
require_once("class.box.php");
require_once("class.alliance.php");
require_once("globals.php");

$page = new Page("Kill details");

if ($config->getConfig('item_values'))
{
    $smarty->assign('item_values', 'true');
    if ($page->isAdmin())
    {
        $smarty->assign('admin', 'true');
        if (isset($_POST['submit']))
        {
            // Send new value for item to the database
            $IID = $_POST['IID'];
            $Val = $_POST[$IID];
            //echo "UPDATE kb3_items SET itm_value ='" . $Val . "' WHERE itm_id='" . $IID . "'";
            $qry = new DBQuery();
            $qry->execute("UPDATE kb3_items SET itm_value ='".$Val."' WHERE itm_id='".$IID."'");
        }
    }
}

if (!$kll_id = intval($_GET['kll_id']))
{
    $html = "No kill id specified.";
    $page->setContent($html);
    $page->generate($html);
    exit;
}

$kill = new Kill($kll_id);
if (!$kill->exists())
{
    $html = "That kill doesn't exist.";
    $page->setContent($html);
    $page->generate($html);
    exit;
}

// victim $smarty->assign('',);
$smarty->assign('VictimPortrait', $kill->getVictimPortrait(64));
$smarty->assign('VictimURL', "?a=pilot_detail&plt_id=" . $kill->getVictimID());
$smarty->assign('VictimName', $kill->getVictimName());
$smarty->assign('VictimCorpURL', "?a=corp_detail&crp_id=" . $kill->getVictimCorpID());
$smarty->assign('VictimCorpName', $kill->getVictimCorpName());
$smarty->assign('VictimAllianceURL', "?a=alliance_detail&all_id=" . $kill->getVictimAllianceID());
$smarty->assign('VictimAllianceName', $kill->getVictimAllianceName());

// involved
$i = 1;
$involved = array();
foreach ($kill->involvedparties_ as $inv)
{
    $pilot = new Pilot($inv->getPilotID());
    $corp = new Corporation($inv->getCorpID());
    $alliance = new Alliance($inv->getAllianceID());
    $ship = $inv->getShip();
    $weapon = $inv->getWeapon();

    $involved[$i]['shipImage'] = $ship->getImage(64);
    $involved[$i]['PilotURL'] = "?a=pilot_detail&plt_id=" . $pilot->getID();
    $involved[$i]['PilotName'] = $pilot->getName();
    $involved[$i]['CorpURL'] = "?a=corp_detail&crp_id=" . $corp->getID();
    $involved[$i]['CorpName'] = $corp->getName();
    $involved[$i]['AlliURL'] = "?a=alliance_detail&all_id=" . $alliance->getID();
    $involved[$i]['AlliName'] = $alliance->getName();
    $involved[$i]['ShipName'] = $ship->getName();

    if ($pilot->getID() == $kill->getFBPilotID())
    {
        $involved[$i]['FB'] = "true";
    }
    else
    {
        $involved[$i]['FB'] = "false";
    }

    if ($corp->isNPCCorp())
    {
        $involved[$i]['portrait'] = $corp->getPortraitURL(64);
    }
    else
    {
        $involved[$i]['portrait'] = $pilot->getPortraitURL(64);
    }

    if ($weapon->getName() != "Unknown" && $weapon->getName() != $ship->getName())
        $involved[$i]['weaponName'] = $weapon->getName();
    else
        $involved[$i]['weaponName'] = "Unknown";
    ++$i;
}
$smarty->assign_by_ref('involved', $involved);

if ($config->getConfig('comments'))
{
    include('comments.php');
    $smarty->assign('comments', $comment);
}
// ship, ship details
$ship = $kill->getVictimShip();
$shipclass = $ship->getClass();
$system = $kill->getSystem();

$smarty->assign('VictimShip', $kill->getVictimShip());
$smarty->assign('ShipClass', $ship->getClass());
$smarty->assign('ShipImage', $ship->getImage(64));
$smarty->assign('ShipName', $ship->getName());
$smarty->assign('ClassName', $shipclass->getName());
$smarty->assign('System', $system->getName());
$smarty->assign('SystemURL', "?a=system_detail&amp;sys_id=".$system->getID());
$smarty->assign('SystemSecurity', $system->getSecurity(true));
$smarty->assign('TimeStamp', $kill->getTimeStamp());
$smarty->assign('VictimShipImg', $ship->getImage(64));

// ship fitting
if (count($kill->destroyeditems_) > 0)
{
    $dest_array = array();
    $dest_array[1] = array('img' => 'fitted_-_high_slot.jpg', 'text' => 'Fitted - High slot', 'items' => array());
    $dest_array[2] = array('img' => 'fitted_-_medium_slot.jpg', 'text' => 'Fitted - Mid slot', 'items' => array());
    $dest_array[3] = array('img' => 'fitted_-_low_slot.jpg', 'text' => 'Fitted - Low slot', 'items' => array());
    $dest_array[6] = array('img' => 'drone_bay.jpg', 'text' => 'Drone bay', 'items' => array());
    $dest_array[4] = array('img' => 'cargo.jpg', 'text' => 'Cargo Bay', 'items' => array());

    foreach($kill->destroyeditems_ as $destroyed)
    {
        $item = $destroyed->getItem();
        if ($config->getConfig('item_values'))
        {
            $value = $destroyed->getValue();
            $value_single = $value;
            if ($value > 0)
            {
                $value = $destroyed->getValue() * $destroyed->getQuantity();
                $TotalValue = $TotalValue + $value;
                // Value Manipulation for prettyness.
                if (strlen($value) > 1) // 1000's ?
                {
                    $Formatted = number_format($value, 2);
                    $Formatted = $Formatted . " isk";
                }

                if (strlen($value) > 3) // 1000's ?
                {
                    $Formatted = round($value / 1000, 2);

                    $Formatted = number_format($Formatted, 2);
                    $Formatted = $Formatted . " K";
                }

                if (strlen($value) > 6) // Is this value in the millions?
                {
                    $Formatted = round($value / 1000000, 2);
                    $Formatted = number_format($Formatted, 2);
                    $Formatted = $Formatted . " M";
                }
            }
            else
            {
                $value = 0;
                $Formatted = "0 isk";
            }
        }
        $dest_array[$destroyed->getLocationID()]['items'][] = array('Icon' => $item->getIcon(32), 'Name' => $item->getName(), 'Quantity' => $destroyed->getQuantity(), 'Value' => $Formatted, 'single_unit' => $value_single, 'itemID' => $item->getID());
    }
}

if ($TotalValue >= 0)
{
    $Formatted = number_format($TotalValue, 2);
}

// Get Ship Value
$ShipValue = $ship->getPrice();
$TotalLoss = number_format($TotalValue + $ShipValue, 2);
$ShipValue = number_format($ShipValue, 2);

$smarty->assign_by_ref('destroyed', $dest_array);
$smarty->assign('ItemValue', $Formatted);
$smarty->assign('ShipValue', $ShipValue);
$smarty->assign('TotalLoss', $TotalLoss);

$menubox = new Box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption", "View");
$menubox->addOption("link", "Killmail", "javascript:sndReq('index.php?a=kill_mail&kll_id=".$kill->getID()."');ReverseContentDisplay('popup')");
if ($kill->relatedKillCount() > 1 || $kill->relatedLossCount() > 1)
{
    $menubox->addOption("link", "Related kills (" . $kill->relatedKillCount() . "/" . $kill->relatedLossCount() . ")", "?a=kill_related&kll_id=" . $kill->getID());
}
if ($page->isAdmin())
{
    $menubox->addOption("caption", "Admin");
    $menubox->addOption("link", "Delete", "javascript:openWindow('?a=kill_delete&kll_id=" . $kill->getID() . "', null, 420, 300, '' );");
}
$page->addContext($menubox->generate());

if ($config->getKillPoints())
{
    $scorebox = new Box("Points");
    $scorebox->addOption("points", $kill->getKillPoints());
    $page->addContext($scorebox->generate());
}

$mapbox = new Box("Map");
$mapbox->addOption("img", "?a=mapview&sys_id=" . $system->getID() . "&mode=map&size=145");
$mapbox->addOption("img", "?a=mapview&sys_id=" . $system->getID() . "&mode=region&size=145");
$mapbox->addOption("img", "?a=mapview&sys_id=" . $system->getID() . "&mode=cons&size=145");
$page->addContext($mapbox->generate());

$html = $smarty->fetch(get_tpl('kill_detail'));
$page->setContent($html);
$page->generate();
?>