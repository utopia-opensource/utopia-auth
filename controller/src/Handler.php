<?php
	namespace App\Controller;
	//класс для связывания Logic, Database и User
	class Handler {
		public $logic      = null;
		public $user       = null;
		public $renderT    = null;
		public $last_error = "";
		public $last_code  = 100; //inherit from UtopiaClient->status_code
		
		private $db      = null;
		private $enviro  = null;
		private $client  = null; //UtopiaClient
		
		public function __construct() {
			$this->enviro  = new \App\Model\Environment();
			$this->db      = new \App\Model\DataBase();
			$this->logic   = new \App\Controller\Logic();
			$this->user    = new \App\Controller\User();
			$this->renderT = new \App\Controller\Render([]);
			
			$this->logic->setdb($this->db);
			$this->user->setdb($this->db);
			$this->logic->setUser($this->user);
		}
		
		public function render($data = []) {
			$this->renderT = new \App\Controller\Render($data);
			$this->renderT->twigRender();
		}
		
		public function utopia_unit() {
			$this->client = new \App\Model\UtopiaClient();
		}
		
		public function auth_request($pubkey = ""): bool {
			if($pubkey == "") {
				$pubkey = \App\Model\Utilities::data_filter($_POST['pubkey']);
			}
			if($pubkey == "") {
				$this->last_error = "empty pubkey given";
				return false;
			}
			if(strlen($pubkey) != 64) {
				$this->last_error = "pubkey must be 64 characters long";
				return false;
			}
			
			//create auth key & seed
			$auth_code = \App\Model\Utilities::generateCode(16);
			$auth_seed = \App\Model\Utilities::generateCode(8);
			//clear last keys
			$sql_query = "DELETE FROM auth WHERE pubkey='" . $pubkey . "'";
			if(! $this->db->tryQuery($sql_query)) {
				$this->last_error = "failed to clear old authorization keys";
				return false;
			}
			//add auth entry to db
			$sql_query = "INSERT INTO auth SET pubkey='" . $pubkey . "', code='" . $auth_code . "', seed='" . $auth_seed . "'";
			if(! $this->db->tryQuery($sql_query)) {
				$this->last_error = "failed to create new authorization key, query: \n" . $sql_query;
				return false;
			}
			
			//place seed in session
			$_SESSION['auth_seed'] = $auth_seed;
			
			$subject = "uAuthExample request";
			$message = "An authorization was requested for your public key. If you did not request authorization, simply delete this message.";
			$message .= "\n\n";
			$message .= "To confirm authorization on the uAuthExample service, follow the link:\n";
			$link = "utopia://auth/check/" . $auth_code;
			$message .= $link;
			
			$result = $this->client->sendEmailMessage($pubkey, $subject, $message);
			if(!$result) {
				$client_code = $this->client->status_code;
				switch($client_code) {
					case 700:
						//we pass the code that the authorization request has been sent
						$_SESSION['wait_pubkey'] = $pubkey;
						$this->last_code = $client_code;
						break;
					case 100:
						//regular code. just handle the error
						$this->last_error = "failed to send uMail. status " . $client_code;
						if($this->client->last_error != "") {
							$this->last_error .= ": " . $this->client->last_error;
						}
						break;
				}
				return false;
			}
			return true;
		}
		
		public function get_whois($pubkey = "") {
			return $this->client->whois($pubkey);
		}
		
		public function parse_whois($whois_info = []) {
			if(!isset($whois_info['general'])) {
				$this->last_error = "Failed to determine public key ownership";
				return [];
			}
			$general = $whois_info['general'];
			
			$is_known = false;
			for($i = 0; $i < count($general); $i++) {
				$line = $general[$i];
				if($line['name'] == "You have this Public Key in your contact list") {
					if($line['value'] == "Yes") {
						$is_known = true;
					} else {
						$is_known = false;
					}
					break;
				}
			}
			
			$result = $whois_info;
			$result['pubkey_known'] = $is_known;
			return $result;
		}
		
		//not working ???
		public function isPubkeyKnown($pubkey = ""): bool {
			$whois_info = $this->get_whois();
			if(!isset($whois_info['general'])) {
				$this->last_error = "Failed to determine public key ownership";
				return false;
			}
			//check if this public key is added to us as a contact
			$whois_general = $whois_info['general'];
			$pubkey_isContact = false;
			for($i=0; $i < count($whois_general); $i++) {
				$entry = $whois_general[$i];
				if($entry['name'] == "You have this Public Key in your contact list") {
					$pubkey_isContact = true;
					break;
				}
			}
			if(!$pubkey_isContact) {
				$this->last_error = "You have not yet confirmed the authorization request (pubkey not known)";
				return false;
			} else {
				return true;
			}
		}
		
		public function auth_first($pubkey = ""): bool {
			//recreate client //fix
			$this->utopia_unit();
			//send a request for permission to include the user in our contact list
			$status_success = $this->client->sendAuthorizationRequest($pubkey);
			
			if(!$status_success) {
				$this->last_error = $this->client->last_error;
			} else {
				$_SESSION['wait_pubkey'] = $pubkey;
			}
			
			return $status_success;
		}
		
		public function auth_wait($pubkey = "", $pubkey_unknown = false): bool {
			if($pubkey == "") {
				if(!isset($_SESSION['wait_pubkey'])) {
					$this->last_error = "There is currently no authorization request pending confirmation.";
					return false;
				}
				$pubkey = $_SESSION['wait_pubkey'];
			}
			
			if(strlen($pubkey) != 64) {
				$this->last_error = "Invalid or empty pubkey in session";
				return false;
			}
			//check if this public key is added to us as a contact
			/* if(!$pubkey_unknown) {
				$pubkey_isContact = $this->isPubkeyKnown($pubkey);
				if(!$pubkey_isContact) {
					return false;
				}
			} */
			if(!$pubkey_unknown) {
				$whois_info = $this->get_whois($pubkey);
				$parsed = $this->parse_whois($whois_info);
				$pubkeyKnown = $parsed['pubkey_known'];
			}
			
			if(!$pubkeyKnown) {
				return false;
			}
			//pubkey is authorized, go ahead
			$status_success = $this->auth_request($pubkey);
			return $status_success;
		}
		
		public function auth_check(): bool {
			//get & filter auth code
			$auth_code = \App\Model\Utilities::data_filter($_GET['code']);
			if($auth_code == "") {
				$this->last_error = "empty auth code given";
				return false;
			}
			//strlen(authcode) == 16 by default
			//TODO: make it variable
			if(strlen($auth_code) != 16) {
				$this->last_error = "auth code must be 64 characters long";
				return false;
			}
			
			//check seed from session
			if(!isset($_SESSION['auth_seed']) || $_SESSION['auth_seed'] == "") {
				$this->last_error = "authorization code is out of date";
				return false;
			}
			
			//find auth entry in db
			//no more than 1 hour per authorization code
			$sql_query = "SELECT id,pubkey,code,seed FROM auth WHERE seed='" . $_SESSION['auth_seed'] . "' AND code='" . $auth_code . "' AND auth_timestamp >= date_sub(NOW(), INTERVAL 1 HOUR)";
			$auth_data = $this->db->query2arr($sql_query);
			//check response
			if($auth_data == []) {
				$this->last_error = "invalid or out of date authorization code";
				return false;
			}
			
			//delete auth entry
			$sql_query = "DELETE from auth WHERE id=" . $auth_data['id'];
			if(! $this->db->tryQuery($sql_query)) {
				//The question of life, the universe and in general
				$this->last_error = "System error. Code 42";
				return false;
			}
			
			//finish auth
			$_SESSION['pubkey'] = $auth_data['pubkey'];
			return true;
		}
	}
