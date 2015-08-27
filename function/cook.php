<?php

/**
* 时间距离计算
*
* @return void
*/
function timeDay($show_time){
	$now_time = time();
	$dur = $now_time - $show_time;
	$str_date = date("Y.m.d",$show_time);
	
	if($dur < 0){
		return $str_date;
	}else{
		if($dur < 60){
			return $dur.'秒前';
		}else{
			if($dur < 3600){
				return floor($dur/60).'分钟前';
			}else{
				if($dur < 86400){
					return floor($dur/3600).'小时前';
				}else{
					if($dur < 259200){//3天内
						return floor($dur/86400).'天前';
					}else{
						return $str_date;
					}
				}
			}
		}
	}
}


/**
* 时间距离计算
*
* @return void
*/

function ipToAddress($ip) {
	require "libraries/ipip/ip.php";
	$ipip = \libraries\ipip\Ip::find($ip);
	return implode($ipip);
}