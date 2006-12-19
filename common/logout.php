<?php
require_once("db.php");
require_once("class.session.php");

$session = new Session();
$session->destroy();
header('Location: ?a=admin');
?>