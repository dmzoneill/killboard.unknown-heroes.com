<?php
require_once('class.thumb.php');

$thumb = new thumb($_GET['id'], $_GET['size'], $_GET['type']);
$thumb->display();
?>