<?php
/**
*  微信素材管理
*/
require_once(__DIR__."/Base.php");

class Material extends Base{
	
	//允许上传的临时素材类型
	private $snapAllowType = array('image','voice','video','thumb');

	//允许上传的永久素材类型
	private $lastAllowType = array('image','voice','video','thumb','news');


	/**
	* 获取永久素材列表
	* @param sring $type 获取的素材类型
	* @param int $count 获取的素材类型
	* @param int $offset 从全部素材的该偏移位置开始返回，0表示从第一个素材 返回
	* return json
	*/
	public function getList($type,$count = 20,$offset = 0){
		$con = array(
			"type" => $type,
			"offset" => $offset,
			"count" => $count
		);
		$url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=".$this->getToken();

		$res = Until::httpPost($url,json_encode($con));
		return $res;
	}


	/**
	* 获取所有永久素材总数
	* return json
	*/
	public function getCount(){
		$url = "https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=".$this->getToken();
		$res = Until::httpGet($url);
		return $res;
	}


	/**
	* 新增素材
	* @param string $type 新增素材类型（临时、永久；临时只存在3天、永久图文消息素材、图片素材上限为5000，其他类型为1000。）
	* @param string $materialType 素材类型
	* @param array $local 素材信息（包括路径、名称等信息）
	* return json
	https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=ACCESS_TOKEN&type=TYPE
	https://api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE
	*/
	public function add($type,$materialType,$local){
		switch ($type) {
			case 'snap':
				$res = $this->addSnap($materialType,$local);
				break;
			case 'lasting':
				$res = $this->addLast($materialType,$local);
				break;
			default:
				$res = $this->setMessage(1,"参数错误，请选择snap(临时)或lasting(永久)");
				break;
		}
		return $res;
	}


	/**
	* 上传临时素材
	* @param string $materialType 素材类型
	* @param array $local 素材信息（包括路径、名称等信息）
	* return json
	*/
	private function addSnap($materialType,$local){
		//检测类型是否符合
		if(!in_array($materialType,$this->snapAllowType)){
			return $this->setMessage(1,"素材类型不符合");
		}
		$res = $this->snapOther($materialType,$local);
		return $res;
	}

	/**
	* 上传永久素材
	* @param string $materialType 素材类型
	* @param array $local 素材信息（包括路径、名称、文章内容信息）
	* return json
	*/
	private function addLast($materialType,$local){
		//检测类型是否符合
		if(!in_array($materialType,$this->lastAllowType)){
			return $this->setMessage(1,"素材类型不符合");
		}
		if($materialType == "news"){
			//上传图文素材
			$res = $this->lastNews($local);
		}else{
			//除图文之外的素材，音频、图片等
			$res = $this->lastOther($materialType,$local);
		}
		return $res;
	}


	/**
	* 上传图文素材
	* @param array $artArr 文章信息
	*/
	private function lastNews($artArr){
		$url = "https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=".$this->getToken();
		$upload['articles'] = $artArr;
		$res = Until::httpPost($url,json_encode($upload,JSON_UNESCAPED_UNICODE));
		return $res;
	}

	/**
	* 上传永久素材（图片、音频等）
	* @param string $materialType 素材类型
	* @param array $local 素材信息（包括路径、名称等信息）
	* return json
	*/
	private function lastOther($materialType,$local){
		if($materialType == "video"){
			//取出视频相关参数
			$video['title'] = $local['title'];
			$video['introduction'] = $local['introduction'];

			$upload['media'] = new \CURLFile($local['path']);
			$upload['description'] = $video;
			$upload['type'] = $materialType;
		}else{
			$upload['media'] = new \CURLFile($local['path']);
			$upload['type'] = $materialType;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=".$this->getToken();
		$res = Until::httpPost($url,$upload);
		return $res;
	}

	/**
	* 上传临时素材
	* @param string $materialType 素材类型
	* @param array $local 素材信息（路径、名称等）
	* return json
	*/
	private function snapOther($materialType,$local){
		$url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=".$this->getToken();
		// $upload['media'] = "@".$local['path'].";filename=".$local['filename'];
		$upload['media'] = new \CURLFile($local['path']);
		$upload['type'] = $materialType;
		$res = Until::httpPost($url,$upload);
		return $res;
	}


}
?>