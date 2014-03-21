<?php
/**
 * 处理所有表单的提交的类
 *
 * 原则上，所以网页提交的参数都要求不带标签，除非你有明确的目的
 * 请根据变量的用途使用明确的返回值类型
 */
class Tool_Request{

    /**
     * 获取对应的请求参数，原始的，未经过处理的
     * 请不要使用这个函数，除非你有明确的目的
     *
     * @param string $rname     获取的参数名
     * @param string $type      获取参数的类型，可以是空，get，post，cookie这些值
     *
     * @return string           传到服务器的原始值
     */
    public static function getRaw($rname, $type = ''){
        switch($type){
        case '':
            return isset($_REQUEST[$rname]) ? $_REQUEST[$rname] : NULL;
        case 'post':
            return isset($_POST[$rname]) ? $_POST[$rname] : NULL;
        case 'get':
            return isset($_GET[$rname]) ? $_GET[$rname] : NULL;
        case 'cookie':
            return isset($_COOKIE[$rname]) ? $_COOKIE[$rname] : NULL;
        default:
            return isset($_REQUEST[$rname]) ? $_REQUEST[$rname] : NULL;
        }
    }

    /**
     * 获取整型值
     *
     * @param string $rname     获取的参数名
     * @param string $type      获取参数的类型，可以是空，get，post，cookie这些值
     *
     * @return int              如果参数里有非数字的字符，则返回0,其它返回整型值
     */
    public static function getInt($rname, $type = ''){
        static $cache = array();
        //缓存的key值
        $key = $rname . "_" . $type;
        if(!isset($cache[$key])){
            $raw = self::getRaw($rname, $type);
            if(is_numeric($raw)){
                $cache[$key] = intval($raw);
            }else
                $cache[$key] = 0;
        }
        return $cache[$key];
    }

    /**
     * 获取bool值
     *
     * @param string $rname     获取的参数名
     * @param string $type      获取参数的类型，可以是空，get，post，cookie这些值
     *
     * @return bool
     */
    public static function getBool($rname, $type = ''){
        static $cache = array();
        //缓存的key值
        $key = $rname . "_" . $type;
        if(!isset($cache[$key])){
            $raw = self::getRaw($rname, $type);
            $cache[$key] = boolval($raw);
        }
        return $cache[$key];
    }

    /**
     * 获取字符串值
     * 与getRaw不一样的是会对字符串做addslashes和strip_tags处理
     *
     * @param string $rname     获取的参数名
     * @param string $type      获取参数的类型，可以是空，get，post，cookie这些值
     *
     * @return string           返回处理过的字符串
     */
    public static function getStr($rname, $type = ''){
        static $cache = array();
        //缓存的key值
        $key = $rname . "_" . $type;
        if(!isset($cache[$key])){
            $raw = self::getRaw($rname, $type);
            $cache[$key] = empty($raw) ? $raw : trim(addslashes(strip_tags($raw)));
        }
        return $cache[$key];
    }
    /**
     * 是否有post数据
     *
     * @return bool 返回是否是post请求
     */
    public static function hasPost(){
        return !empty($_POST);
    }
}
