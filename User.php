<?php
/**
* 微信获取用户信息、auth认证类
*/
require_once(__DIR__."/Base.php");

class User extends Base{
	

	/**
	* 根据openid获取用户信息
	* @param string $openid 用户openid
	* return array
	*/
	public function getInfo($openid){
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$this->getToken()."&openid={$openid}&lang=zh_CN";
		$res = Until::httpGet($url);
		return $res;
	}


	/**
	* 网页授权根据code换取token和openid
	* @param string $code 微信code用于换取token和openid
	* return array|false
	*/
	private function useCode($code){
		if(isset($code)){
			$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->getAppId()."&secret=".$this->getAppSecret()."&code={$code}&grant_type=authorization_code";
			//通过请求地址获取token openid等信息
			$res = Until::httpGet($url);
			return json_decode($res,true);	
		}else{
			return false;
		}
	}

	/**
	* 根据code换取的token和openid查询用户基本信息（头像等）
	* @param string $code 微信获取的code
	* return json
	*/
	public function auth($code){
		$codeRes = $this->useCode($code);
		if(isset($codeRes['errcode'])){
			return json_encode($codeRes);
		}
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$codeRes['access_token']."&openid=".$codeRes['openid']."&lang=zh_CN";
		$res = Until::httpGet($url);
		return $res;
	}




}
?>