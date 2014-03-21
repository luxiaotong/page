<?php
class PartnerModel{

    private $redis;

    public function __construct(){
        $this->_getRedis();
    }

    private function _getRedis(){
        //初使化redis配置
        $config = new Yaf_Config_Ini(APP_PATH . "/conf/source.ini", "product");
        $this->redis = new DB_Redis($config->redis);
    }

    /**
     * 获取指定业务码的配置，如果业务码为空，表示获取所有的业务码
     *
     * @params string $no   获取指定业务码的配置，如果为空表示获取所有的配置
     */
    public function getPartners($no = ''){
        static $partners = array();
        if(empty($partners) && $this->redis){
            $raw = $this->redis->hgetall("page_partners");
            foreach((array)$raw as $key => $val){
                if(!empty($val))
                    $partners[$key] = json_decode($val, true);
            }
        }
        if(empty($no))
            return $partners;
        else
            return empty($partners[$no]) ? array() : $partners[$no];
    }

    public function setPartners($no, $config){
        if(!empty($no) && $this->redis)
            return $this->redis->hset("page_partners" , $no, json_encode($config));
        else return false;
    }
}
