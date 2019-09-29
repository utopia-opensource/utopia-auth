<?php
	namespace App\Model;
	
	class Utilities {
		function isJson($string): bool {
			//\App\Model\Utilities::isJson
			return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
		}
		
		function json2Arr($json = ""): array {
			//\App\Model\Utilities::json2Arr
			if(! \App\Model\Utilities::isJson($json)) {
				return [];
			} else {
				return json_decode($json, true);
			}
		}
		
		function data_filter($string = "", $db_link = null) {
			//\App\Model\Utilities::data_filter
			$string = strip_tags($string);
			$string = stripslashes($string);
			$string = htmlspecialchars($string);
			$string = trim($string);
			if(isset($db_link) && $db_link != null) {
				$string = $db_link->filter_string($string);
			}
			return $string;
		}
		
		function generateCode($length = 6): string {
			// \App\Model\Utilities::generateCode
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
			$code = "";
			$clen = strlen($chars) - 1;
			while (strlen($code) < $length) {
				$code .= $chars[mt_rand(0, $clen)];
			}
			return $code;
		}
		
		//кажется, return: mixed
		function checkFields($arr = [], $keysArr = [], $errCode = "error", $db_link = null, $ignore_errors = false) {
			$data = [];
			foreach ($keysArr as $key) {
				if(!isset($arr[$key]) || (empty($arr[$key]) && $arr[$key] != "0" && $arr[$key] != 0)) {
					if(!$ignore_errors) {
						exit($errCode.' ('.$key.' is empty)');
					}
				} else {
					$data[$key] = \App\Model\Utilities::data_filter($arr[$key], $db_link);
				}
			}
			return $data;
		}
		
		function checkINT($value = 0, $db_link = null): int {
			//\App\Model\Utilities::checkINT
			$value = \App\Model\Utilities::data_filter($value, $db_link) + 0;
			if(!is_int($value)) {
				$value = 0;
			}
			return $value;
		}
		
		function checkFloat($value = 0, $db_link = null): float {
			$value = floatval(\App\Model\Utilities::data_filter($value, $db_link));
			if(!is_float($value)) {
				$value = 0;
			}
			return $value;
		}
		
		function checkINTFields($arr = [], $keysArr = [], $db_link = null): array {
			//$db_link - ссылка на экземпляр \App\Model\DataBase
			$data = [];
			foreach ($keysArr as $key) {
				if(!isset($arr[$key]) || empty($arr[$key])) {
					$data[$key] = 0;
				} else {
					$data[$key] = \App\Model\Utilities::checkINT($arr[$key], $db_link);
				}
			}
			return $data;
		}
		
		function format_amount($amount = 1, $precision = 4) {
			//\App\Model\Utilities::format_amount
			return rtrim(rtrim(number_format($amount, $precision, '.', ' '), "0"), ".");
		}
	}
	