<?php
require "./X.php";

use Engine\X;
use Engine\Gvar;
use Engine\Mysql;

require '__init__.php';


/********************************************/


/********************************************/

// Bottle::map('notFound', function(){
// 	echo("~~~~~~~~~~~~notFound~~~~~~~~~~~~");
// });

X::map('exception', function(){
	echo("~~~~~~~~~~error~~~~~~~~~~~~~~");
});


X::init([
	'online' => false, //开发者模式
	'baseUrl' => dirname(__FILE__).DIRECTORY_SEPARATOR,
	'templateFuncFile' => 'function/template.php'
]);

//连接数据库
X::register('db',function(){
	return new Mysql(X::getConfigVar('database'));
});


Gvar::set([
	'ip' => ip()
],'global');


//csrf_token
X::start([
	'audit'
]);

