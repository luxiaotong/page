<?php
class PartnerModel{

    private $redis;
    private $partners;

    public function __construct(){
        $this->_getRedis();
    }

    private function _getRedis(){
        //初使化redis配置
        $config = new Yaf_Config_Ini(APP_PATH . "/conf/source.ini", "redis");
        $this->redis = new DB_Redis($config);
    }

    /**
     * 获取指定业务码的配置，如果业务码为空，表示获取所有的业务码
     *
     * @params string $no   获取指定业务码的配置，如果为空表示获取所有的配置
     */
    public function getPartners($no = ''){
        if(empty($this->partners) && $this->redis){
            $raw = $this->redis->hgetall("page_partners");
            foreach((array)$raw as $key => $val){
                if(!empty($val))
                    $this->partners[$key] = json_decode($val, true);
            }
        }
        if(empty($no))
            return $this->partners;
        else
            return empty($this->partners[$no]) ? array() : $this->partners[$no];
    }

    public function setPartner($no, $config){
        if(!empty($no) && $this->redis){
            $this->partners = array();
            return $this->redis->hset("page_partners" , $no, json_encode($config));
        }else return false;
    }
    public function delPartner($no){
        if(!empty($no)){
            $this->partners = array();
            return $this->redis->hdel("page_partners", $no);
        }else 
            return false;
    }
}
