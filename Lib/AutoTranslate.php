<?php
/**
 * 自动翻译多种语言
 * User: song(343547175@qq.com)
 * Date: 2018-04-16
 * Time: 16:06
 */

namespace Rainbow\Lib;
use Exception;

define("CURL_TIMEOUT",   10);
define("URL",            "http://api.fanyi.baidu.com/api/trans/vip/translate");
define("APP_ID",         "APPID"); //替换为您的APPID
define("SEC_KEY",        "KEY");//替换为您的密钥
/*
 * 支持源语言语种
zh	中文
en	英语
yue	粤语
wyw	文言文
jp	日语
kor	韩语
fra	法语
spa	西班牙语
th	泰语
ara	阿拉伯语
ru	俄语
pt	葡萄牙语
de	德语
it	意大利语
el	希腊语
nl	荷兰语
pl	波兰语
bul	保加利亚语
est	爱沙尼亚语
dan	丹麦语
fin	芬兰语
cs	捷克语
rom	罗马尼亚语
slo	斯洛文尼亚语
swe	瑞典语
hu	匈牙利语
cht	繁体中文
vie	越南语
*/
class AutoTranslate {
    //翻译入口
    public static function translate($query, $from, $to)
    {
        $args = array(
            'q' => $query,
            'appid' => APP_ID,
            'salt' => rand(10000,99999),
            'from' => $from,
            'to' => $to,

        );
        $args['sign'] = self::buildSign($query, APP_ID, $args['salt'], SEC_KEY);
        $ret = self::call(URL, $args);
        $ret = json_decode($ret, true);
        if(isset($ret['error_code']) && $ret['error_code'] != 52000)
            throw new Exception(self::erronMsg($ret['error_code']));
        return $ret['trans_result'][0]['dst'];
    }

    //加密
    public static function buildSign($query, $appID, $salt, $secKey)
    {
        $str = $appID . $query . $salt . $secKey;
        $ret = md5($str);
        return $ret;
    }

    //发起网络请求
    public static function call($url, $args=null, $method="post", $testflag = 0, $timeout = CURL_TIMEOUT, $headers=array())
    {
        $ret = false;
        $i = 0;
        while($ret === false)
        {
            if($i > 1)
                break;
            if($i > 0)
            {
                sleep(1);
            }
            $ret = self::callOnce($url, $args, $method, false, $timeout, $headers);
            $i++;
        }
        return $ret;
    }

    public static function callOnce($url, $args=null, $method="post", $withCookie = false, $timeout = CURL_TIMEOUT, $headers=array())
    {
        try {
            $ch = curl_init();
            if ($method == "post") {
                $data = self::convert($args);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_POST, 1);
            } else {
                $data = self::convert($args);
                if ($data) {
                    if (stripos($url, "?") > 0) {
                        $url .= "&$data";
                    } else {
                        $url .= "?$data";
                    }
                }
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
            if ($withCookie) {
                curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);
            }
            $r = curl_exec($ch);
            if($r){
                curl_close($ch);
                return $r;
            }else{
                $error = curl_errno($ch);//错误代号
                curl_close($ch);
                throw new Exception('网络错误(errno:'.$error.')');
            }
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    public static function convert(&$args)
    {
        $data = '';
        if (is_array($args))
        {
            foreach ($args as $key=>$val)
            {
                if (is_array($val))
                {
                    foreach ($val as $k=>$v)
                    {
                        $data .= $key.'['.$k.']='.rawurlencode($v).'&';
                    }
                }
                else
                {
                    $data .="$key=".rawurlencode($val)."&";
                }
            }
            return trim($data, "&");
        }
        return $args;
    }

    private static function erronMsg($code){
        switch($code){
            case 52000:
                $msg = '成功';
                break;
            case 52001:
                $msg = '请求超时';
                break;
            case 52002:
                $msg = '系统错误';
                break;
            case 52003:
                $msg = '未授权用户';
                break;
            case 54000:
                $msg = '必填参数为空';
                break;
            case 54001:
                $msg = '签名错误';
                break;
            case 54003:
                $msg = '访问频率受限';
                break;
            case 54004:
                $msg = '账户余额不足';
                break;
            case 54005:
                $msg = '长query请求频繁';
                break;
            case 58000:
                $msg = '客户端IP非法';
                break;
            case 58001:
                $msg = '译文语言方向不支持';
                break;
        }
        return $msg;
    }
}
