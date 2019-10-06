<?php
	session_start();
	require_once __DIR__ . "/../vendor/autoload.php";
	
	$handler = new \App\Controller\Handler();
	$render_data = [
		'tag'   => 'auth_update',
		'title' => 'wait for auth',
		'user'  => $handler->user->data,
		'error' => ''
	];
	$handler->utopia_unit();
	
	$status_success = $handler->auth_wait();
	if(!$status_success) {
		$render_data['error'] = $handler->last_error;
		$handler->render($render_data);
	} else {
		$handler->user->redirect('/');
	}
	