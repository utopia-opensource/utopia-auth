<?php
	session_start();
	require_once __DIR__ . "/../vendor/autoload.php";
	
	$handler = new \App\Controller\Handler();
	$render_data = [
		'tag'   => 'auth_wait',
		'title' => 'wait for auth',
		'user'  => $handler->user->data
	];
	
	$pubkey = \App\Model\Utilities::data_filter($_POST['pubkey']);
	$handler->utopia_unit();
	
	//check if the user is already in the contact list
	//if yes, then go to the second step (auth_request)
	
	//now working. WTF??
	//$pubkeyKnown = $handler->isPubkeyKnown($pubkey);
	
	$whois_info = $handler->get_whois($pubkey);
	$parsed = $handler->parse_whois($whois_info);
	$pubkeyKnown = $parsed['pubkey_known']; //fix
	//exit(var_dump($pubkeyKnown));
	
	if(!$pubkeyKnown) {
		//step 1
		$status_success = $handler->auth_first($pubkey);
		if(!$status_success) {
			$render_data['tag']   = 'auth_error';
			$render_data['error'] = $handler->last_error;
		} else {
			$render_data['tag']   = 'auth_update';
			$render_data['error'] = "";
		}
		$handler->render($render_data);
	}
	
	if(! $handler->auth_request($pubkey)) {
		//step 2
		switch($handler->last_code) {
			default:
				$render_data['tag']   = 'auth_error';
				$render_data['error'] = $handler->last_error;
				break;
			case 700:
				$render_data['tag']   = 'auth_wait';
				$render_data['error'] = "";
				break;
		}
	}
	
	$handler->render($render_data);
	