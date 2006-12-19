<?php
// liq's feed syndication mod v1.3

@set_time_limit(0);
require_once("class.corp.php");
require_once("class.alliance.php");
require_once("class.killlist.php");
require_once("class.kill.php");

$html .= "<rss version=\"2.0\">
<channel>
<title>".KB_TITLE."</title>
<description>Kill Feed v1.3</description>
<link>".KB_HOST."</link>
<copyright>".KB_TITLE."</copyright>\n";

$klist = new KillList();
$klist->setPodsNoobShips(true);

if ($_GET['week'])
{
    $klist->setWeek($_GET['week']);
}
elseif (!$_GET['lastkllid'])
{
    $klist->setWeek(date("W"));
}
if ($_GET['lastkllid'])
{
    if (method_exists($klist, 'setMinKllID'))
    {
        $klist->setMinKllID($_GET['lastkllid']);
    }
}

if ($_GET['corp'] || $_GET['corp_name'])
{
    if ($_GET['corp'])
    {
        $c = $_GET['corp'];
    }
    if ($_GET['corp_name'])
    {
        $c = $_GET['corp_name'];
    }
    $corp = new Corporation();
    $corp->lookup(urldecode($c));
}

if ($_GET['alli'] || $_GET['alliance_name'])
{
    if ($_GET['alli'])
    {
        $a = $_GET['alli'];
    }
    if ($_GET['alliance_name'])
    {
        $a = $_GET['alliance_name'];
    }
    $alli = new Alliance();
    $alli->add(urldecode($a));
}

if ($_GET['week'])
{
    $klist->setWeek($_GET['week']);
}
elseif (!$_GET['lastkllid'])
{
    $klist->setWeek(date("W"));
}

if ($_GET['losses'])
{
    if (CORP_ID)
    {
        $klist->addVictimCorp(new Corporation(CORP_ID));
    }
    if (ALLIANCE_ID)
    {
        $klist->addVictimAlliance(new Alliance(ALLIANCE_ID));
    }
    if ($corp)
    {
        $klist->addInvolvedCorp($corp);
    }
    if ($alli)
    {
        $klist->addInvolvedAlliance($alli);
    }
}
else
{
    if (CORP_ID)
    {
        $klist->addInvolvedCorp(new Corporation(CORP_ID));
    }
    if (ALLIANCE_ID)
    {
        $klist->addInvolvedAlliance(new Alliance(ALLIANCE_ID));
    }
    if ($corp)
    {
        $klist->addVictimCorp($corp);
    }
    if ($alli)
    {
        $klist->addVictimAlliance($alli);
    }
}

$kills = array();
while ($kill = $klist->getKill())
{
    $kills[$kill->getID()] = $kill->getTimestamp();
}
asort($kills);

foreach ($kills as $id => $timestamp)
{
    $kill = new Kill($id);
    $html .= "<item>
<title>".$id."</title>
<description> <![CDATA[ ".$kill->getRawMail()." ]]>  </description>
<guid>?a=kill_detail&amp;kll_id=".$id."</guid>
<pubDate>".strftime("%a, %d %b %Y %T %Z", strtotime($timestamp))."</pubDate>
</item>\n";
}
$html .= "</channel>
</rss>";

if ($_GET['gz'] || $_GET['compress'] == 1)
{
    echo gzdeflate($html, 9);
}
else
{
    echo $html;
}
?>