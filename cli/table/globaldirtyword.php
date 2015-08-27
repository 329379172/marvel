<?php
use Engine\X;
require './cli/start.php';

$db = X::db();

$rows = $db->table('hulkx_g_dirtyword')->find();

$writetime = time();

foreach($rows as $row){
	$db->table('globaldirtyword')->insert([
		'dwcategory_id' => $row['dwcategory_id'],
		'category_name' => $row['category_name'],
		'words' => $row['words'],
		'dirty_level' => $row['dirty_level'],
		'writetime' => $writetime,
		'if_deny_id' => 0,
		'hit_rate' => 0,
		'dirty_level' => $row['dirty_level']
	]);
}

print($db->table('globaldirtyword')->count());