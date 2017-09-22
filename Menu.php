<?php
/**
* 微信自定义菜单类
*/
require_once(__DIR__."/Base.php");

class Menu extends Base{
	
	
	/**
	* 创建自定义菜单
	* @param array $menuArr 生成的菜单信息
	* @param string $pk 菜单数组中的主键名称
	* @param string $pid 子菜单中的父id名称
	* @param string $child 子集名称
	* return json
	*/
	public function create($menuArr,$pk = 'id', $pid = 'pid', $child = 'sub_button'){
		$data['button'] = Until::list_to_tree($menuArr,$pk = 'id', $pid = 'pid', $child = 'sub_button');
		//向微信发起请求，生成菜单(重新生成之后菜单信息要转为json格式)
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->getToken();
		$res = Until::httpPost($url,json_encode($data,JSON_UNESCAPED_UNICODE));
		return $res;
	}	


	/**
	* 删除自定义菜单
	* return json
	*/
	public function del(){
		$url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".$this->getToken();
		$res = httpGet($url);
		return $res;
	}






}
?>