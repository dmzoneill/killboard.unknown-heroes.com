<?php
$stoppage = true;
include('../common/php_compat.php');

$struct = $opt = $data = array();
$dir = opendir('./sql');
while ($file = readdir($dir))
{
    if (strpos($file, 'sql_') !== false)
    {
        $table = str_replace(array('sql_tblstrct_', 'sql_tbldata_opt_', 'sql_tbldata_', '.sql'), '', $file);
        $table = 'kb3_'.preg_replace('/(_p\d{1,4})/', '', $table);
        if (strpos($file, 'tblstrct'))
        {
            $structc++;
            $struct[$table][] = $file;
        }
        elseif (strpos($file, '_opt_'))
        {
            $dcnt++;
            $optcnt++;
            $opt[$table][] = $file;
            asort($opt[$table]);
        }
        else
        {
            $dcnt++;
            $datacnt++;
            $data[$table][] = $file;
            asort($data[$table]);
        }
    }
}

$db = mysql_pconnect($_SESSION['sql']['host'], $_SESSION['sql']['user'], $_SESSION['sql']['pass']);
mysql_select_db($_SESSION['sql']['db']);
$result = mysql_query('show tables');
while ($row = mysql_fetch_row($result))
{
    $table = $row[0];
    unset($struct[$table]);
}

if ($_REQUEST['sub'] == 'struct')
{
    foreach ($struct as $table => $files)
    {
        foreach ($files as $file)
        {
            echo 'Creating table '.$table.' from file '.$file.'...';
            $query = file_get_contents('./sql/'.$file);
            $id = mysql_query($query);
            if ($id)
            {
                echo 'done<br/>';
            }
            else
            {
                echo 'error: '.mysql_error().'<br/>';
            }
        }
        unset($struct[$table]);
    }
}
if ($_REQUEST['do'] == 'reset')
{
    unset($_SESSION['sqlinsert']);
    unset($_SESSION['doopt']);
}

if ($_REQUEST['sub'] == 'data')
{
    if (!isset($_SESSION['sqlinsert']))
    {
        $_SESSION['sqlinsert'] = 1;
        if (isset($_POST['opt']))
        {
            $_SESSION['useopt'] = array();
            foreach ($_POST['opt'] as $table => $value)
            {
                $_SESSION['useopt'][] = $table;
            }
        }
    }

    $i = 0;
    $did = false;
    $errors = false;
    if (!isset($_SESSION['doopt']))
    {
        foreach ($data as $table => $files)
        {
            foreach ($files as $file)
            {
                $i++;
                if ($_SESSION['sqlinsert'] > $i)
                {
                    continue;
                }
                echo 'Inserting data ('.$i.'/'.$datacnt.') into '.$table.' from file '.$file.'...<br/>';

                $error = '';
                $querys = file('./sql/'.$file);
                $lines = count($querys);
                $errors = 0;
                foreach ($querys as $query)
                {
                    if (trim($query))
                    {
                        $query = trim($query);
                        if (substr($query, -1, 1) == ';')
                        {
                            $query = substr($query, 0, -1);
                        }
                        $query_count++;
                        $id = mysql_query($query);
                        if (!$id)
                        {
                            $error .= 'error: '.mysql_error().'<br/>';
                            $errors++;
                        }
                    }
                }
                echo 'File '.$file.' had '.$lines.' lines with '.$query_count.' querys. '.$errors.' Querys failed.<br/>';
                if (!$error)
                {
                    echo 'done<br/>';
                    echo '<meta http-equiv="refresh" content="1; URL=?step=4&sub=data" />';
                    echo 'Automatic reload in 1s for next chunk. <a href="?step=4&sub=data">Manual Link</a><br/>';
                }
                else
                {
                    echo $error;
                    echo '<meta http-equiv="refresh" content="20; URL=?step=4&sub=data" />';
                    echo 'Automatic reload in 20s for next chunk because of the error occured. <a href="?step=4&sub=data">Manual Link</a><br/>';
                }
                $_SESSION['sqlinsert']++;

                $did = true;
                break 2;
            }
        }
    }

    if (isset($_SESSION['useopt']) && !$did)
    {
        $i = 0;
        if (!isset($_SESSION['doopt']))
        {
            $_SESSION['doopt'] = true;
            $_SESSION['sqlinsert'] = 1;
        }
        foreach ($opt as $table => $files)
        {
            if (!in_array($table, $_SESSION['useopt']))
            {
                continue;
            }
            foreach ($files as $file)
            {
                $optsel++;
            }
        }
        foreach ($opt as $table => $files)
        {
            if (!in_array($table, $_SESSION['useopt']))
            {
                continue;
            }
            foreach ($files as $file)
            {
                $i++;
                if ($_SESSION['sqlinsert'] > $i)
                {
                    continue;
                }
                echo 'Inserting optional data ('.$i.'/'.$optsel.') into '.$table.' from file '.$file.'...';
                $querys = file('./sql/'.$file);
                foreach ($querys as $query)
                {
                    $query = trim($query);
                    if ($query)
                    {
                        if (substr($query, -1, 1) == ';')
                        {
                            $query = substr($query, 0, -1);
                        }
                        $id = mysql_query($query);
                    }
                }
                if ($id)
                {
                    echo 'done<br/>';
                }
                else
                {
                    echo 'error: '.mysql_error().'<br/>';
                }
                $_SESSION['sqlinsert']++;
                echo '<meta http-equiv="refresh" content="1; URL=?step=4&sub=data" />';
                echo 'Automatic reload in 1s for next chunk. <a href="?step=4&sub=data">Manual Reload</a><br/>';
                $did = true;
                break 2;
            }
        }
    }
    if (!$did)
    {
        $stoppage = false;
        echo 'All tables imported. Checking tables for correct data...<br/>';
        $check = file('./sql/tbl_check.txt');
        foreach ($check as $line)
        {
            $tmp = explode(chr(9), $line);
            $table = trim($tmp[0]);
            $count = trim($tmp[1]);
            echo 'Checking table '.$table.': ';
            $result = mysql_query('SELECT count(*) as cnt FROM '.$table);
            $test = mysql_fetch_array($result);
            if ($test['cnt'] != $count)
            {
                echo $test['cnt'].'/'.$count.' - FAILED';
                $stoppage = true;
            }
            else
            {
                echo $test['cnt'].'/'.$count.' - PASSED';
            }
            echo '<br/>';
        }
        if ($stoppage)
        {
            echo 'There has been an error with one of the tables, please <a href="?step=4&do=reset">Reset</a> and try again.<br/>';
        }
        else
        {
            echo '<br/>All tables passed.<br/>';
            echo 'You can now create or search your corporation/alliance: <a href="?step=5">Next Step</a><br/>';
        }
    }
    echo '<br/>Use <a href="?step=4&sub=datasel&do=reset">Reset</a> to step back to the sql-opt select.<br/>';
}
?>
<div class="block-header2">MySQL Data Import</div>
Found <?php echo $structc; ?> table structures and <?php echo $dcnt; ?> data files for <?php echo count($opt)+count($data); ?> tables.<br/>
<?php

$structadd = 0;
foreach ($struct as $table => $file)
{
    echo 'Table struct has to be added: '.$table.'<br/>';
    $structadd++;
}
if (!$structadd && $_REQUEST['sub'] != 'datasel' && $_REQUEST['sub'] != 'data')
{
    echo 'All table structures seem to be in the database.<br/>';
    echo 'I will now check some table structures in case you are upgrading from a previous version... ';
    include('install_step4_tblchk.php');
    echo 'done<br/>Please continue with <a href="?step=4&sub=datasel">Importing Data</a><br/>';

    echo '<br/><br/>In case you aborted the install and you got already data in those table you can bypass the import now by with <a href="?step=5">this link</a><br/>';
    echo 'To be sure i will check some table data for you now:<br/>';
    $check = file('./sql/tbl_check.txt');
    foreach ($check as $line)
    {
        $tmp = explode(chr(9), $line);
        $table = trim($tmp[0]);
        $count = trim($tmp[1]);
        echo 'Checking table '.$table.': ';
        $result = mysql_query('SELECT count(*) as cnt FROM '.$table);
        $test = mysql_fetch_array($result);
        $failed = 0;
        if ($test['cnt'] != $count)
        {
            echo $test['cnt'].'/'.$count.' - FAILED';
            $failed++;
        }
        else
        {
            echo $test['cnt'].'/'.$count.' - PASSED';
        }
        echo '<br/>';
    }
    echo 'Checking table kb3_items: ';
    $result = mysql_query('SELECT count(*) as cnt FROM kb3_items');
    $test = mysql_fetch_array($result);
    $failed = 0;
    if ($test['cnt'] <= 5000)
    {
        echo $test['cnt'].'<5000 - FAILED';
        $failed++;
    }
    else
    {
        echo $test['cnt'].'>5000 - PASSED';
    }
    echo '<br/>';
    if (!$failed)
    {
        echo 'All important table data seems to be there, you are safe to bypass the import.<br/>';
    }
    else
    {
        echo 'There was an error in one of the important tables, please run the import.<br/>';
    }
}
elseif ($structadd)
{
    echo 'Some table structures have to be added, please continue with <a href="?step=4&sub=struct">Creating Tables</a><br/>';
}

if ($_REQUEST['sub'] == 'datasel')
{
?>
<p>Please select optional SQL data to be inserted into the database:<br/></p>
<form id="options" name="options" method="post" action="?step=4">
<input type="hidden" name="step" value="4">
<input type="hidden" name="sub" value="data">
<table class="kb-subtable">
<?php
    foreach ($opt as $table => $files)
    {
?>
<tr><td width="120"><b><?php echo $table; ?></b></td><td><input type="checkbox" name="opt[<?php echo $table; ?>]"></td></tr>
<?php
    }
    ?>
<tr><td width="120"></td><td><input type=submit name=submit value="Ok"></td></tr>
</table>
<?php
}
?>
<?php if ($stoppage)
{
    return;
}?>
<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>