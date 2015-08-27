<?php
use Engine\X;/**
* 时间距离计算
*
* @return void
*/
function Fragments($controller,$action,$data=[]){
	\Engine\fragment($controller,$action,$data);
}


/**
* 发起post
*
* @return void
*/
function post($url,$data=[],$header=[]) {
	return X::lib('fetch')->post($url,$data,$header);
}

