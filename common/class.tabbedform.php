<?
  require_once( "class.page.php" );

  class TabbedForm
  {
    function TabbedForm( $page )
    {
      $this->page_ = $page;
      $this->tabs_ = array();
    }

    function addTab( $id, $name, $html )
    {
      $this->tabs_[$id]['name'] = $name;
      $this->tabs_[$id]['content'] = $html;
    }

    function generate()
    {
      $html = "<table class=kb-table><tr class=kb-table-header>";
      foreach( $this->tabs_ as $k => $v ) {
        $html .= "<td width=100 align=center><a href=\"javascript: tabToggle( '".$k."' );\">".$v['name']."</a></td>";
      }
      $html .= "</tr></table><div class=tab>";
      $c = 0;
      foreach( $this->tabs_ as $k => $v ) {
        $html .= "<div id=".$k." style=\"display: none;\">".
	         $v['content'].
		 "</div>";
        if ( $c == 0 ) 
	  $this->page_->setOnLoad( "curtab = document.getElementById( '".$k."' ); tabToggle( '".$k."' );" );
	$c++;
      }
      $html .= "</div>";
      return $html;
    }
  }
?>
