<?php
	namespace App\Model;
	//alpha
	class HttpClient {
		protected $cookie  = '';
		protected $referer = '';
		
		function query($url, $p = null) {
			$curlDefault = true;
			$headers = [];
			//with no guzzle
			if($curlDefault) {
				$ch = \curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				if(isset($_SERVER['HTTP_USER_AGENT'])) {
					curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
				}
				if($this->referer != '') {
					curl_setopt($ch, CURLOPT_REFERER, $this->referer);
				}
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				if($this->cookie != '') {
					curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
				}
				if($p != null) {
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
					curl_setopt($ch, CURLOPT_POST, 1);
					if(\App\Model\Utilities::isJson($p)) {
						curl_setopt($ch, CURLOPT_POSTFIELDS, $p);
					} else {
						curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($p));
					}
					$headers[] = "Content-Type: application/json";
				}
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				$result = curl_exec($ch);
				curl_close($ch);
				if($result) {
					return $result;
				} else {
					return '';
				}
			} else {
				try {
					//TODO: implement cookie
					$client = new \GuzzleHttp\Client();
					if($p != null) {
						parse_str($p, $params);
						$request = $client->post($url, [], [
							'body' => $params
						]);
					} else {
						$request = $client->get($url);
					}
					return $request->getbody();
				} catch(Exception $e) {
					//TODO: traceback
					echo 'guzzle error: ' . $e->getMessage();
				}
			}
		}
		
		function GET($url) {
			return $this->query($url);
		}
	}
	