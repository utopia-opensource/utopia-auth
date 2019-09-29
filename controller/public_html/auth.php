<?php
	session_start();
	require_once __DIR__ . "/../vendor/autoload.php";
	
	$handler = new \App\Controller\Handler();
	$render_data = [
		'tag'   => 'auth_wait',
		'title' => 'wait for auth',
		'user'  => $handler->user->data
	];
	$handler->utopia_unit();
	
	if(! $handler->auth_request()) {
		$render_data['tag'] = 'auth_error';
		$render_data['error'] = $handler->last_error;
	}
	
	$handler->render($render_data);
	