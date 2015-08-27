<?php
namespace libraries;

class Fetch {
	public function multi(Array $urls) {
	    $queue = curl_multi_init();
	    $map = [];
	 
	    foreach ($urls as $alias => $url) {
	        $ch = curl_init();
	 
	        curl_setopt($ch, CURLOPT_URL, $url);
	        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_HEADER, 0);
	        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
	   		curl_setopt($ch,CURLOPT_USERAGENT, "beihai365_rest");
	 
	        curl_multi_add_handle($queue, $ch);
	        $map[(string) $ch] = $alias;
	    }
	 
	    $responses = [];

	    do {
	        while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM) ;
	 
	        if ($code != CURLM_OK) { break; }
	 
	        // a request was just completed -- find out which one
	        while ($done = curl_multi_info_read($queue)) {
	 
	            // get the info and content returned on the request
	            $info = curl_getinfo($done['handle']);
	            $error = curl_error($done['handle']);
	            $results = curl_multi_getcontent($done['handle']);
	            $responses[$map[(string) $done['handle']]] = $results; //compact('info', 'error', 'results');
	 
	            // remove the curl handle that just completed
	            curl_multi_remove_handle($queue, $done['handle']);
	            curl_close($done['handle']);
	        }
	 
	        if ($active > 0) {
	            curl_multi_select($queue, 0.5);
	        }
	 
	    } while ($active);
	 
	    curl_multi_close($queue);
	    return $responses;
	}

	//发起post
	public function post ($url,$args=[],$headers=[]) {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,1);

		$_header = [];
		if($headers){
			foreach($headers as $k => $v){
				$_header[] = $k . ":" . $v;
			}
			curl_setopt($ch,CURLOPT_HTTPHEADER,$_header);
		}

		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_TIMEOUT,60);
		curl_setopt($ch,CURLOPT_USERAGENT, "beihai365");
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		
		curl_setopt($ch,CURLOPT_POSTFIELDS,$args);
		
		$return = curl_exec($ch);
		curl_close($ch);
		return $return;
	}


	//发起get
	public function get($url,$headers=[]) {
	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL,$url);

		$_header = [];
		if($headers){
			foreach($headers as $k => $v){
				$_header[] = $k . ":" . $v;
			}
			curl_setopt($curl,CURLOPT_HTTPHEADER,$_header);
		}

	    curl_setopt($curl, CURLOPT_HEADER,0);
	    curl_setopt($curl,CURLOPT_TIMEOUT,60);
	    curl_setopt($curl,CURLOPT_USERAGENT, "beihai365");
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    $data = curl_exec($curl);
	    curl_close($curl);
	    return $data;
	}

}