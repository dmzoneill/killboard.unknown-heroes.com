<?php
require_once("db.php");
require_once("class.page.php");

$page = new Page("Login");

if (trim($_POST['password']))
{
    if ($_POST['password'] == ADMIN_PASSWORD || $_POST['password'] == SUPERADMIN_PASSWORD)
    {
        if ($_POST['password'] == SUPERADMIN_PASSWORD)
        {
            $redir = "admin";
            $super = 1;
        }
        else
        {
            $redir = "admin";
            $super = 0;
        }

        $page->session_->create($super);

        header("Location: ?a=" . $redir);
    }
    else
        $html .= "Invalid password.<br><br>";
}

$html .= "<form name=login id=login method=post action=?a=login>";
$html .= "Admin password: <input name=password id=password type=password>&nbsp;<input type=\"submit\" name=submit id=submit name=Go value=Go>";
$html .= "</form>";

$page->setContent($html);
$page->generate();
?>