<?php
namespace Engine;

class XException extends \Exception{}

class X {

	private static $_env = []; //环境变量容器
	private static $_obj = []; //对象容器
	private static $_layouts = []; //模板容器
	private static $_config = []; //配置容器
	private static $_var = []; //全局容器
	private static $_map = []; //打点容器

	private function __construct() {}
    private function __destruct() {}
    private function __clone() {}


    private static function __report($online) {
		if (false === $online) {
			ini_set("display_errors", "On");
			error_reporting(E_ALL);
		}else{
			ini_set("display_errors", "Off");
			error_reporting(0);
		}	
    }

    private static function __init__() {
		date_default_timezone_set("Asia/Shanghai");
		header("Content-type: text/html; charset=utf-8");

		//加载系统配置
		self::configure('application');	
    }

	public static function init(Array $opt = []) {

		self::__init__();

		set_exception_handler(function($e){

			if($fn = self::getMap("exception")){
				$fn();exit;
			}

			$msg = sprintf('<h1>500 Internal Server Error</h1>'.
		            '<h3>%s (%s)</h3>'.
		            '<pre>%s</pre>',
					$e->getMessage(),
		            $e->getCode(),
		            $e->getTraceAsString()
		        );
		    self::response()->status(500)->write($msg)->send();
		});

		self::$_env['csrf'] = self::getArrayValue($opt,'csrf',true); //是否关闭csrf
		self::$_env['online'] = self::getArrayValue($opt,'online',false); //开发模式
		self::$_env['controllersDir'] = self::getArrayValue($opt,'controllersDir','controllers'); //控制器目录
		self::$_env['viewDir'] = self::getArrayValue($opt,'viewDir','views'); //模板目录
		self::$_env['module'] = self::getArrayValue($opt,'module','module'); //模型目录
		self::$_env['baseUrl'] = self::getArrayValue($opt,'baseUrl',dirname(__FILE__).DIRECTORY_SEPARATOR);
		self::$_env['templateFuncFile'] = self::getArrayValue($opt,'templateFuncFile',''); //模板内置函数文件

		self::__report(self::$_env['online']);

		PHP_VERSION < '5.5.0' && self::abort('php version < 5.5.0',500);

		self::$_env['request_uri'] = self::getServer('REQUEST_URI','/');

		$_embedGet = self::embedGet();

		$_ru = strstr(self::$_env['request_uri'],'?',true);
		if($_ru) self::$_env['request_uri'] = $_ru;

		self::register('response',function(){
			$_class = __NAMESPACE__ . '\Response';
			return new $_class();
		}); 

		self::register('request',function() use ($_embedGet){
			$_class = __NAMESPACE__ . '\Request';

			return new $_class(new Collection($_embedGet),new Collection($_POST),new Collection($_FILES));
		}); 

		//请求
		self::register('security',function(){
			$_class = __NAMESPACE__ . '\Security';
			return new $_class;
		});

		unset($_GET,$_POST,$_REQUEST);

		self::$_env['request_method'] = self::getServer('REQUEST_METHOD','GET');

		spl_autoload_register(array(__CLASS__,'classAutoLoadPath'));
	}

	public static function cli(Array $opt=[]) {

		self::__init__();

		set_exception_handler(function($e){

			if($fn = self::getMap("exception")){
				$fn();exit;
			}

			$msg = sprintf('<h1>500 Internal Server Error</h1>'.
		            '<h3>%s (%s)</h3>'.
		            '<pre>%s</pre>',
					$e->getMessage(),
		            $e->getCode(),
		            $e->getTraceAsString()
		        );
		   	print($msg);
		});

		self::$_env['baseUrl'] = '';
		spl_autoload_register(array(__CLASS__,'classAutoLoadPath'));	

		self::$_env['online'] = self::getArrayValue($opt,'online',false); //开发模式

		self::__report(self::$_env['online']);
	}

	private static function embedGet() {
		$params = [];

        $args = parse_url(self::$_env['request_uri']);
        if (isset($args['query'])) {
            parse_str($args['query'], $params);
        }

        return $params;
	}

	public static function map($ac,callable $fn) {
		self::$_map[$ac] = $fn;
	}

	public static function getMap($ac) {
		if(self::$_env['online'] === true) {
			return isset(self::$_map[$ac]) ? self::$_map[$ac] : false;
		}
		return false;
	}

	public static function register($name,callable $func) {
		self::$_obj[$name] = $func();
	}

	public static function __callStatic($className,$params) {
		if(method_exists(self::$_obj[$className],'OOO')){
			self::$_obj[$className]->OOO($params); //预定义
		}
		
		return self::$_obj[$className];
	}

	//类自动加载路径
	public static function classAutoLoadPath($class) {

		if(strpos($class,'\\') !== false) {
			$class = self::$_env['baseUrl'] . str_replace('\\','/',$class);
		}
		
		$class = $class . '.php';

		file_exists($class) && require_once($class);
	}

	public static function getConfigVar($var='',$default='') {
		if($var == '') return self::$_config;
		return isset(self::$_config[$var]) && !empty(self::$_config[$var]) ? self::$_config[$var] : $default;
	}

	public static function getArrayValue($array,$key,$default='') {
		return isset($array[$key]) && !empty($array[$key]) ? $array[$key] : $default;
	}	

	public static function getEnv($var='',$default='') {
		if($var == '') return self::$_env;
		return isset(self::$_env[$var]) && !empty(self::$_env[$var]) ? self::$_env[$var] : $default;
	}

	public static function setEnv($var='',$values) {
		self::$_env[$var] = $values;
	}

	public static function getRegisterObj($var) {
		return isset(self::$_obj[$var]) ? self::$_obj[$var] : false;
	}

	public static function getServer($var='',$default='') {
		if($var == '') return $_SERVER;
		return isset($_SERVER[$var]) && !empty($_SERVER[$var]) ? $_SERVER[$var] : $default;
	}

	public static function set($var,$value='default',$scope='default') {
		if(is_array($var)){
			$scope = $value;
			foreach($var as $k => $v) {
				self::$_var[$scope][$k] = $v;
			}
		}else{
			self::$_var[$scope][$var] = $value;
		}
	}

	public static function get($var='',$scope='default') {
		if($var == '') return self::$_var;
		return isset(self::$_var[$scope][$var]) ? self::$_var[$scope][$var] : '';
	}

	public static function has($var,$scope='default') {
		if(isset(self::$_var[$scope][$var])){
			return true;
		}
		return false;
	}

	public static function clear($var='') {
		if($var) {
			unset(self::$_var[$var]);
			return true;
		}
		unset(self::$_var);
		return true;
	}

	public static function abort($msg,$status = 200) {

		self::response()->status($status)
		                ->write("<html>
				           <head><title>$msg</title></head>
				           <body bgcolor='white'>
				           <center><h1>$msg</h1></center>
				           <hr><center>power by X</center>
				           </body>
				        </html>")
		               ->send();
	}

	public static function start(Array $controllers=[],$fn='') {

		$requestUri = self::$_env['request_uri'];
		self::$_env['request_method'] = self::getServer('REQUEST_METHOD','GET');

		// 自定义的 控制器
		foreach($controllers as $controller){


			if(!_startWith($requestUri,'/' . $controller)) continue;

			if($requestUri == '/'.$controller){

				self::$_env['controller'] = $controller;
				self::$_env['action'] = 'index';	

				//加载初始化文件
				$initFile = self::$_env['controllersDir'] .'/' . $controller . '/__init__.php';
				if(file_exists($initFile)){
					include_once($initFile);
				}

				//加载自定义
				self::__loadControllerAction(self::$_env['controller'],$fn);	

			}else if($_actionUri = _substr($requestUri,$controller)) {

				$_action_uri = explode('/',$_actionUri,3);

				if(sizeof($_action_uri) == 2 && !file_exists(self::$_env['controllersDir'] .'/' . $controller . '/'.$_action_uri[1] . '.php')){
					self::$_env['controller'] = $controller . '/index';
					self::$_env['action'] = self::getArrayValue($_action_uri,1,'index');

				}else{
					self::$_env['controller'] = $controller . '/' . self::getArrayValue($_action_uri,1,'index');
					self::$_env['action'] = self::getArrayValue($_action_uri,2,'index');
				}



				//加载初始化文件
				$initFile = self::$_env['controllersDir'] .'/' . $controller . '/__init__.php';
				if(file_exists($initFile)){
					include_once($initFile);
				}	

				//加载自定义
				self::__loadControllerAction(self::$_env['controller'],$fn);
			}

		}

		//-- end

		$_requestUri = explode('/',$requestUri,3);

		self::$_env['controller'] = self::getArrayValue($_requestUri,1,'index');
		self::$_env['action'] = self::getArrayValue($_requestUri,2,'index');

		self::__loadControllerAction(self::$_env['controller'],$fn);
	}

	private static function __loadControllerAction($controller,$fn='') {

		is_callable($fn) && $fn();

		if(file_exists(self::$_env['controllersDir'] . '/' . $controller . '.php')){
			include(self::$_env['controllersDir'] . '/' . $controller . '.php');
		}elseif(file_exists(self::$_env['controllersDir'] . '/' . $controller . '/index.php')){
			include(self::$_env['controllersDir'] . '/' . $controller . '/index.php');
		}else if($_dir = strstr($controller,'/',true)){
			if(file_exists(self::$_env['controllersDir'] . '/' . $_dir . '/index.php')){


				self::$_env['action'] = substr(self::$_env['request_uri'],strlen('/'.$_dir)+1) ?: 'index';
				self::$_env['controller'] = $_dir;

				include(self::$_env['controllersDir'] . '/' . $_dir . '/index.php');	

			}
		}else{
			self::__exception(sprintf("Can't find controller file %s",$controller));
		}

		Route::process();
		exit;
	}

	private static function __exception($msg = '') {
		throw new XException($msg);
	}

	public static function throwException($msg = '') {
		self::__exception($msg);
	}

	//hook
	private static function __notFound() {
		self::abort("404 Not Found",404);
	}

	public static function configure($filename) {
		self::$_config = array_merge(self::$_config,include_once('configure/'. $filename . '.php'));
	}


    public static function redirect($url,$code=303) {
		self::response()->status($code)
		             ->header('Location', $url)
		             ->write($url)
		             ->send();
		exit;
	}

	private static function __csrfToken() {
		return md5(uniqid(time())); 
	}

   	public static function render($tpl,$T=array(),$layoutsName='') {

   		if(self::$_env['templateFuncFile']){
   			require_once self::$_env['templateFuncFile'];
   		}

		if(!empty($T)){
			$T = X::security()->htmlVar($T);
			extract($T);
		}

		// csrf token
		if(!$layoutsName && ($session = self::getRegisterObj('session'))){
			$__csrf = self::__csrfToken();
			$session->set(['__csrf'=>$__csrf]);
		}

        $_f = self::$_env['viewDir'] . '/' .$tpl.'.tpl.php';

        if($layoutsName){
        	self::$_layouts[$layoutsName] = self::__include($_f);
        }else{

        	if(!empty(self::$_layouts)){
        		extract(self::$_layouts);
        	}
        	if(!include_once($_f)){
        		self::$_layouts = [];
	        	self::__exception(sprintf("Can't find view file %s",$_f));
	        }
        }
	}

		private static function __include($filename) {
		if (is_file($filename)) {
	        ob_start();
	        include_once($filename);
	        $contents = ob_get_contents();
	        ob_end_clean();
	        return $contents;
	   	}
	    return '';
	}

	//模块别名
	public static function module($class,callable $fn = NULL) {
		return self::instance('module\\'.$class,$fn);
	}

	//表模型别名
	public static function table($class,callable $fn = NULL) {
		return self::instance('table\\'.$class,$fn);
	}

	//类库
	public static function lib($class,callable $fn = NULL) {
		return self::instance('libraries\\'.$class,$fn);
	}

	//实例化
	public static function instance($class,callable $fn=NULL) {

		//兼容路径的
		if(strpos($class,'/') !== false) {
			require_once($class . '.php');
		}

		$_className = str_replace('/','',$class);


		if(!isset(self::$_obj[$_className])) {

			if(!empty($fn)){
				self::register($class,$fn);
			}else{
				self::$_obj[$_className] = new $class();
			}
		}
		return self::$_obj[$_className];
	}
}

function _substr($string,$needle) {
	$pos = stripos($string,$needle);

	if($pos === false) {
		return false;
	}
	return substr($string,strlen($needle)+1);
}

function _startWith($string,$needle) {
    return strpos($string, $needle) === 0;
}

//加载片段
function fragment($controller,$action,$data=[]) {

	if(include(X::getEnv('controllersDir') . '/' . $controller . '.php')){

		$containers = Route::fragmentContainer();


		foreach($containers as $name => $containe){
			if($action == $name) {
				list($fn,$data) = $containe;
				call_user_func_array($fn,$data);
			}
		}
	}
}

//全局变量
class Gvar {

	private static $_var = [];

	public static function __callStatic($_var,$arguments=[]) {
		if($arguments){
			return isset(self::$_var[$_var][$arguments[0]]) ? self::$_var[$_var][$arguments[0]] : '';
		}else{
			return isset(self::$_var[$_var]) ? self::$_var[$_var] : '';
		}
	}

	public static function set($var,$value='default',$scope='default') {
		if(is_array($var)){
			$scope = $value;
			foreach($var as $k => $v) {
				self::$_var[$scope][$k] = $v;
			}
		}else{
			self::$_var[$scope][$var] = $value;
		}
	}
}

class Route{

	private static $_route = []; //路由规则
	private static $_get = [];
	private static $_post = [];
	private static $_fragment = [];

	public static function get() {

		list($action,$fn) = func_get_args();

		self::$_get[] = [$action,$fn];
	}

	public static function post() {

		list($action,$fn) = func_get_args();

		self::$_post[] = [$action,$fn];
	}

	public static function whole() {

		list($action,$fn) = func_get_args();

		self::$_route[] = [$action,$fn];
	}

	public static function	fragment($action,callable $fn,$data=[]) {
		self::$_fragment[$action] = [$fn,$data];
	}

	public static function fragmentContainer() {
		return self::$_fragment;
	}

	//路由正则修正
	private static function __waysCorrect($way) {
		return  preg_replace_callback("/@(\w+):([^\/]+?)@/",
				function($matches){
					// var_dump($matches);
		            if (isset($matches[2])) {
		                return '(?P<'.$matches[1].'>'.$matches[2].')';
		            }
		            return '(?P<'.$matches[1].'>[^/\?]+)';
				}, $way);
	}

	public static function process() {

		$request_method = X::getEnv('request_method');

		// get 路由
		if($request_method === 'GET'){

			foreach(self::$_get as $action){
				self::__actionMatching($action);
			}
		}
		
		// post 路由
		if($request_method === 'POST'){
			foreach(self::$_post as $action){
				self::__actionMatching($action);
			}
		}

		// whole 路由
		foreach(self::$_route as $action){
			self::__actionMatching($action);
		}

		if($fn = X::getMap('notFound')){
			$fn();exit;
		}else{
			X::throwException(sprintf("Can't find action:%s regular:%s",X::getEnv('action'),X::getEnv('queryUriRegular')));
		}
	}

	private static function __csrf() {
		// csrf
		if($session = X::getRegisterObj('session')){
			if($token = $session->get('__csrf')){
				if(X::request()->post(['__csrf'])->__csrf != $token){

					if($fn = X::getMap('csrf')){
						$fn();exit;
					}else{
						X::abort('CSRF defense!');
					}
				}
			}
		}
	}

	// action+query uri 匹配
	private static function __actionMatching($actionFn) {

		$_requestUri = X::getEnv('request_uri');

		$_controller = X::getEnv('controller');
		$_action = X::getEnv('action');

		list($actionRegular,$fn) = $actionFn;

		//csrf
		if(X::getEnv('request_method') === 'POST' && X::getEnv('csrf')){
			self::__csrf();
		}
		

		$actionRegular = self::__waysCorrect($actionRegular);

		//匹配 query uri 获取参数传递到回调闭包里面
		if(preg_match('/^'.addcslashes($actionRegular,"\57").'$/i',$_action,$matchs)){

			X::setEnv('queryUriRegular',$actionRegular);

    		array_shift($matchs);

    		call_user_func_array($fn,$matchs);
    		exit;
    	}	
	}
}

//输出
class Response {

    public $status = 200;

    public $headers = [];

    public $body;

    public static $codes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',

        226 => 'IM Used',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',

        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',

        426 => 'Upgrade Required',

        428 => 'Precondition Required',
        429 => 'Too Many Requests',

        431 => 'Request Header Fields Too Large',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',

        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    ];

    public function status($code = null) {
        if ($code === null) {
            return $this->status;
        }

        if (array_key_exists($code, self::$codes)) {
            $this->status = $code;
        }
        else {
            throw new XException('Invalid status code');
        }

        return $this;
    }

    public function header($name, $value = null) {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->headers[$k] = $v;
            }
        }
        else {
            $this->headers[$name] = $value;
        }

        return $this;
    }

    public function write($str) {
        $this->body .= $str;

        return $this;
    }

    public function clear() {
        $this->status = 200;
        $this->headers = [];
        $this->body = '';

        return $this;
    }

    public function cache($expires) {
        if ($expires === false) {
            $this->headers['Expires'] = 'Mon, 26 Jul 1997 05:00:00 GMT';
            $this->headers['Cache-Control'] = [
                'no-store, no-cache, must-revalidate',
                'post-check=0, pre-check=0',
                'max-age=0'
            ];
            $this->headers['Pragma'] = 'no-cache';
        }
        else {
            $expires = is_int($expires) ? $expires : strtotime($expires);
            $this->headers['Expires'] = gmdate('D, d M Y H:i:s', $expires) . ' GMT';
            $this->headers['Cache-Control'] = 'max-age='.($expires - time());
        }
        return $this;
    }

    public function sendHeaders() {

        header(
            sprintf(
                '%s %d %s',
                (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1'),
                $this->status,
                self::$codes[$this->status]),
            true,
            $this->status
        );

        foreach ($this->headers as $field => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    header($field.': '.$v, false);
                }
            }
            else {
                header($field.': '.$value);
            }
        }

        if (($length = strlen($this->body)) > 0) {
            header('Content-Length: '.$length);
        }

        return $this;
    }

    public function send() {
        if (ob_get_length() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            $this->sendHeaders();
        }

        exit($this->body);
    }

    public function output($msg) {
		exit($msg);
	}

	public function json($data, $code = 200, $encode = true) {
        $json = $encode ? json_encode($data) : $data;

        $this->status($code)
             ->header('Content-Type', 'application/json')
             ->write($json)
             ->send();
    }
	
    public function jsonp($data, $param = 'jsonp', $code = 200, $encode = true) {
        $json = ($encode) ? json_encode($data) : $data;

        $callback = X::request()->query[$param];

        $this->status($code)
             ->header('Content-Type', 'application/javascript')
             ->write($callback.'('.$json.');')
             ->send();
    }

    public function lastModified($time) {
        $this->header('Last-Modified', date(DATE_RFC1123, $time));

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
            strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) === $time) {
            $this->halt(304);
        }
    }

    public function halt($code = 200, $message = '') {
        $this->status($code)
             ->write($message)
             ->send();
    }
}

//请求
class Request {
	
	private  $_G = [];
	PRIVATE  $_P = [];
	private  $_FILES = [];

	public function __construct(Collection $G,Collection $P,Collection $FILES) { 

		$this->_G = $G;
		$this->_P = $P;
		$this->_FILES = $FILES;
	}

	public function __set($property,$value) {

	}

	public function __get($property) {

	}

	private function __process(&$container,Array $params = []) {

		if(empty($params)){
			return $container;
		}

		foreach($params as $k => $v){

			if(is_numeric($v)){
				$container[$k] = isset($container[$k]) ? intval($container[$k]) : $v;
			}else if(is_string($v)){
				$container[$k] = isset($container[$k]) ? X::security()->sqlVar($container[$k]) : $v;
			}
		}
		return $container;
	}

	public function get(Array $params = []) {
		return $this->__process($this->_G,$params);
	}

	public function post(Array $params = []) {
		return $this->__process($this->_P,$params);
	}

	public function file(Array $params = []) {
		return $this->__process($this->_FILES,$params);
	}
}



//安全
class Security {

	public $__css = ''; //外部提交 token

	public function __construct(){ }

	//数据库必须
	public function sqlVar($var) {
		return $this->addslashes($var);
	}

	public function addslashes($var){
		if(is_array($var)){
			$arrVar = array();
			foreach($var as $k => $v){
				$arrVar[$k] = $this->addslashes($v);
			}
			return $arrVar;
		}else{
			return addslashes($var);
		}
	}

	//输出模板必须
	public function htmlVar($var) {
		return $this->htmlspecialchars($var);
	}

	public function htmlspecialchars($var){
		if(is_array($var)){
			$arrVar = array();
			foreach($var as $k => $v){
				if($k[0] == '_' && $k[1] == '_'){
					$arrVar[$k] = $v;
				}else{
					$arrVar[$k] = $this->htmlspecialchars($v);
				}
			}

			return $arrVar;
		}else{
			return htmlspecialchars($var);
		}
	}
}

class Collection implements \ArrayAccess, \Iterator, \Countable {

    private $data;

    public function __construct(array $data = array()) {
        $this->data = $data;
    }

    public function __get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function __set($key, $value) {
        $this->data[$key] = $value;
    }

    public function __isset($key) {
        return isset($this->data[$key]);
    }

    public function __unset($key) {
        unset($this->data[$key]);
    }

    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        }
        else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    public function rewind() {
        reset($this->data);
    }
 
    public function current() {
        return current($this->data);
    }
 
    public function key() {
        return key($this->data);
    }
 
    public function next() {
        return next($this->data);
    }
 
    public function valid(){
        $key = key($this->data);
        return ($key !== NULL && $key !== FALSE);
    }

    public function count() {
        return sizeof($this->data);
    }

    public function keys() {
        return array_keys($this->data);
    }

    public function all() {
        return $this->data;
    }

    public function set(array $data) {
        $this->data = $data;
    }

    public function clear() {
        $this->data = array();
    }
}

class Mysql {

    private $db = [];
    private $_db_identify = 'default';
    public static $debug = false;
    protected $tableName = ''; //表名
    protected $_where = '';
    protected $_raw = '';
    private $_params = [];

    public function __construct($params,$identify='default') {
    	$this->_params = $params;
    	//默认的db link
    	$this->db[$identify] = $this->getConnection($this->_params);
    }

    //初始化回调
    public function OOO($ooo) {
    	
    }

    public function db($server='default') {
    	return $this->db[$server];
    }

    public function getConnection(Array $opt) {
       	$_db = @new \mysqli($opt['host'],$opt['user'],$opt['passwd']);
    	if (mysqli_connect_errno()) {
            throw new XException('database connect error!');
    	}
    	
    	$_db->set_charset($opt['charset']);
    	$_db->select_db($opt['database']); 
    	return $_db;
    }
    
    public function selectDb($_db){
    	if(!$this->db()->select_db($_db)){
    		throw new XException(sprintf("%s don't exist",$_db));
    	}
    }

    public function query($sql) {

    	if(self::$debug) {
    		print_r(debug_backtrace());
    		echo("<pre>".$sql."</pre>");
    		exit;
		}

        if(!($result = $this->db()->query($sql))){
            throw new XException($this->db()->error);
        }
        return $result;
    }

    public function num_rows($sql) {
    	$result = $this->query($sql);	
    	return $result->num_rows;
    }


    public function fetch_assoc($resource) {
        return $resource->fetch_assoc();
    }


    public function fetchAll($sql){
        $result = array();
        $rs = $this->query($sql);
        while($row = $rs->fetch_assoc()){
            $result[] = $row;
        }
        return $result;
    }


    public function getOne($sql) {
        $rs = $this->query($sql . ' LIMIT 1');
        $row = $rs->fetch_assoc();
        return $row;
    }
    
    public function getFirstField($sql) {
    	$rs = $this->query($sql);
    	$row = $rs->fetch_row();
    	return $row[0];
    }

    public function insert_id() {
        return $this->db()->insert_id;
    }

    public function close() {
    	foreach($this->db as $_db){
    		$_db->close();
    	}
    }

    public function table($tableName) {
    	$this->tableName = $tableName;
    	return $this;
    }

    public function getTable() {
    	return $this->tableName;
    }

    //重新选择数据库
    public function change($dbName) {
    	if($this->_db_identify != 'default'){
    		throw new XException("Don't allow change");
    	}
    	$this->selectDb($dbName);
    	return $this;
    }

   	public function insert(Array $data) {

		list($field,$value) = $this->__getWhereField($data);
		
		$this->query(sprintf("INSERT INTO %s (%s) VALUES(%s)",$this->getTable(),$field,$value));
		return $this->insert_id();
	}

	public function where() {
		$args = func_get_args();
		$templet = array_shift($args);

		$this->_where = ' WHERE ' . vsprintf($templet,$args);

		return $this;
	}

	private function __getWhere() {
		return $this->_where ? $this->_where : $this->_raw;
	}

	public function limit($start,$end=0) {
		if($end){
			$_sql = " LIMIT $start,$end";
		}else{
			$_sql = " LIMIT $start";
		}

		$this->_where = $this->__getWhere() . $_sql;
		return $this;
	}

	public function delete() {

		$this->__isThereWhere();
		$this->query(sprintf("DELETE FROM %s %s",$this->getTable(),$this->__getWhere()));
		$this->__clearWhere();
	}

	public function has() {
		$this->__isThereWhere();
		$rowsAmount = $this->num_rows(sprintf("SELECT * FROM  %s %s",$this->getTable(),$this->__getWhere()));
		$this->__clearWhere();	
		return $rowsAmount;
	}

	public function count() {
		$row = $this->getFirstField(sprintf("SELECT Count(*) FROM  %s %s",$this->getTable(),$this->__getWhere()));
		$this->__clearWhere();	
		return $row;		
	}

	public function raw() {

		$args = func_get_args();

		if(func_num_args() > 1){
			$templet = array_shift($args);

			$this->_raw = vsprintf($templet,$args);
		}else{
			$this->_raw = $args[0];
		}

		return $this;
	}

	public function find($fields='') {

		$field = is_array($fields) ? $this->__getField($fields) : '*';

		$result = $this->fetchAll(sprintf("SELECT %s FROM %s %s",$field,$this->getTable(),$this->__getWhere()));
		$this->__clearWhere();

		return $result;
	}

	public function findOne($fields='') {
		$field = is_array($fields) ? $this->__getField($fields) : '*';

		$result = $this->getOne(sprintf("SELECT %s FROM %s %s",$field,$this->getTable(),$this->__getWhere()));
		$this->__clearWhere();

		return $result;
	}

	public function update($data) {

		if(is_array($data)) {
			$this->__isThereWhere();
			$pair = $this->__getFieldValuePair($data);
		}else{
			$args = func_get_args();
			array_shift($args);

			$pair = vsprintf($data,$args);
		}
		
		$this->query(sprintf("UPDATE %s SET  %s %s",$this->getTable(),$pair,$this->__getWhere()));
		$this->__clearWhere();

	}

	private function __isThereWhere() {
		if(!$this->_where){
			throw new XException('皇上~您忘记写 sql的条件了: where');
		}
	}

	private function __clearWhere() {
		$this->_where = '';
	}

	private function __sqlType($param) {
		if(is_string($param)) {
			return "'".$param."'";
		}
		return $param;
	}

	private function __getWhereField($data) {
		$_field = $_value = [];

		foreach($data as $k => $v) {
			$_field[] = $k;
			$_value[] = $this->__sqlType($v);
		}

		$field = implode(',',$_field);
		$value = implode(',',$_value);

		return [$field,$value];
	}

	private function __getFieldValuePair($data) {
		$_pair = [];

		foreach($data as $k => $v) {
			$_pair[] = $k . '=' . $this->__sqlType($v);
		}

		return implode(',', $_pair);
	}

	private function __getField($data) {
		return implode(',',$data);
	}
}

trait dbServer {
	public function db($server='db') {
		return X::$server();
	}	
}

class Table extends Mysql { 
	public function __construct() { }

	use dbServer;
}

class Module extends Mysql {
	public function __construct() { }

	use dbServer;
}