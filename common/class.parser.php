<?php
require_once("class.alliance.php");
require_once("class.corp.php");
require_once("class.pilot.php");
require_once("class.kill.php");
require_once("class.item.php");

class Parser
{
    function Parser($killmail)
    {
        $this->error_ = array();
        $this->killmail_ = trim(str_replace("\r", '', $killmail));

        if (strpos($this->killmail_, 'Beteiligte Parteien:'))
        {
            $this->preparse('german');
        }

        if (strpos($this->killmail_, '**** Truncated - mail is too large ****') > 0)
        {
            $this->killmail_ = str_replace('**** Truncated - mail is too large ****', '', $this->killmail_);
        }

        // Parser fix, since some killmails don't have a final blow, they would break the KB
        if (strpos($this->killmail_, 'laid the final blow') < 1)
        {
            $this->needs_final_blow_ = 1;
        }
    }

    function parse($checkauth = true)
    {
        global $config;
        // header
        $involvedpos = strpos($this->killmail_, "Involved parties:");

        $header = substr($this->killmail_, 0, $involvedpos);
        $timestamp = substr($header, 0, 16);

        if (preg_match("/Victim: (.*?)Alliance: (.*)/", $header))
        {
            $this->error('Found no linefeeds.');
            return 0;
        }

        if (preg_match("/Victim: (.*)/", $header, $matches))
        {
            $victimname = trim($matches[1]);
        }
        else
        {
            $this->error('No victim found.');
        }
        if (preg_match("/Alliance: (.*)/", $header, $matches))
        {
            $alliancename = trim($matches[1]);
        }
        else
        {
            $this->error('No alliance found.');
        }
        if (preg_match("/Corp: (.*)/", $header, $matches))
        {
            $corpname = trim($matches[1]);
        }
        else
        {
            $this->error('No corp found.');
        }
        if (preg_match("/Destroyed: (.*)/", $header, $matches))
        {
            $shipname = trim($matches[1]);
        }
        else
        {
            $this->error('No destroyed ship found.');
        }
        if (preg_match("/System: (.*)/", $header, $matches))
        {
            $systemname = trim($matches[1]);
        }
        else
        {
            $this->error('No system found.');
        }
        if (preg_match("/Security: (.*)/", $header, $matches))
        {
            $systemsec = trim($matches[1]);
        }
        else
        {
            $this->error('No security found.');
        }

        if (!isset($timestamp) ||
                !isset($alliancename) ||
                !isset($corpname) ||
                !isset($victimname) ||
                !isset($shipname) ||
                !isset($systemname) ||
                !isset($systemsec))
            return 0;

        if ($checkauth)
        {
            $authorized = false;
        }
        else
        {
            $authorized = true;
        }

        // populate/update database
        $alliance = new Alliance();
        $alliance->add($alliancename);
        $corp = new Corporation();
        $corp->add($corpname, $alliance, $timestamp);
        $victim = new Pilot();
        $victim->add($victimname, $corp, $timestamp);
        $system = new SolarSystem();
        $system->lookup($systemname);
        if (!$system->getID())
        {
            $this->error('System not found.', $systemname);
        }
        $ship = new Ship();
        $ship->lookup($shipname);
        if (!$ship->getID())
        {
            $this->error('Ship not found.', $shipname);
        }
        $kill = new Kill();
        $kill->setTimeStamp($timestamp);
        $kill->setVictimID($victim->getID());
        $kill->setVictimCorpID($corp->getID());
        $kill->setVictimAllianceID($alliance->getID());
        $kill->setVictimShip($ship);
        $kill->setSolarSystem($system);

        if (ALLIANCE_ID != 0 && $alliance->getID() == ALLIANCE_ID)
            $authorized = true;
        elseif (CORP_ID != 0)
        {
            $corps = explode(",", CORP_ID);
            foreach($corps as $checkcorp)
            {
                if ($corp->getID() == $checkcorp)
                    $authorized = true;
            }
        }

        // involved
        $end = strpos($this->killmail_, "Destroyed items:");
        if ($end == 0)
        {
            $end = strlen($this->killmail_);
        }
        $involved = explode("\n", trim(substr($this->killmail_, strpos($this->killmail_, "Involved parties:") + 17, $end - (strpos($this->killmail_, "Involved parties:") + 17))));

        $i = 0;

        $order = 0;
        while ($i < count($involved))
        {
            if ($involved[$i] == "")
            {
                $i++;
                continue;
            }

            preg_match("/Name: (.*)/", $involved[$i], $matches);
            $ipname = $matches[1];

            preg_match("/(.*) \\(laid the final blow\\)/", $ipname, $matches);
            if ($matches[1])
            {
                $ipname = $matches[1];
                $finalblow = 1;
            }
            else
            {
                $finalblow = 0;
            }

            /* This is part of the final blow fix, mentioned above */
            if ($this->needs_final_blow_)
            {
                $finalblow = 1;
                $this->needs_final_blow_ = 0;
            }
            /* END FIX */

            preg_match("/Security: (.*)/", $involved[$i + 1], $matches);
            $secstatus = $matches[1];

            if ($secstatus == "") // NPC or POS
            {
                $secstatus = "0.0";
                preg_match("/(.*) \/ (.*)/", $ipname, $pmatches);
                $icname = $pmatches[2];
                $isname = "Unknown";
                $iwname = $pmatches[1];
                if (!strlen($icname) && !strlen($iwname))
                {
                    // fucked up bclinic killmail, no person here, continue
                    $i++;
                    continue;
                }

                $tmpcorp = new Corporation();
                $tmpcorp->lookup($icname);

                if (!$tmpcorp->getID())
                {
                    // not a known corp, add it
                    $ialliance = new Alliance();
                    $ialliance->add('None');
                    $icorp = new Corporation();
                    $icorp->add($icname, $ialliance, $kill->getTimeStamp());
                    $tmpcorp = $icorp;
                }
				$iweapon = new Item();
				$iweapon->lookup($pmatches[1]);
				$ipname = '#'.$tmpcorp->getID().'#'.$iweapon->getID().'#'.$iwname;
                $tmpall = $tmpcorp->getAlliance();
                // name will be None if no alliance is set
                $ianame = $tmpall->getName();

                $ialliance = &$tmpall;
                $icorp = &$tmpcorp;

                $i++;
            }
            else
            {
                preg_match("/Alliance: (.*)/", $involved[$i + 2], $matches);
                $ianame = $matches[1];

                preg_match("/Corp: (.*)/", $involved[$i + 3], $matches);
                $icname = $matches[1];

                preg_match("/Ship: (.*)/", $involved[$i + 4], $matches);
                $isname = $matches[1];

                preg_match("/Weapon: (.*)/", $involved[$i + 5], $matches);
                $iwname = $matches[1];
                $i += 6;

                $ialliance = new Alliance();
                $ialliance->add($ianame);
                $icorp = new Corporation();
                $icorp->add($icname, $ialliance, $kill->getTimeStamp());
            }

            $ipilot = new Pilot();
            $ipilot->add($ipname, $icorp, $timestamp);

            $iship = new Ship();
            $iship->lookup($isname);
            if (!$iship->getID())
            {
                $this->error('Ship not found.', $isname);
            }
            $iweapon = new Item();
            $iweapon->lookup($iwname);
            if (!$iweapon->getID())
            {
                $this->error('Weapon not found.', $iwname);
            }

            if (ALLIANCE_ID != 0 && $ialliance->getID() == ALLIANCE_ID)
            {
                $authorized = true;
            }
            elseif (CORP_ID != 0)
            {
                $corps = explode(",", CORP_ID);
                foreach($corps as $corp)
                {
                    if ($icorp->getID() == $corp)
                        $authorized = true;
                }
            }
            if (!$authorized)
            {
                if ($string = $config->getConfig('post_permission'))
                {
                    if ($string == 'all')
                    {
                        $authorized = true;
                    }
                    else
                    {
                        $tmp = explode(',', $string);
                        foreach ($tmp as $item)
                        {
                            if (!$item)
                            {
                                continue;
                            }
                            $typ = substr($item, 0, 1);
                            $id = substr($item, 1);
                            if ($typ == 'a')
                            {
                                if ($ialliance->getID() == $id || $kill->getVictimAllianceID() == $id)
                                {
                                    $authorized = true;
                                    break;
                                }
                            }
                            elseif ($typ == 'c')
                            {
                                if ($icorp->getID() == $id || $kill->getVictimCorpID() == $id)
                                {
                                    $authorized = true;
                                    break;
                                }
                            }
                            elseif ($typ == 'p')
                            {
                                if ($ipilot->getID() == $id || $kill->getVictimID() == $id)
                                {
                                    $authorized = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            $iparty = new InvolvedParty($ipilot->getID(), $icorp->getID(),
                $ialliance->getID(), $secstatus, $iship, $iweapon);
            $kill->addInvolvedParty($iparty);

            if ($finalblow == 1)
            {
                $kill->setFBPilotID($ipilot->getID());
                $kill->setFBCorpID($icorp->getID());
                $kill->setFBAllianceID($ialliance->getID());
            }
        }

        // destroyed items
        $destroyedpos = strpos($this->killmail_, "Destroyed items:");

        if ($destroyedpos > 0)
        {
            $destroyed = explode("\n", trim(substr($this->killmail_,
                         strpos($this->killmail_, "Destroyed items:") + 16,
                         strlen($this->killmail_) - (strpos($this->killmail_, "Destroyed items:") + 16))));

            $i = 0;
            while ($i < count($destroyed))
            {
                if ($destroyed[$i] == "")
                {
                    $i++;
                    continue;
                }

                if ($destroyed[$i] == "Empty.")
                {
                    $container = false;
                    $i++;
                    continue;
                }

                $qtypos = 0;
                $locpos = 0;
                $itemname = "";
                $quantity = "";
                $location = "";

                $qtypos = strpos($destroyed[$i], ", Qty: ");
                $locpos = strpos($destroyed[$i], "(");

                if ($container && $locpos != false)
                {
                    $container = false;
                }
                if (strpos($destroyed[$i], "Container"))
                {
                    $container = true;
                }
                if ($qtypos <= 0 && !$locpos)
                {
                    $itemlen = strlen($destroyed[$i]);
                    if ($container) $location = "Cargo";
                }
                if ($qtypos > 0 && !$locpos)
                {
                    $itemlen = $qtypos;
                    $qtylen = strlen($destroyed[$i]) - $qtypos;
                    if ($container) $location = "Cargo";
                }
                if ($locpos > 0 && $qtypos <= 0)
                {
                    $itemlen = $locpos - 1;
                    $qtylen = 0;
                    $loclen = strlen($destroyed[$i]) - $locpos - 2;
                    if (!$locpos) $container = false;
                }
                if ($locpos > 0 && $qtypos > 0)
                {
                    $itemlen = $qtypos;
                    $qtylen = $locpos - $qtypos - 7;
                    $loclen = strlen($destroyed[$i]) - $locpos - 2;
                    if (!$locpos) $container = false;
                }

                $itemname = substr($destroyed[$i], 0, $itemlen);
                if ($qtypos) $quantity = substr($destroyed[$i], $qtypos + 6, $qtylen);
                if ($locpos) $location = substr($destroyed[$i], $locpos + 1, $loclen);

                if ($quantity == "")
                {
                    $quantity = 1;
                }

                $item = new Item();
                $item->lookup(trim($itemname));
                if (!$item->getID())
                {
                    $this->error('Item not found.', trim($itemname));
                }
                $ditem = new DestroyedItem($item, $quantity, $location);
                $kill->addDestroyedItem($ditem);

                $i++;
            }
        }

        if (!$authorized)
        {
            return -2;
        }
        if ($this->getError())
        {
            return 0;
        }

        $id = $kill->add();
        if ($id == -1)
        {
            $this->dupeid_ = $kill->dupeid_;
        }

        return $id;
    }

    function error($message, $debugtext = null)
    {
        $this->error_[] = array($message, $debugtext);
    }

    function getError()
    {
        if (count($this->error_))
        {
            return $this->error_;
        }
        return false;
    }

    function preparse($set)
    {
        if ($set == 'german')
        {
            $search = array('Ziel:','Allianz: nichts','Allianz:','Zerstört','Sicherheit:','Beteiligte Parteien:','Anz:');
            $replace = array('Victim:','Alliance: None','Alliance:','Destroyed','Security:','Involved parties:', 'Qty:');
            $this->killmail_ = str_replace($search, $replace, $this->killmail_);
            return;
        }
    }
}
?>