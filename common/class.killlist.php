<?php
require_once('class.kill.php');
require_once('class.pagesplitter.php');

class KillList
{
    function KillList()
    {
        $this->qry_ = new DBQuery();
        $this->killpointer_ = 0;
        $this->kills_ = 0;
        $this->losses_ = 0;
        $this->killisk_ = 0;
        $this->lossisk_ = 0;
        $this->exclude_scl_ = array();
        $this->vic_scl_id_ = array();
        $this->regions_ = array();
        $this->systems_ = array();
        $this->groupby_ = array();
        $this->offset_ = 0;
        $this->killcounter_ = 0;
        $this->realkillcounter_ = 0;
        $this->ordered_ = false;
    }

    function execQuery()
    {
        if (!$this->qry_->executed_)
        {
            if (!count($this->groupby_))
                $this->sql_ = 'select distinct kll.kll_id, kll.kll_timestamp, plt.plt_name,
                                crp.crp_name, crp.crp_id, ali.all_name, ali.all_id, kll.kll_ship_id,
                                kll.kll_system_id, kll.kll_ship_id,
                                kll.kll_victim_id, plt.plt_externalid,
                                kll.kll_crp_id, kll.kll_points,
        	                 	shp.shp_class, shp.shp_name,
        		                shp.shp_externalid, shp.shp_id,
                				scl.scl_id, scl.scl_class, scl.scl_value,
                				sys.sys_name, sys.sys_sec,
                                fbplt.plt_name as fbplt_name,
                                fbplt.plt_externalid as fbplt_externalid,
                                fbcrp.crp_name as fbcrp_name';

            global $config;
            if ($config->getConfig('ship_values'))
            {
                $this->sql_ .= ', ksv.shp_value';
            }
            if (count($this->groupby_))
            {
                $this->sql_ .= "select count(*) as cnt, ".implode(",", $this->groupby_);
            }

            $this->sql_ .= "    from kb3_kills kll
	  		   inner join kb3_ships shp
	  		      on ( shp.shp_id = kll.kll_ship_id )
	  		   inner join kb3_ship_classes scl
	  		      on ( scl.scl_id = shp.shp_class )";
            if ($config->getConfig('ship_values'))
            {
                $this->sql_ .= ' left join kb3_ships_values ksv on (shp.shp_id = ksv.shp_id) ';
            }

            $this->sql_ .= "inner join kb3_pilots plt
                              on ( plt.plt_id = kll.kll_victim_id )
                           inner join kb3_corps crp
                              on ( crp.crp_id = kll.kll_crp_id )
                           inner join kb3_alliances ali
                              on ( ali.all_id = kll.kll_all_id )
                           inner join kb3_pilots fbplt
                              on ( fbplt.plt_id = kll.kll_fb_plt_id )
                           inner join kb3_corps fbcrp
                              on ( fbcrp.crp_id = kll.kll_fb_crp_id )
                           inner join kb3_systems sys
                              on ( sys.sys_id = kll.kll_system_id )";

            // involved filter
            if (! $this->mixedinvolved_)
            {
                if ($this->inv_plt_)
                    $this->sql_ .= " inner join kb3_inv_plt inp
	                       on ( inp.inp_plt_id in ( ".substr($this->inv_plt_, 0, strlen($this->inv_plt_) - 2)." ) and kll.kll_id = inp.inp_kll_id ) ";
                if ($this->inv_crp_)
                    $this->sql_ .= " inner join kb3_inv_crp inc
	                      on ( inc.inc_crp_id in ( ".substr($this->inv_crp_, 0, strlen($this->inv_crp_) - 2)." ) and kll.kll_id = inc.inc_kll_id
                               and inc.inc_crp_id != kll.kll_crp_id ) ";
                if ($this->inv_all_)
                    $this->sql_ .= " inner join kb3_inv_all ina
	                      on ( ina.ina_all_id in ( ".substr($this->inv_all_, 0, strlen($this->inv_all_) - 2)." ) and kll.kll_id = ina.ina_kll_id
                               and ina.ina_all_id != kll.kll_all_id ) ";
            }
            else
            {
                $this->sql_ .= " <ph> ";
            }
            // echo $this->sql_;
            // regions
            if (count($this->regions_))
            {
                $this->sql_ .= " inner join kb3_constellations con
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
            // echo $this->sql_;
            // ship filter
            if (count($this->exclude_scl_))
            {
                $this->sql_ .= " and scl.scl_id not in ( ".implode(",", $this->exclude_scl_)." )";
            }
            if (count($this->vic_scl_id_))
            {
                $this->sql_ .= " and scl.scl_id in ( ".implode(",", $this->vic_scl_id_)." )";
            }
            // related
            if ($this->related_)
            {
                $rqry = new DBQuery();
                $rsql = "select kll_timestamp, kll_system_id from kb3_kills where kll_id = ".$this->related_;

                $rqry->execute($rsql);
                $rrow = $rqry->getRow();

                $this->sql_ .= " and kll.kll_system_id = ".$rrow['kll_system_id']."
	                   and kll.kll_timestamp <=
			       date_add( '".$rrow['kll_timestamp']."', INTERVAL '15:0' MINUTE_SECOND )
	                   and kll.kll_timestamp >=
			       date_sub( '".$rrow['kll_timestamp']."', INTERVAL '15:0' MINUTE_SECOND )";
            }
            // timeframe
            if ($this->timeframe_)
                $this->sql_ .= $this->timeframe_;

            if (!strpos($this->sql_, " join ") && !$this->mixedinvolved_)
                $this->sqlhead_ .= " where 1 = 1";

            if ($this->mixedinvolved_)
            {
                if ($this->inv_plt_)
                {
                    $replace = " inner join kb3_inv_plt inp
	                    on ( inp.inp_plt_id in ( ".substr($this->inv_plt_, 0, strlen($this->inv_plt_) - 2)." ) and kll.kll_id = inp.inp_kll_id ) ";
                    $psql = str_replace("<ph>", $replace, $this->sql_);
                }
                if ($this->inv_crp_)
                {
                    $replace = " inner join kb3_inv_crp inc
	                    on ( inc.inc_crp_id in ( ".substr($this->inv_crp_, 0, strlen($this->inv_crp_) - 2)." ) and kll.kll_id = inc.inc_kll_id ) ";
                    $csql = str_replace("<ph>", $replace, $this->sql_);
                }
                if ($this->inv_all_)
                {
                    $replace = " inner join kb3_inv_all ina
	                    on ( ina.ina_all_id in ( ".substr($this->inv_all_, 0, strlen($this->inv_all_) - 2)." ) and kll.kll_id = ina.ina_kll_id ) ";
                    $asql = str_replace("<ph>", $replace, $this->sql_);
                }

                if ($psql)
                    $nsql = $psql." union ";
                if ($csql)
                    $nsql .= $csql." union ";
                if ($asql)
                    $nsql .= $asql;

                $this->sql_ = $nsql;
            }
            if ($this->minkllid_)
            {
                $this->sql_ .= ' WHERE kll.kll_id > \''.$this->minkllid_.'\' ';
            }

            // group by
            if ($this->groupby_) $this->sql_ .= " group by ".implode(",", $this->groupby_);
            // order/limit
            if ($this->ordered_)
            {
                if (!$this->orderby_) $this->sql_ .= " order by kll_timestamp desc";
                else $this->sql_ .= " order by ".$this->orderby_;
            }
            if ($this->limit_) $this->sql_ .= " limit ".$this->offset_.", ".$this->limit_;
            // echo '<p>'.$this->sql_."</p>";
            $this->qry_->execute($this->sql_);
        }
    }

    function getRow()
    {
        $this->execQuery();
        if ($this->plimit_ && $this->killcounter_ >= $this->plimit_)
        {
            // echo $this->plimit_." ".$this->killcounter_;
            return null;
        }

        $skip = $this->poffset_ - $this->killpointer_;
        if ($skip > 0)
        {
            for ($i = 0; $i < $skip; $i++)
            {
                $this->killpointer_++;
                $row = $this->qry_->getRow();
            }
        }

        $row = $this->qry_->getRow();

        return $row;
    }

    function getKill()
    {
        $this->execQuery();
        if ($this->plimit_ && $this->killcounter_ >= $this->plimit_)
        {
            // echo $this->plimit_." ".$this->killcounter_;
            return null;
        }

        $skip = $this->poffset_ - $this->killpointer_;
        if ($skip > 0)
        {
            for ($i = 0; $i < $skip; $i++)
            {
                $this->killpointer_++;
                $row = $this->qry_->getRow();
            }
        }

        $row = $this->qry_->getRow();
        if ($row)
        {
            $this->killcounter_++;
            if ($row['scl_class'] != 2 && $row['scl_class'] != 3 && $row['scl_class'] != 11)
                $this->realkillcounter_++;

            global $config;
            if ($config->getConfig('ship_values'))
            {
                if ($row['shp_value'])
                {
                    $row['scl_value'] = $row['shp_value'];
                }
            }

            $this->killisk_ += $row['scl_value'] / 1000000;
            $this->killpoints_ += $row['kll_points'];

            $kill = new Kill($row['kll_id']);
            $kill->setTimeStamp($row['kll_timestamp']);
            $kill->setSolarSystemName($row['sys_name']);
            $kill->setSolarSystemSecurity($row['sys_sec']);
            $kill->setVictimName($row['plt_name']);
            $kill->setVictimCorpName($row['crp_name']);
            $kill->setVictimCorpID($row['crp_id']);
            $kill->setVictimAllianceName($row['all_name']);
            $kill->setVictimAllianceID($row['all_id']);
            $kill->setVictimShipName($row['shp_name']);
            $kill->setVictimShipExternalID($row['shp_externalid']);
            $kill->setVictimShipClassName($row['scl_class']);
            $kill->setVictimShipValue(round($row['scl_value'] / 1000000, 2));
            $kill->setVictimID($row['kll_victim_id']);
            $kill->setFBPilotName($row['fbplt_name']);
            $kill->setFBCorpName($row['fbcrp_name']);
            $kill->setKillPoints($row['kll_points']);
            $kill->plt_ext_ = $row['plt_externalid'];
            $kill->fbplt_ext_ = $row['fbplt_externalid'];
            $kill->_sclid = $row['scl_id'];
            $kill->_shpid = $row['shp_id'];

            return $kill;
        }
        else return null;
    }

    function getAllKills()
    {
        while ($this->getKill())
        {
        }
        $this->rewind();
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

    function setRelated($killid)
    {
        $this->related_ = $killid;
    }

    function setLimit($limit)
    {
        $this->limit_ = $limit;
    }

    function setOrderBy($orderby)
    {
        $this->orderby_ = $orderby;
    }

    function setMinKllID($id)
    {
        $this->timeframe_ = '';
        $this->minkllid_ = $id;
    }

    function getCount()
    {
        $this->execQuery();
        return $this->qry_->recordCount();
    }

    function getRealCount()
    {
        $this->execQuery();
        return $this->qry_->recordCount();
    }

    function getISK()
    {
        $this->execQuery();
        return $this->killisk_;
    }

    function getPoints()
    {
        return $this->killpoints_;
    }

    function rewind()
    {
        $this->qry_->rewind();
        $this->killcounter_ = 0;
    }

    function setPodsNoobShips($flag)
    {
        if (!$flag)
        {
            array_push($this->exclude_scl_, 2);
            array_push($this->exclude_scl_, 3);
            array_push($this->exclude_scl_, 11);
        }
    }

    function setOrdered($flag)
    {
        $this->ordered_ = $flag;
    }
}
?>