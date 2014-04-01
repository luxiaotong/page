<?php
class PageController extends Yaf_Controller_Abstract{

    //page处理的model层类
    private $pm;

    //所有控制器之前要完成的行为
    public function init(){
        $this->pm = new PartnerModel();
    }

    /**
     * 对于需要配置合作方信息的页面都要进行这样处理，这包括编辑和添加
     */
    private function _assignPartner($partnerid = ''){
        $partnerDesc = $this->pm->getPageconfigDesc("partners");
        if(!empty($partnerid)){
            $partnerInfo = $this->pm->getPageconfigRedis("partners", $partnerid);
            //将转化成配置文件样式的扁平数组
            $partnerInfo = Tool_Func::array2ini($partnerInfo, "-");
        }else{
            $partnerInfo = array();
        }
        //获取可以添加的字段
        $fieldAvail = array();
        foreach($partnerDesc as $k => $v){
            $field = substr($k, 0, -5);
            if(empty($partnerInfo[$field])){
                $fieldAvail[$field] = true;
            }
        }
        //获取hosts数据
        $hosts = array();
        foreach($partnerInfo as $k => $v){
            //如果没有设置则返回false，如果这个值不是一个链接，则返回null
            $hosts[$k] = Tool_Host::getHostByUrl($v);
        }

        $this->getView()->assign(
            array(
                "partnerid" => $partnerid,
                "partner_info" => $partnerInfo,
                "partner_desc" => $partnerDesc,
                "hosts" => $hosts,
                "field_avail" => $fieldAvail,
            )
        );
    }

    /**
     * 首页，添加合作方页面，但提交不是提交到这里，提交到addAction处
     */
    public function indexAction(){
        $assignParams = array(
            "title" => "新加合作方-Page合作方配置",
        );
        $partners = $this->pm->getPageconfigRedis("partners");
        if(empty($partners)){
            //当没有一个合作方信息时，引导去导入数据
            Yaf_Dispatcher::getInstance()->disableView();
            $msg = "没有合作方信息，请先<a href='/page/reset'>导入</a>";
            $assignParams['box_level'] = 3;
            $assignParams['box_msg'] = $msg;
            $this->getView()->assign($assignParams);
            $this->getView()->display("pub/msg.phtml");
            return ;
        }
        //由于是首页，提示操作细节
        $assignParams['box_msg'] = "请在左侧选择你要修改的业务号信息，请不要修改不属于你管理的业务号，我们会对每一次修改记录或者在下面添加一个新的合作方!";
        $assignParams['partners'] = $partners;
        $assignParams['action'] = "add";//说明是增加业务方的操作
        $assignParams['action_url'] = "/page/add";//提交表单的地址
        $this->_assignPartner();

        $this->getView()->assign($assignParams);
    }

    /**
     * 展示已经添加的业务方信息
     */
    public function showAction(){
        //由于这个控制器用的是index的模板，所以开始就禁用掉模板输出
        Yaf_Dispatcher::getInstance()->disableView();

        $partners = $this->pm->getPageconfigRedis("partners");
        $partnerid = Tool_Request::getStr("partnerid");
        $assignParams = array(
            "title" => "编辑合作方{$partnerid}-Page合作方配置",
        );

        //当业务码不存在的时候，报这个错
        if(empty($partners[$partnerid])){
            //当输入错误的业务码时
            $assignParams['box_msg'] = "您输入的业务码({$partnerid})不存在，请核实";
            $assignParams['box_level'] = 3;
            $assignParams['box_url'] = '/page/';
            $this->getView()->assign($assignParams);
            $this->getView()->display("pub/msg.phtml");
            return;
        }

        $assignParams['action'] = "edit";//说明是编辑业务方的操作
        $assignParams['action_url'] = "/page/edit?partnerid=" . $partnerid;//提交表单的地址
        $assignParams['partners'] = $partners;
        $this->_assignPartner($partnerid);

        $this->getView()->assign($assignParams);
        $this->getView()->display("page/index.phtml");
    }

    /**
     * 修改业务码时提交的ajax请求，返回的格式遵守mymodal的返回格式规范
     */
    public function editAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        $partnerid = Tool_Request::getStr("partnerid");
        $this->response = array();
        if(empty($partnerid) || !Tool_Request::hasPost()){
            $this->response["content"] = "参数错误!!";
        }else{
            $partnerinfo = $this->pm->getPageconfigRedis("partners", $partnerid);
            if(empty($partnerinfo)){
                $this->response["content"] = "未找到对应的合作方";
            }else{
                //上传上来的配置文件，去掉了partnerid这个字段
                $config = Tool_Request::getPost();
                if(isset($config['partnerid']))
                    unset($config['partnerid']);
                if(empty($config)){
                    $this->response["content"] = "配置信息为空，请核实";
                }else{
                    $this->pm->setPageconfig("partners", $partnerid, Tool_Func::ini2array($config, "-"));

                    $diff= Tool_Diff::arrDiff(Tool_Func::array2ini($partnerinfo, "-"), $config);

                    $this->response["content"] = "更新成功,以下是修改的内容:<br/>";
                    //渲染diff相关的模板
                    $this->getView()->assign("diff", $diff);
                   // $this->response["content"] .= $this->getView()->render("page/diff.phtml");
                    $this->response["content"] .= $this->getView()->render("include/diff.phtml");
                    $this->response["refresh"] = true;
                }
            }
        }
        echo json_encode($this->response);
    }
    /**
     * 添加业务操作，此接口与edit接口一样，遵守mymodal的返回格式规范
     */
    public function addAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        $partnerid = Tool_Request::getStr("partnerid");
        $this->response = array();
        if(empty($partnerid) || !Tool_Request::hasPost()){
            $this->response["content"] = "参数错误!!";
        }else{
            $partnerinfo = $this->pm->getPageconfigRedis("partners", $partnerid);
            if(!empty($partnerinfo)){
                $this->response["content"] = "已经存在这个业务码，请核实";
            }else{
                //上传上来的配置文件，去掉了partnerid这个字段
                $config = Tool_Request::getPost();
                if(isset($config['partnerid']))
                    unset($config['partnerid']);
                if(empty($config)){
                    $this->response["content"] = "配置信息为空，请核实";
                }else{
                    $this->pm->setPageconfig("partners", $partnerid, Tool_Func::ini2array($config, "-"));
                    $this->response["content"] = "添加业务成功，业务码为$partnerid";
                    $this->response["refresh"] = true;
                    $this->response["refresh_url"] = '/page/show?partnerid=' . $partnerid;
                }
            }
        }
        echo json_encode($this->response);

    }

    /**
     * 删除业务码，此为一个页面级别的action
     * 与其它的上行操作不一样
     */
    public function delAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        $partnerid = Tool_Request::getStr("partnerid");
        if(empty($partnerid) || !Tool_Request::hasPost()){
            $msg = "参数错误!!";
            $assignParams['box_msg'] = $msg;
            $assignParams['box_url'] = '/page/';
        }else{
            //删除操作
            $t = $this->pm->delPageconfig("partners", $partnerid);
            $msg = "成功删除业务码{$partnerid}";
            $assignParams['box_msg'] = $msg;
            $assignParams['box_url'] = '/page/';
        }
        $this->getView()->assign($assignParams);
        $this->getView()->display("pub/msg.phtml");
    }

    /**
     * 从ini配置文件覆盖到redis数据
     * 如果redis里没有数据，将直接导入数据
     * 如果有数据，将生成diff信息
     */
    public function resetAction(){
        $this->getView()->assign("title", "使用配置文件初使化redis");
        $pageconfig = $this->pm->getPageconfigRedis();
        //如果是有post数据，或者是redis里没有数据，将直接导入
        if(Tool_Request::hasPost() || empty($pageconfig)){
            Yaf_Dispatcher::getInstance()->disableView();
            $this->pm->resetRedisFromIni();
            $pageconfig = $this->pm->getPageconfigRedis();

            $msg = "导入完成:redis里的信息为：<br/>";
            foreach($pageconfig as $k => $v){
                $msg .= "{$k}有 " . count($v) . " 条记录<br/>";
            }

            $this->getView()->assign(array("box_level" => 0, "box_msg" => $msg));
            $this->getView()->display("pub/msg.phtml");
        }else{
            //这里要写两个文件diff相关的信息
        }
    }
}
