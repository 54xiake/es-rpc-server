<?php
namespace App\Command;

use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use Swoole\Coroutine\Scheduler;


class LoadApolloConfig implements CommandInterface
{
    public function commandName(): string
    {
        return 'LoadApolloConfig';
    }

    public function exec(array $args): ?string
    {
//        getenv();
        //启动前调用协程
        $scheduler = new Scheduler();
        $scheduler->add(function() use($args) {
            if (!isset($args[1]) || $args[1]=='dev') {
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