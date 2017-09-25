<?php
/**
* 工具类
*/

class Until{

	/**
	 * 把返回的数据集转换成Tree
	 * @param array $list 要转换的数据集
	 * @param string $pk 信息主键
	 * @param string $pid parent标记字段
	 * @param string $child 子分类名称
	 * return array
	 */	
	static public function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0){
	    // 创建Tree
	    $tree = array();
	    if (is_array($list)) {
	        // 创建基于主键的数组引用
	        $refer = array();
	        foreach ($list as $key => $data) {
	            $refer[$data[$pk]] =& $list[$key];
	        }
	        foreach ($list as $key => $data) {
	            // 判断是否存在parent
	            $parentId = $data[$pid];
	            if ($root == $parentId) {
	                $tree[] =& $list[$key];
	            } else {
	                if (isset($refer[$parentId])) {
	                    $parent =& $refer[$parentId];
	                    $parent[$child][] =& $list[$key];
	                }
	            }
	        }
	    }

	    return $tree;
	}

	/**
	* 通过curl post来获取信息
	* @param string $url 请求信息地址
	* @param arr $data 请求携带信息
	* return json
	*/
	static public function httpPost($url,$data){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$tmpInfo = curl_exec($ch);
		 
		if (curl_errno($ch)) {
			return ("curl_error: ".curl_error($ch));
		}
		curl_close($ch);
		return $tmpInfo;
	}

	/**
	* 通过get方式来请求信息
	* @param string $url 请求信息的地址
	* return json
	*/
	static public function httpGet($url){
		$curl = curl_init();
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_TIMEOUT, 100);
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	    $res = curl_exec($curl);
	    curl_close($curl);
	    return $res;
	}

}
?>