<?php
namespace App\Command;

use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use Swoole\Coroutine\Scheduler;


class LoadApolloConfig implements CommandInterface
{
    public function commandName(): string
    {
        return 'LoadApolloConfig';
    }

    public function exec(array $args): ?string
    {
        //启动前调用协程
        $scheduler = new Scheduler();
        $scheduler->add(function() use($args) {
            if (in_array('produce',$args)) {
                $metaServer = APOLLO_PRO_SERVER;
                $saveFile = RUNNING_ROOT.'/produce.php';
            } else {
                $metaServer = APOLLO_DEV_SERVER;
                $saveFile = RUNNING_ROOT.'/dev.php';
            }

            $server = new \EasySwoole\Apollo\Server([
                'server'  => $metaServer,
                'appId'   => APOLLO_APP_ID,
                'cluster' => 'default'
            ]);
            //创建apollo客户端
            $apollo = new \EasySwoole\Apollo\Apollo($server);
            $result = $apollo->sync('application');
            if ($result->isModify()) {
                $data = $result->getConfigurations();
                if(is_array($data)) {
                    foreach($data as $k=>$v) {
                        if ($v=='null') {
                            $data[$k] = null;
                        }elseif (is_array(json_decode($v, true))) {
                            $data[$k] = json_decode($v,1);
                        }
                    }
                    $content = '<?php return '.var_export($data, true).';';
                    file_put_contents($saveFile, $content);
                }
            }

            //加载公共配置
            $cResult = $apollo->sync('common');
            if ($cResult->isModify()) {
                $data = $cResult->getConfigurations();
                if(is_array($data)) {
                    foreach($data as $k=>$v) {
                        if ($v=='null') {
                            $data[$k] = null;
                        }elseif (is_array(json_decode($v, true))) {
                            $data[$k] = json_decode($v,1);
                        }
                    }
                    $commonFile = RUNNING_ROOT.'/common.php';
                    $content = '<?php return '.var_export($data, true).';';
                    file_put_contents($commonFile, $content);
                }
            }
        });
        $scheduler->start();
        return null;
    }

    public function help(array $args): ?string
    {
        //输出logo
        $logo = Utility::easySwooleLog();
        return $logo."加载Apollo配置文件";
    }
}