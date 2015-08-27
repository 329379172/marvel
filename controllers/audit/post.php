<?php
namespace controllers;
use Engine\X;
use Engine\Gvar;
use Engine\Route;

/**
* 首页
*/
Route::get('index',function(){

	$get = X::request()->get([
		'p' => 1
	]);

	list($count,$log) = X::module('post')->allByBanLog(Gvar::audit('company_id'),$get->p);

	X::render('audit/post',[
		'log' => $log,
		'__page_html' => defaultPageHtml($count,$get->p,'?')
	]);
});