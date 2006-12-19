<?php
require_once("db.php");
require_once("class.corp.php");
require_once("class.item.php");

class Pilot
{
    function Pilot($id = 0)
    {
        $this->id_ = $id;
        $this->qry_ = new DBQuery();
    }

    function getID()
    {
        return $this->id_;
    }

    function getName()
    {
        $this->execQuery();
        $pos = strpos($this->row_['plt_name'], "#");
        if ($pos === false)
        {
            return $this->row_['plt_name'];
        }
        else
        {
            $name = explode("#", $this->row_['plt_name']);
            $item = new Item($name[2]);
            return $item->getName();
        }
    }

    function getPortraitURL($size = 64)
    {
        $this->execQuery();
        return '?a=thumb&amp;id='.$this->row_['plt_externalid'].'&amp;size='.$size;
    }

    function execQuery()
    {
        if (!$this->qry_->executed_)
        {
            $this->sql_ = 'select * from kb3_pilots plt, kb3_corps crp, kb3_alliances ali
            	  	       where crp.crp_id = plt.plt_crp_id
            		       and ali.all_id = crp.crp_all_id
            			   and plt.plt_id = '.$this->id_;
            $this->qry_->execute($this->sql_) or die($this->qry_->getErrorMsg());
            $this->row_ = $this->qry_->getRow();
            if (!$this->row_)
                $this->valid_ = false;
            else
                $this->valid_ = true;
        }
    }

    function getCorp()
    {
        $this->execQuery();
        return new Corporation($this->row_['plt_crp_id']);
    }

    function exists()
    {
        $this->execQuery();
        return $this->valid_;
    }

    function add($name, $corp, $timestamp)
    {
        $qry = new DBQuery();
        $qry->execute("select *
                        from kb3_pilots
                       where plt_name = '".slashfix($name)."'");

        if ($qry->recordCount() == 0)
        {
            $qry->execute("insert into kb3_pilots values ( null,
                                                        '".slashfix($name)."',
                                                        ".$corp->getID().",
                                                        0, 0, 0,
                                                        date_format( '".$timestamp."', '%Y.%m.%d %H:%i:%s'))");
            $this->id_ = $qry->getInsertID();
        }
        else
        {
            $row = $qry->getRow();
            $this->id_ = $row['plt_id'];
            if ($this->isUpdatable($timestamp) && $row['plt_crp_id'] != $corp->getID())
            {
                $qry->execute("update kb3_pilots
                             set plt_crp_id = ".$corp->getID().",
                                 plt_updated = date_format( '".$timestamp."', '%Y.%m.%d %H:%i:%s') where plt_id = ".$this->id_);
            }
        }

        return $this->id_;
    }

    function isUpdatable($timestamp)
    {
        $qry = new DBQuery();
        $qry->execute("select plt_id
                        from kb3_pilots
                       where plt_id = ".$this->id_."
                         and ( plt_updated < date_format( '".$timestamp."', '%Y.%m.%d %H:%i')
                               or plt_updated is null )");

        return $qry->recordCount() == 1;
    }

    function setCharacterID($id)
    {
        $qry = new DBQuery();
        $qry->execute("update kb3_pilots set plt_externalid = ".$id."
                       where plt_id = ".$this->id_) or die($qry->getErrorMsg());
    }
}
?>