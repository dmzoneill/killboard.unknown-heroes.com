<?php
require_once('db.php');

class Session
{
    function Session()
    {
        if (isset($_REQUEST['PHPSESSID']))
        {
            session_start();
        }
    }

    function isAdmin()
    {
        return isset($_SESSION['admin']);
    }

    function isSuperAdmin()
    {
        return isset($_SESSION['admin_super']);
    }

    function create($super)
    {
        session_start();
        $_SESSION['admin'] = 1;
        $_SESSION['admin_super'] = $super;
    }

    function destroy()
    {
        session_destroy();
    }
}
?>