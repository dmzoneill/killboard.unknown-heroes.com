<?php
require_once("db.php");

class Alliance
{
    function Alliance($id = null)
    {
        $this->id_ = $id;
        $this->qry_ = new DBQuery();

        $this->sql_ = "select * from kb3_alliances where all_id = " . $this->id_;
    }

    function getID()
    {
        return $this->id_;
    }

    function getUnique()
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', $this->getName());
    }

    function getName()
    {
        $this->execQuery();
        return $this->row_['all_name'];
    }

    function execQuery()
    {
        if (!$this->qry_->executed_)
        {
            $this->qry_->execute($this->sql_);
            $this->row_ = $this->qry_->getRow();
        }
    }

    function add($name)
    {
        $qry = new DBQuery();
        $qry->execute("select * from kb3_alliances where all_name = '".slashfix($name)."'");

        if ($qry->recordCount() == 0)
        {
            $qry->execute("insert into kb3_alliances (all_id,all_name) values (null,'".slashfix($name)."')");
            $this->id_ = $qry->getInsertID();
        }
        else
        {
            $row = $qry->getRow();
            $this->id_ = $row['all_id'];
        }
    }
}
?>