<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-17
 * Time: 19:26
 */

namespace App\Process;


use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\ServerManager;
use Swoole\Timer;

class ApolloSync extends AbstractProcess
{

    protected function run($arg)
    {
        if (Core::getInstance()->isDev()) {
            $metaServer = APOLLO_DEV_SERVER;
            $saveFile = RUNNING_ROOT . '/dev.php';
        } else {
            $metaServer = APOLLO_PRO_SERVER;
            $saveFile = RUNNING_ROOT . '/produce.php';
        }

        $server = new \EasySwoole\Apollo\Server([
            'server' => $metaServer,
            'appId' => APOLLO_APP_ID,
            'cluster' => 'default'
        ]);
        //创建apollo客户端
        $apollo = new \EasySwoole\Apollo\Apollo($server);
        Timer::tick(1000, function () use ($apollo, $saveFile) {
            //动态变量加载到内存
            $result = $apollo->sync('dynamic');
            if ($result->isModify()) {
                $data = $result->getConfigurations();
                if (is_array($data)) {
                    foreach ($data as $k => $v) {
                        if ($v == 'null') {
                            $data[$k] = null;
                        } elseif (is_array(json_decode($v, true))) {
                            $data[$k] = json_decode($v, 1);
                        }
                    }
                    var_dump($data);
                    //重新加载配置信息
                    $conf = Config::getInstance();
                    $conf->setConf('dynamic', $data);
                    ServerManager::getInstance()->getSwooleServer()->reload();
                }
            }
        });
    }
}