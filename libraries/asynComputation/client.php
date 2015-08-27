<?php
namespace libraries\asynComputation;

class Client extends \GearmanClient{ 

	public function serialize($var) {
		return serialize($var);
	}

	public function connect(Array $conf) {
		$this->addServer($conf['host'],$conf['port']);
	}

	public function task($action,$param) {
		$this->doBackground($action, $param);

		if ($this->returnCode() != GEARMAN_SUCCESS){
		  return false;
		}
		return true;
	}
}