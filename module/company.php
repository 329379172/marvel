<?php
namespace module;
use Engine\X;
use Engine\Module;

/*
   +----------------------------------------------------------------------+
   | 公司相关类                                                   		  |
   +----------------------------------------------------------------------+
   | Notes:
   +----------------------------------------------------------------------+
 */

class Company extends Module{

	/**
	* 是否存在这个公司
	*
	* @return void
	*/
	public function exists($access_token) {
		$company = $this->db()->table('company')
					->where("access_token='%s'",$access_token)
					->findOne();

		return $company;
	}

	/**
	* 公司请求统计
	*
	* @return void
	*/
	public function statisticsRequest($company_id) {
		//统计
	    $now = time();
	    
	    list($year,$month,$day) = explode('-',date("Y-m-d",$now));
	    
	    $requestAmount = $this->db()->table('logstatistics')
	    			->where("company_id = %d  AND  rq_year = %d AND rq_month = %d AND rq_day = %d",$company_id,$year,$month,$day)
	    			->count();

	    if($requestAmount){
	    	$this->db()->table('logstatistics')
	    				->where("company_id = %d  AND  rq_year = %d AND rq_month = %d AND rq_day = %d",$company_id,$year,$month,$day)
	    				->update('rq_amount = rq_amount + 1');

	    }else{
	    	$this->db()->table('logstatistics')
	    				->insert([
	    					'company_id' => $company_id,
	    					'rq_year' => $year,
	    					'rq_month' => $month,
	    					'rq_day' => $day,
	    					'rq_amount' => 1,
	    					'rq_hit_amount' => 0
	    				]);
	    }
	}

	/**
	* 公司拦截统计
	*
	* @return void
	*/
	public function statisticsHit($company_id) {
		$now = time();
	    
	    list($year,$month,$day) = explode('-',date("Y-m-d",$now));

	    $this->db()->table('logstatistics')
	    			->where("company_id = %d  AND  rq_year = %d AND rq_month = %d AND rq_day = %d",$company_id,$year,$month,$day)
	    			->update('rq_hit_amount = rq_hit_amount + 1');
	}

	/**
	* 公司登录
	*
	* @return void
	*/
	public function signin($account,$password) {

		if(!$account || !$password){ exit; }

		$password = md5($password.X::getConfigVar('salt'));

		
		if($result = $this->db()->table('company')
					->where("account = '%s' AND password = '%s'",$account,$password)
					->findOne()) {

			X::session()->set([
				'company_name' => $result['company_name'],
				'account' => $account,
				'company_id' => $result['company_id']
			]);
			return $result;
		}else{
			return false;
		}

	}
}