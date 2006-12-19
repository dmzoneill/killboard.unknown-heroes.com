<?php
        //////
//////  //////
//////  liq's feed syndication mod v1.3
////////////
////////////
////////////
////////////


// set this to 1 if you are running a master killboard and want 
// to even fetch mails not related to your corp / alliance
define( MASTER, 0 );



@set_time_limit(0);
require_once( "feed_fetcher.php" );
require_once( "common/class.page.php" );
require_once( "common/admin_menu.php" );
require_once( 'common/class.corp.php' );
require_once( 'common/class.alliance.php' );

	$page = new Page( "Administration - Feeds" );
	$page->setAdmin();

	$validurl = "/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}((:[0-9]{1,5})?\/.*)?$/i";
	$html .= "<table class=kb-subtable>";

	if (getConfig('fetch_feed_count'))
		$feedcount = getConfig('fetch_feed_count');
	else
		$feedcount = 3;


	if ( $_POST['submit'] || $_POST['fetch']  ) {
			if ( ctype_digit($_POST['fetch_feed_count']) ) {
			setConfig('fetch_feed_count', $_POST['fetch_feed_count']);
			$feedcount = $_POST['fetch_feed_count'];
			for ($i = 99; $i>=$feedcount; $i--) {
				delConfig('fetch_url_'.$i);
			}
		}

		if ( $_POST['fetch_verbose'] )
			setConfig('fetch_verbose', '1');
		else
			setConfig('fetch_verbose', '0');
		
		if ( $_POST['fetch_compress'] )
			setConfig('fetch_compress', '0');
		else
			setConfig('fetch_compress', '1');

		if ( $_POST['fetch_comment'] )
			setConfig('fetch_comment', $_POST['fetch_comment']);
		else
			setConfig('fetch_comment', '');

 		for ($i = 1; $i<=$feedcount; $i++) {
  			$url = "fetch_url_".$i;
			if ( preg_match($validurl ,$_POST[$url]) ) {
				setConfig($url, $_POST[$url].':::'.$time[$i]);
				$feed[$i] = $_POST[$url];
    		} else
				setConfig($url, '');
				$feed[$i] = '';
		}
	}

	$feed = array();
  	for ($i = 1; $i<=$feedcount; $i++) {
		$str = getConfig('fetch_url_'.$i);
		$tmp = explode(':::', $str);
		$feed[$i] = $tmp[0];
		$time[$i] = $tmp[1];
  	}

  	if ( $_POST['fetch'] ) {
         if (CORP_ID && !MASTER) {
			 $corp = new Corporation(CORP_ID);
             $myid = '&corp='.urlencode($corp->getName());
         }
         if (ALLIANCE_ID && !MASTER) {
             $alli = new Alliance(ALLIANCE_ID);
             $myid = '&alli='.urlencode($alli->getName());
		}

  		 for ($i=1; $i<=$feedcount; $i++) {
			$feedfetch = new Fetcher();
			$cfg = "fetch_url_".$i;
			if (preg_match($validurl , $feed[$i]) && $_POST["fetch_feed_".$i]) {
			    $str = '';
				if ($time[$i])
                	$str .= '&lastkllid='.$time[$i];
				if ( $_POST['fetch_losses'] )
                	$str .= "&losses=1";
				if ( !getConfig('fetch_compress') )
                	$str .= "&gz=1";
				if ( $_POST['graball'] ) {
					for ($l = 1; $l<=52; $l++) {
						$html .= "<b>Week: ". $l ."</b><br>";
						$html .= $feedfetch->grab( $feed[$i]."&week=".$l, $myid.$str );
					}
			    } else
				$html .= $feedfetch->grab( $feed[$i], $myid.$str );
  			}

		    setConfig($cfg, $feed[$i].':::'.$lastkllid);
            $time[$i] = $lastkllid;
		}
  	}

	$html .= "<form id=options name=options method=post action=?a=settings_feed>";
    $html .= "</table>";

    $html .= "<div class=block-header2>Feeds</div><table>";

    for ($i = 1; $i<=$feedcount; $i++) {
        $html .= "<tr><td width=85px><b>Feed url #".$i."</b></td><td><input type=text name=fetch_url_".$i." size=50 class=password value=\"";
        if ( $feed[$i] )
           $html .= $feed[$i];
        $html .= "\"> ";
		$html .= "<input type=checkbox name=fetch_feed_".$i." id=fetch_feed_".$i;
     	if ( $feed[$i] )
			$html .= " checked=\"checked\"";
 		$html .= "><b>Fetch?</b><br>";
		$html .= "</td></tr>";
	}

	$html .= "</table><i>Example: http://killboard.eve-d2.com/?a=feed</i><br><br><br>";

	$html .= "<table><tr><td height=30px width=150px><b>Get kills instead of losses?</b></td>";
 	$html .= "<td><input type=checkbox name=fetch_losses id=fetch_losses>";
	$html .= "<i> (by default only your losses get fetched, when ticked all kills where one of your pilots is involved get fetched instead)</i></td></tr>";

    $html .= "<tr><td height=30px width=150px><b>Grab ALL mails from the feed servers?</b></td>";
	$html .= "<td><input type=checkbox name=graball id=graball>";
    $html .= "<i> (fetches all mails from the feed servers! use this may take upto several hours depending on the amount of kills to import!)</i></td>";
	$html .= "</tr></table><br><br>";

	$html .= "<input type=submit id=submit name=fetch value=\"Fetch!\"><br><br>";

	$html .= "<div class=block-header2>Options</div><table>";
	$html .= "<tr><td height=30px width=150px><b>Number of feeds:</b></td>";
	$html .= "<td><input type=text name=fetch_feed_count size=2 maxlength=2 class=password value=\"".$feedcount."\"</td></tr>";

	$html .= "<tr><td height=50px width=150px><b>Comment for automatically parsed killmails?</b></td>";
	$html .= "<td><input type=text size=50 class=password name=fetch_comment id=fetch_comment value=\"";
    if ( getConfig('fetch_comment') )
		$html .= getConfig('fetch_comment');
	$html .= "\"><br><i> (leave blank for none)</i><br></td></tr>";

	$html .= "<tr><td height=30px width=150px><b>Enable compression?</b></td>";
    $html .= "<td><input type=checkbox name=fetch_compress id=fetch_compress";
	if ( !getConfig('fetch_compress') )
		$html .= " checked=\"checked\"";
 	$html .= "><i> (enables GZip compression for feeds that support this feature, for streams that do not support GZip compression regular html mode will be used automatically)</i></td>";
	$html .= "</tr>";
	
	$html .= "<tr><td height=30px width=150px><b>Verbose mode?</b></td>";
    $html .= "<td><input type=checkbox name=fetch_verbose id=fetch_verbose";
	if ( getConfig('fetch_verbose') )
		$html .= " checked=\"checked\"";
 	$html .= "><i> (displays errormessages when the imported mail is rejected for being malformed, already existing, not being related etc.)</i></td>";
	$html .= "</tr></table><br><br>";

    $html .= "<input type=submit id=submit name=submit value=\"Save\">";
	$html .= "</form>";

    $page->addContext( $menubox->generate() );
    $page->setContent( $html );
    $page->generate();

?>