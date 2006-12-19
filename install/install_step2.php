<?php
$stoppage = false;

// graphics
echo '<div class="block-header2">Graphics</div>';
if (function_exists('imagecreatefromstring'))
{
    echo 'GD is available.<br/>';
    if (!function_exists('imagecreatetruecolor'))
    {
        echo 'Your GD is outdated though and will cause problems, please contact your system administrator to upgrade to GD 2.0 or higher.<br/>';
    }
    echo 'Now let\'s see if you got the FreeType library needed for painting TrueType .ttf fonts onto images<br/>';
    if (function_exists('imagettftext'))
    {
        echo 'I found FreeType support, this is needed by the signaturemod. Good!<br/>';
    }
    else
    {
        echo 'Unfortunatly i was unable to locate FreeType support so you cannot use all available signatures. :(<br/>';
    }
}
else
{
    echo 'GD is NOT available.<br/>The Killboard will be unable to output character portraits or corporation logos, please speak with your system administrator to install GD 2.0 or higher.<br/>';
    echo 'However, you can continue the installation but the Killboard might not run smoothly.<br/>';
}

// directorys
echo '<br/><div class="block-header2">Directory structure</div>';
function checkdir($dir)
{
    if (!file_exists($dir))
    {
        echo 'Creating '.$dir.' for you...<br/>';
        mkdir($dir);
        chmod($dir, 0777);
    }
    if (is_writeable($dir))
    {
        echo 'Directory '.$dir.' is there and writeable, excellent.<br/>';
    }
    else
    {
        echo 'I cannot write into '.$dir.', you need to fix that for me before you can continue.<br/>';
        echo 'Please issue a "chmod 777 '.$dir.'" on the commandline inside of this directory<br/>';
        global $stoppage;
        $stoppage = true;
    }
}

if (is_writeable('../cache'))
{
    echo 'Cache directory is writeable, testing for subdirs now:<br/>';
    checkdir('../cache/corps');
    checkdir('../cache/data');
    checkdir('../cache/map');
    checkdir('../cache/portraits');
    checkdir('../cache/templates_c');
}
else
{
    $stoppage = true;
    echo 'I cannot write into ../cache, you need to fix that for me before you can continue.<br/>';
    echo 'Please issue a "chmod 777 ../cache" and "chmod 777 ../cache/*" on the commandline inside of this directory<br/>';
}

echo '<br/><div class="block-header2">Connectivity</div>';
// connectivity
$url = 'http://sync.eve-dev.net/?a=sync_server';
if (ini_get('allow_url_fopen'))
{
    echo 'allow_url_fopen is on, i will try to fetch a testpage from "'.$url.'".<br/>';
    if (count(file($url)))
    {
        echo 'Seems to be ok, i got the file.<br/>';
    }
    else
    {
        echo 'I could not get the file this might be a firewall related issue or the eve-dev server is not available.<br/>';
    }
}
else
{
    include('../common/class.http.php');
    echo 'allow_url_fopen is disabled, nethertheless i will try a socket connect now.<br/>';

    $http = new http_request($url);
    if ($http->get_content())
    {
        echo 'Seems to be ok, i got the file.<br/>';
    }
    else
    {
        echo 'I could not get the file this might be a firewall related issue or the eve-dev server is not available.<br/>';
    }
}
?>

<?php if ($stoppage)
{
    return;
}?>
<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>