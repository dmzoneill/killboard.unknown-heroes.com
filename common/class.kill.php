<?php
require_once("db.php");
require_once("class.item.php");
require_once("class.corp.php");
require_once("class.alliance.php");
require_once("class.ship.php");
require_once("class.system.php");
require_once("class.pilot.php");
require_once("class.killlist.php");

class Kill
{
    function Kill($id = 0)
    {
        $this->id_ = $id;
        $this->involvedparties_ = array();
        $this->destroyeditems_ = array();
    }

    function getID()
    {
        return $this->id_;
    }

    function getTimeStamp()
    {
        $this->execQuery();
        return $this->timestamp_;
    }

    function getVictimName()
    {
        $this->execQuery();
        return $this->victimname_;
    }

    function getVictimID()
    {
        $this->execQuery();
        return $this->victimid_;
    }

    function getVictimPortrait($size = 32)
    {
        $this->execQuery();
        $plt = new Pilot($this->victimid_);
        return $plt->getPortraitURL($size);
    }

    function getVictimCorpID()
    {
        $this->execQuery();
        return $this->victimcorpid_;
    }

    function getVictimCorpName()
    {
        $this->execQuery();
        return $this->victimcorpname_;
    }

    function getVictimAllianceName()
    {
        $this->execQuery();
        return $this->victimalliancename_;
    }

    function getVictimAllianceID()
    {
        $this->execQuery();
        return $this->victimallianceid_;
    }

    function getVictimShip()
    {
        $this->execQuery();
        return $this->victimship_;
    }

    function getSystem()
    {
        $this->execQuery();
        return $this->solarsystem_;
    }

    function getFBPilotID()
    {
        $this->execQuery();
        if (!$this->fbpilotid_) return "null";
        else return $this->fbpilotid_;
    }

    function getFBPilotName()
    {
        $this->execQuery();
        return $this->fbpilotname_;
    }

    function getFBCorpID()
    {
        $this->execQuery();
        if (!$this->fbcorpid_) return "null";
        else return $this->fbcorpid_;
    }

    function getFBCorpName()
    {
        $this->execQuery();
        return $this->fbcorpname_;
    }

    function getFBAllianceID()
    {
        $this->execQuery();
        if (!$this->fballianceid_) return "null";
        else return $this->fballianceid_;
    }

    function getFBAllianceName()
    {
        $this->execQuery();
        return $this->fballiancename_;
    }

    function getKillPoints()
    {
        $this->execQuery();
        return $this->killpoints_;
    }

    function getSolarSystemName()
    {
        return $this->solarsystemname_;
    }

    function getSolarSystemSecurity()
    {
        return $this->solarsystemsecurity_;
    }

    function getVictimShipName()
    {
        return $this->victimshipname_;
    }

    function getVictimShipExternalID()
    {
        return $this->victimshipexternalid_;
    }

    function getVictimShipClassName()
    {
        return $this->victimshipclassname_;
    }

    function getVictimShipValue()
    {
        return $this->victimshipvalue_;
    }

    function getVictimShipImage($size)
    {
        return IMG_URL."/ships/".$size."_".$size."/".$this->victimshipexternalid_.".png";
    }

    function getVictimShipValueIndicator()
    {
        $value = $this->getVictimShipValue();

        if ($value >= 0 && $value <= 1)
            $color = "gray";
        elseif ($value > 1 && $value <= 15)
            $color = "blue";
        elseif ($value > 15 && $value <= 25)
            $color = "green";
        elseif ($value > 25 && $value <= 40)
            $color = "yellow";
        elseif ($value > 40 && $value <= 80)
            $color = "red";
        elseif ($value > 80 && $value <= 250)
            $color = "orange";
        elseif ($value > 250 && $value <= 7000)
            $color = "purple";

        return IMG_URL."/ships/ship-".$color.".gif";
    }

    function getRawMail()
    {
        $this->execQuery();

        $mail .= substr(str_replace('-', '.' , $this->getTimeStamp()), 0, 16)."\r\n\r\n";
        $mail .= "Victim: ".$this->getVictimName()."\r\n";
        $mail .= "Alliance: ".$this->getVictimAllianceName()."\r\n";
        $mail .= "Corp: ".$this->getVictimCorpName()."\r\n";
        $ship = $this->getVictimShip();
        $mail .= "Destroyed: ".$ship->getName()."\r\n";
        $system = $this->getSystem();
        $mail .= "System: ".$system->getName()."\r\n";
        $mail .= "Security: ".$system->getSecurity(true)."\r\n\r\n";
        $mail .= "Involved parties:\r\n\r\n";

        foreach ($this->involvedparties_ as $inv)
        {
            $pilot = new Pilot($inv->getPilotID());
            $corp = new Corporation($inv->getCorpID());
            $alliance = new Alliance($inv->getAllianceID());

            $weapon = $inv->getWeapon();
            $ship = $inv->getShip();
            if ($pilot->getName() == $weapon->getName())
            {
                $name = $pilot->getName()." / ".$corp->getName();
            }
            else
            {
                $name = $pilot->getName();
            }

            $mail .= "Name: ".$name;
            if ($pilot->getID() == $this->getFBPilotID())
            {
                $mail .= " (laid the final blow)";
            }
            $mail .= "\r\n";

            if ($pilot->getName() != $weapon->getName())
            {
                $mail .= "Security: ".$inv->getSecStatus()."\r\n";
                $mail .= "Alliance: ".$alliance->getName()."\r\n";
                $mail .= "Corp: ".$corp->getName()."\r\n";
                $mail .= "Ship: ".$ship->getName()."\r\n";
                $mail .= "Weapon: ".$weapon->getName()."\r\n";
            }
            $mail .= "\r\n";
        }

        if (count($this->destroyeditems_) > 0)
        {
            $mail .= "\r\nDestroyed items:\r\n\r\n";

            foreach($this->destroyeditems_ as $destroyed)
            {
                $item = $destroyed->getItem();
                $mail .= $item->getName();
                if ($destroyed->getQuantity() > 1)
                    $mail .= ", Qty: ".$destroyed->getQuantity();
                if ($destroyed->getLocationID() == 4) // cargo
                    $mail .= " (Cargo)";
                if ($destroyed->getLocationID() == 6) // drone
                    $mail .= " (Drone Bay)";
                $mail .= "\r\n";
            }
        }

        return $mail;
    }

    function getDupe($checkonly = false)
    {
        if (!$checkonly)
        {
            $this->execQuery();
        }
        $dupe = 0;
        $qry = new DBQuery();
        if (!$this->getFBPilotID() || !$this->victimid_)
            return 0;
        $qry->execute("select kll_id
                        from kb3_kills
                        where kll_timestamp <=
                        date_add( '".$this->timestamp_."', INTERVAL '5:0' MINUTE_SECOND )
                        and kll_timestamp >=
                        date_sub( '".$this->timestamp_."', INTERVAL '5:0' MINUTE_SECOND )
                        and kll_victim_id = ".$this->victimid_."
                        and kll_ship_id = ".$this->victimship_->getID()."
                        and kll_system_id = ".$this->solarsystem_->getID()."
                        and kll_fb_plt_id = ".$this->getFBPilotID()."
                        and kll_id != ".$this->id_);

        $row = $qry->getRow();
        if ($row)
            return $row['kll_id'];
        else
            return 0;
    }

    function execQuery()
    {
        if (!$this->timestamp_)
        {
            $qry = new DBQuery();

            $this->qry_ = new DBQuery();
            $this->sql_ = "select kll.kll_id, kll.kll_timestamp, plt.plt_name,
                              crp.crp_name, ali.all_name, ali.all_id, kll.kll_ship_id,
                              kll.kll_system_id, kll.kll_ship_id,
            	  		      kll.kll_victim_id, plt.plt_externalid,
            	 		      kll.kll_crp_id, kll.kll_points,
            			      fbplt.plt_id as fbplt_id,
            			      fbplt.plt_externalid as fbplt_externalid,
            			      fbcrp.crp_id as fbcrp_id,
            			      fbali.all_id as fbali_id,
                              fbplt.plt_name as fbplt_name,
                              fbcrp.crp_name as fbcrp_name,
                              fbali.all_name as fbali_name
                         from kb3_kills kll, kb3_pilots plt, kb3_corps crp,
                              kb3_alliances ali, kb3_alliances fbali, kb3_corps fbcrp,
                              kb3_pilots fbplt
                        where kll.kll_id = '".$this->id_."'
                          and plt.plt_id = kll.kll_victim_id
                          and crp.crp_id = kll.kll_crp_id
                          and ali.all_id = kll.kll_all_id
                          and fbali.all_id = kll.kll_fb_all_id
                          and fbcrp.crp_id = kll.kll_fb_crp_id
                          and fbplt.plt_id = kll.kll_fb_plt_id";

            $this->qry_->execute($this->sql_);
            $row = $this->qry_->getRow();
            if (!$row)
            {
                $this->valid_ = false;
                return false;
            }
            else
            {
                $this->valid_ = true;
            }

            $this->setTimeStamp($row['kll_timestamp']);
            $this->setSolarSystem(new SolarSystem($row['kll_system_id']));
            $this->setVictimID($row['kll_victim_id']);
            $this->setVictimName($row['plt_name']);
            $this->setVictimCorpID($row['kll_crp_id']);
            $this->setVictimCorpName($row['crp_name']);
            $this->setVictimAllianceID($row['all_id']);
            $this->setVictimAllianceName($row['all_name']);
            $this->setVictimShip(new Ship($row['kll_ship_id']));
            $this->setFBPilotID($row['fbplt_id']);
            $this->setFBPilotName($row['fbplt_name']);
            $this->setFBCorpID($row['fbcrp_id']);
            $this->setFBCorpName($row['fbcrp_name']);
            $this->setFBAllianceID($row['fbali_id']);
            $this->setFBAllianceName($row['fbali_name']);
            $this->setKillPoints($row['kll_points']);
            $this->plt_ext_ = $row['plt_externalid'];
            $this->fbplt_ext_ = $row['fbplt_externalid'];

            // involved
            $sql = "select ind_plt_id, ind_crp_id, ind_all_id, ind_sec_status,
                    ind_shp_id, ind_wep_id
                    from kb3_inv_detail
                    where ind_kll_id = ".$this->getID()."
                    order by ind_order";

            $qry->execute($sql) or die($qry->getErrorMsg());
            while ($row = $qry->getRow())
            {
                $involved = new InvolvedParty($row['ind_plt_id'],
                    $row['ind_crp_id'],
                    $row['ind_all_id'],
                    $row['ind_sec_status'],
                    new Ship($row['ind_shp_id']),
                    new Item($row['ind_wep_id']));
                array_push($this->involvedparties_, $involved);
            }
            // destroyed items
            $sql = "select sum( itd.itd_quantity ) as itd_quantity, itd_itm_id,
                    itd_itl_id, itl_location
                    from kb3_items_destroyed itd, kb3_items itm,
                    kb3_item_locations itl
                    where itd.itd_kll_id = ".$this->getID()."
                    and itd.itd_itm_id = itm.itm_id
                    and ( itd.itd_itl_id = itl.itl_id or itd.itd_itl_id = 0 )
                    group by itd_itm_id, itd_itl_id
                    order by itd.itd_itl_id, itm.itm_type";

            $qry->execute($sql);
            while ($row = $qry->getRow())
            {
                $destroyed = new DestroyedItem(new Item($row['itd_itm_id']),
                    $row['itd_quantity'],
                    $row['itl_location']);
                array_push($this->destroyeditems_, $destroyed);
            }
        }
    }

    function exists()
    {
        $this->execQuery();
        return $this->valid_;
    }

    function relatedKillCount()
    {
        $kslist = new KillList();
        $kslist->setRelated($this->id_);
        if (CORP_ID)
            $kslist->addInvolvedCorp(new Corporation(CORP_ID));
        if (ALLIANCE_ID)
            $kslist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));

        return $kslist->getCount();
    }

    function relatedLossCount()
    {
        $lslist = new KillList();
        $lslist->setRelated($this->id_);
        if (CORP_ID)
            $lslist->addVictimCorp(new Corporation(CORP_ID));
        if (ALLIANCE_ID)
            $lslist->addVictimAlliance(new Alliance(ALLIANCE_ID));

        return $lslist->getCount();
    }

    function countComment($kll_id)
    {
        $qry = new DBQuery();
        $sql = "SELECT * FROM kb3_comments WHERE kll_id = '$kll_id'";
        $count = $qry->execute($sql);
        $count = $qry->recordCount();
        return $count;
    }

    function setID($id)
    {
        $this->id_ = $id;
    }

    function setTimeStamp($timestamp)
    {
        $this->timestamp_ = $timestamp;
    }

    function setSolarSystem($solarsystem)
    {
        $this->solarsystem_ = $solarsystem;
    }

    function setSolarSystemName($solarsystemname)
    {
        $this->solarsystemname_ = $solarsystemname;
    }

    function setSolarSystemSecurity($solarsystemsecurity)
    {
        $this->solarsystemsecurity_ = $solarsystemsecurity;
    }

    function setVictim($victim)
    {
        $this->victim_ = $victim;
    }

    function setVictimID($victimid)
    {
        $this->victimid_ = $victimid;
    }

    function setVictimName($victimname)
    {
        $this->victimname_ = $victimname;
    }

    function setVictimCorpID($victimcorpid)
    {
        $this->victimcorpid_ = $victimcorpid;
    }

    function setVictimCorpName($victimcorpname)
    {
        $this->victimcorpname_ = $victimcorpname;
    }

    function setVictimAllianceID($victimallianceid)
    {
        $this->victimallianceid_ = $victimallianceid;
    }

    function setVictimAllianceName($victimalliancename)
    {
        $this->victimalliancename_ = $victimalliancename;
    }

    function setVictimShip($victimship)
    {
        $this->victimship_ = $victimship;
    }

    function setVictimShipName($victimshipname)
    {
        $this->victimshipname_ = $victimshipname;
    }

    function setVictimShipExternalID($victimshipexternalid)
    {
        $this->victimshipexternalid_ = $victimshipexternalid;
    }

    function setVictimShipClassName($victimshipclassname)
    {
        $this->victimshipclassname_ = $victimshipclassname;
    }

    function setVictimShipValue($victimshipvalue)
    {
        $this->victimshipvalue_ = $victimshipvalue;
    }

    function setFBPilotID($fbpilotid)
    {
        $this->fbpilotid_ = $fbpilotid;
    }

    function setFBPilotName($fbpilotname)
    {
        $npc = strpos($fbpilotname, "#");
		if ($npc === false)
        {
    		$this->fbpilotname_ = $fbpilotname;
		}
		else
        {
    		$name = explode("#", $fbpilotname);
    		$plt = new Item($name[2]);
    		$this->fbpilotname_ = $plt->getName();
		}
    }

    function setFBCorpID($fbcorpid)
    {
        $this->fbcorpid_ = $fbcorpid;
    }

    function setFBCorpName($fbcorpname)
    {
        $this->fbcorpname_ = $fbcorpname;
    }

    function setFBAllianceID($fballianceid)
    {
        $this->fballianceid_ = $fballianceid;
    }

    function setFBAllianceName($fballiancename)
    {
        $this->fballiancename_ = $fballiancename;
    }
    function setKillPoints($killpoints)
    {
        $this->killpoints_ = $killpoints;
    }

    function calculateKillPoints()
    {
        $ship = $this->getVictimShip();
        $shipclass = $ship->getClass();
        $vicpoints = $shipclass->getPoints();
        $maxpoints = round($vicpoints * 1.2);

        foreach ($this->involvedparties_ as $inv)
        {
            $shipinv = $inv->getShip();
            $shipclassinv = $shipinv->getClass();
            $invpoints += $shipclassinv->getPoints();
        }

        $gankfactor = $vicpoints / ($vicpoints + $invpoints);
        $points = ceil($vicpoints * ($gankfactor / 0.75));

        if ($points > $maxpoints) $points = $maxpoints;

        $points = round($points, 0);
        return $points;
    }

    function add($id = null)
    {
        global $config;
        if (!$this->solarsystem_->getID())
        {
            echo 'INTERNAL ERROR; SOLARSYSTEM NOT FOUND; PLEASE CONTACT A DEV WITH THIS MESSAGE<br/>';
            var_dump($this->solarsystem_);
            var_dump($this->solarsystemname_);
            return 0;
        }

        $dupe = $this->getDupe(true);
        if ($dupe == 0)
        {
            $this->realadd();
        }
        elseif ($config->getConfig('readd_dupes'))
        {
            $this->dupeid_ = $dupe;
            $this->id_ = $dupe;
            $this->remove(false);
            $this->realadd($dupe);
            $this->id_ = -1;
        }
        else
        {
            $this->dupeid_ = $dupe;
            $this->id_ = -1;
        }
        return $this->id_;
    }

    function realadd($id = null)
    {
        // if ( $this->timestamp_ == "" || !$this->victimid_ || !$this->victimship_->getID() || !$this->solarsystem_->getID() ||
        // !$this->victimallianceid_ || !$this->victimcorpid_ || !$this->getFBAllianceID() || !$this->getFBCorpID() ||
        // !$this->getFBPilotID() )
        // return 0;
        if ($id == null)
        {
            $qid = 'null';
        }
        else
        {
            $qid = $id;
        }

        $qry = new DBQuery();
        $sql = "insert into kb3_kills values (".$qid.",
	            date_format('".$this->timestamp_."', '%Y.%m.%d %H:%i:%s'),
                ".$this->victimid_.", ".$this->victimallianceid_.",
                ".$this->victimcorpid_.", ".$this->victimship_->getID().",
                ".$this->solarsystem_->getID().", ".$this->getFBAllianceID().",
                ".$this->getFBCorpID().", ".$this->getFBPilotID().", ".$this->calculateKillPoints()." )";
        $qry->execute($sql);

        if ($id)
        {
            $this->id_ = $id;
        }
        else
        {
            $this->id_ = $qry->getInsertID();
        }

        // involved
        $order = 0;
        $invall = array();
        $invcrp = array();
        $invplt = array();
        foreach ($this->involvedparties_ as $inv)
        {
            $ship = $inv->getShip();
            $weapon = $inv->getWeapon();
            if (!$inv->getPilotID() || $inv->getSecStatus() == "" || !$inv->getAllianceID() || !$inv->getCorpID() || !$ship->getID() || !$weapon->getID())
            {
                $this->remove();
                return 0;
            }

            $sql = "insert into kb3_inv_detail
    	            values ( ".$this->getID().", ".$inv->getPilotID().", '".$inv->getSecStatus()."', "
                    .$inv->getAllianceID().", ".$inv->getCorpID().", ".$ship->getID().", "
                    .$weapon->getID().", ".$order++." )";
            $qry->execute($sql) or die($qry->getErrorMsg());

            if (!in_array($inv->getAllianceID(), $invall) && $inv->getAllianceID() != 14)
            {
                array_push($invall, $inv->getAllianceID());
                $qry->execute("insert into kb3_inv_all values ( ".$this->getID().", ".$inv->getAllianceID()." )") or die($qry->getErrorMsg());
            }
            if (!in_array($inv->getCorpID(), $invcrp))
            {
                array_push($invcrp, $inv->getCorpID());
                $qry->execute("insert into kb3_inv_crp values ( ".$this->getID().", ".$inv->getCorpID()." )") or die($qry->getErrorMsg());
            }
            if (!in_array($inv->getPilotID(), $invplt))
            {
                array_push($invplt, $inv->getPilotID());
                $qry->execute("insert into kb3_inv_plt values ( ".$this->getID().", ".$inv->getPilotID()." )") or die($qry->getErrorMsg());
            }
        }

        // destroyed
        foreach ($this->destroyeditems_ as $dest)
        {
            $item = $dest->getItem();
            if (!$item->getID() || !$dest->getQuantity() || $dest->getLocationID() === false)
            {
                continue;
            }

            $sql = "insert into kb3_items_destroyed
        	        values ( ".$this->getID().", ".$item->getID().", ".$dest->getQuantity().", "
                    .$dest->getLocationID()." )";
            $qry->execute($sql);
        }
        return $this->id_;
    }

    function remove($delcomments = true)
    {
        if (!$this->id_)
            return;

        $qry = new DBQuery();
        $qry->execute("delete from kb3_kills where kll_id = ".$this->id_);
        $qry->execute("delete from kb3_inv_detail where ind_kll_id = ".$this->id_);
        $qry->execute("delete from kb3_inv_all where ina_kll_id = ".$this->id_);
        $qry->execute("delete from kb3_inv_crp where inc_kll_id = ".$this->id_);
        $qry->execute("delete from kb3_inv_plt where inp_kll_id = ".$this->id_);
        $qry->execute("delete from kb3_items_destroyed where itd_kll_id = ".$this->id_);
        if ($delcomments)
        {
            $qry->execute("delete from kb3_comments where kll_id = ".$this->id_);
        }
    }

    function addInvolvedParty($involved)
    {
        array_push($this->involvedparties_, $involved);
    }

    function addDestroyedItem($destroyed)
    {
        array_push($this->destroyeditems_, $destroyed);
    }
}

class InvolvedParty
{
    function InvolvedParty($pilotid, $corpid, $allianceid,
        $secstatus, $ship, $weapon)
    {
        $this->pilotid_ = $pilotid;
        $this->corpid_ = $corpid;
        $this->allianceid_ = $allianceid;
        $this->secstatus_ = $secstatus;
        $this->ship_ = $ship;
        $this->weapon_ = $weapon;
    }

    function getPilotID()
    {
        return $this->pilotid_;
    }

    function getCorpID()
    {
        return $this->corpid_;
    }

    function getAllianceID()
    {
        return $this->allianceid_;
    }

    function getSecStatus()
    {
        return $this->secstatus_;
    }

    function getShip()
    {
        return $this->ship_;
    }

    function getWeapon()
    {
        return $this->weapon_;
    }
}

class DestroyedItem
{
    function DestroyedItem($item, $quantity, $location)
    {
        $this->item_ = $item;
        $this->quantity_ = $quantity;
        $this->location_ = $location;
    }

    function getItem()
    {
        return $this->item_;
    }

    function getQuantity()
    {
        if ($this->quantity_ == "") $this->quantity = 1;
        return $this->quantity_;
    }

	function getValue()
	//returns the value of an item
	{
		$value = 0; 				// Set 0 value incase nothing comes back
		$id = $this->item_->getID(); // get Item ID
		$qry = new DBQuery();
        $qry->execute("select itm_value from kb3_items where itm_id= '".$id."'");
        $row = $qry->getRow();
        $value = $row['itm_value'];
		if ($value == '')
        {
			$value = 0;
		}
		return $value;
	}

    function getLocationID()
    {
        $id = false;
        if (strlen($this->location_) < 2)
        {
            $id = $this->item_->getSlot();
        }
        else
        {
            $qry = new DBQuery();
            $qry->execute("select itl_id from kb3_item_locations where itl_location = '".$this->location_."'");
            $row = $qry->getRow();
            $id = $row['itl_id'];
        }
        return $id;
    }
}
?>