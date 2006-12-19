<?php
require_once('db.php');
require_once('class.killboard.php');
require_once('smarty/Smarty.class.php');

$page = str_replace('.', '', $_GET['a']);
$page = str_replace('/', '', $page);
if ($page == '')
{
    $page = 'home';
}
if (substr($_SERVER['HTTP_USER_AGENT'], 0, 15) == 'EVE-minibrowser')
{
    define('IS_IGB', true);
    if (!isset($_GET['a']))
    {
        $page = 'igb';
    }
}
else
{
    define('IS_IGB', false);
}

$killboard = new Killboard(KB_SITE);
$config = $killboard->getConfig();
$smarty = new Smarty();
$smarty->template_dir = './templates';
$smarty->compile_dir = './cache/templates_c';
$smarty->cache_dir = './cache/data';
$smarty->assign('style_url', STYLE_URL);
$smarty->assign('img_url', IMG_URL);
$smarty->assign_by_ref('config', $config);
if (!is_dir('./cache/templates_c'))
{
    if (mkdir('./cache/templates_c'))
    {
        chmod('./cache/templates_c', 0777);
    }
    else
    {
        exit('please create cache/templates_c and chmod it 777');
    }
}
// if ($killboard->isSuspended())
// $page = 'suspended';

if (substr($page, 0, 9) == 'settings_')
{
    $settingsPage = true;
}
else
{
    $settingsPage = false;
}
$mods_active = explode(',', $config->getConfig('mods_active'));
$modOverrides = false;
foreach ($mods_active as $mod)
{
    if (file_exists('mods/'.$mod.'/'.$page.'.php'))
    {
        if ($modOverrides)
        {
            die('Error: Two or more of the mods you have activated are conflicting');
        }
        $modOverrides = true;
        $modOverride = $mod;
    }
}
if (!$settingsPage && !file_exists('common/'.$page.'.php') && !$modOverrides)
{
    $page = 'home';
}

if (KB_CACHE == 1 && count($_POST) == 0 && !in_array($page, $cacheignore))
{
    $docache = true;
}
else
{
    $docache = false;
}

if ($docache)
{
    if (!file_exists(KB_CACHEDIR . '/' . KB_SITE))
    {
        @mkdir(KB_CACHEDIR . '/' . KB_SITE);
    }

    if ($cachetimes[$page])
    {
        $cachetime = $cachetimes[$page];
    }
    else
    {
        $cachetime = 5;
    }

    $cachetime = $cachetime * 60;

    $cachefile = KB_CACHEDIR . '/' . KB_SITE . '/' . md5($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '.cache';
    $timestamp = ((@file_exists($cachefile))) ? @filemtime($cachefile) : 0;

    if (time() - $cachetime < $timestamp)
    {
        ob_start('ob_gzhandler');
        @readfile($cachefile);
        ob_end_flush();
        exit();
    }

    ob_start();
}

if ($settingsPage)
{
    include ('mods/'.substr($page, 9, strlen($page)-9).'/settings.php');
}
elseif ($modOverrides)
{
    include('mods/'.$modOverride.'/'.$page.'.php');
}
else
{
    include('common/'.$page.'.php');
}

if ($docache)
{
    $fp = @fopen($cachefile, 'w');
    @fwrite($fp, ob_get_contents());
    @fwrite($fp, '<!-- Generated from cache -->');
    @fclose($fp);
    ob_end_flush();
}
?>