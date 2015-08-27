<?php
require "./X.php";

use Engine\X;
use Engine\Mysql;

require '__init__.php';



X::cli([
	'online' => false //开发者模式
]);


//连接数据库
X::register('db',function(){
	return new Mysql(X::getConfigVar('database'));
});
