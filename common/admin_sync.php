<?php
require_once("class.page.php");
require_once("class.contract.php");
require_once("admin_menu.php");
require_once('class.http.php');

$page = new Page();
$page->setAdmin();
$page->setTitle("Administration - Synchronization");

if ($_REQUEST['do'] == 'sync')
{
    $http = new http_request(KB_SYNCURL);
    $fp = $http->connect();
    if (!$fp)
    {
        $html .= "Could not connect to Server:<br/>\n";
        $html .= $http-getError();

        $page->addContext($menubox->generate());
        $page->setContent($html);
        $page->generate();
        return;
    }

    $content_file = 'DATA_START';
    $qry = new DBQuery();
    $qry->execute("select plt_name, plt_externalid from kb3_pilots where plt_externalid != 0");
    while ($data = $qry->getRow())
    {
        $content_file .= '!'.$data['plt_name'].'|'.$data['plt_externalid'].'-';
    }

    $content_file .= 'ITEMS_START';
    $qry->execute("select itm_name, itm_externalid, itm_value from kb3_items where itm_value != 0");
    while ($data = $qry->getRow())
    {
        $content_file .= '§'.$data['itm_name'].'|'.$data['itm_externalid'].'|'.$data['itm_value'].'-';
    }

    $content_file = gzdeflate($content_file);

    $http->set_postdata('data', $content_file);
    $http->set_useragent("EVE-KB SYNC (VER ".KB_VERSION.")");
    $http->set_header('X-KBHost: '.base64_encode(KB_HOST));
    $http->setSockettimeout(30);
    $file = $http->get_content();
    $header = $http->get_header();

    preg_match("/X-KBVersion: (.*)/", $header, $match);
    $version = explode('.', trim($match[1]));
    $recv = $http->get_recv();
    $sended = $http->get_sent();

    // the response ($file) contains ids new to us
    $data = @gzinflate($file);
    if ($data == false)
    {
        $html .= "getting compressed data failed, server response was:<br><pre>\n";
        $html .= $file."</pre>\n";
    }
    else
    {
        unset($file);

        // get all names we'll find
        preg_match_all("^!(.*?)\|(.*?)-^", $data, $matches);
        $results = count($matches[1]);
        $update = new DBQuery();
        for ($i = 0; $i<$results; $i++)
        {
            $update->execute("update kb3_pilots set plt_externalid='".addslashes($matches[2][$i])."' where plt_name='".addslashes($matches[1][$i])."' limit 1");
        }
        $html .= "Synchronization complete, got $results new ids from server running version ".$version[0].'.'.$version[1].'.'.$version[2].'.<br/>';

        if (isset($_REQUEST['itm_update']))
        {
            preg_match_all('^§(.*?)\|(.*?)\|(.*?)-^', $data, $matches);
            unset($data);
            $results = count($matches[1]);
            for ($i = 0; $i<$results; $i++)
            {
                $update->execute("update kb3_items set itm_externalid='".addslashes($matches[2][$i])."', itm_value='".addslashes($matches[3][$i])."' where itm_name='".addslashes($matches[1][$i])."' limit 1");
            }
            if ($results == 0)
            {
                $html .= 'No items fetched, itm_sync_module may be offline.<br/>';
            }
            else
            {
                $html .= $results.' item prices have been fetched.<br/>';
            }
        }

        $html .= "Sent ".round($sended/1024, 2)." kB and received ".round($recv/1024, 2)." kB of data.<br>\n";
        $html .= '<a href="?a=admin_sync">Back</a>';

        // check for updates here
        // we might move this to a new/second point some time
        $ownversion = explode('.', KB_VERSION);
        if ($version[1] > $ownversion[1] && $version[1] % 2 == 1)
        {
            // test for new minor updates below the dev-version
            if ($version[1]-1 > $ownversion[1])
            {
                $upgrade = true;
            }
        }
        elseif ($version[1] > $ownversion[1] && $version[1] % 2 == 0)
        {
            // we get here in case there is a new minor version thats not a dev
            $upgrade = true;
        }
        if ($version[0] > $ownversion[0] || $upgrade)
        {
            $html .= "Looks like your Killboard version is pretty old, perhaps you want to upgrade it ?<br/>\n";
            $html .= "Check the <a href='http://www.eve-dev.net/forums/viewforum.php?f=2'>EVE-Dev Forums</a> for new releases and additional information<br>\n";
        }
    }
}
else
{
    $html .= 'You can synchronize your external characterids for the portrait generation with the EVE-Dev.org-Server here.<br>';
    $html .= 'Your Server will try to contact <i>"'.KB_SYNCURL.'"</i> to exchange the data.<br>';
    $html .= 'One synchronization every one or two weeks should be enough.<br>';
    $html .= 'Please don\'t abuse this free service!<br>';
    $html .= '<form id="options" name="options" method="post" action="?a=admin_sync">';
    $html .= "<table class=kb-subtable>";
    $html .= "<tr><td width=120><b>Update item values</b></td><td><input type=checkbox name=itm_update id=itm_update";
    $html .= " checked=\"checked\"></td></tr>";
    $html .= '<input type="hidden" name="do" value="sync">';
    $html .= '<tr><td width=120></td><td><input type=submit name=submit value="Synchronize now"></td></tr></table></form>';
}
$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();
?>