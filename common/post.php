<?php
require_once("class.page.php");
require_once("class.killboard.php");
require_once("class.parser.php");
require_once("class.phpmailer.php");
require_once("class.kill.php");

$page = new Page("Post killmail");
$kb = new Killboard(KB_SITE);

if (isset($_POST['killmail']))
{
    if ($_POST['password'] == $config->getPostPassword() || $page->isAdmin())
    {
        $parser = new Parser($_POST['killmail']);

        // Filtering
        if ($config->getConfig("filter_apply"))
        {
            $filterdate = $config->getConfig("filter_date");
            $year = substr($_POST['killmail'], 0, 4);
            $month = substr($_POST['killmail'], 5, 2);
            $day = substr($_POST['killmail'], 8, 2);
            $killstamp = mktime(0, 0, 0, $month, $day, $year);
            if ($killstamp < $filterdate)
            {
                $killid = -3;
            }
            else
            {
                $killid = $parser->parse(true);
            }
        }
        else
        {
            $killid = $parser->parse(true);
        }

        if ($killid == 0 || $killid == -1 || $killid == -2 || $killid == -3)
        {
            if ($killid == 0)
            {
                $html = "Killmail is malformed.<br/>";
                if ($errors = $parser->getError())
                {
                    foreach ($errors as $error)
                    {
                        $html .= 'Error: '.$error[0];
                        if ($error[1])
                        {
                            $html .= ' The text lead to this error was: "'.$error[1].'"';
                        }
                        $html .= '<br/>';
                    }
                }
            }
            elseif ($killid == -1)
            {
                $html = "That killmail has already been posted <a href=\"?a=kill_detail&kll_id=".$parser->dupeid_."\">here</a>.";
            }
            elseif ($killid == -2)
            {
                $html = "You are not authorized to post this killmail.";
            }
            elseif ($killid == -3)
            {
                $filterdate = date("j F Y", $config->getConfig("filter_date"));
                $html = "You are not allowed to post killmails older than $filterdate.";
            }
        }
        else
        {
            if ($config->getPostMailto() != "")
            {
                $mailer = new PHPMailer();
                $kill = new Kill($killid);

                $mailer->From = "mailer@".$config->getConfig('mail_host');
                $mailer->FromName = $config->getConfig('mail_host');
                $mailer->Subject = "Killmail #" . $killid;
                $mailer->Host = "localhost";
                $mailer->Port = 25;
                $mailer->Helo = "localhost";
                $mailer->Mailer = "smtp";
                $mailer->AddReplyTo("no_reply@".$config->getConfig('mail_host'), "No-Reply");
                $mailer->Sender = "mailer@".$config->getConfig('mail_host');
                $mailer->Body = $kill->getRawMail();
                $mailer->AddAddress($config->getPostMailto());
                $mailer->Send();
            }

            $qry = new DBQuery();
            $qry->execute("insert into kb3_log
	                       values(".$killid.",'".KB_SITE."','".$_SERVER['REMOTE_ADDR']."', now())");

            header("Location: ?a=kill_detail&kll_id=".$killid);
            exit;
        }
    }
    else
    {
        $html = "Invalid password.";
    }
}
elseif (!$config->getConfig('post_forbid'))
{
    $html .= "Paste the killmail from your EVEMail inbox into the box below. Make sure you post the <b>ENTIRE</b> mail.<br>Posting fake or otherwise edited mails is not allowed. All posts are logged.";
    $html .= "<br><br>Remember to post your losses as well.<br><br>";
    $html .= "<b>Killmail:</b><br>";
    $html .= "<form id=postform name=postform class=f_killmail method=post action=\"?a=post\">";
    $html .= "<textarea name=killmail id=killmail class=f_killmail cols=\"70\" rows=\"24\"></textarea>";
    if (!$page->isAdmin())
    {
        $html .= "<br><br><b>Password:</b><br><input id=password name=password type=password></input>";
    }
    $html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id=submit name=submit type=submit value=\"Process !\"></input>";
    $html .= "</form>";
}
else
{
    $html .= 'Posting killmails is disabled<br/>';
}

$page->setContent($html);
$page->generate();
?>