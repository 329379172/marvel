<?php
use Engine\X;

function ip() { 
	if (getenv('HTTP_CLIENT_IP')) { 
		$ip = getenv('HTTP_CLIENT_IP'); 
	} 
	elseif (getenv('HTTP_X_FORWARDED_FOR')) { 
		$ip = getenv('HTTP_X_FORWARDED_FOR'); 
	} 
	elseif (getenv('HTTP_X_FORWARDED')) { 
		$ip = getenv('HTTP_X_FORWARDED'); 
	} 
	elseif (getenv('HTTP_FORWARDED_FOR')) { 
		$ip = getenv('HTTP_FORWARDED_FOR'); 
	} 
	elseif (getenv('HTTP_FORWARDED')) { 
		$ip = getenv('HTTP_FORWARDED'); 
	} 
	else { 
		$ip = $_SERVER['REMOTE_ADDR']; 
	} 
	return $ip; 
} 

/**
* rest 封包
*
* @return void
*/
function apiOutput($result,$data) {

	if(is_array($data)){
		$_errno = 0;
		$msg = '';$_data = $data;
	}else{
		$_errno = X::getConfigVar('errno_' . str_replace(' ','_',$data)) ?: 0;
		$msg = $data;$_data = [];
	}

	$values = ['result' => $result,'msg' => $msg,'errno' => $_errno] + ['data' => $_data];
	X::response()->json($values);
}


/**
* 数组转化
*
* @return void
*/
function toArray($json){
	return json_decode($json,true);
}


/**
* 运行时间
*
* @return void
*/
function getCurrentTime(){
	list ($msec, $sec) = explode(" ", microtime());
	return (float)$msec + (float)$sec;
}

/**
* 默认分页函数
*
* @return void
*/
function defaultPageHtml($totalnum,$page,$fenyeurl,$rewrite=0){
	$fenyeurl .= 'p=';
	$page = max($page,1);
	$totalpage = ceil($totalnum / X::getConfigVar('defaultPageRecordNumber'));
	$rangepage = 6;

	$startpage = max(1,$page - $rangepage);
	$endpage   = min($totalpage,$startpage+$rangepage*2 - 1);
	$startpage = min($startpage,$endpage - $rangepage*2 + 1);
	if($startpage < 1) $startpage = 1;

	$fileext = $rewrite ? '.html':'';
	$html = '<ul class="pagination">';
	$html .= '<li><a aria-label="Previous" href="'.$fenyeurl.'1">首页</a></li>';
	$html .= $page > 1 ? '<li><a href="'.$fenyeurl.($page-1).'">上一页</a></li>':'';
	for($i = $startpage;$i <= $endpage;$i++){
		$html .= '<li'.($page == $i ? ' class="active"':'').'><a href="'.$fenyeurl.$i.$fileext.'">'.$i.'</a></li>';
		if($i == $totalpage) break;
	}
	$html .= $page < $totalpage ? '<li><a href="'.$fenyeurl.($page+1).'">下一页</a></li>':'';
	$html .= '<li><a aria-label="Next" href="'.$fenyeurl.$totalpage.'">末页</a></li>';
	$html .= '</ul>';
	
	if(!$totalpage) $html = "";

	return $html;
}

/**
* 默认分页limit
*
* @return void
*/

function defaultPageLimit($page) {
	$end = X::getConfigVar('defaultPageRecordNumber');		
	$start = ($page - 1) * $end;
	return [$start,$end];
}

