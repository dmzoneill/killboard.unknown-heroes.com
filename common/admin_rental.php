<?
  require_once( "class.page.php" );
  require_once( "admin_menu.php" );

  $page = new Page( "Administration - Rental" );

  $sql = "select *
            from kb3_rental
           where rtl_site = '".KB_SITE."'";
                                                                                
  $qry = new DBQuery();
  $qry->execute( $sql ) or die( $qry->getErrorMsg() );
                                                                                
  if ( $qry->recordCount() == 1 ) {
    $html = "<table class=kb-table cellspacing=1>";
    $html .= "<tr class=kb-table-header><td align=center width=100>Weekly fee</td><td align=center width=140>Current week</td><td align=center width=160>Payment received up to</td><td align=center width=140>Payment required</td><td align=center width=140>Contact</td></tr>";
                                                                                
    $row = $qry->getRow();
    if ( $row['rtl_paidyear'] > date( "Y" ) ) {
      $due = 0;
    }
    else if ( $row['rtl_paidweek'] > date( "W" ) && $row['rtl_paidyear'] < date( "Y" ) ) {
        $due = date( "W" ) + ( 52 - $row['rtl_paidweek'] );
    }
    else
      $due = date( "W" ) - $row['rtl_paidweek'];
                                                                                
    $due = $due * $row['rtl_rent'];
                                                                                
    if ( $due <= 0 ) {
      $due = 0;
      $paydue = "-";
    }
    else $paydue = $due."M ISK";
                                                                                
    if ( $due > 0 )
      $dueclass = "kl-loss";
    else
      $dueclass = "kl-kill";
                                                                                
    if ( date( "W" ) < 10 )
      $curweek = substr( date( "W" ), 1, 1 );
    else
      $curweek = date( "W" );
                                                                                
    $html .= "<tr class=kb-table-row-odd><td align=center>".$row['rtl_rent']."M ISK</td><td align=center>Week ".$curweek." (".date( "Y" ).")</td><td align=center>Week ".$row['rtl_paidweek']." (".$row['rtl_paidyear'].")</td><td class=".$dueclass." align=center>".$paydue."</td><td align=center width=70>".$row['rtl_contact']."</td></tr>";
                                                                                
   $html .= "</table><br>Payment is to be wired to rig0r ingame. Thank you !";
  }
  else {
    $html .= "No details.<br><br>";
  }
                                                                                
  $page->setContent( $html );
  $page->addContext( $menubox->generate() );
  $page->generate();
?>
