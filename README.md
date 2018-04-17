# PHP版系统多语言自动生成，模板，代码中自动替换
<?php
use Workerman\Worker;
use Workerman\Protocols\Http;
use WebWorker\Libs\Mredis;
use WebWorker\Libs\Mdb;
use WebWorker\Libs\Mmysqli;
use WebWorker\Libs\Maccess;

require_once 'vendor/autoload.php';

$app = new WebWorker\App("http://0.0.0.0:1215");
$app->count = 1;

$config = array();
$config["redis"]["host"] = "127.0.0.1";
$config["redis"]["port"] = 6379;
$config["redis"]["password"] = "123456";
$config["redis"]["db"] = 1;
$config["db"]["host"] = "127.0.0.1";
$config["db"]["user"] = "root";
$config["db"]["password"] = "123456";
$config["db"]["db"] = "test";
$config["db"]["port"] = 3306;
$config["db"]["charset"] = "utf8";
$config["access"]["appid"] = "123456";
$config["access"]["appsecret"] = "abcdef";


$app->name = "demo";

//设置每个进程处理多少请求后重启(防止程序写的有问题导致内存泄露)，默认为10000
$app->max_request = 1000;

//进程数
$app->count = 4;

//自动加载目录--会加载目录下的所有php文件
$app->autoload = array();

//启动时执行的代码，这儿包含的文件支持reload
$app->onAppStart = function($app) use($config){
    WebWorker\autoload_dir($app->autoload);     
};

//应用级中间件--对/hello访问启用ip限制访问
$app->AddFunc("/hello",function() {
    if ( $_SERVER['REMOTE_ADDR'] != '127.0.0.1' ) {
        $this->ServerHtml("禁止访问");
        return true;//返回ture,中断执行后面的路由或中间件，直接返回给浏览器
    }   
});

//应用级中间件--对所有以api前缀开头的启用签名验证
$app->AddFunc("/api",function() use($config) {
    $data = $_GET ? $_GET : $_POST;
    if ( !Maccess::verify_sign($data,$config["access"]) ){
        $this->ServerHtml("禁止访问");
        return true;
    }
});


//注册路由api/test
$app->HandleFunc("/api/test",function() {
    $this->ServerHtml("api test hello");
});

//注册路由hello
$app->HandleFunc("/hello",function() {
    $this->ServerHtml("Hello World WorkerMan WebWorker!");
});

//注册路由json
$app->HandleFunc("/json",function() {
     //以json格式响应
     $this->ServerJson(array("name"=>"WebWorker"));
});

//注册路由/
$app->HandleFunc("/",function() {
     //自定义响应头
     $this->Header("server: xtgxiso");
     //设置cookie
     $this->Setcookie("xtgxiso",time()); 
     //以json格式响应
     $this->ServerJson(array("name"=>"WebWorker"));
});
