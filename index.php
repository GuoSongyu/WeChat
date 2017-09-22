<?php

require_once(__DIR__."/User.php");

$u = new User();

$res = $u->getAll("okhjrsmlKzQKhyvAAgE1XYYrrBBw");
$arr = json_decode($res,true);
echo "<pre/>";
print_r($arr['data']['openid']);

?>