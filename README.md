# Rainbow
基于PHP实现系统多语言自动替换，语言包自动生成，可设置从中文转换多种语言包。
# 快速开始
test.php
<?php
header("content-type:text/html;charset=utf-8");
//获取单号最后一条物流
use Rainbow\Rainbow;
require_once(__DIR__ . '/Autoload.php');

$dir = 'D:/wamp/www/stointl/source/php/Public/Member/js/page/';
$langpackdir = 'D:/wamp/www/stointl/source/php/Public/Member/js/page/';
$test = new Rainbow($dir,$langpackdir);
$test->langpackfilename ='zh-cn';
if($test->run()){
    echo "success";
}else{
    echo "fail";
}
