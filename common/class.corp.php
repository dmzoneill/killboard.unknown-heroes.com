<?php
require_once("db.php");
require_once("class.alliance.php");

class Corporation
{
    function Corporation($id = 0)
    {
        $this->id_ = $id;
        $this->qry_ = new DBQuery();
    }

    function isNPCCorp()
    {
        global $corp_npc;
        if (in_array($this->getName(), $corp_npc))
        {
            return true;
        }
    }

    function getPortraitURL($size = 64)
    {
        return '?a=thumb&amp;type=corp&amp;id='.$this->id_.'&amp;size='.$size;
    }

    function getID()
    {
        return $this->id_;
    }

    function getName()
    {
        $this->execQuery();
        return $this->row_['crp_name'];
    }

    function getAlliance()
    {
        $this->execQuery();
        return new Alliance($this->row_['crp_all_id']);
    }

    function lookup($name)
    {
        $qry = new DBQuery();
        $qry->execute("select * from kb3_corps
                       where crp_name = '".slashfix($name)."'");
        $row = $qry->getRow();
        if ($row['crp_id']) $this->id_ = $row['crp_id'];
    }

    function execQuery()
    {
        if (!$this->qry_->executed_)
        {
            $this->sql_ = "select * from kb3_corps
	  	                   where crp_id = ".$this->id_;
            $this->qry_->execute($this->sql_);
            $this->row_ = $this->qry_->getRow();
        }
    }

    function add($name, $alliance, $timestamp)
    {
        $qry = new DBQuery();
        $qry->execute("select * from kb3_corps
		               where crp_name = '".slashfix($name)."'");

        if ($qry->recordCount() == 0)
        {
            $qry->execute("insert into kb3_corps values ( null,'".slashfix($name)."',"
                           .$alliance->getID().",0,date_format('".$timestamp."','%Y.%m.%d %H:%i:%s'))");
            $this->id_ = $qry->getInsertID();
        }
        else
        {
            $row = $qry->getRow();
            $this->id_ = $row['crp_id'];
            if ($this->isUpdatable($timestamp) && $row['crp_all_id'] != $alliance->getID())
            {
                $qry->execute('update kb3_corps
	                           set crp_all_id = '.$alliance->getID().",
			                   crp_updated = date_format( '".$timestamp."','%Y.%m.%d %H:%i:%s')
			                   where crp_id = ".$this->id_);
            }
        }

        return $this->id_;
    }

    function isUpdatable($timestamp)
    {
        $qry = new DBQuery();
        $qry->execute("select crp_id from kb3_corps
		               where crp_id = ".$this->id_."
		               and ( crp_updated < date_format( '".$timestamp."', '%Y.%m.%d %H:%i' )
			           or crp_updated is null )");
        return $qry->recordCount() == 1;
    }
}
?>