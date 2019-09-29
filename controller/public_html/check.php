<?php
	session_start();
	require_once __DIR__ . "/../vendor/autoload.php";
	
	$handler = new \App\Controller\Handler();
	$handler->utopia_unit();
	
	if(! $handler->auth_check()) {
		$handler->render([
			'tag'   => 'auth_error',
			'title' => 'wait for auth',
			'user'  => $handler->user->data,
			'error' => $handler->last_error
		]);
	} else {
		$handler->user->redirect('/');
	}
	