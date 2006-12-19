<?php
require_once("class.page.php");
require_once("class.pilot.php");
require_once("class.corp.php");
require_once("class.alliance.php");

$page = new Page("Capture portrait");

$html .= "<html><head><title>Update portrait</title></head><body>";

if (!$page->igb())
{
    $html .= "You need to access this page from the EVE ingame browser.";
}
else
{
    if (($_SERVER['HTTP_EVE_TRUSTED'] == 'no'))
    {
        Header('eve.trustme:http://' . $_SERVER['HTTP_HOST'] . '/::Need trust to grab character portrait.');
        $html .= '<h1>Trust Required</h1>';
        $html .= 'This site needs to be trusted in order to grab your character portrait.';
    }
    else
    {
        $now = date("Y-m-d H:m:s");

        $alliance = new Alliance();
        $all_id = $alliance->add($_SERVER['HTTP_EVE_ALLIANCENAME']);
        $corp = new Corporation();
        $crp_id = $corp->add($_SERVER['HTTP_EVE_CORPNAME'], $alliance, $now);
        $pilot = new Pilot();
        $plt_id = $pilot->add($_SERVER['HTTP_EVE_CHARNAME'], $corp, $now);
        $pilot->setCharacterID($_SERVER['HTTP_EVE_CHARID']);
        @unlink("cache/portraits/" . $_SERVER['HTTP_EVE_CHARID'] . "_32.jpg");
        @unlink("cache/portraits/" . $_SERVER['HTTP_EVE_CHARID'] . "_64.jpg");
        @unlink("cache/portraits/" . $_SERVER['HTTP_EVE_CHARID'] . "_128.jpg");
        @unlink("cache/portraits/" . $_SERVER['HTTP_EVE_CHARID'] . "_512.jpg");
        //$html .= "<img src=\"".$pilot->getPortraitURL(64).".jpg\" border=\"0\">";
        $html .= "Character portrait updated !<br>";
        $html .= "<a href=\"?a=igb\">Return</a><br>";

        //$updated = true;
    }
}

$html .= "</body></html>";

echo $html;
if ($updated)
{
    flush();
    ignore_user_abort(1);
    $id = $_SERVER['HTTP_EVE_CHARID'];

    $img = imagecreatefromjpeg("http://img.eve.is/serv.asp?s=512&c=".$id);
    if ($img)
    {
        $newimg = imagecreatetruecolor(32, 32);
        imagecopyresampled($newimg, $img, 0, 0, 0, 0, 32, 32, 512, 512);
        imagejpeg($newimg, "cache/portraits/" . $id . "_32.jpg");
        $newimg = imagecreatetruecolor(64, 64);
        imagecopyresampled($newimg, $img, 0, 0, 0, 0, 64, 64, 512, 512);
        imagejpeg($newimg, "cache/portraits/" . $id . "_64.jpg");
        $newimg = imagecreatetruecolor(128, 128);
        imagecopyresampled($newimg, $img, 0, 0, 0, 0, 128, 128, 512, 512);
        imagejpeg($newimg, "cache/portraits/" . $id . "_128.jpg");
    }
}
?>