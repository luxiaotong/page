<?php

/**
 *  * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 *   * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 *    * 调用的次序, 和申明的次序相同
 *     */
class Bootstrap extends Yaf_Bootstrap_Abstract{

    public function _initPlugin($dispatcher){
        $dispatcher->registerPlugin(new LoginPlugin());
        $dispatcher->registerPlugin(new AssignPlugin());
    }

    public function _initConfig($dispatcher) {
        $assignParams = array(
            "navs" => array(
                "page" => "PAGE",
//                "machine" => "测试机管理",
                "deploy" => "SVN部署",
                "tool" => "工具",
            ),
            "pageNavs" => array(
                "index" => "合作方配置",
                "reset" => "使用配置文件初使化redis",
                "diff" => "比较与发布",
        //        "resetredis" => "重新填写redis数据",
            ),
            "toolNavs" => array(
                "diff" => "两文件比较",
                "jsondiff" => "两个json比较",
            ),
        );
        $view = $dispatcher->initView(APP_PATH . "/application/views");
	$view->assign($assignParams);
	$view->assign('title', '微博后台');
    }
}
