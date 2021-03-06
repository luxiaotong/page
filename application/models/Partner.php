<?php
class PartnerModel{

    //redis链接，会在构造的时候初始化
    private $redis;

    //从redis读出来的配置
    private $pageconfigFromRedis;

    //从配置文件里读出来的配置
    private $pageconfigFromIni;

    //pageconfig的ini文件目录
    private $pageconfigInifile;

    //redis的配置节名称
    private $redisConfig = "redis";

    //存储在redis里的所有keys值枚举
    private static $configKeys = array(
        "partners",
        "pageTypes",
        "objectTypes",
        "pageDomains",
        "longUrls",
        "urlTypeInfos",
        "sdkTypeInfos"
    );

    //page字段描述说明的配置文件地址
    private $pageconfigDescInifile;

    //page字段描述的说明配置
    private $pageconfigDesc;

    public function __construct($redis = '', $ini = '', $descini = ''){
        if(!empty($redis)){
            $this->redisConfig = $redis;
        }
        if(!empty($ini)){
            $this->pageconfigInifile = $ini;
        }else{
            $this->pageconfigInifile = "/data1/sinawap/resource/rsync/config/ttt_front/v5_conf/pageconfig.ini";
        }
        if(!empty($descini)){
            $this->pageconfigInifile = $descini;
        }else{
            $this->pageconfigDescInifile = APP_PATH . "/conf/page/pageconfigdesc.ini";
        }
        $this->_getRedis();
    }

    /**
     * 获取redis，在最开始的时候调用
     */
    private function _getRedis(){
        //初使化redis配置
        $config = new Yaf_Config_Ini(APP_PATH . "/conf/source.ini", $this->redisConfig);
        $this->redis = new DB_Redis($config);
    }

    //是否已经从redis获取出所有的数据了
    private function _hasGotAll(){
        foreach(self::$configKeys as $key){
            if(empty($this->pageconfigFromRedis[$key]))
                return false;
        }
        return true;
    }

    //将redis的数据都取出来放入类变量
    private function _getAll(){
        foreach(self::$configKeys as $key){
            if(!empty($this->pageconfigFromRedis[$key])){
                continue;
            }
            $t = $this->redis->hgetall($key);
            foreach($t as $k => $v){
                $this->pageconfigFromRedis[$key][$k] = json_decode($v, true);
            }
        }
    }

    //校验获取的配置的键名是否合法
    private function _checkKey($name){
        return empty($name) || in_array($name, self::$configKeys);
    }

    /**
     * 获取所有的配置，如果$name为空的话获取所有的配置
     */
    public function getPageconfigRedis($name = '', $subkey = NULL){
        if(empty($name)){
            if(!$this->_hasGotAll()){
                $this->_getAll();
            }
            return $this->pageconfigFromRedis;
        }
        if(!$this->_checkKey($name)){
            return array();
        }
        if(empty($this->pageconfigFromRedis[$name])){
            $raw = $this->redis->hgetall($name);
            foreach((array)$raw as $key => $val){
                $this->pageconfigFromRedis[$name][$key] = json_decode($val, true);
            }
        }
        if(is_null($subkey)){
            return $this->pageconfigFromRedis[$name];
        }else if(!empty($this->pageconfigFromRedis[$name][$subkey])){
            return $this->pageconfigFromRedis[$name][$subkey];
        }else{
            return array();
        }
    }

    //获取可以上线的所有配置
    public function getPublishPageconfig(){
        $pageconfig = $this->getPageconfigRedis();
        foreach($pageconfig['partners'] as $partnerid => $c){
            if(!empty($c['offline'])){
                unset($pageconfig['partners'][$partnerid]);
            }
        }
        return $pageconfig;
    }


    /**
     * 获取支持的配置键名
     */
    public function getConfigKeys(){
        return self::$configKeys;
    }

    /**
     * 由于对配置的操作都是对redis的操作
     * 修改ini文件只有一种方式，即生成文件
     * 所以此处的函数名没有带上跟redis相关的字眼
     * 但操作的都是redis
     */
    public function setPageconfig($name, $subkey, $config){
        if(!empty($subkey) && !empty($name) && $this->_checkKey($name)){
            $this->pageconfigFromRedis[$name] = array();
            return $this->redis->hset($name, $subkey, json_encode($config));
        }else return false;
    }
    /**
     * 删除配置，这个函数要小心校验$subkey的值
     * 如果为空，则将整个配置删除
     */
    public function delPageconfig($name, $subkey = ""){
        if(!empty($name) && $this->_checkKey($name)){
            $this->pageconfigFromRedis[$name] = array();
            if(empty($subkey)){
                return $this->redis->del($name);
            }else{
                return $this->redis->hdel($name, $subkey);
            }
        }else{
            return false;
        }
    }

    /**
     * 使用配置文件$config重置redis的数据
     */
    public function resetRedis($config){
        $this->pageconfigFromRedis = array();
        foreach($config as $k => $v){
            if(!$this->_checkKey($k)){
                continue;
            }
            foreach($v as $kk => $vv){
               $r =  $this->redis->hset($k, $kk, json_encode($vv));
            }
        }
    }

    /**
     * 使用ini文件的内容来重置redis的数据
     */
    public function resetRedisFromIni(){
        $this->resetRedis($this->getPageconfigIni());
    }

    /**
     * 从配置文件中一次性读取所有数据出来
     */
    public function getPageconfigIni($name = '', $subkey = NULL){
        if(empty($this->pageconfigFromIni)){
            if(file_exists($this->pageconfigInifile)){
                $c = new Yaf_Config_Ini($this->pageconfigInifile);
                $this->pageconfigFromIni = $c->toArray();
            }else{
                $this->pageconfigFromIni = array();
            }
        }
        if(empty($name))
            return $this->pageconfigFromIni;
        if(is_null($subkey))
            return $this->pageconfigFromIni[$name];
        else return $this->pageconfigFromIni[$name][$subkey];
    }

    /**
     * 获取对于page配置里的每一个字段的说明
     *
     * @param string $nodeName 节点名字
     */
    public function getPageconfigDesc($nodeName = ''){
        if(!$this->_checkKey($nodeName)){
            return array();
        }
        if(empty($this->pageconfigDesc)){
            $c = new Yaf_Config_Ini($this->pageconfigDescInifile);
            foreach(self::$configKeys as $key){
                if(empty($c[$key])){
                    continue;
                }
                $this->pageconfigDesc[$key] = Tool_Func::array2ini($c[$key]->toArray(), "-");
            }
        }
        if(empty($nodeName))
            return $this->pageconfigDesc;
        else if(!empty($this->pageconfigDesc[$nodeName])){
            return $this->pageconfigDesc[$nodeName];
        }else{
            return array();
        }
    }
    public function writeToini($config){
        //写入v4可以读取的配置文件,v4写入json
        $v4_config = self::partnermap($config);
        Tool_Log::writeToFile(json_encode($v4_config), "/data1/sinawap/resource/rsync/config/ttt_front/pageconfig.txt");
        /*
        $v4_config = self::partnermap($config);
        $content = "[product]\n";
        $v4_config = Tool_Func::array2ini($v4_config);
        foreach($v4_config as $k => $v){
            $content .= "$k = \"$v\"\n";
        }
        Tool_Log::writeToFile($content, "/data1/sinawap/resource/rsync/config/ttt_front/pageconfig.txt");
         */

        //写入v5的配置文件
        $content = "";
        $config = Tool_Func::array2ini($config);
        foreach($config as $k => $v){
            $content .= "$k = \"$v\"\n";
        }
        return Tool_Log::writeToFile($content, $this->pageconfigInifile);
    }
    static function partnermap($ini){
        static $maps = array(
            "Data_Product_Page_Weibo::" => "data_product_page_weibo::",
            "Data_Product_Page_Pub::" => "data_product_page_pub::",
            "Data_Product_Page_Interest::" => "data_product_page_interest::",
            "Data_Product_Page_Photo::" => "data_product_page_photo::",
            "Data_Product_Page_Topic_Event::" => "data_product_page_topicEvent::",
            "Data_Product_Page_Topic::" => "data_product_page_topic::",
            "Data_Product_Page_Event::" => "data_product_page_event::",
            "Data_Product_Page_Group::" => "data_product_page_group::",
            "Data_Weibo_Recommend::" => "weibo_recommend::",
            "Data_Product_Page_Ext::" => "data_product_page_ext::",
            "Data_Product_Page_Friends::" => "data_product_page_friends::",
            "Data_Product_Page_Profile::" => "data_product_page_profile::",
            "Data_Product_Page_Review::" => "data_product_page_review::",
            "Data_Product_Page_Tool::" => "data_product_page_tool::",
            "Data_Product_Page_Object::" => "data_product_page_object::",
            "Data_Product_Page_Topict::" => "data_product_page_topicT::",
            "Data_Product_Page_Topic_Night::" => "data_product_page_topic_night::",
            "Data_Product_Page_Profile_Cardlist::" => "data_product_page_profile_cardlist::",
            "Data_Product_Page_Findfriends::" => "data_product_page_findFriends::",
            "Data_Product_Page_AdVideo::" => "data_product_page_adVideo::",
            "Data_Product_Page_Vote::" => "data_product_page_vote::",
            "Data_Product_Page_Like::" => "data_product_page_like::",
            "Data_Product_Page_Favorites::" => "data_product_page_favorites::",
            "Page_Tool_String::replace" => "data_product_page_tool::strReplace",
        );
        static $search, $replace;
        empty($search) && $search = array_keys($maps);
        empty($replace) &&  $replace = array_values($maps);
        foreach($ini as $k => $each_ini){
            if(is_string($each_ini)){
                $ini[$k] = str_replace($search, $replace, $each_ini);
            }else{
                $ini[$k] = self::partnermap($each_ini);
            }
        }
        return $ini;
    }
}
