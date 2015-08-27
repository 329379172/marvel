<?php
namespace controllers;
use Engine\X;
use Engine\Gvar;
use Engine\Route;


/**
* 添加脏词
*/
Route::get('add',function(){
	X::render('audit/dirtyword_add');
});

Route::post('add',function(){

	$post = X::request()->post([
		'dirtyword' => '', //脏词
	]);

	//添加脏词
	X::module('dirtyword')->add([
		'words' => $post->dirtyword,
		'company_id' => Gvar::audit('company_id')
	]);

	X::redirect("/audit/dirtyword/add");
});


Route::get('delete',function(){

	$get = X::request()->get([
		'id' => '', //脏词
	]);

	//添加脏词
	X::module('dirtyword')->del($get->id);

	echo("success");
});



