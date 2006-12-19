<?php
require_once('db.php');

// modify the header here so the client gets our version
header('X-KBVersion: '.KB_VERSION);

if (!strstr($_SERVER['HTTP_USER_AGENT'], 'EVE-KB SYNC'))
{
    echo 'Current version is '.KB_VERSION.'.<br>';
    echo 'You should not call this file directly unless you\'re told to<br>';
    return;
}

// this is an example for version checking with client ban
// in case we change the protocol/data format
preg_match("^\(VER (.*?)\)^", $_SERVER['HTTP_USER_AGENT'], $match);
$version = explode('.', $match[1]);
if ($version[0] <= 1 && $version[1] < 1)
{
    echo "Your Killboard is too old, please upgrade to 1.2.2 or newer<br>\n";
    return;
}

if (!function_exists('apache_request_headers'))
{
    function apache_request_headers()
    {
        return getallheaders();
    }
}
$header = apache_request_headers();
foreach ($header as $key => $value)
{
    if ($key == 'X-KBHost')
    {
        $host = base64_decode($value);
    }
}

// check if we got a file named 'data' supplied with the request
if (!file_exists($_FILES['data']['tmp_name']))
{
    var_dump($_REQUEST);
    var_dump($_FILES);
    echo "malformed request, expecting data-file<br>\n";
    return;
}

// we got our file, process
$data = gzinflate(file_get_contents($_FILES['data']['tmp_name']));

// get all names we'll find
preg_match_all("^!(.*?)\|(.*?)-^", &$data, $matches);
$data = strstr($data, 'ITEMS_START');
$results = count($matches[1]);
if ($results == 0)
{
    // if we got no ids from the client we won't send him ours
    // bad idea for new installations
    //echo "malformed request<br>\n";
    //return;
}

$s_data = array();
// construct an array with name as key and id as value
for ($i = 0; $i<$results; $i++)
{
    $s_data[$matches[1][$i]] = $matches[2][$i];
}
unset($matches);

if ($host)
{
    $qry = new DBQuery();
    $qry->execute("show tables like '%item_%'");
    while ($row = $qry->getRow())
    {
        $tables .= array_pop($row);
    }
    if (!strstr($tables, 'kb3_item_stats'))
    {
        $qry->execute("CREATE TABLE `kb3_item_stats` (\n `itm_name` varchar(128) NOT NULL,\n `kb_host` int(11) NOT NULL,\n `itm_externalid` int(11) NOT NULL,\n `itm_value` bigint(4) NOT NULL,\n PRIMARY KEY (`itm_name`,`kb_host`)\n) TYPE=MyISAM");
        $qry->execute("CREATE TABLE `kb3_item_hosts` (\n `kb_host` int(11) NOT NULL,\n `kb_name` varchar(255) NOT NULL,\n `itm_update` timestamp NOT NULL default '0000-00-00 00:00:00',\n RPIMARY KEY (`kb_host`)\n) TYPE=MyISAM");
    }
    preg_match_all('^§(.*?)\|(.*?)\|(.*?)-^', &$data, $matches);

    $hostid = abs(crc32($host));
    $qry->execute('replace into kb3_item_hosts (kb_host, kb_name, itm_update) VALUES (\''.$hostid.'\',\''.addslashes($host).'\',\''.date('Y-m-d H:i:s').'\')');

    $results = count($matches[1]);
    for ($i = 0; $i<$results; $i++)
    {
        $qry->execute("replace into kb3_item_stats (itm_name,kb_host,itm_externalid,itm_value) VALUES ('".addslashes($matches[1][$i])."','".$hostid."','".addslashes($matches[2][$i])."','".addslashes($matches[3][$i])."')");
    }
}
unset($data);

// now get our list from the database
$qry = new DBQuery();
$qry->execute("select plt_name, plt_externalid from kb3_pilots where plt_externalid != 0");
while ($data = $qry->getRow())
{
    $data_array[$data['plt_name']] = $data['plt_externalid'];
}
$update = new DBQuery();

// compare the entries supplied with our own
foreach ($s_data as $name => $id)
{
    if (!$data_array[$name])
    {
        // we dont got that one in our database, update
        // TODO: we don't care about missing pilots yet
        $update->execute("update kb3_pilots set plt_externalid='".addslashes($id)."' where plt_name='".addslashes($name)."' limit 1");
    }
    else
    {
        // unset to save comparison time, we know the client has it
        unset($data_array[$name]);
    }
}

// $data_array now contains only unknown ids to the client
$content_file = 'DATA_START';
foreach ($data_array as $name => $id)
{
    $content_file .= '!'.$name.'|'.$id.'-';
}

$data_array = $data_values = array();
$qry->execute('select itm_name, itm_externalid, itm_value from kb3_item_stats order by itm_name asc, itm_value asc');
while ($data = $qry->getRow())
{
    $data_array[$data['itm_name']] = $data;
    $data_values[$data['itm_name']][] = $data['itm_value'];
}

$content_file .= 'ITEMS_START';
foreach ($data_array as $data)
{
    // get the median value for every item
    $val_cnt = count($data_values[$data['itm_name']]);
    $val_cnt = min(ceil($val_cnt/2), $val_cnt)-1;
    $value = $data_values[$data['itm_name']][$val_cnt];

    $content_file .= '§'.$data['itm_name'].'|'.$data['itm_externalid'].'|'.$value.'-';
}

// return the compressed data back to the client
echo gzdeflate($content_file);
?>