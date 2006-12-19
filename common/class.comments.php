<?php

class Comments
{

    function Comments($kll_id)
    {
        $this->id_ = $kll_id;
        $this->raw_ = false;

        $this->comments_ = array();
        $qry = new DBQuery();
        $qry->execute("SELECT *,id FROM kb3_comments WHERE `kll_id` = '".$kll_id."' order by posttime asc");
        while ($row = $qry->getRow())
        {
            $this->comments_[] = array('time' => $row['posttime'], 'name' => $row['name'], 'comment' => stripslashes($row['comment']), 'id' => $row['id']);
        }
    }

    function getComments()
    {
        global $smarty;

        $smarty->assign_by_ref('comments', $this->comments_);
        return $smarty->fetch(get_tpl('block_comments'));
    }

    function addComment($name, $text)
    {
        $comment = $this->bbencode($text);

        $name = slashfix(strip_tags($name));
        $qry = new DBQuery();
        $qry->execute("INSERT INTO kb3_comments (`kll_id`,`comment`,`name`,`posttime`)
                       VALUES ('".$this->id_."','".$comment."','".$name."','".date('Y-m-d H:i:s')."')");
        $id = $qry->getInsertID();
        $this->comments_[] = array('time' => date('Y-m-d H:i:s'), 'name' => $name, 'comment' => stripslashes($comment), 'id' => $id);
    }

    function delComment($c_id)
    {
        $qry = new DBQuery();
        $qry->execute("DELETE FROM kb3_comments WHERE id='".$c_id."' LIMIT 1");
    }

    function postRaw($bool)
    {
        $this->raw_ = $bool;
    }

    function bbencode($string)
    {
        if (!$this->raw_)
        {
            $string = strip_tags(stripslashes($string));
        }
        $string = str_replace(array('[b]','[/b]','[i]','[/i]','[u]','[/u]'),
                              array('<b>','</b>','<i>','</i>','<u>','</u>'), $string);
        $string = preg_replace('^\[color=(.*?)](.*?)\[/color]^', '<font color="\1">\2</font>', $string);
        $string = preg_replace('^\[kill=(.*?)](.*?)\[/kill]^', '<a href="\?a=kill_detail&kll_id=\1">\2</a>', $string);
        $string = preg_replace('^\[pilot=(.*?)](.*?)\[/pilot]^', '<a href="\?a=pilot_detail&plt_id=\1">\2</a>', $string);
    	return nl2br(addslashes($string));
    }
}
?>