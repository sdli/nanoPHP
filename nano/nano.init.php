<?php
date_default_timezone_set('Asia/Shanghai'); 
include 'nano.config.php';//配置信息

define("HOST_URL",getUrl("ALL"));

if(v('p')){
	if(file_exists(ROOT.M_PATH.v('p').'.php')){
		include ROOT.M_PATH.v('p').'.php';
	}else{
		if(DEBUGM){throw new Exception("no model found for ".ROOT.M_PATH.v('p').'.php');} 
	};
}elseif(v('c') && v('m')){
	if(file_exists(ROOT.C_PATH.v('c').'.class.php')){
		include ROOT.C_PATH.v('c').'.class.php';
	}else{
		if(DEBUGM){throw new Exception("no controller found for ".ROOT.C_PATH.v('p').'.php');} 
	};
}elseif(empty(v('c')) && !empty(v('m'))){
	if(DEBUGM){throw new Exception("controller and method should be both defined! Contoller is empty, please check your 【c】 define!!");} 
}elseif(!empty(v('c')) && empty(v('m'))){
	if(DEBUGM){throw new Exception("controller and method should be both defined! Method is empty, please check your 【m】define!! ");} 
}else{
	include ROOT.M_PATH.'index.php';
}

/**
 * use a render class to paint the web world!
 * construct @param $view(full name)
 * construct @param $param (data for rendering!) 
 */

class newRender{
	private $view_page;
	private $data;

	/**
	 * recieve pages.
	 */
	public function __construct($arr,$data){
		$this->view_page = !empty($arr)?$arr:array('index'=>'index');
		$this->data = $data;
	}

	/**
	 * { get view page and render with params }
	 *
	 * @throws     Exception  (when page does not exists.)
	 */
	public function render(){
		if(!empty($this->data) && $this->data != 0){
			@extract($this->data);
		}
		if(!empty($this->view_page)){
			foreach($this->view_page as $key => $val){
				if(file_exists(ROOT.V_PATH.$key.D.$val.'.tpl.html')){
					include ROOT.V_PATH.$key.D.$val.'.tpl.html';
				}else{
					throw new Exception("no file found for ".ROOT.V_PATH.$key.D.$val.'.tpl.html'); 
				}
			}
		}
	}
}

/**
 * 快捷渲染
 */
function loadPage($path,$page,array $param=null){
	$r = new newRender(array($path=>$page),$param);
	$r -> render();
}

/**
 * { 获取http请求，并处理相应内容！}
 * @param (method) {get or post method}
 * @param (url) {http(s) link}
 * @param (data) {data in array}
 */

class httpRequest{
	private $method;
	private $url;
	private $data;
	private $func;
	private $header;
	private $ifJSON;

	public function __construct($method,$url,array $data=null,closure $func=null,$ifJson=null){
		$this->method = strtolower($method);
		$this->url = $url;
		$this->data = $data;
		$this->url = $this->getMethodToUrl();
		$this->func = $func;
		$this->ifJSON = !empty($ifJson)?$ifJson:false;
	}

	/**
	 * { initiate a http request with data }
	 *
	 * @return     <Multiply>  ( http responese )
	 */
	
	public function exec_request(){
		$result = $this->https_request();
		if(!empty($this->func)){
			$callBack = call_user_func_array($this->func, array($result));
			return $callBack;
		}
	}

	private function https_request(){
		switch ($this->method) {
			case 'get':
				//初始化
				$ch = curl_init();
				//设置选项，包括URL
				curl_setopt($ch, CURLOPT_URL, $this->url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
			    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
				if(!empty($this->header)){
					curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
				}

				//执行并获取HTML文档内容
				$output = curl_exec($ch);
				//释放curl句柄
				curl_close($ch);
				//打印获得的数据
				
				return $output;
				break;
			case 'post':
				$ch = curl_init();
			    curl_setopt($ch,CURLOPT_URL,$this->url);
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
			    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
			    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
			    if(!empty($this->data)){
			        curl_setopt($ch,CURLOPT_POST,1);
			        if($this->ifJSON){
			        	curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($this->data));
			        }else{
			        	curl_setopt($ch,CURLOPT_POSTFIELDS,$this->data);
			        } 
			    }else{
			    	curl_setopt($ch,CURLOPT_POSTFIELDS,null);
			    }
			    if(!empty($this->header)){
					curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
				}
			    $output = curl_exec($ch);
			    curl_close($ch);
			    return $output;
			    break;
		}  
	}

	/**
	 * { if it's get method, change the array data into url}
	 * @return <$this-url> {new url}
	 */
	public function setHeader($headerArr){
		if(is_array($headerArr)){
			$this->header = $headerArr;
		}else{
			throw new Exception("$headerArr should be array!", 1);
		}
	}

	private function getMethodToUrl(){
		if($this->method == 'get' && !empty($this->data)){
			if(strstr($this->url,"?")){
				$urlReset = '';
				foreach($this->data as $key => $val){
					$urlReset .= '&'.$key.'='.$val;
				}
				return $this->url.$urlReset;
			}else{
				$urlReset = '?';
				foreach($this->data as $key => $val){
					$urlReset .= $key.'='.$val.',';
				}
				$urlReset = str_replace(',', '&', $urlReset);
				$urlReset = substr($urlReset, 0,-1);
				return $this->url.$urlReset;
			}		
		}else{
			return $this->url;
		}
	}
}


/**
 * { data form all paths, both get or post methods! And get_magic_quotes_gpc()id detected.}
 */

function v($param){
	if(!$param){
		echo 'v should have a param';
		exit;
	}elseif($param != 'POST_ALL'){
		@$data = !empty($_GET[$param])?$_GET[$param]:'';
		if(empty($data)){
			@$data = !empty($_POST[$param])?$_POST[$param]:'';
		}

		if(!get_magic_quotes_gpc() && !is_array($data)){
			@$data = !empty($data)?addslashes($data):'';
		}
	}elseif($param == 'POST_ALL'){
		@$data = !empty($_POST)?$_POST:json_decode(file_get_contents("php://input"),true);
		if(!get_magic_quotes_gpc() && !is_array($data)){
			@$data = !empty($data)?addslashes($data):'';
		}
	}

	return @$data;
}

/**
 * { delete space charactors }
 *
 * @param      <string>     $param  The parameter
 *
 * @throws     Exception  (when param is not defined)
 *
 * @return     <string>     (return parameter without space charactors )
 */
function t($param){
	if(!$param){
		throw new Exception('t should hava a param!');
	}else{
		@$newParam = trim($param);
		return @$newParam;
	}
}


function includeTpl($group,$item){
	if(file_exists(ROOT.V_PATH.$group.D.$item.'.tpl.html')){
		require_once(ROOT.V_PATH.$group.D.$item.'.tpl.html');
	}else{
		throw new Exception('no tpl.html found in ['.$group.'] path!');
	}
}

function includeActiveTpl($group,$item,$activeItem){
	$active = $activeItem;
	if(file_exists(ROOT.V_PATH.$group.D.$item.'.tpl.html')){
		require_once(ROOT.V_PATH.$group.D.$item.'.tpl.html');
	}else{
		throw new Exception('no tpl.html found in ['.$group.'] path!');
	}
}

class ConnectMySql{
	private $connect_url;
	private $connect_db;
	private $connect_username;
	private $connect_password;
	private $db;

	function __construct(){
		$this->db = new mysqli(DB_URL,DB_USER_NAME,DB_PASSWORD,DB_NAME);
		if (mysqli_connect_error()) {
    		die('Connect Error ('. mysqli_connect_errno() . ')'. mysqli_connect_error());
		}else{
			return "mysql connect succ!";
		}
	}

	function get_query($query){
		if(empty($query)){
			$arr = array('code'=>-1,"msg"=>'query fail!');
			return json_encode($arr);
		}else{
			$result = mysqli_query($this->db,$query);
			$data = [];
			$i =0;
			while( $Array = mysqli_fetch_array($result, MYSQLI_ASSOC)){
               $data[$i++] = $Array;
            }
            $arr = array(
            	'code' => 1,
            	'msg' => $data
        	);
			return json_encode($arr);
		}
	}

	function insert_query($query){
		if(empty($query)){
			throw new Exception('Please Enter query!');
		}else{
			$result = mysqli_query($this->db,$query);
			if($result){
				return $result;
			}else{
				return mysqli_error($this->db); 
			}
		}
	}
}

/**
 * @param  path [str] path for file
 * @param  uploadName [str] post file name
 * @return [json]
 */
function save_file($path,$uploadName){
	if($_FILES[$uploadName]["tmp_name"]){
		$fileOrigin = explode('.', $_FILES[$uploadName]["name"]);
		$newFileRoot = FILE_UPLOAD_PATH.$path.D.$fileOrigin[0].rand(1000,9999).'.'.$fileOrigin[1];
		move_uploaded_file($_FILES[$uploadName]["tmp_name"],$newFileRoot);
		return $newFileRoot;
	}else{
		throw new Exception("fileNotExits", 1);
		return false;
	}
}

function echo_json($code,$msg,$data = null){
	$arr = array(
		'code'=>$code,
		'msg'=>$msg,
		'data'=>!empty($data)?$data:[]
	);
	echo json_encode($arr);
}

function getUrl($type){
	$host_string = $_SERVER["HTTP_HOST"];
	$f = strpos($host_string,"localhost");
	switch ($type) {
		case 'ALL':
			if($f === false){
				return "http://".$host_string.$_SERVER["REQUEST_URI"];
			}else{
				return $host_string.$_SERVER["REQUEST_URI"];
			}
			break;
		
		default:
			if($f === false){
				return "http://".$host_string;
			}else{
				return $host_string;
			}
			break;
	}
}

function errorPage(array $data=null){
	$page = new newRender(array('error'=>'error'), $data);
	$page->render();
}