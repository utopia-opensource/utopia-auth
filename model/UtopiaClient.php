<?php
	namespace App\Model;
	
	class UtopiaClient {
		public $api_version    = "1.0";
		public $status_code    = 100;
		//100 = -
		//700 = wait for approve auth request
		
		protected $credentials = [];
		protected $client      = null; //HttpClient
		
		public function __construct() {
			$this->credentials = [
				'token' => getenv('api_token'),
				'host'  => getenv('api_host'),
				'port'  => getenv('api_port')
			];
			$this->client = new \App\Model\HttpClient();
		}
		
		function api_query($method = "", $params = [], $filter = []) {
			$post_fields = [
				'method' => $method,
				'params' => $params,
				'token'  => $this->credentials['token']
			];
			if($filter != []) {
				$post_fields['filter'] = $filter;
			}
			
			$json = $this->client->query(
				"http://" . $this->credentials['host'] . ":" . $this->credentials['port'] . "/api/" . $this->api_version,
				$post_fields
			);
			
			$response = \App\Model\Utilities::json2Arr($json);
			if(!isset($response['result'])) {
				$response['result'] = [];
			}
			
			return $response;
		}
		
		//public function filterPubKey($pubkey = ""): string {
		//	
		//}
		
		function sendEmailMessage($data_to = "", $data_subject = "hello", $data_body = "empty message"): bool {
			$this->reset_status();
			//data_to = pubkey
			$params = [
				'to'      => $data_to,
				'subject' => $data_subject,
				'body'    => $data_body
			];
			//exit(json_encode($params));
			
			$response = $this->api_query("sendEmailMessage", $params);
			//exit(json_encode($response));
			
			if(!isset($response['result'])) {
				$this->last_error = "failed to send api request";
				return false;
			} else {
				//debug
				//$this->last_error = json_encode($response);
				//return false;
				//debug
				if(isset($response['result']) && $response['result'] == true) {
					return true;
				} else {
					//it looks like pubkey is not authorized as a contact
					//somehow change that. or remove
					$this->status_code = 700;
					$auth_request_sended = $this->sendAuthorizationRequest($data_to);
					$this->last_error = "";
					return false;
					//$this->last_error = $response['error'];
				}
			}
		}
		
		function reset_status() {
			$this->status_code = 100;
		}
		
		function sendAuthorizationRequest($pubkey = "", $message = "uAuth request"): bool {
			if($pubkey == "") {
				$this->last_error = "empty pubkey given for sendAuthorizationRequest method";
				return false;
			}
			$params = [
				'pk'      => $pubkey,
				'message' => $message
			];
			$response = $this->api_query("sendAuthorizationRequest", $params);
			//exit(json_encode($response) . PHP_EOL . PHP_EOL . json_encode($params));
			if(isset($response['error']) && $response['error'] != "") {
				$this->last_error = "failed to request authorization via sendAuthorizationRequest method";
				return false;
			}
			return true;
		}
		
		public function whois($pubkey = "") {
			$params = [
				'owner' => $pubkey
			];
			$response = $this->api_query("getWhoIsInfo", $params);
			return $response['result'];
		}
	}
	