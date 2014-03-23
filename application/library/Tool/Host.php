<?php
/**
 * 用于处理hosts文件的类
 */
class Tool_Host{
    //指定的hosts文件，可以直接修改
    static $hostsFile = "/etc/hosts";
    //用来记录所有的hosts
    private static $hosts = array();
    public static function getHosts($host = ""){
        if(empty(self::$hosts)){
            $fp = fopen(self::$hostsFile, "ro");
            if(!$fp){
                Tool_Log::log("Can't open " . self::$hosts, 2);
            }else{
                while(!feof($fp)){
                    $line = ltrim(fgets($fp));//去掉左边的空格
                    $sharpPos = strpos($line, "#");
                    if($sharpPos !== false){
                        $line = substr($line, 0,  $sharpPos);
                    }
                    $line = rtrim($line);
                    if(!empty($line)){
                        $t = preg_split("/\s+/", $line);
                        foreach($t as $k => $v){
                            if($k == 0 || !empty(self::$hosts[$v])) continue;
                            else self::$hosts[$v] = $t[0];
                        }
                    }
                }
            }
            self::$hosts['localhostxxx'] = "127.0.0.1";//为了防止这个数组为空
        }
        if(empty($host)){
            return self::$hosts;
        }else if(empty(self::$hosts[$host])){
            return false;
        }else 
            return self::$hosts[$host];
    }
    /**
     * 获取所给url 对应的hosts
     *
     * @retrun mixed 如果有对应的hosts返回ip,如果没有对应的返回false， 如果不是一个有效的链接，返回NULL
     */
    public static function getHostByUrl($url){
        $t = preg_match("/\s*https?:\/\/([^\/\#]+)/", $url, $match);
        if($t){
            return self::getHosts($match[1]);
        }else 
            return NULL;
    }
}
