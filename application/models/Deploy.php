<?php 
class DeployModel {
    
    private $test_mechine = array(
        '218.30.115.93', 
        '10.210.74.179', 
        '10.210.74.178', 
        '202.108.37.212',
        '172.16.11.212',
        '172.16.152.239',
        '172.16.152.240',
        '172.16.152.241',
        '172.16.152.242',
        '172.16.152.244',
        '172.16.152.245',
        '172.16.152.246',
        '172.16.152.247',
        '218.206.86.80',//加上80仿真测试机 不然看不见default日志 by haoming 20130717
        '172.16.86.80',
    );

    private $svn_account = array();


    const SVN_PREFIX_V3 = 'https://svn1.intra.sina.com.cn/weibo/wap/';
    const SVN_PREFIX_V4 = 'https://svn1.intra.sina.com.cn/weibo_mobile/wap/';

    const RSYNC_MODULE_V3 = 'weibo_source';
    const RSYNC_MODULE_V4 = 'weibov4_wap';

    //const SVN_PARAM = ' export --force --config-dir ~www/.subversion/ ';
    const SVN_PARAM = ' export --force --config-dir ~www-data/.subversion/ ';
    const RSYNC_PARAM = '-avz --port=8875 --delete --include=js/static/ --exclude=js/* --exclude=css/* --exclude=img/* --exclude=test/* ';

    //const CHECKOUT_PATH_V3 = '/data1/sinawapcms/code/svn/weibo/svn/';
    //const CHECKOUT_PATH_V4 = '/data1/sinawapcms/code/svn/weibo/svn_v4/';

    const CHECKOUT_PATH_V3 = '~www-data/';
    const CHECKOUT_PATH_V4 = '~www-data/';

    public function __construct() {
        //$config = new Yaf_Config_Ini(APP_PATH . "/conf/deploy.ini", "account");
        //$this->svn_account = $config->svn;
        $this->svn_account['user'] = 'xiaotong3';
        $this->svn_account['password'] = 'sina@713711';
    }

    public function get_test_mechine() {
        return $this->test_mechine;
    }

    public function get_primary_svn() {
        return array(self::SVN_PREFIX_V3, self::SVN_PREFIX_V4);
    }

    public function run_deploy($srcaddr, $desaddr) {

        
        $checkout_path = self::CHECKOUT_PATH_V4;
        $rsync_module = self::RSYNC_MODULE_V4;

        /** svn命令组装 */
        $svn_command = "svn " . self::SVN_PARAM . " {$srcaddr}  " . $checkout_path; //force可以覆盖已存在目录
        $svn_command .=" --username {$this->svn_account['user']} --password {$this->svn_account['password']} >~www-data/output 2>&1 &";
        /** 加入命令池，便于统一管理，检测返回值，根据逻辑终止执行相应语句 */
        $command_pool['svn'] = array('cmd' => $svn_command);
        
        /** rsync命令组装 */
        $rsync_command = " rsync " . self::RSYNC_PARAM . " {$checkout_path} {$desaddr}::{$rsync_module} 2>&1";
        $command_pool['rsync'] = array('cmd' => $rsync_command);

        $this->run_command($command_pool);
    }

    private function run_command($command_pool) {

        if ( !empty($command_pool) ) {
            foreach ( $command_pool as $key => $command ) {
                if ( $key == 'rsync' ) {
                    var_dump($command['cmd'], $output);exit;
                }
                $output = shell_exec($command['cmd']);
            }
        }
        exit;
    }

    /**
     * 对检出路径做备份操作,最早按可以对任意子目录进行备份设计，现在只需
     * 备份$svnPrefix即可
     * @global <type> $svnPrefix svn检出前缀限制
     * @param <type> $localPath  本地绝对路径
     * @return <type>
     */
    private function BackupPath($localPath)
    {
        if ( is_dir($localPath) ) {//存在则备份不存在说明第一次检出
            $backdir = rtrim($localPath, '/') . '.old';
            if ( is_dir($backdir) ) {
                $ret = $this->deldir($backdir);
                if ( !$ret ) throw new Exception($this->message['rmdir']);
            }
            if ( $this->debug ) {
                echo "move the file $localPath to $backdir";
            }
            $ret = @rename($localPath, $backdir);
            if ( !$ret ) throw new Exception($this->message['movedir']);
            if ( ltrim(self::SVN_CHECKOUT_PATH) == ltrim($localPath) ) {
                $ret = @mkdir($localPath); //根路径移动到.old后要重建，无法根据svn检出重建
                if ( !$ret ) throw new Exception($this->message['mkdir']);
            }
            return true;
        }else {
            $ret = mkdir($localPath, 0755, true); //不存在则第一次检出需要建立
            if ( !$ret ) throw new Exception($this->message['mkdir'] . $localPath);
            return false;
        }
    }
}
