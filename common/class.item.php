<?php
require_once("db.php");

class Item
{
    function Item($id = 0)
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
        return $this->row_['itm_name'];
    }

    function getIcon($size = 32)
    {
        $this->execQuery();
        global $smarty;

        // slot 6 is dronebay
        if ($this->row_['itt_slot'] == 6)
        {
            $img = IMG_URL.'/drones/'.$size.'_'.$size.'/'.$this->row_['itm_externalid'].'.png';
        }
        else
        {
            // fix for new db structure, just make sure old clients dont break
            if (!strstr($this->row_['itm_icon'], 'icon'))
            {
                $this->row_['itm_icon'] = 'icon'.$this->row_['itm_icon'];
            }
            $img = IMG_URL.'/items/'.$size.'_'.$size.'/'.$this->row_['itm_icon'].'.png';
        }

        if (substr($this->getName(), strlen($this->getName()) - 2, 2) == "II" || $this->row_['techlevel'] == 2)
        {
            $icon .= IMG_URL.'/items/32_32/t2.gif';
        }
        else
        {
            $icon= IMG_URL.'/items/32_32/blank.gif';
        }

        $smarty->assign('img', $img);
        $smarty->assign('icon', $icon);
        return $smarty->fetch(get_tpl('icon'));
    }

    function getSlot()
    {
        $this->execQuery();
        return $this->row_['itt_slot'];
    }

    function execQuery()
    {
        if (!$this->qry_->executed_)
        {
            if (!$this->id_)
            {
                return false;
            }
            $this->sql_ = "select *
                           from kb3_items, kb3_item_types
      		               where itm_id = '".$this->id_."'
      		               and itm_type = itt_id";
            $this->qry_->execute($this->sql_);
            $this->row_ = $this->qry_->getRow();
        }
    }

    function lookup($name)
    {
        $name = trim($name);
        $qry = new DBQuery();
        $qry->execute("select * from kb3_items itm
                        where itm_name = '".slashfix($name)."'");
        $row = $qry->getRow();
        if (!isset($row['itm_id']))
        {
            global $config;
            if ($config->getConfig('adapt_items'))
            {
                // if the item is a tec2 we likely have the tec1
                if (substr($name, -2, 2) == 'II')
                {
                    $qry->execute("select * from kb3_items itm
                                    where itm_name = '".slashfix(substr($name,0,-1))."'");
                    $row = $qry->getRow();
                    if (!$row['itm_type'])
                    {
                        return false;
                    }
                    $qry->execute("INSERT INTO kb3_items (itm_name,itm_volume,itm_type,itm_externalid,itm_techlevel,itm_icon)
                                    VALUES ('".slashfix($name)."','".$row['itm_volume']."','".$row['itm_type']."','".$row['itm_externalid']."','2','".$row['itm_icon']."')");
                }
                else
                {
                    // no idea what this is, insert as 'Temp'
                    $qry->execute("INSERT INTO kb3_items (itm_name,itm_type)
                                    VALUES ('".slashfix($name)."','721')");
                }
                $row['itm_id'] = $qry->getInsertID();
            }
        }
        $this->id_ = $row['itm_id'];
    }

    function get_item_id($name)
    {
        $qry = new DBQuery();
        $qry->execute("select *
                        from kb3_items
                        where itm_name = '".slashfix($name)."'");

        $row = $qry->getRow();
        if ($row['itm_id']) return $row['itm_id'];
    }
}
?>