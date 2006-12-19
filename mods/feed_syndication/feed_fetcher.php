<?php
        //////
//////  //////
//////  liq's feed syndication mod v1.3
////////////
////////////
////////////
////////////
 

@set_time_limit(0);
require_once( "common/class.kill.php" );
require_once( "common/class.parser.php" );
if ( file_exists("../../common/class.comments.php") ) // for the Eve-Dev Comment Class
  	require_once( "class.comments.php" );
if ( file_exists("../../common/class.comment.php") ) // for the D2 Killboard Comment Class
  	require_once( "class.comment.php" );

$insideitem = false;
$tag = "";
$title = "";
$description = "";
$link = "";
$x=0;

function setConfig($key, $value) {
    global $config;
    if (method_exists($config, 'setConfig'))
        return $config->setConfig($key, $value);
    $qry = new DBQuery();
    $qry->execute("select cfg_value from kb3_config
                   where cfg_key = '".$key."' and cfg_site = '".KB_SITE."'");
    if ($qry->recordCount())
        $sql = "update kb3_config set cfg_value = '".$value."'
                where cfg_site = '".KB_SITE."' and cfg_key = '".$key."'";
    else
        $sql = "insert into kb3_config values ( '".KB_SITE."','".$key."','".$value."' )";
    $qry->execute($sql);
}

function getConfig($key) {
    global $config;
    if (method_exists($config, 'getConfig'))
        return $config->getConfig($key);
    $qry = new DBQuery();
    $qry->query("select ".$key." from kb3_config where cfg_site = '".KB_SITE."'");
    $row = $qry->getRow();
    if (isset($row[$key]))
        return $row[$key];
    return false;
}

function delConfig($key) {
    global $config;
    $qry = new DBQuery();
    $qry->execute("delete from kb3_config where cfg_key = '".$key."' and cfg_site = '".KB_SITE."'");
}

class Fetcher {

function grab($url, $str) {
		global $x, $uurl;
		$x=0;
		$fetchurl = $url.$str;
		$uurl = $url;
       	$xml_parser = xml_parser_create();
		xml_set_object ( $xml_parser, $this );
		xml_set_element_handler($xml_parser, "startElement", "endElement");
		xml_set_character_data_handler ( $xml_parser, 'characterData' );
		$fp = @fopen($fetchurl,"r"); 
		while ($chunk = @fread($fp, 4096)) {
			$data .= $chunk;
		}
		$data = preg_replace('<<!--.*?-->>', '', $data); // remove <!-- Cached --> message, else it will break gzinflate
		
		if (!@gzinflate($data)) {
			$cprs = "raw HTML stream";
		} else { 
			$data = gzinflate($data);
			$cprs = "GZip compressed stream";
		}	
		
		if (!xml_parse( $xml_parser, $data, feof($fp) ) && !feof($fp) )
			return "<i>Error getting XML data from ".$url."</i><br><br>";		
			
		@fclose($fp);
		xml_parser_free($xml_parser);
		
		if ($x)
			$html .= "<div class=block-header2>".$x." kills added from feed: ".$url." <i>(".$cprs.")</i></div>";
		else
			$html .= "<div class=block-header2>No kills added from feed: ".$url." <i>(".$cprs.")</i></div>";
		//echo "url:".$url." --- ";
		//echo " strg:".$str;
		
return $html;
}

function startElement($parser, $name, $attrs) {
	global $insideitem, $tag, $title, $description, $link;
	if ($insideitem)
		$tag = $name;
	elseif ($name == "ITEM")
		$insideitem = true;
}

function endElement($parser, $name) {
	global $insideitem, $tag, $title, $description, $link, $html, $x, $uurl;

	if ($name == "ITEM") {
		if ( isset( $description ) ) {
		  	$parser = new Parser( $description );
      		$killid = $parser->parse( true );
      		if ( $killid == 0 || $killid == -1 || $killid == -2 ) {
				if ( $killid == 0 && getConfig('fetch_verbose') )
                	$html .= "Killmail is malformed.<br>";
		       	if ( $killid == -2 && getConfig('fetch_verbose') )
  	  	            $html .= "Killmail is not related to ".KB_TITLE.".<br>";
	  			if ( $killid == -1 && getConfig('fetch_verbose') )
				    $html .= "Killmail already posted <a href=\"?a=kill_detail&kll_id=".$parser->dupeid_."\">here</a>.<br>";
			}
			else {
				$qry = new DBQuery();
				$qry->execute( "insert into kb3_log	values( ".$killid.", '".KB_SITE."','".$_SERVER['REMOTE_ADDR']."',now() )" );
				$html .= "Killmail succsessfully posted <a href=\"?a=kill_detail&kll_id=".$killid."\">here</a>.<br>";

				if (class_exists('Comments') && getConfig('fetch_comment')) { // for the Eve-Dev Comment Class
					$comments = new Comments($killid);
                	$comments->addComment("liq's feed syndication", getConfig('fetch_comment')." mail fetched from: ".$uurl.")");
				}
				if (class_exists('Comment') && getConfig('fetch_comment')) { // for the D2 Killboard Comment Class
					$comment = new Comment($killid);
                	$comment->postComment(getConfig('fetch_comment')." \n\n\n <i>mail fetched from:\n ".$uurl."</i>", "liquidism");
				}
				$x++;
			}
    	}
		$title = "";
		$description = "";
		$link = "";
		$insideitem = false;
	}
}

function characterData($parser, $data) {
	global $insideitem, $tag, $title, $description, $link;
	if ($insideitem) {
		switch ($tag) {
			case "TITLE":
			$title .= $data;
			break;
			case "DESCRIPTION":
			$description .= $data;
			break;
			case "LINK":
			$link .= $data;
			break;
		}
	}
}

}

?>
