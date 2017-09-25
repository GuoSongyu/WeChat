<?php

require_once(__DIR__."/User.php");
require_once(__DIR__."/Menu.php");

$u = new User();
$m = new Menu();

// $res = $u->createTag("暮语");
// $res = $u->belongTag(101);
// $res = $u->editTag(array('id'=>101,'name'=>'newTag'));
// $u->delTag(101);
// $res = $u->getAllTag();
// $res = $u->getAll();
// $openidList = array(
// 		'okhjrsuupKjS5o7QHbKMMGmD-vkU'
// 	);
// $res = $u->batchMakeTag("tag",$openidList,100);
// $res = $u->getTag("okhjrsuupKjS5o7QHbKMMGmD-vkU");
// var_dump($res);exit;
$arr = array(
	array('id'=>1,'pid'=>0,'name'=>'个性化一','type'=>'view','url'=>'http://www.baidu.com'),
	array('id'=>2,'pid'=>0,'name'=>'父菜单二','type'=>'view','url'=>'http://www.baidu.com'),
	array('id'=>3,'pid'=>0,'name'=>'个性化三','type'=>'view','url'=>'http://www.baidu.com'),
	array('id'=>4,'pid'=>1,'name'=>'子菜单一一','type'=>'view','url'=>'http://www.baidu.com'),
	array('id'=>5,'pid'=>1,'name'=>'子菜单一二','type'=>'view','url'=>'http://www.baidu.com'),
	array('id'=>6,'pid'=>1,'name'=>'子菜单一三','type'=>'view','url'=>'http://www.baidu.com'),
	array('id'=>7,'pid'=>2,'name'=>'子菜单二一','type'=>'view','url'=>'http://www.baidu.com'),
	array('id'=>8,'pid'=>2,'name'=>'子菜单二二','type'=>'view','url'=>'http://www.baidu.com'),
	array('id'=>9,'pid'=>2,'name'=>'子菜单二三','type'=>'view','url'=>'http://www.baidu.com'),
);
//已创建个性化菜单 411929092 411929687 411930185
$m->build($arr);
// $res = $m->create();
// $res = $m->createSpecial(array('tag_id'=>100));
$res = $m->getSpecial("guo13889155105");
// $result = $u->batchMakeTag("untag",$openidList,100);
// var_dump($result);
var_dump($res);
?>