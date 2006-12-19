<?php
require_once("db.php");
require_once("class.page.php");
require_once("class.ship.php");

class KillSummaryTable
{
    function KillSummaryTable($klist = null, $llist = null)
    {
        $this->klist_ = $klist;
        $this->llist_ = $llist;

        $this->verbose_ = false;
        $this->filter_ = true;
        $this->inv_crp_ = array();
        $this->inv_all_ = array();
    }

    function setBreak($break)
    {
        $this->break_ = $break;
    }

    function setVerbose($verbose)
    {
        $this->verbose_ = $verbose;
    }

    function setFilter($filter)
    {
        $this->filter_ = $filter;
    }

    function getTotalKills()
    {
        return $this->tkcount_;
    }

    function getTotalLosses()
    {
        return $this->tlcount_;
    }

    function getTotalKillPoints()
    {
        return $this->tkpoints_;
    }

    function getTotalLossPoints()
    {
        return $this->tlpoints_;
    }

    function getTotalKillISK()
    {
        return $this->tkisk_;
    }

    function getTotalLossISK()
    {
        return $this->tlisk_;
    }

    function setView($string)
    {
        $this->view_ = $string;
    }

    function addInvolvedCorp($corp)
    {
        $this->inv_crp_[] = $corp->getID();
        if ($this->inv_plt_ || $this->inv_all_)
        {
            $this->mixedinvolved_ = true;
        }
    }

    function addInvolvedAlliance($alliance)
    {
        $this->inv_all_[] = $alliance->getID();
        if ($this->inv_plt_ || $this->inv_crp_)
        {
            $this->mixedinvolved_ = true;
        }
    }

    // do it faster, baby!
    function getkills()
    {
        global $config;
        if ($this->mixedinvolved_)
        {
            echo 'mode not supported<br>';
            exit;
        }

        $this->entry_ = array();
        // as there is no way to do this elegant in sql
        // i'll keep it in php
        $sql = "select scl_id, scl_class from kb3_ship_classes
               where scl_class not in ('Drone','Unknown') order by scl_class";

        $qry = new DBQuery();
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $this->entry_[$row['scl_class']] = array('id' => $row['scl_id'],
                                                     'kills' => 0, 'kills_isk' => 0,
                                                     'losses' => 0, 'losses_isk' => 0);
        }

        $sql = 'SELECT count(*) AS knb, scl_id, scl_class,';
        if ($config->getConfig('ship_values'))
        {
            $sql .= ' sum(ifnull(ksv.shp_value,scl.scl_value)) AS kisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
                    left join kb3_ships_values ksv on (shp.shp_id = ksv.shp_id)';
        }
        else
        {
            $sql .= ' sum(scl.scl_value) AS kisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )';
        }
        $sql .= ' INNER JOIN kb3_ship_classes scl ON ( scl.scl_id = shp.shp_class )';

        if ($this->inv_crp_)
        {
            $sql .= ' inner join kb3_inv_crp inc on ( inc.inc_crp_id in ( '.join(',', $this->inv_crp_).' ) and kll.kll_id = inc.inc_kll_id ) ';
        }
        elseif ($this->inv_all_)
        {
            $sql .= ' inner join kb3_inv_all ina on ( ina.ina_all_id in ( '.join(',', $this->inv_all_).' ) and kll.kll_id = ina.ina_kll_id ) ';
        }
        $sql .= 'GROUP BY scl_class order by scl_class';

        $qry = new DBQuery();
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $this->entry_[$row['scl_class']]['kills'] = $row['knb'];
            $this->entry_[$row['scl_class']]['kills_isk'] = $row['kisk'];
            $this->tkcount_ += $row['knb'];
            $this->tkisk_ += $row['kisk'];
        }

        $sql = 'SELECT count(*) AS lnb, scl_id, scl_class,';
        if ($config->getConfig('ship_values'))
        {
            $sql .= ' sum(ifnull(ksv.shp_value,scl.scl_value)) AS lisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )
                    left join kb3_ships_values ksv on (shp.shp_id = ksv.shp_id)';
        }
        else
        {
            $sql .= ' sum(scl.scl_value) AS lisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )';
        }
        $sql .= ' INNER JOIN kb3_ship_classes scl ON ( scl.scl_id = shp.shp_class )';

        if ($this->inv_crp_)
        {
            $sql .= ' where kll.kll_crp_id in ( '.join(',', $this->inv_crp_).' ) ';
        }
        elseif ($this->inv_all_)
        {
            $sql .= ' where kll.kll_all_id in ( '.join(',', $this->inv_all_).' ) ';
        }
        $sql .= 'GROUP BY scl_class order by scl_class';

        $qry = new DBQuery();
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $this->entry_[$row['scl_class']]['losses'] = $row['lnb'];
            $this->entry_[$row['scl_class']]['losses_isk'] =  $row['lisk'];

            $this->tlcount_ += $row['lnb'];
            $this->tlisk_ += $row['lisk'];
        }
    }

    function generate()
    {
        if ($this->klist_)
        {
            $entry = array();
            // build array
            $sql = "select scl_id, scl_class
                    from kb3_ship_classes
                   where scl_class not in ( 'Drone', 'Unknown' )
                  order by scl_class";

            $qry = new DBQuery();
            $qry->execute($sql) or die($qry->getErrorMsg());
            while ($row = $qry->getRow())
            {
                if (!$row['scl_id'])
                    continue;

                $shipclass = new ShipClass($row['scl_id']);
                $shipclass->setName($row['scl_class']);

                $entry[$shipclass->getName()]['id'] = $row['scl_id'];
                $entry[$shipclass->getName()]['kills'] = 0;
                $entry[$shipclass->getName()]['kills_isk'] = 0;
                $entry[$shipclass->getName()]['losses'] = 0;
                $entry[$shipclass->getName()]['losses_isk'] = 0;
            }
            // kills
            while ($kill = $this->klist_->getKill())
            {
                $classname = $kill->getVictimShipClassName();
                $entry[$classname]['kills']++;
                $entry[$classname]['kills_isk'] += $kill->getVictimShipValue();
                $this->tkcount_++;
                $this->tkisk_ += $kill->getVictimShipValue();
            }
            // losses
            while ($kill = $this->llist_->getKill())
            {
                $classname = $kill->getVictimShipClassName();
                $entry[$classname]['losses']++;
                $entry[$classname]['losses_isk'] += $kill->getVictimShipValue();
                $this->tlcount_++;
                $this->tlisk_ += $kill->getVictimShipValue();
            }
        }
        else
        {
            $this->getkills();
            $entry = &$this->entry_;
        }

        $odd = false;
        $prevdate = "";
        $html .= "<table class=kb-subtable width=\"100%\" border=\"0\" cellspacing=0>";
        if ($this->break_)
            $html .= "<tr><td valign=top><table class=kb-table cellspacing=\"1\" width=\"100%\">";
        $counter = 1;

        if ($this->verbose_)
        {
            $header = "<tr class=kb-table-header><td class=kb-table-cell width=110>Ship class</td><td class=kb-table-cell width=60 align=center>Kills</td><td class=kb-table-cell width=60 align=center>ISK (M)</td><td class=kb-table-cell width=60 align=center>Losses</td><td class=kb-table-cell width=60 align=center>ISK (M)</td></tr>";
        }
        else
        {
            $header = "<tr class=kb-table-header><td class=kb-table-cell width=110>Ship class</td><td class=kb-table-cell width=30 align=center>K</td><td class=kb-table-cell width=30 align=center>L</td></tr>";
        }
        $html .= $header;

        foreach ($entry as $k => $v)
        {
            if (!$v['id'] || $v['id'] == 3)
                continue;
            if ($this->break_ && $counter > $this->break_)
            {
                $html .= "</table></td>";
                $html .= "<td valign=top><table class=kb-table cellspacing=\"1\">";
                $html .= $header;
                $counter = 1;
            }

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

            if ($_GET['scl_id'] != "" && $v['id'] == $_GET['scl_id'])
                $highlight = "-hl";
            else
                $highlight = "";

            if ($v['kills'] == 0)
                $kclass = "kl-kill-null";
            else
                $kclass = "kl-kill";

            if ($v['losses'] == 0)
                $lclass = "kl-loss-null";
            else
                $lclass = "kl-loss";

            if ($this->verbose_)
            {
                $kclass .= "-bg";
                $lclass .= "-bg";
            }

            $html .= "<tr class=" . $class . ">";

            $qrystring = preg_replace("/&scl_id=([0-9]?[0-9])/", "", $_SERVER['QUERY_STRING']);
            $qrystring = preg_replace("/&page=([0-9]?[0-9])/", "", $qrystring);
            if ($this->view_)
            {
                $qrystring .= '&view='.$this->view_;
            }
            $html .= "<td class=kb-table-cell><b>";

            if ($this->filter_) $html .= "<a class=kb-shipclass" . $highlight . " href=\"?" . $qrystring . "&scl_id=" . $v['id'] . "\">";

            $html .= $k;

            if ($this->filter_) $html .= "</a>";

            $html .= "</b></td>";

            $html .= "<td class=" . $kclass . " align=center>" . $v['kills'] . "</td>";
            if ($this->verbose_)
                $html .= "<td class=" . $kclass . " align=center>" . $v['kills_isk'] . "</td>";
            $html .= "<td class=" . $lclass . " align=center>" . $v['losses'] . "</td>";
            if ($this->verbose_)
                $html .= "<td class=" . $lclass . " align=center>" . $v['losses_isk'] . "</td>";

            $html .= "</tr>";

            $counter++;
            $this->tkcount_ += $kcount;
            $this->tlcount_ += $lcount;
            $this->tkisk_ += $kisk;
            $this->tlisk_ += $lisk;
            $this->tkpoints_ += $kpoints;
            $this->tlpoints_ += $lpoints;
        }
        if ($this->break_)
            $html .= "</table></td>";

        $html .= "</tr></table>";

        if ($_GET['scl_id'] != "")
        {
            $html .= "<table align=center><tr><td align=center valign=top class=weeknav>";
            $qrystring = preg_replace("/&scl_id=([0-9]?[0-9])/", "", $_SERVER['QUERY_STRING']);
            $html .= "[<a href=\"?" . $qrystring . "\">clear filter</a>]</td></tr></table>";
        }

        return $html;
    }
}
?>