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
		if($codeRes == false){
			$msg = "未获取到code值";
			return $this->setMessage(1,$msg);
		}
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$codeRes['access_token']."&openid=".$codeRes['openid']."&lang=zh_CN";
		$res = Until::httpGet($url);
		return $res;
	}


	/**
	* 获取微信下用户列表
	* @param string $nextOpenid 
	* return json
	*/
	public function getAll($nextOpenid = ""){
		$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$this->getToken();
		$finalUrl = $nextOpenid?$url."&next_openid=".$nextOpenid:$url;
		$res = Until::httpGet($finalUrl);

		return $res;
	}	


	/************************ 标签操作 *****************************/

	/**
	* 创建用户标签
	* @param string $name 标签名称
	* return json
	*/
	public function createTag($name){
		$data['tag'] = array('name'=>$name);
		$url = "https://api.weixin.qq.com/cgi-bin/tags/create?access_token=".$this->getToken();

		$res = Until::httpPost($url,json_encode($data));
		return $res;
	}

	/**
	* 获取公众号下已经创建的标签
	* return json
	*/
	public function getAllTag(){
		$url = "https://api.weixin.qq.com/cgi-bin/tags/get?access_token=".$this->getToken();
		$res = Until::httpGet($url);
		return $res;
	}


	/**
	* 编辑标签
	* @param array $tag 要修改的标签内容（id、name）
	* return json
	*/
	public function editTag($tag){
		$data['tag'] = $tag;
		$url = "https://api.weixin.qq.com/cgi-bin/tags/update?access_token=".$this->getToken();
		$res = Until::httpPost($url,json_encode($data));
		return $res;
	}


	/**
	* 删除标签（标签下粉丝超过10W时不可删除，要手动取消该标签粉丝到10W以下才可以删除）
	* @param int $tagId 标签id
	* return json
	*/
	public function delTag($tagId){
		$data['tag'] = array('id'=>$tagId);
		$url = "https://api.weixin.qq.com/cgi-bin/tags/delete?access_token=".$this->getToken();
		$res = Until::httpPost($url,json_encode($data));
		return $res;
	}

	/**
	* 查询标签下的用户列表
	* @param int $tagId 标签id
	* @param stirng $nextOpenid 第一个拉取的openid，不写的话默认从头拉取
	* return json
	*/
	public function belongTag($tagId,$nextOpenid = ""){
		$data['tagid'] = $tagId;
		$data['next_openid'] = $nextOpenid;
		$url = "https://api.weixin.qq.com/cgi-bin/user/tag/get?access_token=".$this->getToken();
		$res = Until::httpPost($url,$data);
		return $res;
	}


	/**
	* 批量操作用户标签
	* @param string $type 操作类型（tag打标签、untag取消标签）
	* @param array $openidList openid列表
	* @param int $tagId 标签id
	* return json
	*/
	public function batchMakeTag($type,$openidList,$tagId){
		switch ($type) {
			case 'tag':
				$url = "https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token=".$this->getToken();
				break;
			case 'untag':
				$url = "https://api.weixin.qq.com/cgi-bin/tags/members/batchuntagging?access_token=".$this->getToken();
				break;
			default:
				return $this->setMessage(1,"标签操作类型错误，请选择tag(打标签)或untag(取消标签)");
				break;
		}
		
		$data['openid_list'] = $openidList;
		$data['tagid'] = $tagId;

		$res = Until::httpPost($url,json_encode($data));
		return $res;
	}


	/**
	* 获取用户身上的标签列表
	* @param string $openid 用户openid
	* return json
	*/
	public function getTag($openid){
		$data['openid'] = $openid;
		$url = "https://api.weixin.qq.com/cgi-bin/tags/getidlist?access_token=".$this->getToken();

		$res = Until::httpPost($url,json_encode($data));
		return $res;
	}

}
?>