<?php
namespace controllers;
use Engine\X;
use Engine\Route;
use Engine\Request;

/**
* 内容检测api
*
*$post['if_spam'] = 0; //是否拦截水贴
*$post['if_duplicate_deny'] = 0; //重复内容超过次数禁IP
*$post['if_spate_deny'] = 0; //短时间内大量发帖  
*
*/
Route::post('testing',function() {

	$post = X::request()->post([
		'title' => '', //标题
		'content' => '', //内容
		'access_token' => '', //access_token
		'client_ip' => '', //发帖人ip
        'if_spam' => 0, //是否拦截水贴
        'if_duplicate_deny' => 0, //重复内容超过次数禁IP
        'if_spate_deny' => 0, //短时间内大量发帖
		'test' => 0 //是否是测试
	]);


	if(!$post->access_token || !$post->content){
		apiOutput(NO,"incomplete request");
	}

	if(!$company = X::module('company')->exists($post->access_token)){
		apiOutput(NO,"company does not exist");
	}

	//临时关闭
	if($company['closed']){
		apiOutput(NO,"service temporarily unavailable");
	}

	//统计请求量
	X::module('company')->statisticsRequest($company['company_id']);

	$now = time();

	require 'function/post.php';
	list($tutu,$process_time) = searchEngine([
		'content' => $post->title . $post->content,
		'if_spam' => $post->if_spam
	],true);


	if(!$tutu){
		apiOutput(NO,"search service is not available");
	}

	$tutu = toArray($tutu);



	require "function/cook.php";
	$from_address = ipToAddress($post->client_ip);

	$tutu['title'] = $post->title;
	$tutu['content'] = $post->content;
	$tutu['posts_time'] = $now;
	$tutu['company_username'] = $company['company_name'];
	$tutu['company_id'] = $company['company_id'];
	$tutu['ip_city'] = $from_address;
	$tutu['process_time'] = $process_time;
	$tutu['ip'] = $post->client_ip;

	//请求日志
	X::module('post')->banLog($tutu);

	//IP是否被拦截
	$ipInfo = X::module('post')->whetherInBanip($post->client_ip);


	if($ipInfo && $tutu['hit'] || $ipInfo && $ipInfo['valid_time'] > $now){

		if($tutu['hit']){
			//看是否内容再次是非法，是就增加IP拦截时效
			if(!$ipInfo['valid_time'] || $ipInfo['valid_time'] < $now){
				$valid_time = $now + 86400; //拦截一天

			}else{
				$valid_time = $ipInfo['valid_time'] + 43200;
			}

			X::db()->query("UPDATE banip 
							SET valid_time = $valid_time ,
								attack_amount = attack_amount + 1
							WHERE ip_id = {$ipInfo['ip_id']}");
		}

		//统计命中率
		X::module('company')->statisticsHit($company['company_id']);

		apiOutput(YES,[
    			'hit'=>YES,
    			'dirty_works' => "ip({$post->client_ip})被拦截"]);

	}

	//命中脏词
	if($tutu['hit']) {

		//统计命中率
		X::module('company')->statisticsHit($company['company_id']);


		//如果命中的词是公共库而且是要封ip的
		if($tutu['depot'] === 2){
			$dwRow = X::module('dirtyword')->byGlobal($tutu['dirty_id']);

			if($dwRow['if_deny_id'] === 1){
				X::module('post')->banIp($post->client_ip);
			}

		}

		apiOutput(YES,[
			'hit'=>YES,
    		'dirty_works' => $tutu['dirty_works'],
    		'dirty_works_category_id' => $tutu['dwcategory_id'],
    		'category_name' => $tutu['category_name'],
    		'depot' => $tutu['depot']
		]);	
	}
});


/**
* ip检测API
*
*$post['if_spam'] = 0; //是否拦截水贴
*$post['if_duplicate_deny'] = 0; //重复内容超过次数禁IP
*$post['if_spate_deny'] = 0; //短时间内大量发帖  
*
*/
Route::get('ip',function(){
	echo('ip');
});