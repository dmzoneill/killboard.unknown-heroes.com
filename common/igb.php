<?php
require_once("class.page.php");

$page = new Page("Killboard IGB Menu");

$page->setContent($smarty->fetch(get_tpl('igb')));
$page->generate();
?>
