<?php
class Tool_Log{
    //如果是记录在文件里，则logfile会是对应的文件名，如果为空。表示直接输出
    private static $logfile = "";
    //多少错误等级的错误需要记录
    private static $loglevel = 1;

    //加载日志记录的配置文件
    private static function _loadConfig(){
        //是否已经load过配置文件了
        static $hasload = false;
        if($hasload) return;

        $config = new Yaf_Config_Ini(APP_PATH . "/conf/log.ini", 'product');
        self::$logfile = $config->logfile;
        self::$loglevel = empty($config->loglevel) ? 1 : intval($config->loglevel);
    }

    /**
     * 记录日志，原样记录信息msg，并判断level值是否是需要记录的范围
     */
    public static function log($msg, $level = 1){
        self::_loadConfig();

        if($level < self::$loglevel) return;
        if(empty(self::$logfile)){
            echo "<pre>ERROR_REPORTING:LEVEL $level\n";
            echo $msg . "\n";
            print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            echo "</pre>";
        }else{
            $line = "[" . date("Y-m-d H:i:s") . "]" . $msg . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)) . "\n";
            self::logToFile($line, self::$logfile);
        }
    }

    //把line写入到file文件中
    public static function logToFile($line, $file){
        //有些服务器的权限有问题
        $oldmask = umask(0);
        self::makeDir(dirname($file));

        $fp = fopen($file, "ab");
        if($fp){
            fwrite($fp, $line);
            fclose($fp);
            umask($oldmask);
            return true;
        }else{
            umask($oldmask);
            return false;
        }
    }

    //创建目录函数
    public static function makeDir($dir){
        if(empty($dir)) return false;
        if(file_exists($dir))  return true;

        $oldmask = umask(0);
        $return = mkdir($dir, 0755, true);
        umask($oldmask);
        return $return;
    }
}
