<?php
/**
* 微信基本类，主要进行token刷新和记录
*/
require_once(__DIR__."/Until.php");

class Base{
	
	//微信appid
	private $appid;

	//微信appsecret
	private $appsecret;

	//微信access_token
	private $access_token;

	//access_token过期时间(时间戳)
	private $expire_time;

	//jsapi_ticket过期时间(时间戳)
	private $jsapi_expire_time;

	//jsapi_ticket
	private $jsapi_ticket;

	//构造函数，获取配置文件中微信参数，当token和jsapi_ticket过期时候，会重新刷新私有属性
	public function __construct(){
		$conf = include("Conf.php");
		$this->appid = $conf['appid'];
		$this->appsecret = $conf['appsecret'];
		$this->access_token = $conf['access_token'];
		$this->expire_time = $conf['expire_time'];
		$this->jsapi_ticket = $conf['jsapi_ticket'];
		$this->jsapi_expire_time = $conf['jsapi_expire_time'];
	}

	protected function getAppId(){
		return $this->appid;
	}

	protected function getAppSecret(){
		return $this->appsecret;
	}

	/**
	* 获取token，并检测token是否过期
	* return string
	*/
	protected function getToken(){
		if(time()<$this->expire_time){
			return $this->access_token;
		}else{
			//token过期，重新获取并写入
			return $this->initToken();
		}
	}

	/**
	* 获取jsapi_ticket，并检测是否过期
	* return string
	*/
	protected function getTicket(){
		if(time()<$this->jsapi_expire_time){
			return $this->jsapi_ticket;
		}else{
			return $this->initTicket();
		}
	}

	//初始化重新获取token
	private function initToken(){
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->appsecret;
		$res = Until::httpGet($url);
		$result = json_decode($res,true);
		if(isset($result['access_token'])){
			$isInit = !$this->access_token?1:0;
			//token获取成功，重写配置文件,刷新私有属性
			$this->access_token = $result['access_token'];
			$this->expire_time = 7000 + time();
			$this->readToken($this->access_token,$this->expire_time,$isInit);
			return $result['access_token'];
		}
	}

	//重新获取jsapi_ticket
	private function initTicket(){
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$this->getToken()."&type=jsapi";
		$res = Until::httpGet($url);
		$result = json_decode($res,true);
		if(isset($result['ticket'])){
			//获取成功，刷新私有属性，并写入配置文件
			$this->jsapi_ticket = $result['ticket'];
			$this->jsapi_expire_time = 7000 + time();
			$this->readTicket($this->jsapi_ticket,$this->jsapi_expire_time);
			return $result['ticket'];
		}
	}


	/**
	* 把token和过期时间写入conf文件中
	* @param string $access_token access_token参数
	* @param int $expire_time token过期时间
	* @param $isInit 是否是第一次写入(fseek函数需要原来位置的长度一样才可以完全匹配位置)
	*/
	private function readToken($access_token,$expire_time,$isInit){
		$fp = fopen(__DIR__.'/Conf.php', 'r+');
		$i = 1;
		while (!feof($fp)) { 
			if ($i == 13) { 
				fseek($fp, 17,SEEK_CUR ); 
				fwrite($fp,$expire_time);
			}
			if ($i == 14) { 
				if($isInit){
					fseek($fp, 0,SEEK_CUR ); 
					fwrite($fp,"'access_token' => '".$access_token."'\r);\r?>");
				}else{
					fseek($fp, 19,SEEK_CUR ); 
					fwrite($fp,$access_token);
				}
				break;
			}
			fgets($fp); 
			$i++; 
		}
	}

	/**
	* 把jsapi_ticket和过期时间写入conf文件中
	* @param string $access_token access_token参数
	* @param int $expire_time token过期时间
	* @param $isInit 是否是第一次写入(fseek函数需要原来位置的长度一样才可以完全匹配位置)
	*/
	private function readTicket($access_token,$expire_time){
		$fp = fopen(__DIR__.'/Conf.php', 'r+');
		$i = 1;
		while (!feof($fp)) { 
			if ($i == 11) { 
				fseek($fp, 23,SEEK_CUR ); 
				fwrite($fp,$expire_time);
			}
			if ($i == 12) { 
				fseek($fp, 19,SEEK_CUR ); 
				fwrite($fp,$access_token);
				break;
			}
			fgets($fp); 
			$i++; 
		}
	}


	/**
	* 返回消息
	* @param int $errcode 消息代码
	* @param string $errmsg 消息内容
	* return json
	*/
	protected function setMessage($errcode,$errmsg){
		$result['errcode'] = $errcode;
		$result['errmsg'] = $errmsg;
		$res = json_encode($result,JSON_UNESCAPED_UNICODE);
		return $res;
	}

}
?>