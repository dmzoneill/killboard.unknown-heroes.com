<?
for($i=8178;$i<11000;$i++){
	$url = "http://freelancer-alliance.net/Killboard/index.php?a=kill_mail&kll_id=$i";
	if($fp = @fopen("$url", "r")){
		$current_line = fgets($fp);
		while (!feof($fp)) {
  			$current_line .= fgets($fp);
		}
		$mail = explode("Original Killmail",(eregi_replace("<br />","",strip_tags($current_line))));
		$mail = eregi_replace("<br />","",$mail[1]);

		if(stristr($mail,"Corp: Unknown-Heroes")){
			$mail = eregi_replace("&nbsp;","",trim($mail));
			$fpt = fopen("C:\\xampp\\htdocs\\UNKH\\$i.txt",'w');
			if(fputs($fpt,$mail)){
				print "Success retrieved UNKH Killmail $i \n";
			}
			else {
				print "Error $i\n";
			}	
		}
		else {
			$mail = eregi_replace("&nbsp;","",trim($mail));
			$fpt = fopen("C:\\xampp\\htdocs\\FLA\\$i.txt",'w');
			if(fputs($fpt,$mail)){
				print "Success retrieved non UNKH Killmail $i \n";
			}
			else {
				print "Error $i\n";
			}	
		}

	fclose($fp);
	}
	else {
		die("Unable to request page on id : $i \n");
	}
}
?>