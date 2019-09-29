<?php
	namespace App\Model;
	
	class UtopiaClient {
		public $api_version    = "1.0";
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
				'filter' => $filter,
				'token'  => $this->credentials['token']
			];
			
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
			$params = [
				'to'      => $data_to,
				'subject' => $data_subject,
				'body'    => $data_body
			];
			//exit(json_encode($params));
			
			$response = $this->api_query("sendEmailMessage", $params);
			//exit(json_encode($response));
			
			if(!isset($response['result'])) {
				return false;
			} else {
				if($response['result'] == true) {
					return true;
				} else {
					return false;
				}
			}
		}
	}
	