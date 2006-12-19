<?php

/**
* Author: Doctor Z
* eMail:  east4now11@hotmail.com
*
*/

class RSSTable extends KillListTable
{
    function RSSTable($kill_list)
    {
        $this->limit = 0;
        $this->offset = 0;

        $this->kill_list_ = $kill_list;
        $this->daybreak_ = true;
    }

    function generate()
    {
        global $config;
        $odd = false;
        $prevdate = "";
        $this->kill_list_->rewind();

        while ($kill = $this->kill_list_->getKill())
        {
            $html .= "<item>
    <title>".$kill->getVictimName()." was killed</title>
    <description>
    <![CDATA[
        <p><b>Ship:</b> ".$kill->getVictimShipName()."
            <br /><b>Victim:</b> ".$kill->getVictimName()."
            <br /><b>Corp:</b> ".shorten($kill->getVictimCorpName())."
            <br /><b>System:</b> ".shorten($kill->getSolarSystemName(), 10)."
            <br />
            <br /><b>Killed By:</b>
            <br /><b>Final Blow:</b> ".$kill->getFBPilotName()."
            <br /><b>Corp:</b> ".shorten($kill->getFBCorpName())."
            <br />
        </p>
     ]]>
    </description>
    <guid>http://".KB_HOST."?a=kill_detail&amp;kll_id=".$kill->getID()."</guid>
    <pubDate>".strftime("%a, %d %b %Y %T %Z" , strtotime($kill->getTimeStamp()))."</pubDate>
</item>\n";
        }

        return $html;
    }
}
?>
