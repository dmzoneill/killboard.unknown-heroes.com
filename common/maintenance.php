<?php
require_once("class.page.php");

$page = new Page("Down for maintenance");
$html .= "The killboard is currently down for maintenance and/or updates. We'll be back soon™ !";
if (KB_MAINTENANCE_MSG != "")
    $html .= "<p><br/>Additional info: " . KB_MAINTENANCE_MSG;
$html .= "<p><br/><a href=\"http://eve-killboard.net\">eve-killboard.net</a>";

$page->setContent($html);
$page->generate();
?>