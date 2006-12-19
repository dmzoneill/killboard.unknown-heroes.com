<?php
if (!$sig_name = $_GET['s'])
{
    $sig_name = 'default';
}
$sig_name = str_replace('.', '', $sig_name);
$sig_name = str_replace('/', '', $sig_name);
$sig_name = str_replace('/', '', $sig_name);

if (!$plt_id = $_GET['i'])
{
    header('Location: mods/signature_generator/error.jpg');
    exit;
}
require_once("common/class.pilot.php");
require_once("common/class.corp.php");
require_once("common/class.alliance.php");
require_once("common/class.killlist.php");

$pilot = new Pilot($plt_id);
if (!$pilot->exists())
{
    header('Location: mods/signature_generator/error.jpg');
    exit;
}
$corp = $pilot->getCorp();
$alliance = $corp->getAlliance();

// we dont generate pictures for non-member
if (ALLIANCE_ID && $alliance->getID() != ALLIANCE_ID)
{
    header('Location: mods/signature_generator/error.jpg');
    exit;
}
elseif (CORP_ID && $corp->getID() != CORP_ID)
{
    header('Location: mods/signature_generator/error.jpg');
    exit;
}

$id = abs(crc32($sig_name));
// check for cached version
if (file_exists('cache/data/sig_'.$id.'_'.$plt_id))
{
    $age = filemtime('cache/data/sig_'.$id.'_'.$plt_id);

    // cache files for 30 minutes
    if (time() - $age < 30*60)
    {
        if (file_exists('mods/signature_generator/signatures/'.$sig_name.'/typ.png'))
        {
            header('Content-Type: image/png');
        }
        else
        {
            header('Content-Type: image/jpeg');
        }
        readfile('cache/data/sig_'.$id.'_'.$plt_id);
        return;
    }
}

// check template
if (!is_dir('mods/signature_generator/signatures/'.$sig_name))
{
    header('Location: mods/signature_generator/error.jpg');
    exit;
}

// let the template do the work, we just output $im
require('mods/signature_generator/signatures/'.$sig_name.'/'.$sig_name.'.php');

if (file_exists('mods/signature_generator/signatures/'.$sig_name.'/typ.png'))
{
    header('Content-Type: image/png');
}
else
{
    header('Content-Type: image/jpeg');
}
imagejpeg($im, 'cache/data/sig_'.$id.'_'.$plt_id, 95);
readfile('cache/data/sig_'.$id.'_'.$plt_id);
?>