<?php
namespace controllers;
use Engine\X;
use Engine\Gvar;
use Engine\Route;

/**
* 首页
*/
Route::get('index',function(){

	$dirtyword = X::module('dirtyword')->byCompany(Gvar::audit('company_id'));

	X::render('audit/index',[
		'dirtyword' => $dirtyword
	]);
});


/**
* 查找
*/
Route::get('search',function(){
	$get = X::request()->get([
		'search' => ''
	]);

	$dw = X::module('dirtyword')->search($get->search);

	X::render('audit/index',[
		'dirtyword' => $dw
	]);
});


/**
* 登录
*/
Route::get('signin',function(){
	X::render('audit/login');
});


Route::post('signin',function(){
	$post = X::request()->post([
		'account' => '', //帐号
		'password' => '', //密码
	]);

	if(!$post->account || !$post->password) {
		X::redirect('/audit/signin');
	}

	if($company = X::module('company')->signin($post->account,$post->password)){

		X::redirect('/audit');
	}else{
		X::redirect('www.baidu.com');
	}
});


/**
* 脏词管理
*/
Route::get('dirtyword',function(){
	X::render('audit/dirtyword');
});
