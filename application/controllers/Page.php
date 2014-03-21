<?php
class PageController extends Yaf_Controller_Abstract{

    //默认的common.php文件目录
    private static $defaultFilePath = "/data1/sinawap/code/weibov4_wap/data/product/page/common.php";

    public function init(){
        $assignParams = array(
          //  "controllerName" => "page",
        );
        $this->getView()->assign($assignParams);
    }

    public function indexAction(){
        $assignParams = array(
            "title" => "Page合作方配置",
        );
        //用来在模板里判断高亮显示哪个栏
        $this->getView()->assign($assignParams);
    }

    /**
     * 根据配置文件/conf/pageconfig.ini导入到redis数据库
     */
    public function easyResetAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        $config = new Yaf_Config_Ini(APP_PATH . "/conf/pageconfig.ini", 'product');
        $partners = $config->partners->toArray();
        $pm = new PartnerModel();
        $succ = 0;
        foreach($partners as $k => $v){
            if($pm->setPartners($k, $v))
                $succ++;
        }
        $msg = "共找到" . count($partners) . " 个合作方，添加 $succ 条, 更新 " . (count($partners) - $succ) . " 条";
        $this->getView()->assign(array("msg_level" => 0, "msg" => $msg));
        $this->getView()->display("pub/msg.phtml");
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
                $error .= $this->_getPagePartner($contents);
            }else{
                $error .= "不存在的文件：$filePath";
            }
        }
        $this->getView()->assign("error", $error);
    }

    private function _getPagePartner($contents){
        if(empty($contents)){
            return array();
        }
        $patern = '/&&\s*\$partners\s*=\s*array\s*\(\s*[^.]+\)\s*;/';
        $patern = '/&&\s*\$partners\s*=\s*(array\s*\(\s*[^;]+\))\s*;/';
        preg_match($patern, $contents, $match);
        $t = $this->parseArray($match[1]);
        var_dump($t);
        die;
    }
    private static function parseArray(&$str){
        $result = array();
        $str = trim($str);
        while($str){
            //'a' => ....首先看看有没有键值
            $partnerKey = '/^\s*([\'"])([^,]*)\1\s*=>\s*?/U';
            $partnerValArray = '/^\s*array\(/';
            $partnerValStr = '/^\s*([\'"])(.*)\1,??/U';
            if(preg_match($partnerKey, $str, $match)){
                $str = substr($str, strlen($match[0]));
                $t = self::parseArray($str);
                $result[$match[2]] = $t[0];
                var_dump($match, $result, $str, "DD");die;
            }else if(preg_match($partnerValArray, $str, $match)){
                $str = substr($str, strlen($match[0]));
                $result[] = self::parseArray($str);
                $str = ltrim($str, " ),");
            }else if(preg_match($partnerValStr, $str, $match)){
                $str = substr($str, strlen($match[0]));
                $result[] = $match[2];
            }else
                break;
        }
        return $result;
    }
}
