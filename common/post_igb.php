<?php
require_once("class.page.php");
require_once("class.killboard.php");
require_once("class.parser.php");
require_once("class.phpmailer.php");
require_once("class.kill.php");

$kb = new Killboard(KB_SITE);

$html = "<html><head><title>" . KB_TITLE . " Killboard - Post Mail" . "</title></head></html><body>";

if (isset($_POST['killmail']))
{
    if ($_POST['password'] == $config->getPostPassword())
    {
        $parser = new Parser($_POST['killmail']);

        $killid = $parser->parse(true);

        if ($killid == 0 || $killid == -1 || $killid == -2 || $killid == -3)
        {
            if ($killid == 0)
            {
                $html = "Killmail is malformed.";
            }
            elseif ($killid == -1)
            {
                $html = "That killmail has already been posted.";
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

            $html .= "<br><br><a href=\"?a=post_igb\">Try again</a>";
        }
        else
        {
            if ($config->getPostMailTo() != "")
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
	                       values( " . $killid . ", '" . KB_SITE . "',
	                               '" . $_SERVER['REMOTE_ADDR'] . "',
				       now() )");

            $html .= "Killmail posted successfully.<br><br>";
            $html .= "<a href=\"?a=post_igb\">Post another killmail</a>";
        }
    }
    else
    {
        $html .= "Invalid password.";
        $html .= "<br><br><a href=\"?a=post_igb\">Try again</a>";
    }
}
elseif (!$config->getConfig('post_forbid'))
{
    $html .= "Paste the killmail from your EVEMail inbox into the box below. Make sure you post the <b>ENTIRE</b> mail.<br>Posting fake or otherwise edited mails is not allowed. All posts are logged.";
    $html .= "<br><br>Remember to post your losses as well.<br><br>";
    $html .= "<b>Killmail:</b><br>";
    $html .= "<form name=postform method=\"post\" action=\"?a=post_igb\">";
    $html .= "<textarea name=killmail id=killmail cols=\"70\" rows=\"24\"></textarea>";
    $html .= "<br><br><b>Password:</b><br><input name=\"password\" type=\"password\">";
    $html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id=submit name=\"submit\" type=\"submit\" value=\"Process !\">";
    $html .= "</form>";
}
else
{
    $html .= 'Posting killmails is disabled<br/>';
}

$html .= "</body></html>";

echo $html;
?>