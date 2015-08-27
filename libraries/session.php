<?php
namespace libraries;

class Session {
	public function start() {
		session_start();
	}

	public function get($s) {
		if(is_array($s)){
			$_result = array();
			foreach($s as $v){
				$_result[$v] = isset($_SESSION[$v]) ? $_SESSION[$v] : null;
			}
			return $_result;
		}else{
			return isset($_SESSION[$s]) ? $_SESSION[$s] : null;
		}
	}

	public function set($s) {
		foreach($s as $k =>$v){
			$_SESSION[$k] = $v;
		}
	}

	public function del($s) {
		if(is_array($s)){
			foreach($s as $v){
				unset($_SESSION[$v]);
			}
		}else{
			unset($_SESSION[$s]);
		}
	}

	public function clear() {
		session_destroy();
	}
}
