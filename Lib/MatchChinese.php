<?php
/**
 * 获取文件中的中文
 * User: song(343547175@qq.com)
 * Date: 2018-04-16
 * Time: 16:10
 */

namespace Rainbow\Lib;
use Rainbow\Lib\ChineseToPy;

class MatchChinese {
    //过滤规则
    public static $preg_match = "/[\x{4e00}-\x{9fa5}]+/u";
    /*
     * 正则匹配
     * @param string $content //字符串
     * @return array //返回字符串中的中文数组
    * */
    public static function pregMatch($content){
        $chinese = [];
        if(empty($content)) return [];
        //去除注释
        $content = preg_replace("@/\*.*?\*/@s", '', str_replace(array("\r\n", "\r"), "\n", $content));
        $content = preg_replace('@\s*//.*$@m','',$content);
        $content = preg_replace('#<!--.*-->#','',$content);
        if (preg_match_all(self::$preg_match,$content,$match))
            $chinese = $match[0];
        return $chinese;
    }

    /*
     *文件中对应语言包替换
     * */
    public static function contentReplaceTowrite($file,$string,$search,$replace = [])
    {
        foreach ($search as $key => $v) {
            $search[$key] = "'$v'";
            $search_1[] = '"'.$v.'"';
            $replace[] = ChineseToPy::encodePY($v, 'all');
        }
        $content = str_replace($search,$replace,$string);
        $content = str_replace($search_1,$replace,$content);
        $f = fopen($file,'wb');
        fwrite($f,$content,strlen($content));
        fclose($f);
    }
}