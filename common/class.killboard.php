<?php
require_once("db.php");

class Killboard
{
    function Killboard($site)
    {
        $this->qry_ = new DBQuery();

        $this->site_ = $site;
        $this->config_ = new Config($site);
    }

    function isSuspended()
    {
        $this->execQuery();
        return $this->row_['rtl_suspended'] == "1";
    }

    function getConfig()
    {
        $this->execQuery();
        return $this->config_;
    }

    function hasCampaigns($active = false)
    {
        $qry = new DBQuery();
        $sql = "select ctr_id
                 from kb3_contracts
	         where ctr_campaign = 1
	           and ctr_site = '".$this->site_."'";
        if ($active) $sql .= " and ctr_ended is null";
        $qry->execute($sql);
        return ($qry->recordCount() > 0);
    }

    function hasContracts($active = false)
    {
        $qry = new DBQuery();
        $sql = "select ctr_id
                 from kb3_contracts
                 where ctr_campaign = 0
                   and ctr_site = '".$this->site_."'";
        if ($active) $sql .= " and ( ctr_ended is null or now() <= ctr_ended )";
        $qry->execute($sql);
        return ($qry->recordCount() > 0);
    }

    function execQuery()
    {
    }
}

class Config
{
    function Config($site)
    {
        $this->qry_ = new DBQuery();
        $this->sql_ = "select * from kb3_config where cfg_site = '".$site."'";

        $this->site_ = $site;
    }

    function getStyleName()
    {
        $this->execQuery();
        return $this->config['style_name'];
    }

    function getStyleBanner()
    {
        $this->execQuery();
        return $this->config['style_banner'];
    }

    function getPostPassword()
    {
        $this->execQuery();
        return $this->config['post_password'];
    }

    function getPostMailto()
    {
        $this->execQuery();
        return $this->config['post_mailto'];
    }

    function getKillPoints()
    {
        $this->execQuery();
        return $this->config['kill_points'];
    }

    function getLeastActive()
    {
        $this->execQuery();
        return $this->config['least_active'];
    }

    function getConfig($key)
    {
        $this->execQuery();
        if (isset($this->config[$key]))
        {
            return $this->config[$key];
        }
        return false;
    }

    function setConfig($key, $value)
    {
        $qry = new DBQuery();
        $qry->execute("select cfg_value from kb3_config
                       where cfg_key = '".$key."'
		               and cfg_site = '".$this->site_."'");
        if ($qry->recordCount())
        {
            $sql = "update kb3_config
                    set cfg_value = '".$value."'
                    where cfg_site = '".$this->site_."'
	                and cfg_key = '".$key."'";
        }
        else
        {
            $sql = "insert into kb3_config values ( '".$this->site_."',
	                                        '".$key."',
	                                        '".$value."' )";
        }
        $qry->execute($sql) or die($qry->getErrorMsg());
        $this->config[$key] = $value;
    }

    function delConfig($key)
    {
        $qry = new DBQuery();
        $qry->execute("delete from kb3_config where cfg_key = '".$key."'
        		       and cfg_site = '".$this->site_."'");
        if (isset($this->config[$key]))
        {
            unset($this->config[$key]);
        }
    }

    function checkCheckbox($name)
    {
        if ($_POST[$name] == 'on')
        {
            $this->setConfig($name, '1');
            return true;
        }
        $this->setConfig($name, '0');
        return false;
    }

    function setStyleName($name)
    {
        $this->setConfig("style_name", $name);
    }

    function setStyleBanner($banner)
    {
        $this->setConfig("style_banner", $banner);
    }

    function setPostPassword($password)
    {
        $this->setConfig("post_password", $password);
    }

    function setPostMailto($mailto)
    {
        $this->setConfig("post_mailto", $mailto);
    }

    function setKillPoints($flag)
    {
        $this->setConfig("kill_points", $flag);
    }

    function setLeastActive($flag)
    {
        $this->setConfig("least_active", $flag);
    }

    function execQuery()
    {
        if (!$this->qry_->executed_)
        {
            $this->qry_->execute($this->sql_);

            $this->config = array();
            while ($row = $this->qry_->getRow())
            {
                $this->config[$row['cfg_key']] = $row['cfg_value'];
            }
            if (count($this->config) == 0)
            {
                // no config supplied, generate standard one

                $this->setConfig('style_name', 'default');
                $this->setConfig('style_banner', 'default');
                $this->setConfig('kill_points', 1);
                $this->setConfig('least_active', 0);
                $this->setConfig('post_password', 'CHANGEME');
            }
        }
    }
}
?>