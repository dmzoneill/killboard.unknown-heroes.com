<?php

//$people = array("Belos Taoris","Burn Lump","Chupicabre","Clogman","DDredd","Gythar","John Koening","Kanoro","Kee Ris","Mrs Dinj","Mufeater","Reiv","Serina Giovanni","ecumenetheo");
$people = array("caliera");

if ($handle = opendir("C:\\xampp\\htdocs\\FLA")) {
    echo "Directory handle: $handle\n";
    echo "Files:\n";

    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        
		if($fp = fopen("C:\\xampp\\htdocs\\FLA\\$file", "r")){
			$current_line = fgets($fp);
			while (!feof($fp)) {
  				$current_line .= fgets($fp);
			}
			fclose($fp);
			
			//if(stristr($current_line,"Unknown-Heroes")){
			//	$mail = $current_line;
			//	$fpt = fopen("C:\\xampp\\htdocs\\NEWM\\$file",'w');
			//	if(fputs($fpt,$mail)){
				//	print "Success retrieved UNKH Killmail $i \n";
				//	fclose($fpt);
			//	}
				//else {
					//print "Error $i\n";
			//	}	
		//	}
			//else {
				for($p=0;$p<count($people);$p++){
					if(stristr($current_line,"$people[$p]")){
						//$mail = eregi_replace("Caldari Anvil","Unknown-Heroes",$current_line);
						$mail = $current_line;
						$fpt = fopen("C:\\xampp\\htdocs\\marie\\$file",'w');
						if(fputs($fpt,$mail)){
							print "Success retrieved UNKH Killmail $i \n";
							fclose($fpt);
						}
						else {
							print "Error $i\n";
						}	
					}
				}
			//}
		}
	}	
}	


	
	
?>