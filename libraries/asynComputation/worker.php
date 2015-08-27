<?php
namespace libraries\asynComputation;

class Worker extends \GearmanWorker{

	public function unserialize($var) {
		return unserialize($var);
	}
}