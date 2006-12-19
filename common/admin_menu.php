<?php
require_once("class.box.php");

$menubox = new Box();
$menubox->setIcon("menu-item.gif");
$menubox->addOption("link","Generic", "?a=admin");
$menubox->addOption("link","Contracts", "?a=admin_cc&op=view&type=contract");
$menubox->addOption("link","Campaigns", "?a=admin_cc&op=view&type=campaign");
$menubox->addOption("link","Standings", "?a=admin_standings");
$menubox->addOption("link","Ship Values", "?a=admin_shp_val");
$menubox->addOption("link","Synchronization", "?a=admin_sync");
$menubox->addOption("link","Map Options", "?a=admin_mapoptions");
$menubox->addOption("link","Post Permissions", "?a=admin_postperm");
$menubox->addOption("link","Mods", "?a=admin_mods");
if (file_exists('common/admin_feed.php'))
{
    $menubox->addOption("link","Feeds", "?a=admin_feed");
}
$menubox->addOption("link","Auditing", "?a=admin_audit");
$menubox->addOption("link","Kill Import - files", "?a=kill_import");
$menubox->addOption("link","Kill Import - csv", "?a=kill_import_csv");
$menubox->addOption("link","Kill Export - files", "?a=kill_export");
$menubox->addOption("link","Kill Export - csv", "?a=kill_export_search");
$menubox->addOption("link","Logout", "?a=logout");
?>
