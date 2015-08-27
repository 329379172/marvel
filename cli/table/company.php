<?php
use Engine\X;
require './cli/start.php';

$db = X::db();

$rows = $db->table('hulkx_company')->find();

$writetime = time();

foreach($rows as $row){
	$db->table('company')->insert([
		'company_name' => $row['company_name'],
		'access_token' => $row['access_token'],
		'allow_ip' => $row['allow_ip'],
		'qq' => $row['qq'],
		'phone' => $row['phone'],
		'url' => $row['url'],
		'chief' => ''
	]);
}

print($db->table('company')->count());