<?php
class PageController extends Yaf_Controller_Abstract{

    //默认的common.php文件目录
    private static $defaultFilePath = "/data1/sinawap/code/weibov4_wap/data/product/page/common.php";

    public function indexAction(){
        //用来在模板里判断高亮显示哪个栏
        $this->getView()->assign("page_type", "page_partner");


    }
    public function resetRedisAction(){
        //用来显示错误信息的变量
        $error = "";

        $pm = new PartnerModel();
        $partners = $pm->getPartners();
        if(!empty($partners)){
            $error .= "发现你的redis关于合作方的数据并不为空，接下来的操作将导致你的redis数据被覆盖!<br/>";
        }
        if(Tool_Request::hasPost()){
            $filePath = rtrim(Tool_Request::getStr("compath", "post"), "/");
            if(empty($filePath)) $filePath= self::$defaultFilePath;
            if(file_exists($filePath)){
                $contents = file_get_contents($filePath);
                var_dump($contents);
                $r = $this->_getPagePartner($filePath);
            }else{
                $error .= "不存在的文件：$filePath";
            }
        }
        $this->getView()->assign("error", $error);
    }

    private function _getPagePartner($filePath){
    }
}
