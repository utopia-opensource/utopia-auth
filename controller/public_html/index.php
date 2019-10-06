<?php
	session_start();
	require_once __DIR__ . "/../vendor/autoload.php";
	
	$handler = new \App\Controller\Handler();
	
	$render_data = [
		'tag'   => 'home',
		'title' => 'main',
		'user'  => $handler->user->data
	);
	
	/* if($handler->user->data['is_auth']) {
		$whois = $handler->get_whois($handler->user->data['pubkey']);
		$render_data['whois'] = $whois;
	} */
	
	$handler->render($render_data);
	