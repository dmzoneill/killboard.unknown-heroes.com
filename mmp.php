<?php


if ($handle = opendir('c:\\xammp\htdocs\\NEW')) {
    echo "Directory handle: $handle\n";
    echo "Files:\n";

    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        
		if($fp = @fopen("$file", "r")){
			$current_line = fgets($fp);
			while (!feof($fp)) {
  				$current_line .= fgets($fp);
			}
			fclose($fp);
			if(stristr($current_line,"Caldari Anvil")){
				$mail = eregi_replace("Caldari Anvil","Unknown-Heroes",$current_line);
				$fpt = fopen("C:\\xampp\\htdocs\\NEWM\\$file",'w');
				if(fputs($fpt,$mail)){
					print "Renamed Corp Succesffuly $i \n";
				}
				else {
					print "Error $i\n";
				}	
			}
		}
		
	}	
}	


	
	
?>