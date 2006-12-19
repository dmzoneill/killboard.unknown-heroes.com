<?php
$stoppage = true;



extract($_SESSION['sql']);
$dbhost = $host;
extract($_SESSION['sett']);

$config = preg_replace("/\{([^\}]+)\}/e", "\\1", join('', file('config.tpl')));
$fp = fopen('../cache/config.php', 'w');
fwrite($fp, trim($config));
fclose($fp);
?>
<p>Here is the config i created for you, i saved it as ../cache/config.php with chmod 777.<br/>
Please move that file to the main dir or create a new one there with the following content.<br/>
You can continue once that config exists, i will try to delete the generated config if it should be stil there on the next step.<br/>
</p>
<?php
highlight_string($config);
?>
<?php
if (!file_exists('../config.php'))
{
    ?>
<p><a href="?step=<?php echo $_SESSION['state']; ?>">Refresh</a></p>
<?php
    return;
}
// config is there, use it to create all config vars which arent there
// to prevent that ppl with running installs get new values
require_once('../config.php');

$db = mysql_pconnect(DB_HOST, DB_USER, DB_PASS);
mysql_select_db(DB_NAME);

$confs = file('config.data');
foreach ($confs as $line)
{
    list($key, $value) = explode(chr(9), trim($line));
    $result = mysql_query('select * from kb3_config where cfg_site=\''.KB_SITE.'\' and cfg_key=\''.$key.'\'');
    if (!$row = mysql_fetch_row($result))
    {
        $sql = "insert into kb3_config values ('".KB_SITE."','".$key."','".$value."')";
        mysql_query($sql);
    }
}
?>
<br/><br/><font size=+1>Found the config on the right place, please continue...</font><br/>
<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>