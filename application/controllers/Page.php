<?php
class PageController extends Yaf_Controller_Abstract{

    //默认的common.php文件目录
    private static $defaultFilePath = "/data1/sinawap/code/weibov4_wap/data/product/page/common.php";
    //page处理的model层类
    private $pm;
    //合作方可配置的字段说明
    private static $mapPartnerInfo;

    private static $iniFile;

    //所有控制器之前要完成的行为
    public function init(){
        self::$iniFile = APP_PATH . "/conf/page/pageconfig.ini";
        $this->pm = new PartnerModel();
        self::$mapPartnerInfo = array();

        $mpi = new Yaf_Config_Ini(self::$iniFile);
        foreach($mpi as $k => $v){
        }
        $this->mappartnerinfo = $mpi->toArray();

        $assignParams = array(
          //  "controllerName" => "page",
        );
        $this->getView()->assign($assignParams);
    }

    //配置合作方信息
    public function indexAction(){
        $assignParams = array(
            "title" => "Page合作方配置",
        );
        $partners = $this->pm->getPartners();
        $partnerid = Tool_Request::getStr("partnerid");
        $assignParams['partnerid'] = $partnerid;

        if(empty($partners)){
            //当没有一个合作方信息时，引导去导入数据
            Yaf_Dispatcher::getInstance()->disableView();
            $msg = "没有合作方信息，请先<a href='/page/easyreset'>导入</a>";
            $assignParams['box_level'] = 3;
            $assignParams['box_msg'] = $msg;
            $this->getView()->assign($assignParams);
            $this->getView()->display("pub/msg.phtml");
            return ;
        }else if($partnerid && Tool_Request::hasPost() && Tool_Request::getStr("action") == "del"){
            //删除操作
            $t = $this->pm->delPartner($partnerid);
            Yaf_Dispatcher::getInstance()->disableView();
            $msg = "成功删除业务码{$partnerid}";
            $assignParams['box_msg'] = $msg;
            $assignParams['box_url'] = '/page/';
            $this->getView()->assign($assignParams);
            $this->getView()->display("pub/msg.phtml");
        }else if(Tool_Request::hasPost()){
            //提交对合作方的修改或者是新建合作方
            $config = Tool_Request::getPost();
            if(isset($config['partnerid']))
                unset($config['partnerid']);
            if(empty($partnerid) || empty($config)){
                //当输入错误的业务码时
                $assignParams['box_msg'] = "缺少关键参数, 提交失败";
                $assignParams['box_url'] = "/page/";
                $assignParams['box_level'] = 3;
            }else {
                $r = $this->pm->setPartner($partnerid, Tool_Func::ini2array($config, "-"));
                if($r == 1){
                    $assignParams['box_msg'] = "新建记录成功";
                    $assignParams['box_url'] = "/page/index?partnerid={$partnerid}";
                }else{
                    $assignParams['box_msg'] = "更新记录成功";
                    $assignParams['box_url'] = "/page/index?partnerid={$partnerid}";
                }
            }
            Yaf_Dispatcher::getInstance()->disableView();
            $this->getView()->assign($assignParams);
            $this->getView()->display("pub/msg.phtml");
        }else if(empty($partnerid)){
            //如果用户没在输入相应的业务码，即刚进来，没有带任何信息
            $assignParams['box_msg'] = "请在左侧选择你要修改的业务号信息，请不要修改不属于你管理的业务号，我们会对每一次修改记录<br/>或者在下面添加一个新的合作方!";
        }else if(empty($partners[$partnerid])){
            //当输入错误的业务码时
            $assignParams['box_msg'] = "您输入的业务码({$partnerid})不存在，请核实";
            $assignParams['box_level'] = 3;
            $assignParams['box_url'] = '/page/';
            Yaf_Dispatcher::getInstance()->disableView();
            $this->getView()->assign($assignParams);
            $this->getView()->display("pub/msg.phtml");
        } else {
            //修改页面
            $partnerInfo = $partners[$partnerid];
            $assignParams['pi'] = Tool_Func::array2ini($partnerInfo, "-");

        }
        if(empty($assignParams['pi'])){
            $assignParams['pi'] = array();
        }
        $assignParams['mpi'] = Tool_Func::array2ini($this->mappartnerinfo, "-");
        //由于每一个字段有两个值，所以接下来要合并这两个值，得到mpi_filter数组
        //并且去掉这个业务码已经有field，得到mpi_can_add数组
        foreach($assignParams['mpi'] as $k => $v){
            $field = substr($k, 0, -5);
            $assignParams['mpi_filter'][$field] = true;
            if(empty($assignParams['pi'][$field])){
                $assignParams['mpi_can_add'][$field] = true;
            }
        }
        foreach($assignParams['pi'] as $k => $v){
            //算出他的hosts来
            $assignParams['hosts'][$k] = Tool_Host::getHostByUrl($assignParams['pi'][$k]);
        }

        $assignParams['partners'] = $partners;
        //用来在模板里判断高亮显示哪个栏
        $this->getView()->assign($assignParams);
    }

    /**
     * 根据配置文件/conf/pageconfig.ini导入到redis数据库
     */
    public function easyResetAction(){
        $this->getView()->assign("title", "使用配置文件初使化redis");
        $partners = $this->pm->getPartners();
        if(Tool_Request::hasPost() || empty($partners)){
            Yaf_Dispatcher::getInstance()->disableView();
            $config = new Yaf_Config_Ini(self::$iniFile, 'product');

            $this->pm->resetRedis($config->toArray());

            $msg = "导入完成";
            $this->getView()->assign(array("box_level" => 0, "box_msg" => $msg));
            $this->getView()->display("pub/msg.phtml");
        }
    }
    /**
     * 比较redis里的数据和配置文件里的数据的不一样的地方
     */
    public function compareAction(){
            $config = new Yaf_Config_Ini(self::$iniFile, 'product');
    }






    /**
     * 根据common.php导入数据
     *
     * @todo 还没完成
     */
    public function resetRedisAction(){
        //用来显示错误信息的变量
        $error = "";

        $partners = $this->pm->getPartners();
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
        $this->getView()->assign(array("error" => $error, "title" => "初使化redis数据"));
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
