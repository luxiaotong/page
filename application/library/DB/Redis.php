<?php

/**
 * 封装的操作redis的类，支持主服务器和从服务器
 * 支持选择数据库
 * 如果配置了主库和从库，那么默认都从主库操作，不管读写
 * 可以调用函数来指定操作的主从库
 * 可以把host配置成一个数组，那么会随机从中抽出一台来操作
 * 目前还不支持权重
 *
 * $config 用来传redis服务器的参数
 * $config = array(
 *  "master" => array(
 *      "host" => "127.0.0.1",
 *      "port" => "6379",
 *      "db" => 1,
 *  ),
 *  "slave" => array(
 *      "host" => "127.0.0.1",
 *      "port" => "6380",
 *      "db" => 1,
 *  ),
 * )
 */

class DB_Redis{
    //redis的服务器配置
    private $config;

    //是操作主库还是从库
    private $queryType;

    //连接缓存
    private $links;

    //超时时间，默认为2s
    private $timeout = 2;

    public function __construct($config){
        if(!empty($config['master'])){
            $this->queryType = "master";
        }else{
            $this->queryType = "slave";
        }
        $this->config = $config;
    }

    //设置操作主库还是从库，如果与配置不符合，将返回false
    public function setQueryType($type){
        if(empty($this->config[$type])){
            return false;
        }
        $this->queryType = $type;
        return true;
    }

    //把这个函数抽象出来，为了以后改配置结构的时候可以更独立点
    private function _connect($single_config){
        if(is_array($single_config['host'])){
            $host = $single_config['host'][array_rand($single_config['host'])];
        }else {
            $host = $single_config['host'];
        }
        $port = empty($single_config['port']) ? 6379 : $single_config['port'];
        $timeout = empty($single_config['timeout']) ? $this->timeout : $single_config['timeout'];
        if(empty($single_config['host'])){
            ToolLog::log("Redis Config Error, Not Find Host", 2);
            return false;
        }
        $this->links[$this->queryType] = $link = new Redis();
        $succ = $link->connect($host, $port, $timeout);
        if(!$succ){
            ToolLog::log("Redis Connect Failed", 2);
            return false;
        }
        if($single_config['db']){
            $link->select($single_config['db']);
        }
        return true;
    }

    private function _getLink(){
        if(empty($this->links[$this->queryType])){
            if(empty($this->config[$this->queryType])){
                ToolLog::log("Redis Config Error, Not Find QueryType " . $this->queryType, 2);
                return false;
            }
            $succ = self::_connect($this->config[$this->queryType]);
            if(!$succ)
                return false;
        }
        return $this->links[$this->queryType];
    }

    //所有实质性的操作函数都会调用到这里来
    public function __call($method, $params){
        //不允许操作的命令
        $denyMethod = array('setoption', 'bgrewriteaof', 'bgsave', 'flushdb', 'flushall');
        if(in_array(strtolower($method), $denyMethod)){
            ToolLog::log("Redis Deny Operator $method!");
            return false;
        }
        $link = self::_getLink();
        if($link){
            return call_user_func_array(array($link, $method), $params);
        }else return false;
    }
}
