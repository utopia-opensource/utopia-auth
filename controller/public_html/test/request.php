<?php
	session_start();
	require_once __DIR__ . "/../../vendor/autoload.php";
	
	$handler = new \App\Controller\Handler();
	
	$client = new \App\Model\UtopiaClient();
	$client->sendAuthorizationRequest("2D486D51D638517B7548E9F4A0AEB4281EECB7AB030A43498E77385FC511AF43");
	