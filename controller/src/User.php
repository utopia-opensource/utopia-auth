<?php
	namespace App\Controller;
	
	class User {
		public $data = [
			"nick_name" => "Anonymous",
			"is_auth"   => false,
			"pubkey"    => "",
			"hash"      => "d41d8cd98f00b204e9800998ecf8427e"
		];
		public $last_error = "";
		protected $db      = null;
		private $config    = [];
		
		public function __construct(bool $need_checkAuth = false) {
			$this->reloadData();
			if($need_checkAuth) {
				$this->checkAuth();
			}
		}
		
		public function reloadData() {
			$this->data['is_auth'] = $_SESSION['pubkey'];
			if($this->data['is_auth']) {
				$this->data['pubkey'] = $_SESSION['pubkey'];
				$this->data['hash']   = md5($_SESSION['pubkey']);
			}
		}
		
		public function checkAuth(): void {
			if($this->data['is_auth'] == false) {
				$this->redirect('/');
			}
		}
		
		public function setdb($db): void {
			$this->db = &$db;
		}
		
		public function redirect($url = '/') {
			header("Location: " . $url); exit;
		}
	}
