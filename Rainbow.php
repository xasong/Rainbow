<?php
/**
 * 自动生成语言包
 * (目前UTF-8文件测试通过)
 * User: song(343547175@qq.com)
 * Date: 2018-04-16
 * Time: 16:01
 */

namespace Rainbow;

use \Rainbow\Exception\RainbowException;
use \Rainbow\Lib\MatchChinese;
use \Rainbow\Lib\AutoTranslate;
use \Rainbow\Lib\ChineseToPy;
use \Rainbow\Autoloader;

class Rainbow {
    const VERSION = '1.0.0';
    /**
     * 读取的目录
     * @var string
     */
    public $filedir;
    /**
     * 语言包文件存放路径
     * @var string
     */
    public $langpackdir;
    /**
     * 语言包文件名
     * @var string
     */
    public $langpackfilename = 'zh';
    /*
     * 语言包文件后缀
     * */
    public $extension;
    /*
     * 默认生成的语言
     * */
    public $langArr = ['en'];

    public function __construct($dir,$langpackdir)
    {
        if(!is_dir($dir)) exit("The read file directory does not exist\n");
        if(!is_dir($langpackdir)) exit("The language package generated directory does not exist\n");
        $this->filedir = $dir;
        $this->langpackdir = $langpackdir;
    }

    /*
     * 目录读取
     * @return string //返回目录中所有文件
     * */
    public function scanDir($filedir){
        $files = array();
        if(@$handle = opendir($filedir)) { //注意这里要加一个@，不然会有warning错误提示：）
            while(($file = readdir($handle)) !== false) {
                if($file != ".." && $file != ".") { //排除根目录；
                    if(is_dir($filedir."/".$file)) { //如果是子文件夹，就进行递归
                        $files[$file] = $this->scanDir($filedir."/".$file);
                    } else { //不然就将文件的名字存入数组；
                        $files[] = $filedir."/".$file;
                    }

                }
            }
            closedir($handle);
            return $files;
        }
        return $files;
    }

    /*
    *执行
    *@return bool
    * */
    public function run(){
        $chineseArr = [];
        try {
            $files = $this->scanDir($this->filedir);
            foreach ($files as $file) {
                if (is_array($file)) {
                    foreach ($file as $v) {
                        $data = $this->loadFile($v);
                        $chinese = MatchChinese::pregMatch($data);
                        if (empty($chinese)) continue;
                        $chineseArr = array_merge($chineseArr, $chinese);
                        MatchChinese::contentReplaceTowrite($file, $data, $chinese);
                    }
                } else {
                    if (!is_file($file)) continue;
                    $data = $this->loadFile($file);
                    $chinese = MatchChinese::pregMatch($data);
                    if (empty($chinese)) continue;
                    $chineseArr = array_merge($chineseArr, $chinese);
                    MatchChinese::contentReplaceTowrite($file, $data, $chinese);
                }
            }
            $chineseArr = array_flip($chineseArr);
            $chineseArr = array_keys($chineseArr);
            $chinese = [];
            foreach ($chineseArr as $key => $v) {
                $chinese[ChineseToPy::encodePY($v, 'all')] = $v;
                unset($chineseArr[$key]);
            }
            $this->CreateLangPack($chinese, $this->extension);
            if (!empty($this->langArr)) {
                foreach ($this->langArr as $lang) {
                    $chinese_new = [];
                    $this->langpackfilename = $lang;
                    /*if(count($chinese) >2000)
                    {
                        $chinese_tmp = array_slice($chinese, 2000);
                    }else{
                        $translate_str = implode('\n',$chinese);
                        $translate = AutoTranslate::translate($translate_str, 'zh', $lang);
                        print_r($translate);
                    }*/
                    foreach ($chinese as $key => $v) {
                        $chinese_new[$key] = AutoTranslate::translate($v, 'zh', $lang);
                    }
                    $this->CreateLangPack($chinese_new, $this->extension);
                }
            }
        } catch (RainbowException $e) {
            return $e->getMessage();
        }
        return true;
    }

    /*
     * 读取文件
     * @param string $sFilename //文件路径
     * @return string //返回文件内容
    */
    public function loadFile($sFilename, $sCharset = 'UTF-8')
    {
        if (floatval(phpversion()) >= 4.3) {
            $sData = file_get_contents($sFilename);
        } else {
            if (!file_exists($sFilename)) return -3;
            $rHandle = fopen($sFilename, 'r');
            if (!$rHandle) return -2;

            $sData = '';
            while(!feof($rHandle))
                $sData .= fread($rHandle, filesize($sFilename));
            fclose($rHandle);
        }
        /*if ($sEncoding = mb_detect_encoding($sData, 'auto', true) != $sCharset) {
            $sData = mb_convert_encoding($sData, $sCharset,"UTF-8");
        }*/
        $path_parts = pathinfo($sFilename);
        if($path_parts['extension'] == 'html'){
            $sData = strip_tags($sData);
            if(empty($this->extension)) $this->extension = 'php';
        }else if($path_parts['extension'] == 'js'){
            if(empty($this->extension)) $this->extension = 'js';
        }
        return $sData;
    }

    /*
     * 生成语言包
     * */
    private function CreateLangPack($data = [],$file_type = 'php'){
        $file_type = strtoupper($file_type);
        switch($file_type){
            case 'PHP':
                $langpackfilename = $this->langpackfilename.'.php';
                $info = "<?php return " . var_export($data, true) . "; ?>";
                break;
            case 'JS':
                $langpackfilename = $this->langpackfilename.'.js';
                $info = '';
                foreach($data as $key => $v){
                    $info .= " var $key='$v';";
                    unset($data[$key]);
                }
                break;
        }
        unset($data);
        if(!file_put_contents($this->langpackdir.$langpackfilename,$info)){
            return false;
        };
        return true;
    }
}