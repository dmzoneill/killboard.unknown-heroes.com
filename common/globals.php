<?php
// current subversion revision
preg_match('/\$Re'.'vision: (.*?) \$/', '$Revision: 162 $', $match);
define('SVN_REV', $match[1]);

// current version: major.minor.sub
// unpair numbers for minor = development version
define('KB_VERSION', '1.2.2');

// set the running-server for id-syncs here
define('KB_SYNCURL', 'http://sync.eve-dev.net/?a=sync_server');

// add new corporations here once you've added the logo to img/corps/
$corp_npc = array("Guristas", 'Serpentis Corporation');

function shorten($shorten, $by = 22)
{
    if (strlen($shorten) > $by)
    {
        $s = substr($shorten, 0, $by) . "...";
    }
    else $s = $shorten;

    return $s;
}

function slashfix($fix)
{
    return addslashes(stripslashes($fix));
}

function roundsec($sec)
{
    if ($sec <= 0)
        $s = 0.0;
    else
        $s = $sec;

    return number_format(round($s, 1), 1);
}

function get_tpl($name)
{
    if (IS_IGB)
    {
        if (file_exists('./templates/igb_'.$name.'.tpl'))
        {
            return 'igb_'.$name.'.tpl';
        }
    }
    return $name.'.tpl';
}
?>