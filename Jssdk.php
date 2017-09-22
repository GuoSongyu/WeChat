<?php
/**
* 微信js-sdk类
*/
require_once(__DIR__."/Base.php");

class Jssdk extends Base{


	/**
	* 组合signature参数
	* @param string $url 访问地址(http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI])
	* return json
	*/
	public function signPackage($url){
		$jsapiTicket = $this->getTicket();
	    $timestamp = time();
	    $nonceStr = $this->nonceStr();
	    //参数的顺序要按照 key 值 ASCII 码升序排序
	    $string = "jsapi_ticket=".$jsapiTicket."&noncestr=".$nonceStr."&timestamp=".$timestamp."&url=".$url;
	    $signature = sha1($string);
	    $signPackage = array(
	      "appId"     => $this->getAppId(),
	      "nonceStr"  => $nonceStr,
	      "timestamp" => $timestamp,
	      "url"       => $url,
	      "signature" => $signature,
	      "rawString" => $string
	    );
	    return json_encode($signPackage); 

	}

	//生成nonceStr
	private function nonceStr($length = 16){
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	    $str = "";
	    for ($i = 0; $i < $length; $i++) {
	      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	    }
	    return $str;
	}


	
}
?>