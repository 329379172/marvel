<?php
use Engine\X;

/**
* 敏感分析服务
*
* @return void
*/
function searchEngine($data,$runtime=false) {

	$process_start_time = getCurrentTime();
    
    $result = post(X::getConfigVar("searchEngine"),[
        "words" => cleanFormat($data['content']),
        'if_spam' => $data['if_spam']
    ]);

    $process_end_time = getCurrentTime();	

    if($runtime) {
    	$process_time = round($process_end_time - $process_start_time,4); //运行时间
    	return [$result,$process_time];
    }
    return $result;
}

/**
* 去除基本格式
*
* @return void
*/
function cleanFormat($contents) {
    // $contents = allow_ubb_tag($contents);
    $contents = strip_tags($contents);
    $contents = str_replace(array("&nbsp;"," ","　"),array("","",""),$contents);
    $contents = preg_replace("/\[[^\x{4e00}-\x{9fa5}]+?\]/ui","",$contents);  //删除汉字
    
    return $contents;   
}