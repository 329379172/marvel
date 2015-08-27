<?php
use Engine\X;
use Engine\Gvar;

X::map('csrf', function(){
	echo("~~~~~~~~~~csrf~~~~~~~~~~~~~~");
});




//关闭csrf
X::setEnv('csrf',false);

//初始化session
X::register('session',function(){
	$session = new libraries\session;
	$session->start();
	return $session;
});

if(!X::session()->get('company_id') && X::getEnv('action') != 'signin' && X::getEnv('controller') != 'audit/index') {
	X::redirect('/audit/signin');
}

//
Gvar::set([
	'company_id' => X::session()->get('company_id'),
	'company_name' => X::session()->get('company_name'),
	'account' => X::session()->get('account')
],'audit');