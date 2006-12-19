<?php
require_once('config.php');
require_once('globals.php');
require_once('php_compat.php');

class DBConnection
{
    function DBConnection()
    {
        if (!$this->id_ = mysql_pconnect(DB_HOST, DB_USER, DB_PASS))
            die("Unable to connect to mysql database.");

        mysql_select_db(DB_NAME);
    }

    function id()
    {
        return $this->id_;
    }

    function affectedRows()
    {
        return mysql_affected_rows($this->id_);
    }
}

class DBQuery
{
    function DBQuery()
    {
        $this->executed_ = false;
        $this->dbconn_ = new DBConnection;
    }

    function execute($sql)
    {
        $t1 = strtok(microtime(), ' ') + strtok('');

        $this->resid_ = mysql_query($sql, $this->dbconn_->id());

        if ($this->resid_ == false)
        {
            if (defined('DB_HALTONERROR') && DB_HALTONERROR)
            {
                echo "Database error: " . mysql_error($this->dbconn_->id()) . "<br>";
                echo "SQL: " . $sql . "<br>";
                exit;
            }
            else
            {
                return false;
            }
        }

        $this->exectime_ = strtok(microtime(), ' ') + strtok('') - $t1;
        $this->executed_ = true;

        if (KB_PROFILE == 2)
        {
            file_put_contents('/tmp/profile.lst', $sql . "\nExecution time: " . $this->exectime_ . "\n", FILE_APPEND);
        }

        return true;
    }

    function recordCount()
    {
        return mysql_num_rows($this->resid_);
    }

    function getRow()
    {
        if ($this->resid_)
        {
            return mysql_fetch_assoc($this->resid_);
        }
        return false;
    }

    function rewind()
    {
        @mysql_data_seek($this->resid_, 0);
    }

    function getInsertID()
    {
        return mysql_insert_id();
    }

    function execTime()
    {
        return $this->exectime_;
    }

    function executed()
    {
        return $this->executed_;
    }

    function getErrorMsg()
    {
        $msg = $this->sql_ . "<br>";
        $msg .= "Query failed. " . mysql_error($this->dbconn_->id());

        return $msg;
    }
}
?>