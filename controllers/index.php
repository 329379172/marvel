<?php
namespace controllers;
use Engine\X;
use Engine\Route;

/**
* 首页
*/
Route::get('index',function(){
	print vsprintf("%04d-%02d-%02d", ["k'3",3,4]); // 1988-08-01
});