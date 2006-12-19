<?php
require_once("common/class.corp.php");
require_once("common/class.alliance.php");
require_once("common/class.killlist.php");
require_once("common/class.killlisttable.php");
require_once("common/globals.php");

function mktable($klist, $limit) 
{
	$this->klist = $klist;
	$this->limit = $limit;
	$odd = false;
	$klist->rewind();
	while ($kill = $klist->getKill()) {
		if ($limit && $c > $limit)
       	        	break;
       		else
			$c++;
		if (!$odd) {
        		$odd = true;
	        	$html .= "<tr bgcolor=#222222><td>";
      		} else {
        		$odd = false;
        		$html .= "<tr><td>";
		}
		$html .= "<img src=\"" .$kill->getVictimShipImage(32). "\">";
		$html .= " ";
		$html .= $kill->getVictimShipName();
		$html .= "(".$kill->getVictimShipClassName().") </td>";
		$html .= "<td>";
		$html .= $kill->getVictimName()."(".  shorten($kill->getVictimCorpName()).")"; 
		$html .= "</td><td>";
		$html .= $kill->getFBPilotName()."(".shorten($kill->getFBCorpName()) .")";
		$html .= "</td><td>";
		$html .= $kill->getTimeStamp();
		$html .= "</td><td>";
		$html .= $kill->getSolarSystemName() ."(".roundsec($kill->getSolarSystemSecurity()).")";
		$html .= "</td></tr>";
	}
	return $html;
}


$html .= "<html><head><title>IGB Killboard</title></head><body>";
$html .= "<a href=\"?a=post_igb\">Post killmail</a> | <a href=\"?a=portrait_grab\">Update portrait</a> | <a href=\"?a=igb&mode=kills\">Kills</a> | <a href=\"?a=igb&mode=losses\">Losses</a><br>";
$html .= "<table width=\"100%\" border=1>";
$html .= "<tr><td>Ship</td><td>Victim</td><td>Final Blow</td><td>Date/Time</td><td>System</td></tr>";
switch ($_GET[mode]) {
	case "losses":
		$klist = new KillList();
		$klist->setOrdered(true);
		if ( CORP_ID )
			$klist->addVictimCorp( new Corporation( CORP_ID ) );
		if ( ALLIANCE_ID )
			$klist->addVictimAlliance( new Alliance( ALLIANCE_ID ) );
			
		$html .= mktable($klist,30);
		break;
	case "kills": 
		$klist = new KillList();
		$klist->setOrdered(true);
		if ( CORP_ID )
			$klist->addInvolvedCorp( new Corporation( CORP_ID ) );
		if ( ALLIANCE_ID )
			$klist->addInvolvedAlliance( new Alliance( ALLIANCE_ID ) );
		$html .= mktable($klist,30);
		break;
	default: 
		$klist = new KillList();
		if ( CORP_ID )
			$klist->addInvolvedCorp( new Corporation( CORP_ID ) );
		if ( ALLIANCE_ID )
			$klist->addInvolvedAlliance( new Alliance( ALLIANCE_ID ) );
		$klist->setOrdered(true);
		$html .= mktable($klist,10);
		break;
}


$html .= "</table>";
$html .= "</body></html>";
echo $html;
?>
