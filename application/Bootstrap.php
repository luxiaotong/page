<?php

/**
 *  * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 *   * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 *    * 调用的次序, 和申明的次序相同
 *     */
class Bootstrap extends Yaf_Bootstrap_Abstract{

    public function _initConfig($dispatcher) {
        $assignParams = array(
            "navs" => array(
                "page" => "PAGE",
                "machine" => "测试机管理",
                "hithere" => "hi there",
            ),
            "pageNavs" => array(
                "index" => "合作方配置",
                "easyreset" => "简单初使化redis",
                "resetredis" => "重新填写redis数据",
            ),
        );
        $dispatcher->initView(APP_PATH . "/application/views")->assign($assignParams);
    }

    public function _initPlugin($dispatcher){
        $dispatcher->registerPlugin(new AssignPlugin());
    }
}
