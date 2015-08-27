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

class Post extends Module{

	/**
	* 请求的IP是否在IP黑名单里
	*
	* @return void
	*/
	public function whetherInBanip($ip) {

		list($ip_section1,$ip_section2,$ip_section3,$ip_section4) = explode('.',$ip);

		// self::$debug= true;

		$row = $this->db()->table('banip')
					->where("ip_section1=%d AND ip_section2=%d AND ip_section3=%d AND ip_section4=%d",$ip_section1,$ip_section2,$ip_section3,$ip_section4)
					->findOne();

		return $row;
	}

	/**
	* 添加拦截日志
	*
	* @return void
	*/
	public function banLog(Array $data) {
		$ip1 = $ip2 = $ip3 = $ip4 = 0;
		if($data['ip']){
			list($ip1,$ip2,$ip3,$ip4) = explode('.', $data['ip']);
		}

		$this->db()->table('posts')
					->insert([
							'title' => $data['title'],
							'contents' => $data['content'],
							'posts_time' => $data['posts_time'],
							'dw_id' => $data['dirty_id'],
							'hit_dirty_words' => $data['dirty_works'],
							'company_username' => $data['company_username'],
							'company_id' => $data['company_id'],
							'process_time' => $data['process_time'],
							'ip_section1' => $ip1,
							'ip_section2' => $ip2,
							'ip_section3' => $ip3,
							'ip_section4' => $ip4,
							'ip_city' => $data['ip_city'],
							'dirty_stock' => $data['depot']
						]);
	}

	/**
	* 禁止ip
	*
	* @return void
	*/
	public function banIp($ip) {
		if(!$r = @explode('.',$ip)){
			return;
		}

		list($ip_section1,$ip_section2,$ip_section3,$ip_section4) = $r;


		$now = time();
		$valid_time = $now + 43200;

		require "function/cook.php";
		$from_address = ipToAddress($ip);

		if($this->db()->table('banip')
					->where("ip_section1=%d AND ip_section2=%d AND ip_section3=%d AND ip_section4=%d",$ip_section1,$ip_section2,$ip_section3,$ip_section4)
					->count()){


			$this->db()->table('banip')
						->where("ip_section1=%d AND ip_section2=%d AND ip_section3=%d AND ip_section4=%d",$ip_section1,$ip_section2,$ip_section3,$ip_section4)
						->update(
							"ban_time = %d,valid_time = %d,attack_amount=attack_amount+1,ip_address = '%s'",$now,$valid_time,$from_address
						);
		}else{
			$this->db()->table('banip')
						->insert([

								'ban_time' => $now,
								'valid_time' => $valid_time,
								'attack_amount' => 0,
								'ip_address' => $from_address,
								'ip_section1' => $ip_section1,
								'ip_section2' => $ip_section2,
								'ip_section3' => $ip_section3,
								'ip_section4' => $ip_section4

							]);
		}
	}


	/**
	* 显示拦截日志
	*
	* @return void
	*/
	public function allByBanLog($company_id,$p) {

		list($start,$end) =  defaultPageLimit($p);

		$result = $this->db()->table('posts')
					->where("company_id = %d ORDER BY posts_time DESC",$company_id)
					->limit($start,$end)
					->find();

		$count  = $this->db()->table('posts')
					 ->where("company_id = %d",$company_id)
					 ->count();

		return [$count,$result];
	}
}