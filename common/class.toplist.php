<?php
require_once("class.killlist.php");
require_once("class.corp.php");
require_once("class.alliance.php");
require_once("class.system.php");
require_once("class.ship.php");

class TopList
{
    function TopList()
    {
        $this->qry_ = new DBQuery();
        $this->exclude_scl_ = array();
        $this->vic_scl_id_ = array();
        $this->regions_ = array();
        $this->systems_ = array();
    }

    function setPodsNoobShips($flag)
    {
        if (!$flag)
        {
            array_push($this->exclude_scl_, 2);
            array_push($this->exclude_scl_, 3);
            array_push($this->exclude_scl_, 11);
        }
        else
        {
            $this->exclude_scl_ = array();
        }
    }

    function setSQLTop($sql)
    {
        $this->sqltop_ = $sql;
    }

    function setSQLBottom($sql)
    {
        $this->sqlbottom_ = $sql;
    }

    function addInvolvedPilot($pilot)
    {
        $this->inv_plt_ .= $pilot->getID().", ";
        if ($this->inv_crp_ || $this->inv_all_)
            $this->mixedinvolved_ = true;
    }

    function addInvolvedCorp($corp)
    {
        $this->inv_crp_ .= $corp->getID().", ";
        if ($this->inv_plt_ || $this->inv_all_)
            $this->mixedinvolved_ = true;
    }

    function addInvolvedAlliance($alliance)
    {
        $this->inv_all_ .= $alliance->getID().", ";
        if ($this->inv_plt_ || $this->inv_crp_)
            $this->mixedinvolved_ = true;
    }

    function addVictimPilot($pilot)
    {
        $this->vic_plt_ .= $pilot->getID().", ";
        if ($this->vic_crp_ || $this->vic_all_)
            $this->mixedvictims_ = true;
    }

    function addVictimCorp($corp)
    {
        $this->vic_crp_ .= $corp->getID().", ";
        if ($this->vic_plt_ || $this->vic_all_)
            $this->mixedvictims_ = true;
    }

    function addVictimAlliance($alliance)
    {
        $this->vic_all_ .= $alliance->getID().", ";
        if ($this->vic_plt_ || $this->vic_crp_)
            $this->mixedvictims_ = true;
    }

    function addVictimShipClass($shipclass)
    {
        array_push($this->vic_scl_id_, $shipclass->getID());
    }

    function addVictimShip($ship)
    {
    }

    function addItemDestroyed($item)
    {
    }

    function addRegion($region)
    {
        array_push($this->regions_, $region->getID());
    }

    function addSystem($system)
    {
        array_push($this->systems_, $system->getID());
    }

    function addGroupBy($groupby)
    {
        array_push($this->groupby_, $groupby);
    }

    function setPageSplitter($pagesplitter)
    {
        if (isset($_GET['page'])) $page = $_GET['page'];
        else $page = 1;
        $this->plimit_ = $pagesplitter->getSplit();
        $this->poffset_ = ($page * $this->plimit_) - $this->plimit_;
        // echo $this->offset_;
        // echo $this->limit_;
    }

    function setWeek($weekno)
    {
        $this->timeframe_ .= " and date_format( kll.kll_timestamp, \"%u\" ) = ";
        $this->timeframe_ .= $weekno;
    }

    function setMonth($monthno)
    {
        $this->timeframe_ .= " and date_format( kll.kll_timestamp, \"%c\" ) = ";
        $this->timeframe_ .= $monthno;
    }

    function setYear($yearno)
    {
        $this->timeframe_ .= " and date_format( kll.kll_timestamp, \"%Y\" ) = ";
        $this->timeframe_ .= $yearno;
    }

    function setStartWeek($weekno)
    {
        $this->timeframe_ .= " and date_format( kll.kll_timestamp, \"%u\" ) >= ";
        $this->timeframe_ .= $weekno;
    }

    function setStartDate($timestamp)
    {
        $this->timeframe_ .= " and kll.kll_timestamp >= '".$timestamp."'";
    }

    function setEndDate($timestamp)
    {
        $this->timeframe_ .= " and kll.kll_timestamp <= '".$timestamp."'";
    }

    function setGroupBy($groupby)
    {
        $this->groupby_ = $groupby;
    }

    function execQuery()
    {
        $this->sql_ .= $this->sqltop_;
        // involved
        if ($this->inv_plt_)
            $this->sql_ .= " inner join kb3_inv_plt inp
                                 on ( inp.inp_plt_id in ( ".substr($this->inv_plt_, 0, strlen($this->inv_plt_) - 2)." ) and kll.kll_id = inp.inp_kll_id ) ";
        if ($this->inv_crp_)
            $this->sql_ .= " inner join kb3_inv_crp inc
	                         on ( inc.inc_crp_id in ( ".substr($this->inv_crp_, 0, strlen($this->inv_crp_) - 2)." ) and kll.kll_id = inc.inc_kll_id ) ";

        if ($this->inv_all_)
            $this->sql_ .= " inner join kb3_inv_all ina
                                 on ( ina.ina_all_id in ( ".substr($this->inv_all_, 0, strlen($this->inv_all_) - 2)." ) and kll.kll_id = ina.ina_kll_id ) ";

        if (count($this->exclude_scl_))
        {
            $this->sql_ .= " inner join kb3_ships shp
	  		         on ( shp.shp_id = kll.kll_ship_id )
	  		 inner join kb3_ship_classes scl
	  		         on ( scl.scl_id = shp.shp_class )";
            $this->sql_ .= " and scl.scl_id not in ( ".implode(",", $this->exclude_scl_)." )";
        }

        if (count($this->vic_scl_id_))
        {
            $this->sql_ .= " inner join kb3_ships shp
	  		         on ( shp.shp_id = kll.kll_ship_id )
	  		 inner join kb3_ship_classes scl
	  		         on ( scl.scl_id = shp.shp_class )";
            $this->sql_ .= " and scl.scl_id in ( ".implode(",", $this->vic_scl_id_)." )";
        }

        if (count($this->regions_))
        {
            $this->sql_ .= " inner join kb3_systems sys
      	                         on ( sys.sys_id = kll.kll_system_id )
                         inner join kb3_constellations con
      	                         on ( con.con_id = sys.sys_con_id )
			 inner join kb3_regions reg
			         on ( reg.reg_id = con.con_reg_id
		         	      and reg.reg_id in ( ".implode($this->regions_, ",")." ) )";
        }
        if (count($this->systems_))
        {
            $this->sql_ .= "   and kll.kll_system_id in ( ".implode($this->systems_, ",").")";
        }
        // victim filter
        if ($this->mixedvictims_)
        {
            $this->sql_ .= " and ( 1 = 0 ";
            $op = "or";
        }
        else $op = "and";

        if ($this->vic_plt_)
            $this->sql_ .= " ".$op." kll.kll_victim_id in ( ".substr($this->vic_plt_, 0, strlen($this->vic_plt_) - 2)." )";
        if ($this->vic_crp_)
            $this->sql_ .= " ".$op." kll.kll_crp_id in ( ".substr($this->vic_crp_, 0, strlen($this->vic_crp_) - 2)." )";
        if ($this->vic_all_)
            $this->sql_ .= " ".$op." kll.kll_all_id in ( ".substr($this->vic_all_, 0, strlen($this->vic_all_) - 2)." )";

        if ($this->mixedvictims_)
            $this->sql_ .= " ) ";

        if ($this->timeframe_) $this->sql_ .= $this->timeframe_;

        $this->sql_ .= " ".$this->sqlbottom_;
        // echo $this->sql_."<br/><br/>";
        $this->qry_->execute($this->sql_);
    }

    function getRow()
    {
        if (!$this->qry_->executed())
            $this->execQuery();

        $row = $this->qry_->getRow();
        return $row;
    }

    function getTimeFrameSQL()
    {
        return $this->timeframe_;
    }
}

class TopKillsList extends TopList
{
    function TopKillsList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(*) as cnt, ind.ind_plt_id as plt_id
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id ";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".substr($this->inv_crp_, 0, strlen($this->inv_crp_) - 2)." )";
        $sql .= ")";

        if ($this->inv_all_)
        {
            $sql .= ' inner join kb3_corps crp on ( crp.crp_id = ind.ind_crp_id ';
            $sql .= " and crp.crp_all_id in ( ".substr($this->inv_all_, 0, strlen($this->inv_all_) - 2)." )";
            $sql .= ')';
        }

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit 30");
        $this->setPodsNoobShips(false);
    }
}

class TopCorpKillsList extends TopList
{
    function TopKillsList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(*) as cnt, ind.ind_crp_id as crp_id
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              inner join kb3_corps crp
	 	      on ( crp.crp_id = ind.ind_crp_id ";
        if ($this->inv_all_)
            $sql .= " and crp.crp_all_id in ( ".substr($this->inv_all_, 0, strlen($this->inv_all_) - 2)." )";

        $sql .= ")";

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_crp_id order by 1 desc
                            limit 30");
        $this->setPodsNoobShips(false);
    }
}

class TopScoreList extends TopList
{
    function TopScoreList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select sum(kll.kll_points) as cnt, ind.ind_plt_id as plt_id
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id ";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".substr($this->inv_crp_, 0, strlen($this->inv_crp_) - 2)." )";

        $sql .= ")";

        if ($this->inv_all_)
        {
            $sql .= ' inner join kb3_corps crp on ( crp.crp_id = ind.ind_crp_id ';
            $sql .= " and crp.crp_all_id in ( ".substr($this->inv_all_, 0, strlen($this->inv_all_) - 2)." )";
            $sql .= ')';
        }

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit 30");
        // $this->setPodsNoobShips(false);
    }
}

class TopLossesList extends TopList
{
    function TopScoreList()
    {
        $this->TopList();
    }

    function generate()
    {
        $this->setSQLTop("select count(*) as cnt, kll.kll_victim_id as plt_id
                           from kb3_kills kll");
        $this->setSQLBottom("group by kll.kll_victim_id order by 1 desc
                            limit 30");
        $this->setPodsNoobShips(false);
    }
}

class TopCorpLossesList extends TopList
{
    function TopScoreList()
    {
        $this->TopList();
    }

    function generate()
    {
        $this->setSQLTop("select count(*) as cnt, kll.kll_crp_id as crp_id
                           from kb3_kills kll");
        $this->setSQLBottom("group by kll.kll_crp_id order by 1 desc
                            limit 30");
        $this->setPodsNoobShips(false);
    }
}

class TopFinalBlowList extends TopList
{
    function TopFinalBlowList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(kll.kll_id) as cnt, kll.kll_fb_plt_id as plt_id
                from kb3_kills kll
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = kll.kll_fb_plt_id ";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".substr($this->inv_crp_, 0, strlen($this->inv_crp_) - 2)." )";

        $sql .= ")";

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by kll.kll_fb_plt_id order by 1 desc
                            limit 30");
        $this->setPodsNoobShips(false);
    }
}

class TopDamageDealerList extends TopList
{
    function TopDamageDealerList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(kll.kll_id) as cnt, ind.ind_plt_id as plt_id
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id and ind.ind_order = 0)
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id ";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".substr($this->inv_crp_, 0, strlen($this->inv_crp_) - 2)." )";

        $sql .= ")";

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit 30");
        $this->setPodsNoobShips(false);
    }
}

class TopSoloKillerList extends TopList
{
    function TopSoloKillerList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(kll.kll_id) as cnt, ind.ind_plt_id as plt_id
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id and ind.ind_order = 0)
	      inner join kb3_inv_detail ind2
		      on ( ind2.ind_kll_id = ind.ind_kll_id
		           and not (ind2.ind_order > 0 ) )
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id
		           and kll.kll_fb_plt_id = plt.plt_id";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".substr($this->inv_crp_, 0, strlen($this->inv_crp_) - 2)." )";

        $sql .= ")";

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit 30");
        $this->setPodsNoobShips(false);
    }
}

class TopPodKillerList extends TopList
{
    function TopPodKillerList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(kll.kll_id) as cnt, ind.ind_plt_id as plt_id
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".substr($this->inv_crp_, 0, strlen($this->inv_crp_) - 2)." )";

        $sql .= ")";

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit 30");
        $this->addVictimShipClass(new ShipClass(2)); // capsule
    }
}

class TopGrieferList extends TopList
{
    function TopGrieferList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(kll.kll_id) as cnt, ind.ind_plt_id as plt_id
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".substr($this->inv_crp_, 0, strlen($this->inv_crp_) - 2)." )";

        $sql .= ")";

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit 30");
        $this->addVictimShipClass(new ShipClass(20)); // freighter
        $this->addVictimShipClass(new ShipClass(22)); // exhumer
        $this->addVictimShipClass(new ShipClass(7)); // industrial
        $this->addVictimShipClass(new ShipClass(12)); // barge
        $this->addVictimShipClass(new ShipClass(14)); // transport
    }
}

class TopCapitalShipKillerList extends TopList
{
    function TopCapitalShipKillerList()
    {
        $this->TopList();
    }

    function generate()
    {
        $sql = "select count(kll.kll_id) as cnt, ind.ind_plt_id as plt_id
                from kb3_kills kll
	      inner join kb3_inv_detail ind
		      on ( ind.ind_kll_id = kll.kll_id )
              inner join kb3_pilots plt
	 	      on ( plt.plt_id = ind.ind_plt_id";
        if ($this->inv_crp_)
            $sql .= " and plt.plt_crp_id in ( ".substr($this->inv_crp_, 0, strlen($this->inv_crp_) - 2)." )";

        $sql .= ")";

        $this->setSQLTop($sql);

        $this->setSQLBottom("group by ind.ind_plt_id order by 1 desc
                            limit 30");
        $this->addVictimShipClass(new ShipClass(20)); // freighter
        $this->addVictimShipClass(new ShipClass(19)); // dread
        $this->addVictimShipClass(new ShipClass(27)); // carrier
        $this->addVictimShipClass(new ShipClass(28)); // mothership
        $this->addVictimShipClass(new ShipClass(26)); // titan
    }
}

class TopContractKillsList extends TopKillsList
{
    function TopContractKillsList()
    {
        $this->TopKillsList();
    }

    function generate()
    {
        parent::generate();
    }

    function setContract($contract)
    {
        $this->setStartDate($contract->getStartDate());
        if ($contract->getEndDate() != "")
            $this->setEndDate($contract->getEndDate());

        while ($target = $contract->getContractTarget())
        {
            switch ($target->getType())
            {
                case "corp":
                    $this->addVictimCorp(new Corporation($target->getID()));
                    break;
                case "alliance":
                    $this->addVictimAlliance(new Alliance($target->getID()));
                    break;
                case "region":
                    $this->addRegion(new Region($target->getID()));
                    break;
                case "system":
                    $this->addSystem(new SolarSystem($target->getID()));
                    break;
            }
        }
    }
}

class TopContractScoreList extends TopScoreList
{
    function TopContractScoreList()
    {
        $this->TopScoreList();
    }

    function generate()
    {
        parent::generate();
    }

    function setContract($contract)
    {
        $this->setStartDate($contract->getStartDate());
        if ($contract->getEndDate() != "")
            $this->setEndDate($contract->getEndDate());

        while ($target = $contract->getContractTarget())
        {
            switch ($target->getType())
            {
                case "corp":
                    $this->addVictimCorp(new Corporation($target->getID()));
                    break;
                case "alliance":
                    $this->addVictimAlliance(new Alliance($target->getID()));
                    break;
                case "region":
                    $this->addRegion(new Region($target->getID()));
                    break;
                case "system":
                    $this->addSystem(new SolarSystem($target->getID()));
                    break;
            }
        }
    }
}

class TopPilotTable
{
    function TopPilotTable($toplist, $entity)
    {
        $this->toplist_ = $toplist;
        $this->entity_ = $entity;
    }

    function generate()
    {
        $this->toplist_->generate();

        $html .= "<table class=kb-table cellspacing=1>";
        $html .= "<tr class=kb-table-header>";
        $html .= "<td class=kb-table-cell align=center colspan=2>Pilot</td>";
        $html .= "<td class=kb-table-cell align=center width=60>".$this->entity_."</td>";
        $html .= "</tr>";

        $odd = true;
        $i = 1;
        while ($row = $this->toplist_->getRow())
        {
            $pilot = new Pilot($row['plt_id']);
            if ($odd)
            {
                $class = "kb-table-row-odd";
                $odd = false;
            }
            else
            {
                $class = "kb-table-row-even";
                $odd = true;
            }
            $html .= "<tr class=".$class.">";
            $html .= "<td><img src=\"".$pilot->getPortraitURL(32)."\"></td>";
            $html .= "<td class=kb-table-cell width=200><b>".$i.".</b>&nbsp;<a class=kb-shipclass href=\"?a=pilot_detail&plt_id=".$row['plt_id']."\">".$pilot->getName()."</a></td>";
            $html .= "<td class=kb-table-cell align=center><b>".$row['cnt']."</b></td>";

            $html .= "</tr>";
            $i++;
        }

        $html .= "</table>";

        return $html;
    }
}

class TopCorpTable
{
    function TopCorpTable($toplist, $entity)
    {
        $this->toplist_ = $toplist;
        $this->entity_ = $entity;
    }

    function generate()
    {
        $this->toplist_->generate();

        $html .= "<table class=kb-table cellspacing=1>";
        $html .= "<tr class=kb-table-header>";
        $html .= "<td class=kb-table-cell align=center>#</td>";
        $html .= "<td class=kb-table-cell align=center>Corporation</td>";
        $html .= "<td class=kb-table-cell align=center width=60>".$this->entity_."</td>";
        $html .= "</tr>";

        $odd = true;
        $i = 1;
        while ($row = $this->toplist_->getRow())
        {
            $corp = new Corporation($row['crp_id']);
            if ($odd)
            {
                $class = "kb-table-row-odd";
                $odd = false;
            }
            else
            {
                $class = "kb-table-row-even";
                $odd = true;
            }
            $html .= "<tr class=".$class.">";
            $html .= "<td class=kb-table-cell align=center><b>".$i.".</b></td>";
            $html .= "<td class=kb-table-cell width=200><a href=\"?a=corp_detail&crp_id=".$row['crp_id']."\">".$corp->getName()."</a></td>";
            $html .= "<td class=kb-table-cell align=center><b>".$row['cnt']."</b></td>";

            $html .= "</tr>";
            $i++;
        }

        $html .= "</table>";

        return $html;
    }
}

class TopShipList extends TopList
{
    function TopShipList()
    {
        $this->TopList();
    }

    function addInvolvedPilot($pilot)
    {
        $this->invplt_ = $pilot;
    }

    function addInvolvedCorp($corp)
    {
        $this->invcrp_ = $corp;
    }

    function addInvolvedAlliance($alliance)
    {
        $this->invall_ = $alliance;
    }

    function generate()
    {
        $sql = "select count(*) as cnt, ind.ind_shp_id as shp_id
              from kb3_inv_detail ind
	      inner join kb3_ships shp on ( shp_id = ind.ind_shp_id )";

        if ($this->invplt_)
            $sql .= " inner join kb3_inv_plt inp
	                  on ( inp.inp_kll_id = ind.ind_kll_id
			       and inp.inp_plt_id = ind.ind_plt_id
			       and inp.inp_plt_id = ".$this->invplt_->getID().")";

        if ($this->invcrp_)
            $sql .= " inner join kb3_inv_crp inc
	                  on ( inc.inc_kll_id = ind.ind_kll_id
			       and inc.inc_crp_id = ind.ind_crp_id
			       and inc.inc_crp_id = ".$this->invcrp_->getID().")";

        if ($this->invall_)
            $sql .= " inner join kb3_inv_all ina
	                  on ( ina.ina_kll_id = ind.ind_kll_id
			       and ina.ina_all_id = ind.ind_all_id
			       and ina.ina_all_id = ".$this->invall_->getID().")";

        $this->setSQLTop($sql);
        $this->setSQLBottom(" and ind.ind_shp_id not in ( 6, 31 )
                             and shp.shp_class != 17
                             group by ind.ind_shp_id order by 1 desc
			     limit 20");
    }
}

class TopShipListTable
{
    function TopShipListTable($toplist)
    {
        $this->toplist_ = $toplist;
    }

    function generate()
    {
        $this->toplist_->generate();

        $html .= "<table class=kb-table cellspacing=1>";
        $html .= "<tr class=kb-table-header>";
        $html .= "<td class=kb-table-cell align=center colspan=2>Ship</td>";
        $html .= "<td class=kb-table-cell align=center width=60>Kills</td>";
        $html .= "</tr>";

        $odd = true;
        while ($row = $this->toplist_->getRow())
        {
            $ship = new Ship($row['shp_id']);
            $shipclass = $ship->getClass();
            if ($odd)
            {
                $class = "kb-table-row-odd";
                $odd = false;
            }
            else
            {
                $class = "kb-table-row-even";
                $odd = true;
            }
            $html .= "<tr class=".$class.">";
            $html .= "<td><img src=\"".$ship->getImage(32)."\"></td>";
            $html .= "<td class=kb-table-cell width=200><b>".$ship->getName()."</b><br>".$shipclass->getName()."</td>";
            $html .= "<td class=kb-table-cell align=center><b>".$row['cnt']."</b></td>";

            $html .= "</tr>";
        }

        $html .= "</table>";

        return $html;
    }
}

class TopWeaponList extends TopList
{
    function TopWeaponList()
    {
        $this->TopList();
    }

    function addInvolvedPilot($pilot)
    {
        $this->invplt_ = $pilot;
    }

    function addInvolvedCorp($corp)
    {
        $this->invcrp_ = $corp;
    }

    function addInvolvedAlliance($alliance)
    {
        $this->invall_ = $alliance;
    }

    function generate()
    {
        $sql = "select count(*) as cnt, ind.ind_wep_id as itm_id
              from kb3_inv_detail ind
	      inner join kb3_items itm on ( itm_id = ind.ind_wep_id )";

        if ($this->invplt_)
            $sql .= " inner join kb3_inv_plt inp
	                  on ( inp.inp_kll_id = ind.ind_kll_id
			       and inp.inp_plt_id = ind.ind_plt_id
			       and inp.inp_plt_id = ".$this->invplt_->getID().")";

        if ($this->invcrp_)
            $sql .= " inner join kb3_inv_crp inc
	                  on ( inc.inc_kll_id = ind.ind_kll_id
			       and inc.inc_crp_id = ind.ind_crp_id
			       and inc.inc_crp_id = ".$this->invcrp_->getID().")";

        if ($this->invall_)
            $sql .= " inner join kb3_inv_all ina
	                  on ( ina.ina_kll_id = ind.ind_kll_id
			       and ina.ina_all_id = ind.ind_all_id
			       and ina.ina_all_id = ".$this->invall_->getID().")";

        $this->setSQLTop($sql);
        $this->setSQLBottom(" and itm.itm_icon not in ( '1', 'icon_null' )
                             and itm.itm_name != 'Unknown'
                             group by ind.ind_wep_id order by 1 desc
			     limit 20");
    }
}

class TopWeaponListTable
{
    function TopWeaponListTable($toplist)
    {
        $this->toplist_ = $toplist;
    }

    function generate()
    {
        $this->toplist_->generate();

        $html .= "<table class=kb-table cellspacing=1>";
        $html .= "<tr class=kb-table-header>";
        $html .= "<td class=kb-table-cell align=center colspan=2>Weapon</td>";
        $html .= "<td class=kb-table-cell align=center width=60>Kills</td>";
        $html .= "</tr>";

        $odd = true;
        while ($row = $this->toplist_->getRow())
        {
            $item = new Item($row['itm_id']);
            if ($odd)
            {
                $class = "kb-table-row-odd";
                $odd = false;
            }
            else
            {
                $class = "kb-table-row-even";
                $odd = true;
            }
            $html .= "<tr class=".$class.">";
            $html .= "<td>".$item->getIcon(32)."</td>";
            $html .= "<td class=kb-table-cell width=200><b>".$item->getName()."</b></td>";
            $html .= "<td class=kb-table-cell align=center><b>".$row['cnt']."</b></td>";

            $html .= "</tr>";
        }

        $html .= "</table>";

        return $html;
    }
}

?>
