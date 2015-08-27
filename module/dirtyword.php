<?php
namespace module;
use Engine\X;
use Engine\Module;

/*
   +----------------------------------------------------------------------+
   | 脏词相关类                                                   		  |
   +----------------------------------------------------------------------+
   | Notes:
   +----------------------------------------------------------------------+
 */

class Dirtyword extends Module{

	/**
	* 获取全局词库
	*
	* @return array
	*/
	public function byGlobal($dirtyword_id) {

		return $this->db()->table('globaldirtyword')
					->where("dw_id = %d",$dirtyword_id)
					->findOne();

	}

	/**
	* 获取自定义词库
	*
	* @return array
	*/
	public function byCompany($company_id) {

		return $this->db()->table('companydirtyword')
					->where("company_id = %d order by dw_id DESC",$company_id)
					->limit(6)
					->find();

	}

	/**
	* 添加脏词
	*
	* @return array
	*/
	public function add(Array $data) {

		$now = time();

		return $this->db()->table('companydirtyword')
					->insert([
						'company_id' => $data['company_id'],
						'dwcategory_id' => 0,
						'category_name' => '自定义',
						'words' => $data['words'],
						'writetime' => $now,
						'hit_rate' => 0
					]);

	}

	/**
	* 删除脏词
	*
	* @return bool
	*/
	public function del($dw_id) {
		return $this->db()->table('companydirtyword')
					->where("dw_id = %d",$dw_id)
					->delete();

	}

	/**
	* 查找脏词
	*
	* @return bool
	*/
	public function search($dw) {

		return $this->db()->table('companydirtyword')
					->where("words like '%s%%'",$dw)
					->find();

	}
}