<?php
require_once("db.php");
require_once("class.killlist.php");
require_once("class.graph.php");
require_once("class.pagesplitter.php");

class Contract
{
    function Contract($ctr_id = 0)
    {
        $this->ctr_id_ = $ctr_id;
        $this->contracttargets_ = array();

        // overall kill/losslist
        $this->klist_ = new KillList();
        $this->llist_ = new KillList();
        if (CORP_ID)
        {
            $this->klist_->addInvolvedCorp(new Corporation(CORP_ID));
            $this->llist_->addVictimCorp(new Corporation(CORP_ID));
        }
        if (ALLIANCE_ID)
        {
            $this->klist_->addInvolvedAlliance(new Alliance(ALLIANCE_ID));
            $this->llist_->addVictimAlliance(new Alliance(ALLIANCE_ID));
        }
        $this->contractpointer_ = 0;
        $this->qry_ = null;
    }

    function execQuery()
    {
        if ($this->qry_)
            return;

        $this->qry_ = new DBQuery();
        // general
        $sql = "select * from kb3_contracts ctr
                where ctr.ctr_id = ".$this->ctr_id_;

        $this->qry_ = new DBQuery();
        if (!$this->qry_->execute($sql))
            die($this->qry_->getErrorMsg());

        $row = $this->qry_->getRow();
        $this->ctr_name_ = $row['ctr_name'];
        $this->ctr_started_ = $row['ctr_started'];
        $this->ctr_ended_ = $row['ctr_ended'];
        $this->campaign_ = ($row['ctr_campaign'] == "1");

        // get corps & alliances for contract
        $sql = "select ctd.ctd_crp_id, ctd.ctd_all_id, ctd.ctd_reg_id, ctd.ctd_sys_id
                from kb3_contract_details ctd
                where ctd.ctd_ctr_id = ".$row['ctr_id']."
	            order by 3, 2, 1";

        $caqry = new DBQuery();
        if (!$caqry->execute($sql))
        {
            include_once('autoupgrade.php');
            check_contracts();
            $caqry->execute($sql);
        }

        while ($carow = $caqry->getRow())
        {
            $contracttarget = &new ContractTarget($this, $carow['ctd_crp_id'], $carow['ctd_all_id'], $carow['ctd_reg_id'], $carow['ctd_sys_id']);
            array_push($this->contracttargets_, $contracttarget);
            if ($carow['ctd_crp_id'])
            {
                $this->klist_->addVictimCorp(new Corporation($carow['ctd_crp_id']));
                $this->llist_->addInvolvedCorp(new Corporation($carow['ctd_crp_id']));
            }
            elseif ($carow['ctd_all_id'])
            {
                $this->klist_->addVictimAlliance(new Alliance($carow['ctd_all_id']));
                $this->llist_->addInvolvedAlliance(new Alliance($carow['ctd_all_id']));
            }
            elseif ($carow['ctd_reg_id'])
            {
                $this->klist_->addRegion(new Region($carow['ctd_reg_id']));
                $this->llist_->addRegion(new Region($carow['ctd_reg_id']));
            }
            elseif ($carow['ctd_sys_id'])
            {
                $this->klist_->addSystem(new SolarSystem($carow['ctd_sys_id']));
                $this->llist_->addSystem(new SolarSystem($carow['ctd_sys_id']));
            }
        }

        $this->klist_->setStartDate($this->getStartDate());
        $this->llist_->setStartDate($this->getStartDate());
        if ($this->getEndDate() != "")
        {
            $this->klist_->setEndDate($this->getEndDate());
            $this->llist_->setEndDate($this->getEndDate());
        }
    }

    function getID()
    {
        return $this->ctr_id_;
    }

    function getName()
    {
        $this->execQuery();
        return $this->ctr_name_;
    }

    function getStartDate()
    {
        $this->execQuery();
        return $this->ctr_started_;
    }

    function getEndDate()
    {
        $this->execQuery();
        return $this->ctr_ended_;
    }

    function getRunTime()
    {
        if (!$datet = $this->getEndDate())
        {
            $datet = 'now';
        }

        $diff = strtotime($datet) - strtotime($this->getStartDate());
        return floor($diff/86400);
    }

    function getCampaign()
    {
        $this->execQuery();
        return $this->campaign_;
    }

    function getCorps()
    {
        $this->execQuery();
        return $this->corps_;
    }

    function getAlliances()
    {
        $this->execQuery();
        return $this->alliances_;
    }

    function getKills()
    {
        $this->execQuery();
        return $this->klist_->getCount();
    }

    function getLosses()
    {
        $this->execQuery();
        return $this->llist_->getCount();
    }

    function getKillISK()
    {
        $this->execQuery();
        if (!$this->klist_->getISK()) $this->klist_->getAllKills();
        return $this->klist_->getISK();
    }

    function getLossISK()
    {
        $this->execQuery();
        if (!$this->llist_->getISK()) $this->llist_->getAllKills();
        return $this->llist_->getISK();
    }

    function getEfficiency()
    {
        $this->execQuery();
        if ($this->klist_->getISK())
            $efficiency = round($this->klist_->getISK() / ($this->klist_->getISK() + $this->llist_->getISK()) * 100, 2);
        else
            $efficiency = 0;

        return $efficiency;
    }

    function getKillList()
    {
        $this->execQuery();
        return $this->klist_;
    }

    function getLossList()
    {
        $this->execQuery();
        return $this->llist_;
    }

    function getContractTarget()
    {
        if ($this->contractpointer_ > 30)
            return null;

        $target = $this->contracttargets_[$this->contractpointer_];
        if ($target)
            $this->contractpointer_++;
        return $target;
    }

    function add($name, $type, $startdate, $enddate = "")
    {
        $qry = new DBQuery();
        if ($type == "campaign") $campaign = 1;
        else $campaign = 0;
        if ($enddate != "") $enddate = "'".$enddate." 23:59:59'";
        else $enddate = "null";

        if (!$this->ctr_id_)
        {
            $sql = "insert into kb3_contracts values ( null, '".$name."',
                                                   '".KB_SITE."', ".$campaign.",
						   '".$startdate." 00:00:00',
						   ".$enddate." )";
            $qry->execute($sql) or die($qry->getErrorMsg());
            $this->ctr_id_ = $qry->getInsertID();
        }
        else
        {
            $sql = "update kb3_contracts set ctr_name = '".$name."',
			                 ctr_started = '".$startdate." 00:00:00',
					 ctr_ended = ".$enddate."
				     where ctr_id = ".$this->ctr_id_;
            $qry->execute($sql) or die($qry->getErrorMsg());
            $this->ctr_id_ = $qry->getInsertID();
        }
    }

    function remove()
    {
        $qry = new DBQuery();

        $qry->execute("delete from kb3_contracts
                       where ctr_id = ".$this->ctr_id_);

        $qry->execute("delete from kb3_contract_details
                       where ctd_ctr_id = ".$this->ctr_id_);
    }

    function validate()
    {
        $qry = new DBQuery();

        $qry->execute("select * from kb3_contracts
                       where ctr_id = ".$this->ctr_id_."
		         and ctr_site = '".KB_SITE."'");
        return ($qry->recordCount() > 0);
    }
}

class ContractTarget
{
    function ContractTarget($contract, $crp_id, $all_id, $reg_id , $sys_id)
    {
        $this->contract_ = $contract;
        $this->crp_id_ = $crp_id;
        $this->all_id_ = $all_id;
        $this->reg_id_ = $reg_id;
        $this->sys_id_ = $sys_id;

        $this->klist_ = &new KillList();
        $this->llist_ = &new KillList();

        if ($this->crp_id_)
        {
            $this->type_ = "corp";
            $this->klist_->addVictimCorp(new Corporation($this->crp_id_));
            $this->llist_->addInvolvedCorp(new Corporation($this->crp_id_));
            $this->id_ = $this->crp_id_;
        }
        elseif ($this->all_id_)
        {
            $this->type_ = "alliance";
            $this->klist_->addVictimAlliance(new Alliance($this->all_id_));
            $this->llist_->addInvolvedAlliance(new Alliance($this->all_id_));
            $this->id_ = $this->all_id_;
        }
        elseif ($this->reg_id_)
        {
            $this->type_ = "region";
            $this->klist_->addRegion(new Region($this->reg_id_));
            $this->llist_->addRegion(new Region($this->reg_id_));
            $this->id_ = $this->reg_id_;
        }
        elseif ($this->sys_id_)
        {
            $this->type_ = "system";
            $this->klist_->addSystem(new SolarSystem($this->sys_id_));
            $this->llist_->addSystem(new SolarSystem($this->sys_id_));
            $this->id_ = $this->sys_id_;
        }
        if (CORP_ID)
        {
            $this->klist_->addInvolvedCorp(new Corporation(CORP_ID));
            $this->llist_->addVictimCorp(new Corporation(CORP_ID));
        }
        if (ALLIANCE_ID)
        {
            $this->klist_->addInvolvedAlliance(new Alliance(ALLIANCE_ID));
            $this->llist_->addVictimAlliance(new Alliance(ALLIANCE_ID));
        }

        $this->klist_->setStartDate($contract->getStartDate());
        $this->llist_->setStartDate($contract->getStartDate());
        if ($contract->getEndDate() != "")
        {
            $this->klist_->setEndDate($contract->getEndDate());
            $this->llist_->setEndDate($contract->getEndDate());
        }
    }

    function getID()
    {
        return $this->id_;
    }

    function getName()
    {
        if ($this->name_ == "")
        {
            $qry = new DBQuery();
            switch ($this->type_)
            {
                case "corp":
                    $qry->execute("select crp_name as name from kb3_corps where crp_id = ".$this->crp_id_);
                    break;
                case "alliance":
                    $qry->execute("select all_name as name from kb3_alliances where all_id = ".$this->all_id_);
                    break;
                case "region":
                    $qry->execute("select reg_name as name from kb3_regions where reg_id = ".$this->reg_id_);
                    break;
                case "system":
                    $qry->execute("select sys_name as name from kb3_systems where sys_id = ".$this->sys_id_);
                    break;
            }
            $row = $qry->getRow();
            $this->name_ = $row['name'];
        }
        return $this->name_;
    }

    function getType()
    {
        return $this->type_;
    }

    function getKillList()
    {
        return $this->klist_;
    }

    function getLossList()
    {
        return $this->llist_;
    }

    function getEfficiency()
    {
        if ($this->klist_->getISK())
            $efficiency = round($this->klist_->getISK() / ($this->klist_->getISK() + $this->llist_->getISK()) * 100, 2);
        else
            $efficiency = 0;

        return $efficiency;
    }

    function getKills()
    {
    }

    function getLosses()
    {
    }

    function add()
    {
        $qry = new DBQuery();
        $sql = "insert into kb3_contract_details
                     values ( ".$this->contract_->getID().",";
        switch ($this->type_)
        {
            case "corp":
                $sql .= $this->id_.", 0, 0, 0 )";
                break;
            case "alliance":
                $sql .= "0, ".$this->id_.", 0, 0 )";
                break;
            case "region":
                $sql .= "0, 0, ".$this->id_.",0 )";
                break;
            case "system":
                $sql .= "0, 0, 0, ".$this->id_." )";
                break;
        }
        $qry->execute($sql) or die($qry->getErrorMsg());
    }

    function remove()
    {
        $qry = new DBQuery();
        $sql = "delete from kb3_contract_details
                    where ctd_ctr_id = ".$this->contract_->getID();
        switch ($this->type_)
        {
            case "corp":
                $sql .= " and ctd_crp_id = ".$this->id_;
                break;
            case "alliance":
                $sql .= " and ctd_all_id = ".$this->id_;
                break;
            case "region":
                $sql .= " and ctd_reg_id = ".$this->id_;
                break;
            case "system":
                $sql .= " and ctd_sys_id = ".$this->id_;
                break;
        }
        $qry->execute($sql) or die($qry->getErrorMsg());
    }
}

class ContractList
{
    function ContractList()
    {
        $this->qry_ = new DBQuery();
        $this->active_ = "both";
        $this->contractcounter_ = 1;
    }

    function execQuery()
    {
        if ($this->qry_->executed())
            return;

        $sql = "select ctr.ctr_id, ctr.ctr_started, ctr.ctr_ended, ctr.ctr_name
                from kb3_contracts ctr
               where ctr.ctr_site = '".KB_SITE."'";
        if ($this->active_ == "yes")
            $sql .= " and ( ctr_ended is null or now() <= ctr_ended )";
        elseif ($this->active_ == "no")
            $sql .= " and ( now() >= ctr_ended )";

        if ($this->campaigns_)
            $sql .= " and ctr.ctr_campaign = 1";
        else
            $sql .= " and ctr.ctr_campaign = 0";

        $sql .= " order by ctr_ended, ctr_started desc";
        // if ( $this->limit_ )
        // $sql .= " limit ".( $this->page_ / $this->limit_ ).", ".$this->limit_;
        $this->qry_ = new DBQuery();
        $this->qry_->execute($sql) or die($this->qry_->getErrorMsg());
    }

    function setActive($active)
    {
        $this->active_ = $active;
    }

    function setCampaigns($campaigns)
    {
        $this->campaigns_ = $campaigns;
    }

    function setLimit($limit)
    {
        $this->limit_ = $limit;
    }

    function setPage($page)
    {
        $this->page_ = $page;
        $this->offset_ = ($page * $this->limit_) - $this->limit_;
    }

    function getContract()
    {
        // echo "off: ".$this->offset_."<br>";
        // echo "cnt: ".$this->contractcounter_."<br>";
        // echo "limit: ".$this->limit_."<br>";
        $this->execQuery();
        if ($this->offset_ && $this->contractcounter_ < $this->offset_)
        {
            for ($i = 0; $i < $this->offset_; $i++)
            {
                $row = $this->qry_->getRow();
                $this->contractcounter_++;
            }
        }
        if ($this->limit_ && ($this->contractcounter_ - $this->offset_) > $this->limit_)
            return null;

        $row = $this->qry_->getRow();
        if ($row)
        {
            $this->contractcounter_++;
            return new Contract($row['ctr_id']);
        }
        else
            return null;
    }

    function getCount()
    {
        $this->execQuery();
        return $this->qry_->recordCount();
    }

    function getActive()
    {
        return $this->active_;
    }
}

class ContractListTable
{
    function ContractListTable($contractlist)
    {
        $this->contractlist_ = $contractlist;
    }

    function paginate($paginate, $page = 1)
    {
        if (!$page) $page = 1;
        $this->paginate_ = $paginate;
        $this->contractlist_->setLimit($paginate);
        $this->contractlist_->setPage($page);
    }

    function generate()
    {
        if ($this->contractlist_->getCount())
        {
            $rowid = 0;
            $html .= "<table class=kb-table width=\"98%\" align=center cellspacing=1>";
            $html .= "<tr class=kb-table-header>";
            $html .= "<td class=kb-table-cell width=180>Name</td>";
            $html .= "<td class=kb-table-cell width=80 align=center>Start date</td>";
            if ($this->contractlist_->getActive() == "no")
                $html .= "<td class=kb-table-cell width=80 align=center>End date</td>";
            $html .= "<td class=kb-table-cell width=50 align=center>Kills</td>";
            $html .= "<td class=kb-table-cell width=70 align=center>ISK (M)</td>";
            $html .= "<td class=kb-table-cell width=50 align=center>Losses</td>";
            $html .= "<td class=kb-table-cell width=70 align=center>ISK (M)</td>";
            $html .= "<td class=kb-table-cell width=70 align=center colspan=2>Efficiency</td>";
            $html .= "</tr>";

            $odd = false;
            $rowclass = "kb-table-row-even";
            while ($contract = $this->contractlist_->getContract())
            {
                if ($odd)
                {
                    $rowclass = "kb-table-row-even";
                    $odd = false;
                }
                else
                {
                    $rowclass = "kb-table-row-odd";
                    $odd = true;
                }

                $html .= "<tr class=".$rowclass." onmouseover=\"this.className='kb-table-row-hover';\" onmouseout=\"this.className='".$rowclass."';\" onClick=\"window.location.href='?a=cc_detail&ctr_id=".$contract->getID()."';\">";
                $html .= "<td class=kb-table-cell><b>".$contract->getName()."</b></td>";
                $html .= "<td class=kb-table-cell align=center>".substr($contract->getStartDate(), 0, 10)."</td>";
                if ($this->contractlist_->getActive() == "no")
                {
                    if ($contract->getEndDate() == "")
                        $ended = "Active";
                    else
                        $ended = substr($contract->getEndDate(), 0, 10);
                    $html .= "<td class=kb-table-cell align=center>".$ended."</td>";
                }
                if ($contract->getKills() == 0)
                    $kclass = "kl-null";
                else
                    $kclass = "kl-kill";

                if ($contract->getLosses() == 0)
                    $lclass = "kl-null";
                else
                    $lclass = "kl-loss";

                $html .= "<td class=".$kclass." align=center>".$contract->getKills()."</td>";
                $html .= "<td class=".$kclass." align=center>".$contract->getKillISK()."</td>";
                $html .= "<td class=".$lclass." align=center>".$contract->getLosses()."</td>";
                $html .= "<td class=".$lclass." align=center>".$contract->getLossISK()."</td>";
                $bar = new BarGraph($contract->getEfficiency(), 100, 75);
                $html .= "<td class=kb-table-cell align=center width=40><b>".$contract->getEfficiency()."%</b></td>";
                $html .= "<td class=kb-table-cell align=left width=75>".$bar->generate()."</td>";
                $html .= "</tr>";
            }
            $html .= "</table>";
            $pagesplitter = new PageSplitter($this->contractlist_->getCount(), 10);
            $html .= $pagesplitter->generate();
        }
        else $html .= "None.";

        return $html;
    }
}
?>