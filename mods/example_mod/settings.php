<?
require_once( "common/class.page.php" );
require_once( "common/admin_menu.php" );

$page = new Page( "Settings - Example Mod" );
                                                                         
$page->setContent( $html );
$page->addContext( $menubox->generate() );
$page->generate();
?>