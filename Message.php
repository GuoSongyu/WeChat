<?php
/**
* 微信消息回复类
*/
require_once(__DIR__."/Base.php");

class Message extends Base{

	//定义消息回复模板

	//纯文本回复
	private $temText = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[%s]]></MsgType>
        <Content><![CDATA[%s]]></Content>
        </xml>";

    //图片回复
    private $temImg = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[%s]]></MsgType>
		<Image>
		<MediaId><![CDATA[%s]]></MediaId>
		</Image>
		</xml>";

	//语音回复
	private $temVoice = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[%s]]></MsgType>
		<Voice>
		<MediaId><![CDATA[%s]]></MediaId>
		</Voice>
		</xml>";

	//视频回复
	private $temVideo = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[%s]]></MsgType>
		<Video>
		<MediaId><![CDATA[%s]]></MediaId>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		</Video> 
		</xml>";

	//音乐回复
	private $temMusic	= "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[%s]]></MsgType>
		<Music>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		<MusicUrl><![CDATA[%s]]></MusicUrl>
		<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
		<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
		</Music>
		</xml>";


	//图文回复
	private $temNews = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[%s]]></MsgType>
		<ArticleCount>%s</ArticleCount>
		<Articles>
		</Articles>
		</xml>";

	private $temArticles = "<item>
		<Title><![CDATA[%s]]></Title> 
		<Description><![CDATA[%s]]></Description>
		<PicUrl><![CDATA[%s]]></PicUrl>
		<Url><![CDATA[%s]]></Url>
		</item>";



	/**
	* 微信消息接收入口，此方法用于判断微信推送的消息类型，并调用相关操作
	* @param obj $postObj 微信发送到服务器的消息载体
	* 					  MsgType 消息类型
	* 					  Event 事件类型（当类型为事件时存在）
	*					  ToUserName 微信平台id
	* 					  FromUserName 用户openid
	*   				  Content 消息内容
	* 暂时无用
	*/
	public function entrance($postObj){
		switch ($postObj->MsgType) {
			case 'event':
				$this->processEvent($postObj);
				break;
			case 'text':
				$this->processText($postObj);
				break;
			default:
				
				break;
		}
	}

	/**
	* 事件回复
	* @param obj $postObj 微信发送到服务器的消息载体
	* 暂时无用
	*/
	private function processEvent($postObj){
		switch ($postObj->Event) {
			case 'subscribe':
				//关注后操作
				break;
			case 'click':
				//菜单点击事件
				break;
			default:
				# code...
				break;
		}
	}


	/**
	* 发送客服消息用户必须在48小时之内与公众号发生如下交互之后才能发送成功
	*	1、用户发送信息
	*	2、点击自定义菜单（仅有点击推事件、扫码推事件、扫码推事件且弹出“消息接收中”提示框这3种菜单类型是会触发客服接口的）
	*	3、关注公众号
	*	4、扫描二维码
	*	5、支付成功
	*	6、用户维权
	* @param string $openid 用户openid
	* @param string $msgtype 消息类型（text、image、voice、video、music、news）
	* @param string|array $content 消息内容（文本、media_id、图文等消息内容）
	*/
	public function send($openid,$msgtype,$content){
		$sendData = array();		
		switch ($msgtype) {
			case 'text':
				$sendData = $this->sendText($content);
				break;
			case 'image':
				$sendData = $this->sendImg($content);
				break;
			case 'voice':
				$sendData = $this->sendVoice($content);
				break;
			case 'music':
				$sendData = $this->sendMusic($content);
				break;
			case 'news':
				$sendData = $this->sendNews($content);
				break;
			default:
				return $this->setMessage(1,"msgtype参数错误，应为text、image、voice、video、music、news其中之一");
				break;
		}
		$sendData['touser'] = $openid;
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$this->getToken();
		$res = Until::httpPost($url,json_encode($sendData,JSON_UNESCAPED_UNICODE));
		return $res;
	}

	/**
	* 消息回复操作
	* @param obj $postObj 微信发送到服务器的消息载体
	* @param string $type 消息回复类型
	* @param string|array $content 消息回复内容（可以是纯文本、media_id、图文信息json）
	*/
	public function reply($postObj,$type,$content){
		switch ($type) {
			case 'text':
				$info = $this->replyText($postObj,$content);
				break;
			case 'image':
				$info = $this->replyImg($postObj,$content);
				break;
			case 'voice':
				$info = $this->replyVoice($postObj,$content);
				break;
			case 'news':
				$info = $this->replyNews($postObj,$content);
				break;
			default:
				#其他类型视为错误
				break;
		}
		// return $info;
		print_r($info);
		exit;
	}


	/**
	* 文本消息回复（关键字）
	* @param obj $postObj 微信发送到服务器的消息载体
	* @param string $type 被动回复消息类型()
	* @param string|array $content 消息内容(文本、素材media_id、图文信息)
	*/
	private function processText($postObj,$type,$content){
		$info = $this->reply($postObj,$type,$content);
		//回复给微信指定消息并exit
		print_r($info);
		exit;
	}


	/**
	* 文本信息回复
	* @param obj $postObj 微信发送到服务器的消息载体
	* @param string $content 消息回复内容
	* @param string $type 消息回复类型
	*/
	private function replyText($postObj,$content,$type = "text"){
		$res = sprintf($this->temText,$postObj->FromUserName,$postObj->ToUserName,time(),$type,$content);
		return $res;
	}


	/**
	* 图片回复
	* @param obj $postObj 微信发送到服务器的消息载体
	* @param string $media_id 图片素材的media_id
	* @param string $type 消息回复类型
	*/
	private function replyImg($postObj,$media_id,$type = "image"){
		$res =sprintf($this->temImg,$postObj->FromUserName,$postObj->ToUserName,time(),$type,$media_id);
		return $res;
	}

	/**
	* 语音回复
	* @param obj $postObj 微信发送到服务器的消息载体
	* @param string $media_id 语音素材media_id
	* @param string $type 消息回复类型
	*/
	private function replyVoice($postObj,$media_id,$type = "voice"){
		$res = sprintf($this->temVoice,$postObj->FromUserName,$postObj->ToUserName,time(),$type,$media_id);
		return $res;
	}


	/**
	* 图文回复
	* @param obj $postObj 微信发送到服务器的消息载体
	* @param array $articles 图文信息数组
	* @param string $type 消息回复类型
	*/
	private function replyNews($postObj,$articles,$type = "news"){
		$articleCount = count($articles);
		$items = "";
		foreach ($articles as $key => $item) {
			$temItem = $this->temArticles;
			$items .= sprintf($temItem,$item['title'],$item['description'],$item['picurl'],$item['url']);
		}
		//把图文信息插入到模板中
		$template = substr_replace($this->temNews,$items,strpos($this->temNews, "<Articles>") + strlen("<Articles>"),0);
		//组合图文消息完整模板
		$res = sprintf($template,$postObj->FromUserName,$postObj->ToUserName,time(),$type,$articleCount);
		return $res;
	}


	/**
	* 发送文本消息
	* @param string $content 文本内容
	* return array
	*/
	private function sendText($content){
		$data['msgtype'] = "text";
		$data['text'] = array('content'=>$content);

		return $data;
	}
	
	/**
	* 发送图片消息
	* @param string $media_id 素材media_id
	* return array
	*/
	private function sendImg($media_id){
		$data['msgtype'] = "image";
		$data['image'] = array('media_id'=>$media_id);

		return $data;
	}

	/**
	* 发送语音消息
	* @param string $media_id 素材media_id
	* return array
	*/
	private function sendVoice($media_id){
		$data['msgtype'] = "voice";
		$data['voice'] = array('media_id'=>$media_id);

		return $data;
	}
	
	/**
	* 发送视频消息
	* @param array $videoArr 视频信息(media_id、thumb_media_id、title、description)
	* return array
	*/
	private function sendVideo($videoArr){
		$data['msgtype'] = "video";
		$data['video'] = array(
				'media_id' => $videoArr['media_id'],
				'thumb_media_id' => $videoArr['thumb_media_id'],
				'title' => $videoArr['title'],
				'description' => $videoArr['description']
			);

		return $data;
	}

	/**
	* 发送音乐消息
	* @param array $musicArr 视频信息(title、description、musicurl、hqmusicurl、thumb_media_id)
	* return array
	*/
	private function sendMusic($musicArr){
		$data['msgtype'] = $music;
		$data['music'] = array(
				'title' => $musicArr['title'],
				'description' => $musicArr['description'],
				'musicurl' => $musicArr['musicurl'],//音乐链接
				'hqmusicurl' => $musicArr['hqmusicurl'],//高质量音乐链接（wifi下优先播放）
				'thumb_media_id' => $musicArr['thumb_media_id']
			);

		return $data;
	}

	/**
	* 发送图文消息
	* @param array $newsArr 图文信息()
	* return array
	*/
	private function sendNews($media_id){
		$data['msgtype'] = "mpnews";
		$data["mpnews"] = array("media_id"=>$media_id);

		return $data;
	}


}
?>