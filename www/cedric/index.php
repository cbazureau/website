<?php 
	
require_once(__DIR__."/../../app/model/Bootstrap.php");
$boot = new \core\Bootstrap();
$output = $boot->launch($_SERVER["REQUEST_URI"],$_GET,$_POST,$_COOKIE);
die($output);
