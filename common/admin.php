<?php
require_once('db.php');
require_once('class.killboard.php');
require_once('class.page.php');
require_once('class.tabbedform.php');
require_once('admin_menu.php');
require_once('autoupgrade.php');

$killboard = new Killboard(KB_SITE);
$page = new Page('Administration - Generic (Current version: '.KB_VERSION.' Build '.SVN_REV.')');
$page->setAdmin();

// check tables for validity and fields
// todo: tidy this mess up!!!!!
check_pilots();
check_invdetail();
check_contracts();
check_index();
check_tblstrct1();
check_tblstrct2();
check_tblstrct3();
check_tblstrct4();
check_tblstrct5();
chk_kb3_items();
chk_kb3_items2();
check_tblstrct6();

if ($_POST['submit'])
{
    $config->setStyleBanner($_POST['style_banner']);
    $config->setStyleName($_POST['style_name']);

    // new function checkCheckbox, checks $_POST[arg] and inserts 0 if != 'on'
    $config->checkCheckbox('kill_points');
    $config->checkCheckbox('ship_values');
    $config->checkCheckbox('least_active');
    $config->checkCheckbox('adapt_items');
    $config->checkCheckbox('show_standing');
    $config->checkCheckbox('bs_podlink');
    $config->checkCheckbox('post_forbid');

    if ($config->checkCheckbox('comments'))
    {
        check_commenttable();
    }
    $config->checkCheckbox('comments_pw');
    $config->checkCheckbox('comments_count');
	$config->checkCheckbox('item_values');
	$config->checkCheckbox('readd_dupes');
    $config->setPostPassword($_POST['post_password']);
    $config->setPostMailto($_POST['post_mailto']);
    $config->setConfig('mail_host', $_POST['post_mailhost']);

    if ($_POST['filter_apply'] == "on")
    {
        $config->setConfig('filter_apply', '1');
        $config->setConfig('filter_date', mktime(0, 0, 0, $_POST['filter_month'], ($_POST['filter_day'] > 31 ? 31 : $_POST['filter_day']), $_POST['filter_year']));
    }
    else
    {
    	$config->setConfig('filter_apply', '0');
    	$config->setConfig('filter_date', 0);
    }

    $html .= "Changes saved.";
}

$html .= "<form id=options name=options method=post action=?a=admin>";
$html .= "<div class=block-header2>Look and feel</div>";
$html .= "<table class=kb-subtable>";
$html .= "<tr><td width=120><b>Banner:</b></td><td><select id=style_banner name=style_banner>";

$dir = "img/logo/";
if (is_dir($dir))
{
    if ($dh = opendir($dir))
    {
        while (($file = readdir($dh)) !== false)
        {
            $file = substr($file, 0, strpos($file, "."));
            if (!is_dir($dir.$file))
            {
                $html .= "<option value=\"".$file."\"";
                if ($file == $config->getConfig('style_banner'))
                {
                    $html .= " selected=\"selected\"";
                }
                $html .= ">".$file."</option>";
            }
        }
        closedir($dh);
    }
}
$html .= "</td></tr>";
$html .= "<tr><td width=120><b>Style:</b></td><td><select id=style_name name=style_name>";

$dir = "style/";
if (is_dir($dir))
{
    if ($dh = opendir($dir))
    {
        while (($file = readdir($dh)) !== false)
        {
            if (is_dir($dir.$file))
            {
                if ($file == "." || $file == ".." || $file == ".svn")
                {
                    continue;
                }
                $html .= "<option value=\"".$file."\"";
                if ($file == $config->getConfig('style_name'))
                {
                    $html .= " selected=\"selected\"";
                }
                $html .= ">".$file."</option>";
            }
        }
        closedir($dh);
    }
}
$html .= "</select></td></tr>";
$html .= "<tr><td></td></tr></table>";

$html .= "<div class=block-header2>Global options</div>";
$html .= "<table class=kb-subtable>";
$html .= "<tr><td width=120><b>Display killpoints:</b></td><td><input type=checkbox name=kill_points id=kill_points";
if ($config->getConfig('kill_points'))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";

$html .= "<tr><td width=120><b>Enable Comments:</b></td><td><input type=checkbox name=comments id=comments";
if ($config->getConfig('comments'))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td width=120><b>Require password for Comments:</b></td><td><input type=checkbox name=comments_pw id=comments_pw";
if ($config->getConfig('comments_pw'))
{
	$html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td width=120><b>Display Comment Count on Killlists:</b></td><td><input type=checkbox name=comments_count id=comments_count";
if ($config->getConfig('comments_count'))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td width=120><b>Display Standings:</b></td><td><input type=checkbox name=show_standing id=show_standing";
if ($config->getConfig('show_standing'))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";

$html .= "<tr><td width=120><b>Enable Lost Item Values</b></td><td><input type=checkbox name=item_values id=item_values";
if ($config->getConfig('item_values'))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td width=120><b>Use custom shipvalues:</b></td><td><input type=checkbox name=ship_values id=ship_values";
if ($config->getConfig('ship_values'))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td width=120><b>Display a link instead of POD on Battlesummary:</b></td><td><input type=checkbox name=bs_podlink id=bs_podlink";
if ($config->getConfig('bs_podlink'))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
// $html .= "<tr><td width=120><b>Display least active:</b></td><td><input type=checkbox name=least_active id=least_active";
// if ( $config->getLeastActive() )
// $html .= " checked=\"checked\"";
// $html .= "></td></tr>";
$html .= "<tr><td></td></tr></table>";

$html .= "<div class=block-header2>Posting</div>";
$html .= "<table class=kb-subtable>";

$html .= "<tr><td width=120><b>Post password:</b></td><td><input type=text name=post_password id=post_password size=20 maxlength=20 value=\"".$config->getPostPassword()."\"></td></tr>";
$html .= "<tr><td width=120><b>Killmail CC:</b></td><td><input type=text name=post_mailto id=post_mailto size=20 maxlength=80 value=\"".$config->getPostMailto()."\"> (e-mail address)</td></tr>";
$html .= "<tr><td width=120><b>Mailhost:</b></td><td><input type=text name=post_mailhost id=post_mailhost size=20 maxlength=80 value=\"".$config->getConfig('mail_host')."\"></td></tr>";
$html .= "<tr><td width=120><b>Disallow any killmails before:</b></td><td>".dateSelector($config->getConfig('filter_apply'), $config->getConfig('filter_date'))."</td></tr>";
$html .= "<tr><td width=120><b>Forbid posting</b></td><td><input type=checkbox name=post_forbid id=post_forbid";
if ($config->getConfig('post_forbid'))
{
    $html .= " checked=\"checked\"";
}
$html .= "> (Checking this option disables mailposting)</td></tr>";
$html .= "<tr><td width=120><b>Enable auto-addition of unknown Items:</b></td><td><input type=checkbox name=adapt_items id=adapt_items";
if ($config->getConfig('adapt_items'))
{
    $html .= " checked=\"checked\"";
}
$html .= "> (This is in case we can't supply a dump with the new items when Kali goes live)</td></tr>";
$html .= "<tr><td width=120><b>ReAdd known killmails</b></td><td><input type=checkbox name=readd_dupes id=readd_dupes";
if ($config->getConfig('readd_dupes'))
{
    $html .= " checked=\"checked\"";
}
$html .= "> (This internally reparses a dupe on submit)</td></tr>";
$html .= "</table>";

$html .= "<div class=block-header2>Save changes</div>";
$html .= "<table class=kb-subtable>";

$html .= "<tr><td width=120></td><td><input type=submit name=submit value=\"Save\"></td></tr>";
$html .= "</table>";

$html .= "</form>";

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();

function dateSelector($apply, $date)
{
	if ($date > 0)
    {
		$date = getdate($date);
	}
    else
    {
		$date = getdate();
	}
	$html = "<input type=\"text\" name=\"filter_day\" id=\"filter_day\" style=\"width:20px\" value=\"{$date['mday']}\"/>&nbsp;";
	$html .= "<select name=\"filter_month\" id=\"filter_month\">";
	for ($i = 1; $i <= 12; $i++)
    {
		$t = mktime(0, 0, 0, $i, 1, 1980);
		$month = date("M", $t);
		if($date['mon'] == $i)
        {
            $selected = " selected=\"selected\"";
        }
        else
        {
            $selected = "";
        }

		$html .= "<option value=\"$i\"$selected>$month</option>";
	}
	$html .= "</select>&nbsp;";

	$html .= "<select name=\"filter_year\" id=\"filter_year\">";
	for ($i = date("Y")-7; $i <= date("Y"); $i++)
    {
		if ($date['year'] == $i)
        {
            $selected = " selected=\"selected\"";
        }
        else
        {
            $selected = "";
        }
		$html .= "<option value=\"$i\"$selected>$i</option>";
	}
	$html .= "</select>&nbsp;";
	$html .= "<input type=checkbox name=filter_apply id=filter_apply";
	if ($apply)
    {
        $html .= " checked=\"checked\"";
    }
	$html .= "/>Apply&nbsp;";
	return $html;
}
?>