<?php
/**
 * Created by PhpStorm.
 * User: muchao
 * Date: 15/7/14
 * Time: 上午10:09
 */

if(!defined("AWS_PATH")){
    define("AWS_PATH", './s3/');
}

require_once 's3.php';

$file = "new_test_300.jpg";
//$content = file_get_contents('http://pic.dofay.com/2015/03/26t01.jpg');
//file_put_contents($file, $content);
var_dump($file);
//Aws_s3::getInstance()->display();
//exit;
$result = AWS_s3::getInstance()->upload($file, 'ffaa890a19d8bc3e3fb9b06f838ba61ea9_300x300.jpg', 'talebox');
var_dump($result);

/*
成功返回
array(3) {
["err"]=>
int(0)
["msg"]=>
string(7) "success"
["url"]=>
string(75) "https://vshow.s3.amazonaws.com/ffaa890a19d8bc3e3fb9b06f838ba61ea9d34589.jpg"
}

失败返回
array(3) {
  ["err"]=>
  int(-1)
  ["msg"]=>
  string(7) "some message"
}

*/
