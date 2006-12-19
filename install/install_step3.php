<?php
$stoppage = true;
if ($_REQUEST['submit'])
{
    $_SESSION['sql'] = array();
    $_SESSION['sql']['host'] = $_POST['host'];
    $_SESSION['sql']['user'] = $_POST['user'];
    $_SESSION['sql']['pass'] = $_POST['dbpass'];
    $_SESSION['sql']['db'] = $_POST['db'];
}

if (!$host = $_SESSION['sql']['host'])
{
    $host = 'localhost';
}
?>
<form id="options" name="options" method="post" action="?step=3">
<input type="hidden" name="step" value="3">
<div class="block-header2">MySQL Database</div>
<table class="kb-subtable">
<tr><td width="120"><b>MySQL Host:</b></td><td><input type=text name=host id=host size=20 maxlength=80 value="<?php echo $host; ?>"></td></tr>
<tr><td width="120"><b>User:</b></td><td><input type=text name=user id=user size=20 maxlength=80 value="<?php echo $_SESSION['sql']['user']; ?>"></td></tr>
<tr><td width="120"><b>Password:</b></td><td><input type=text name=dbpass id=pass size=20 maxlength=80 value="<?php echo $_SESSION['sql']['pass']; ?>"></td></tr>
<tr><td width="120"><b>Database:</b></td><td><input type=text name=db id=db size=20 maxlength=80 value="<?php echo $_SESSION['sql']['db']; ?>"></td></tr>
<tr><td width="120"></td><td><input type=submit name=submit value="Test"></td></tr>
</table>

<?php
if ($_SESSION['sql']['db'])
{
    echo '<div class="block-header2">Testing Settings</div>';
    echo 'Got the data you supplied, trying to connect to that sql server now...<br/>';
    $db = mysql_pconnect($_SESSION['sql']['host'], $_SESSION['sql']['user'], $_SESSION['sql']['pass']);
    if (is_resource($db))
    {
        echo 'Connected to MySQl';
        $result = mysql_query('SELECT VERSION() AS version');
        $result = mysql_fetch_assoc($result);
        if (!$result)
        {
            echo '<br/>Something went wrong:<br/>';
            echo mysql_error();
        }
        else
        {
            echo ' running Version '.$result['version'].'.<br/>';
            if (mysql_select_db($_SESSION['sql']['db']))
            {
                echo 'Successfully selected database "'.$_SESSION['sql']['db'].'", everything is fine to continue.<br/>';
                $stoppage = false;
            }
            else
            {
                echo 'Could not select the database, please check your settings.<br/>';
            }
        }
    }
    else
    {
        echo 'Could not connect to the server, please check your settings.<br/>';
    }
}
?>

<?php if ($stoppage)
{
    return;
}?>
<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>