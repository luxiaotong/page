<?php
class PageController extends Yaf_Controller_Abstract{
    public function indexAction(){
        //用来在模板里判断高亮显示哪个栏
		$this->getView()->assign("page_type", "page_partner");

        //初使化redis配置
        $config = new Yaf_Config_Ini(APP_PATH . "/conf/source.ini", "product");
        $redis = new DB_Redis($config->redis);
        //var_dump($r->set("a", "BBBB"), $config->redis);die;

    }
}
