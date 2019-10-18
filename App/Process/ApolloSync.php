<?php
/**
 * Created by PhpStorm.
 * User: yugang
 * Date: 2019-10-17
 * Time: 19:26
 */

namespace App\Process;


use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\ServerManager;
use Swoole\Timer;

class ApolloSync extends AbstractProcess
{

    protected function run($arg)
    {
        if (Core::getInstance()->isDev()) {
            $metaServer = 'http://127.0.0.1:8080';
            $saveFile = RUNNING_ROOT.'/dev.php';
        } else {
            $metaServer = 'http://127.0.0.1:8080';
            $saveFile = RUNNING_ROOT.'/produce.php';
        }

        $server = new \EasySwoole\Apollo\Server([
            'server'=>$metaServer,
            'appId'=>'es-rpc-server',
            'cluster'=>'default'
        ]);
        //创建apollo客户端
        $apollo = new \EasySwoole\Apollo\Apollo($server);
        Timer::tick(1000, function () use ($apollo, $saveFile) {
            //第一次同步
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

                }
                $content = '<?php return '.var_export($data, true).';';
                file_put_contents($saveFile, $content);
                ServerManager::getInstance()->getSwooleServer()->reload();
            }
        });
    }
}