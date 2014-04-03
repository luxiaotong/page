<?php 
class DeployModel {
    
    private $test_mechine = array(
        '10.210.234.183',
    );

    private $svn_account = array();

    private $redis_obj;

    const SVN_PREFIX_V3 = 'https://svn1.intra.sina.com.cn/weibo/wap/';
    const SVN_PREFIX_V4 = 'https://svn1.intra.sina.com.cn/weibo_mobile/wap/';

    const RSYNC_MODULE_V3 = 'weibo_source';
    const RSYNC_MODULE_V4 = 'weibov4_wap';

    const SVN_PARAM = ' export --force --config-dir ~www/.subversion/ ';
    const RSYNC_PARAM = '-avz --port=8875 --delete --include=js/static/ --exclude=js/* --exclude=css/* --exclude=img/* --exclude=test/* ';

    const SVN_CHECKOUT_PATH = '/data1/sinawapcms/code/svn/weibo/svn/';
    const CHECKOUT_PATH_V3 = '/data1/sinawapcms/code/svn/weibo/svn/';
    const CHECKOUT_PATH_V4 = '/data1/sinawapcms/code/svn/weibo/svn_v4/';
    const CHECKOUT_PATH_V4_STATIC = '/data1/sinawapcms/code/svn/weibo/svn_v4_static/';
    
    private $message = array(
    );

    public function __construct() {
        $config = new Yaf_Config_Ini(APP_PATH . "/conf/deploy.ini", "account");
        $this->svn_account = $config->svn;
        
        //初使化redis配置
        $config = new Yaf_Config_Ini(APP_PATH . "/conf/source.ini", 'redis');
        $this->redis_obj = new DB_Redis($config);
    }

    public function get_test_mechine() {
        return $this->test_mechine;
    }

    public function get_primary_svn() {
        return array(self::SVN_PREFIX_V3, self::SVN_PREFIX_V4);
    }

    public function run_deploy($srcaddr, $desaddr) {

        
        if ( strpos($srcaddr, self::SVN_PREFIX_V3) !== false ) {
            $rsync_module = self::RSYNC_MODULE_V4;
            $checkout_path = self::CHECKOUT_PATH_V3;
        } elseif ( strpos($srcaddr, self::SVN_PREFIX_V4) !== false ) {
            $rsync_module = self::RSYNC_MODULE_V4;
            $checkout_path = self::CHECKOUT_PATH_V4;

            ################新增静态资源主干文件的同步####################
            $this->BackupPath(self::CHECKOUT_PATH_V4_STATIC); //静态资源目录的备份和创建
            $svn_static_command = "svn " . SVN_PARAM . ' ' . SVN_STATIC_ADRESS . ' ' . CHECKOUT_PATH_V4_STATIC; //force可以覆盖已存在目录                     $svn_static_command .=" --username {$this->svnAccount['user']} --password {$this->svnAccount['password']} 2>&1";
            $commandPool['svn_static'] = array('cmd' => $svn_static_command);
        } else {
            exit("SVN 检出路径输入错误");
        }

        /** svn命令组装 */
        $svn_command = "svn " . self::SVN_PARAM . " {$srcaddr}  " . $checkout_path; //force可以覆盖已存在目录
        $svn_command .=" --username {$this->svn_account['user']} --password {$this->svn_account['password']} >~www/svn_output 2>&1";
        $this->BackupPath($checkout_path);

        /** 加入命令池，便于统一管理，检测返回值，根据逻辑终止执行相应语句 */
        $command_pool['svn'] = array('cmd' => $svn_command);
        
        /** rsync命令组装 */
        $rsync_command = " rsync " . self::RSYNC_PARAM . " {$checkout_path} {$desaddr}::{$rsync_module} >~www/rsync_outout 2>&1";
        $command_pool['rsync'] = array('cmd' => $rsync_command);

        if ( !empty($command_pool) ) {
            foreach ( $command_pool as $key => $command ) {
                $output = shell_exec($command['cmd']);
            }
            
            $deploylog = json_encode(array("time" => date("Y-m-d H:i:s"), 
                "srcaddr" => $srcaddr, 
                "desaddr" => "{$desaddr}::{$rsync_module}",
                "username" => ""));
            $this->redis_obj->lpush("deploylog", $deploylog);
            $this->redis_obj->ltrim("deploylog", 0, 29);
        }
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
            /*if ( $this->debug ) {
                echo "move the file $localPath to $backdir";
            }*/
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

    function deldir($dir)
    {
        $dh = opendir($dir);
        while ( $file = readdir($dh) ) {
            if ( $file != "." && $file != ".." ) {
                $fullpath = $dir . "/" . $file;
                if ( !is_dir($fullpath) ) {
                    unlink($fullpath);
                } else {
                    $this->deldir($fullpath); //�ݹ����
                }
            }
        }
        closedir($dh);
        if ( rmdir($dir) ) {
            return true;
        } else {
            return false;
        }
    }

    public function search_srcaddr($srcaddr) {
        $tmp_pos = strrpos($srcaddr, '/');
        $search = substr($srcaddr, 0, $tmp_pos);
        $filter = substr($srcaddr, $tmp_pos + 1);

        //get data from redis
        $output = $this->redis_obj->hget('svnaddr', $search);
        
        //get data from svn list and set to redis
        if ( empty($output) ) {
        
            $svn_command = "svn list --config-dir ~www/.subversion/ --username {$this->svn_account['user']} --password {$this->svn_account['password']} $search";
            $output = shell_exec($svn_command);
            $this->redis_obj->hset('svnaddr', $search, $output);
        }

        // splite output and filter
        return $this->splite_relpaths_from_output($output, $search, $filter);
        
    }

    private function splite_relpaths_from_output($output, $search, $filter)
    {
        if ( !empty($output) ) {
            $rel_paths = explode("\n", rtrim($output, "\n"));
            foreach ( $rel_paths as $key => $rel_path ) {
                if ( empty($filter) || strpos($rel_path, $filter) === 0 ) {
                    $abs_paths[] = $search . '/' . $rel_path;
                }
            }
            return $abs_paths;
        } else {
            return array();
        }
    }

    public function get_deploylog() {
        $logs = $this->redis_obj->lrange("deploylog", 0, 29);
        foreach ( $logs as $key => $log ) {
            $deploylog[] = json_decode($log, true);
        }
        return $deploylog;
    }
}
