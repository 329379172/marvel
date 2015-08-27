<?php
namespace controllers;
use Engine\X;
use Engine\Route;

/**
* 首页
*/
Route::fragment('navigation',function(){
	X::render('fragment/navigation');
});