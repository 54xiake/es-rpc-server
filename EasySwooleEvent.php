<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:33
 */

namespace EasySwoole\EasySwoole;


use App\Process\ApolloSync;
use App\Process\HotReload;
use App\Process\TestJob;
use App\Service\CarService;
use App\Task\TaskOne;
use App\Task\TaskTwo;
use App\Template\Smarty;
use App\Utility\LogPusher;
use EasySwoole\AtomicLimit\AtomicLimit;
use EasySwoole\Component\AtomicManager;
use EasySwoole\Console\Console;
use EasySwoole\Consul\Consul;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\FastCache\Cache;
use EasySwoole\FastCache\CacheProcessConfig;
use EasySwoole\FastCache\SyncData;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Rpc\NodeManager\RedisManager;
use EasySwoole\Rpc\Rpc;
use EasySwoole\Rpc\Test\NodeService;
use EasySwoole\Rpc\Test\OrderService;
use EasySwoole\Rpc\Test\UserService;
use EasySwoole\Template\Render;
use EasySwoole\Trace\TrackerManager;
use EasySwoole\Utility\File;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');

        //加载公共配置信息
        $commonFile = RUNNING_ROOT.'/common.php';
        if (file_exists($commonFile)) {
            $conf = Config::getInstance();
            $conf->loadFile($commonFile);
        }


        //mysql
//        $configData = Config::getInstance()->getConf('MYSQL');
//        $config = new \EasySwoole\Mysqli\Config($configData);
//        $poolConf = \EasySwoole\MysqliPool\Mysql::getInstance()->register('mysql', $config);
//        $poolConf->setMaxObjectNum(20);

        //redis
        $redisConfigData = Config::getInstance()->getConf('REDIS');
        $redisConfig = new \EasySwoole\Redis\Config\RedisConfig($redisConfigData);

        // $config->setOptions(['serialize'=>true]);
        /**
        这里注册的名字叫redis，你可以注册多个，比如redis2,redis3
         */
        $poolConf = \EasySwoole\RedisPool\Redis::getInstance()->register('redis',$redisConfig);
        $poolConf->setMaxObjectNum($redisConfigData['maxObjectNum']);
        $poolConf->setMinObjectNum($redisConfigData['minObjectNum']);

        //注册事件
        \App\Event\Event::getInstance()->set('test', function () {
            echo 'test event';
        });
    }

    public static function mainServerCreate(EventRegister $register)
    {
        //注册进程
        $swooleServer = ServerManager::getInstance()->getSwooleServer();
        $swooleServer->addProcess((new HotReload('HotReload', ['disableInotify' => false]))->getProcess());
        //监听配置文件变更
        $swooleServer->addProcess((new ApolloSync('Rpc.ApolloSync'))->getProcess());

        $register->add($register::onStart, function (\swoole_server $server) {
            echo 'masterPid:' . $server->master_pid. PHP_EOL;
            //管理进程的PID，通过向管理进程发送SIGUSR1信号可实现柔性重启
            echo 'managerPid:' . $server->manager_pid. PHP_EOL;

            //修改主进程名称
//            @swoole_set_process_name("swoole server");

            print_r(swoole_get_local_mac());
        });


        //主服务注册onWorkerStart事件
        $register->add($register::onWorkerStart,function (\swoole_server $server,int $workerId){
            var_dump($workerId.'start');
            file_put_contents('/tmp/123.log', print_r(get_included_files(),1));

            //修改子进程名称
//            @swoole_set_process_name("php-worker-{$workerId}");
        });

        $register->add($register::onReceive, function (\swoole_server $server, int $workerId) {

        });

        //主服务增加onMessage事件
        //给server 注册相关事件 在 WebSocket 模式下  message 事件必须注册 并且交给
        $register->set(EventRegister::onMessage, function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) {
            var_dump($frame);
        });

        ################# tcp 服务器1 没有处理粘包 #####################
        $tcp1ventRegister = $subPort1 = ServerManager::getInstance()->addServer('tcp1', 9502, SWOOLE_TCP, '0.0.0.0', [
            'open_length_check' => false,//不验证数据包
        ]);
        $tcp1ventRegister->set(EventRegister::onConnect,function (\swoole_server $server, int $fd, int $reactor_id) {
            echo "tcp服务1  fd:{$fd} 已连接\n";
            $str = '恭喜你连接成功服务器1';
            $server->send($fd, $str);
        });
        $tcp1ventRegister->set(EventRegister::onClose,function (\swoole_server $server, int $fd, int $reactor_id) {
            echo "tcp服务1  fd:{$fd} 已关闭\n";
        });
        $tcp1ventRegister->set(EventRegister::onReceive,function (\swoole_server $server, int $fd, int $reactor_id, string $data) {
            echo "tcp服务1  fd:{$fd} 发送消息:{$data}\n";
        });





        //实现粘包处理
        $subPort2 = ServerManager::getInstance()->getSwooleServer()->addlistener('0.0.0.0', 9503, SWOOLE_TCP);
        $subPort2->set(
            [
                'open_length_check'     => true,
                'package_max_length'    => 81920,
                'package_length_type'   => 'N',
                'package_length_offset' => 0,
                'package_body_offset'   => 4,
            ]
        );
        $subPort2->on('connect', function (\swoole_server $server, int $fd, int $reactor_id) {
            echo "tcp服务2  fd:{$fd} 已连接\n";
            $str = '恭喜你连接成功服务器2';
            $server->send($fd, pack('N', strlen($str)) . $str);
        });
        $subPort2->on('close', function (\swoole_server $server, int $fd, int $reactor_id) {
            echo "tcp服务2  fd:{$fd} 已关闭\n";
        });
        $subPort2->on('receive', function (\swoole_server $server, int $fd, int $reactor_id, string $data) {
            echo "tcp服务2  fd:{$fd} 发送原始消息:{$data}\n";
            echo "tcp服务2  fd:{$fd} 发送消息:" . substr($data, '4') . "\n";
        });

        /**
         * **************** Crontab任务计划 **********************
         */
//        // 开始一个定时任务计划
//        Crontab::getInstance()->addTask(TaskOne::class);
//        // 开始一个定时任务计划
//        Crontab::getInstance()->addTask(TaskTwo::class);

        // 每隔5秒将数据存回文件
        Cache::getInstance()->setTickInterval(5 * 1000);//设置定时频率
        Cache::getInstance()->setOnTick(function (SyncData $SyncData, CacheProcessConfig $cacheProcessConfig) {
            $data = [
                'data'  => $SyncData->getArray(),
                'queue' => $SyncData->getQueueArray(),
                'ttl'   => $SyncData->getTtlKeys(),
                // queue支持
                'jobIds'     => $SyncData->getJobIds(),
                'readyJob'   => $SyncData->getReadyJob(),
                'reserveJob' => $SyncData->getReserveJob(),
                'delayJob'   => $SyncData->getDelayJob(),
                'buryJob'    => $SyncData->getBuryJob(),
            ];
            $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
            File::createFile($path,serialize($data));
        });

        // 启动时将存回的文件重新写入
        Cache::getInstance()->setOnStart(function (CacheProcessConfig $cacheProcessConfig) {
            $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
            if(is_file($path)){
                $data = unserialize(file_get_contents($path));
                $syncData = new SyncData();
                $syncData->setArray($data['data']);
                $syncData->setQueueArray($data['queue']);
                $syncData->setTtlKeys(($data['ttl']));
                // queue支持
                $syncData->setJobIds($data['jobIds']);
                $syncData->setReadyJob($data['readyJob']);
                $syncData->setReserveJob($data['reserveJob']);
                $syncData->setDelayJob($data['delayJob']);
                $syncData->setBuryJob($data['buryJob']);
                return $syncData;
            }
        });

        // 在守护进程时,php easyswoole stop 时会调用,落地数据
        Cache::getInstance()->setOnShutdown(function (SyncData $SyncData, CacheProcessConfig $cacheProcessConfig) {
            $data = [
                'data'  => $SyncData->getArray(),
                'queue' => $SyncData->getQueueArray(),
                'ttl'   => $SyncData->getTtlKeys(),
                // queue支持
                'jobIds'     => $SyncData->getJobIds(),
                'readyJob'   => $SyncData->getReadyJob(),
                'reserveJob' => $SyncData->getReserveJob(),
                'delayJob'   => $SyncData->getDelayJob(),
                'buryJob'    => $SyncData->getBuryJob(),
            ];
            $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
            File::createFile($path,serialize($data));
        });

        Cache::getInstance()->setTempDir(EASYSWOOLE_TEMP_DIR)->attachToServer(ServerManager::getInstance()->getSwooleServer());




        /**
         * 日志推送到控制台
         */

//        $tcp = $swooleServer->addlistener('0.0.0.0',9600,SWOOLE_TCP);
//
//        /*
//         * 实例化一个控制台，设置密码为123456
//         */
//        $console = new Console('myConsole','123456');
//        /*
//         * 命令注册
//         */
//        $console->moduleContainer()->set(new Test1());
//
//        /*
//         * 依附给server
//         */
//        $console->protocolSet($tcp)->attachToServer($swooleServer);
//
//        /*
//         * 注册日志模块
//         */
//        $console->moduleContainer()->set(new LogPusher());
//        $console->protocolSet($tcp)->attachToServer($swooleServer);
//        /*
//         * 给es的日志推送加上hook
//         */
//        Logger::getInstance()->onLog()->set('remotePush',function ($msg,$logLevel,$category)use($console){
//            if(Config::getInstance()->getConf('logPush')){
//                /*
//                 * 可以在 LogPusher 模型的exec方法中，对loglevel，category进行设置，从而实现对日志等级，和分类的过滤推送
//                 */
//                foreach ($console->allFd() as $item){
//                    $console->send($item['fd'],$msg);
//                }
//            }
//        });
        //打开日志推送
        //log enable

        //在全局的主服务中创建事件中，实例化该Render,并注入你的驱动配置
        Render::getInstance()->getConfig()->setRender(new Smarty());
        Render::getInstance()->getConfig()->setTempDir(EASYSWOOLE_TEMP_DIR);
        Render::getInstance()->attachServer($swooleServer);

        // 注册一个atomic对象
        AtomicManager::getInstance()->add('second');

        //限流器注册
        AtomicLimit::getInstance()->addItem('default')->setMax(2);
        AtomicLimit::getInstance()->addItem('api')->setMax(2);
        AtomicLimit::getInstance()->enableProcessAutoRestore(ServerManager::getInstance()->getSwooleServer(),10*1000);

        //rpc
        $serverIp = Config::getInstance()->getConf('MAIN_SERVER.LISTEN_ADDRESS');

        $config = new \EasySwoole\Rpc\Config();
        $config->setServerIp($serverIp);//注册提供rpc服务的ip
        $redisConfigData = Config::getInstance()->getConf('REDIS');
        $redisConfig = new RedisConfig($redisConfigData);
        $redisPool = new RedisPool($redisConfig);
        $nodeManager = new RedisManager($redisPool);
        $config->setNodeManager($nodeManager);//注册节点管理器
//        $config->getBroadcastConfig()->setEnableBroadcast(true);//启用广播
//        $config->getBroadcastConfig()->setEnableListen(true);   //启用监听
//        $config->getBroadcastConfig()->setSecretKey('lucky');        //设置秘钥
        $config->setWorkerNum(4);

        $rpc = Rpc::getInstance($config);;
        $rpc->add(new UserService());  //注册服务
//        $rpc->add(new OrderService());
//        $rpc->add(new NodeService());
        $rpc->add(new CarService());
        $rpc->attachToServer(ServerManager::getInstance()->getSwooleServer());


//        $list = $rpc->generateProcess(); //获取注册的rpc worker自定义进程
//        foreach ($list['worker'] as $p){
//            $p->getProcess()->start();
//        }
//
//        foreach ($list['tickWorker'] as $p){//获取注册的rpc tick自定义进程(ps:处理广播和监听广播)
//            $p->getProcess()->start();
//        }
//
//        while($ret = \Swoole\Process::wait()) {
//            echo "PID={$ret['pid']}\n";
//        }

        $config = new \EasySwoole\ORM\Db\Config();
        $config->setDatabase('easyswoole');
        $config->setUser('root');
        $config->setPassword('asdfghjkl');
        $config->setHost('127.0.0.1');
        //连接池配置
        $config->setGetObjectTimeout(3.0); //设置获取连接池对象超时时间
        $config->setIntervalCheckTime(30*1000); //设置检测连接存活执行回收和创建的周期
        $config->setMaxIdleTime(15); //连接池对象最大闲置时间(秒)
        $config->setMaxObjectNum(20); //设置最大连接池存在连接对象数量
        $config->setMinObjectNum(5); //设置最小连接池存在连接对象数量

        \EasySwoole\ORM\DbManager::getInstance()->addConnection(new \EasySwoole\ORM\Db\Connection($config), 'default');

        $list = ServerManager::getInstance()->getSwooleServer();
        print_r($list);
    }

    public static function onRequest(Request $request, Response $response): bool
    {
//        if(isset($request->get['api'])){
//            if(AtomicLimit::isAllow('api')){
//                $response->write('api success');
//            }else{
//                $response->write('api refuse');
//            }
//        }else{
//            if(AtomicLimit::isAllow('default')){
//                $response->write('default success');
//            }else{
//                $response->write('default refuse');
//            }
//        }
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        $t = new TrackerManager();

        $tracker = $t->getTracker('test');

        $tracker->setPoint('request',[
            'sql'=>'sql statement one'
        ]);
        $tracker->endPoint('request');

        echo $tracker->toString();


        $responseMsg = $response->getBody()->__toString();
        Logger::getInstance()->console("响应内容:".$responseMsg);
        // 响应状态码:
        // var_dump($response->getStatusCode());


        // tracker结束,结束之后,能看到中途设置的参数,调用栈的运行情况
        // TODO: Implement afterAction() method.
    }
}