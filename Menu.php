<?php
/**
* 微信自定义菜单类
*/
require_once(__DIR__."/Base.php");

class Menu extends Base{
	
	//个性化菜单条件
	private $specialCon = array('tag_id','sex','client_platform_type','country','province','city','language');
	
	//重组的菜单数组
	private $buttonData;


	/**
	* 构建菜单数组
	* @param array $menuArr 生成的菜单信息
	* @param string $pk 菜单数组中的主键名称
	* @param string $pid 子菜单中的父id名称
	* @param string $child 子集名称
	*/
	public function build($menuArr,$pk = 'id', $pid = 'pid', $child = 'sub_button'){
		$this->buttonData = Until::list_to_tree($menuArr,$pk, $pid, $child);
		return $this->buttonData;
	}

	/**
	* 创建自定义菜单
	* return json
	*/
	public function create(){
		if(!$this->buttonData){
			$msg = "菜单信息为空，请先指定build方法构建菜单";
			return $this->setMessage(1,$msg);
		}
		$data['button'] = $this->buttonData;
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

	/**
	* 创建个性化菜单，按照如下条件匹配用户：
	* 1、用户标签（开发者的业务需求可以借助用户标签来完成）(tag_id)
 	* 2、性别 (sex)
	* 3、手机操作系统 (client_platform_type IOS(1), Android(2),Others(3))
	* 4、地区（用户在微信客户端设置的地区）(country,province,city按顺序验证，从大到小，小的可不填写)
	* 5、语言（用户在微信客户端设置的语言）(language)
	* 但是创建个性化菜单之前一定要创建好默认菜单（自定义菜单接口）；
	* 用户匹配条件必须存在一个；
	* 用户身上为多标签时，以最后一个为匹配条件
	* 如果为多个个性化，从后向前进行匹配（从最新的开始匹配）
	* @param array $buttonCon 个性化菜单条件
	* return json
	*/
	public function createSpecial($buttonCon){
		$checkRes = $this->checkCon($buttonCon);
		if(!is_array($checkRes)){
			return $checkRes;
		}
		if(!$this->buttonData){
			$msg = "菜单信息为空，请先指定build方法构建菜单";
			return $this->setMessage(1,$msg);
		}
		$data['button'] = $this->buttonData;
		$data['matchrule'] = $checkRes;
		$url = "https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token=".$this->getToken();
		$res = Until::httpPost($url,json_encode($data,JSON_UNESCAPED_UNICODE));
		return $res;
	}	


	/**
	* 查询用户的个性化匹配
	* @param string user_id 用户微信号或openid
	* return json
	*/
	public function getSpecial($user_id){
		$url = "https://api.weixin.qq.com/cgi-bin/menu/trymatch?access_token=".$this->getToken();
		$data['user_id'] = $user_id;
		$res = Until::httpPost($url,json_encode($data));
		return $res;
	}


	/**
	* 删除个性化菜单
	* @param int $menu_id 个性化菜单id
	* return json
	*/
	public function delSpecial($menu_id){
		$url = "https://api.weixin.qq.com/cgi-bin/menu/delconditional?access_token=".$this->getToken();
		$data['menuid'] = $menu_id;
		$res = Until::httpPost($url,json_encode($data,JSON_UNESCAPED_UNICODE));
		return $res;
	}


	/**
	* 检测条件信息是否正确，并是否空值,并对省市区进行排序
	* @param array $con 菜单条件二维数组
	* @param boolean 
	* return array|json
	*/
	private function checkCon($con,$res = true){
		//检测参数是缺失
		$value = array();
		$area = array();
		foreach ($con as $k => $v) {
			if(!in_array($k,$this->specialCon)){
				$res = false;
				break;
			}
			if(isset($k) && $k == "country"){
				$area[0] = $v;
				unset($con[$k]);
			}
			if(isset($k) && $k == "province"){
				$area[1] = $v;
				unset($con[$k]);
			}
			if(isset($k) && $k == "city"){
				$area[2] = $v;
				unset($con[$k]);
			}
			$value[] = $v;
		}
		if(!$res){
			$msg = "参数错误，必须是".implode(",",$this->specialCon)."之间的参数";
			return $this->setMessage(1,$msg);
		}
		if(count($value) < 1){
			$msg = "请至少填写一个条件";
			return $this->setMessage(1,$msg);
		}
		//合并省市区参数排序
		$newArr = array_merge($con,$area);
		return $newArr;
	}


}
?>