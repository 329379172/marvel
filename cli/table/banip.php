<?php
use Engine\X;
require './cli/start.php';

$db = X::db();

$rows = $db->table('hulkx_ban_ip')->find();

$writetime = time();

require "libraries/ipip/ip.php";

foreach($rows as $row){

	$ip_address = '';

	if($row['ip']){
		if($ipip = \libraries\ipip\Ip::find($row['ip'])){
			$ip_address = implode($ipip);
		}

		list($ip_section1,$ip_section2,$ip_section3,$ip_section4) = explode('.',$row['ip']);

		$db->table('banip')->insert([
			'ban_time' => $row['ban_time'],
			'valid_time' => $row['valid_time'],
			'attack_amount' => 0,
			'ip_address' => $ip_address,
			'ip_section1' => $ip_section1,
			'ip_section2' => $ip_section2,
			'ip_section3' => $ip_section3,
			'ip_section4' => $ip_section4
		]);
	}
}

print($db->table('banip')->count());