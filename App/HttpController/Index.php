<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-10
 * Time: 14:36
 */

namespace App\HttpController;


use App\Event\Event;
use co;
use EasySwoole\Component\AtomicManager;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Consul\Config;
use EasySwoole\Consul\Consul;
use EasySwoole\Consul\Request\Acl\Bootstrap;
use EasySwoole\Consul\Request\Acl\Replication;
use EasySwoole\Consul\Request\Catalog\Deregister;
use EasySwoole\Consul\Request\Catalog\Register;
use EasySwoole\Consul\Request\Catalog\Service;
use EasySwoole\Consul\Request\Catalog\Services;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\FastCache\Cache;
use EasySwoole\FastCache\Job;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Template\Render;
use EasySwoole\Validate\Validate;
use Exception;

class Index extends Controller
{

    function index()
    {
        $this->request()->getRequestParam();
        // TODO: Implement index() method.
        $this->response()->write('hello world');
    }

    function rpc() {
        return ['title'=>'abc'];
    }

    function test()
    {
        go(function () {
            co::sleep(0.5);
            echo "hello";
        });
//        go('test');
        go([$this, "test1"]);
        $this->response()->write('test');
    }

    function test1()
    {
        echo "test1";
    }

    function waitGroup()
    {
        go(function () {
            $ret = [];

            $wait = new \EasySwoole\Component\WaitGroup();

            $wait->add();
            go(function () use ($wait, &$ret) {
                \co::sleep(0.1);
                $ret[] = time();
                $wait->done();
            });

            $wait->add();
            go(function () use ($wait, &$ret) {
                \co::sleep(2);
                $ret[] = time();
                $wait->done();
            });

            $wait->wait();

            var_dump($ret);
        });
    }

    function channel()
    {
        go(function () {
            $channel = new \Swoole\Coroutine\Channel();
            go(function () use ($channel) {
                //模拟执行sql
                \co::sleep(0.1);
                $channel->push(1);
            });
            go(function () use ($channel) {
                //模拟执行sql
                \co::sleep(0.1);
                $channel->push(2);
            });
            go(function () use ($channel) {
                //模拟执行sql
                \co::sleep(0.1);
                $channel->push(3);
            });

            $i = 3;
            while ($i--) {
                var_dump($channel->pop());
            }
        });
    }

    public function csp()
    {
        go(function () {
            $csp = new \EasySwoole\Component\Csp();
            $csp->add('t1', function () {
                \co::sleep(0.1);
                return 't1 result';
            });
            $csp->add('t2', function () {
                \co::sleep(6);
                return 't2 result';
            });

            var_dump($csp->exec());
        });
    }

    public function context()
    {
        go(function () {
            ContextManager::getInstance()->set('key', 'key in parent');
            go(function () {
                ContextManager::getInstance()->set('key', 'key in sub');
                var_dump(ContextManager::getInstance()->get('key') . " in");
            });
            \co::sleep(1);
            var_dump(ContextManager::getInstance()->get('key') . " out");
        });
    }

    //HTTP往TCP推送
    function push()
    {
        $fd = intval($this->request()->getRequestParam('fd'));
        $info = ServerManager::getInstance()->getSwooleServer()->connection_info($fd);
        print_r($info);
        if (is_array($info)) {
            ServerManager::getInstance()->getSwooleServer()->send($fd, 'push in http at ' . time());
        } else {
            $this->response()->write("fd {$fd} not exist");
        }
    }

    function event()
    {
        Event::getInstance()->hook('test');
    }

    function log()
    {
        Logger::getInstance()->log('log level info', Logger::LOG_LEVEL_INFO, 'DEBUG');//记录info级别日志//例子后面2个参数默认值
        Logger::getInstance()->log('log level notice', Logger::LOG_LEVEL_NOTICE, 'DEBUG2');//记录notice级别日志//例子后面2个参数默认值
        Logger::getInstance()->console('console', Logger::LOG_LEVEL_INFO, 'DEBUG');//记录info级别日志并输出到控制台
        Logger::getInstance()->info('log level info');//记录info级别日志并输出到控制台
        Logger::getInstance()->notice('log level notice');//记录notice级别日志并输出到控制台
        Logger::getInstance()->waring('log level waring');//记录waring级别日志并输出到控制台
        Logger::getInstance()->error('log level error');//记录error级别日志并输出到控制台
        Logger::getInstance()->onLog()->set('myHook', function ($msg, $logLevel, $category) {
            //增加日志写入之后的回调函数
        });
    }

    function trigger()
    {
        throw new Exception("test error");
    }

    function onException(\Throwable $throwable): void
    {
//        parent::onException($throwable); // TODO: Change the autogenerated stub
        //记录错误异常日志,等级为Exception
        Trigger::getInstance()->throwable($throwable);
        //记录错误信息,等级为FatalError
        Trigger::getInstance()->error($throwable->getMessage() . '666');

        Trigger::getInstance()->onError()->set('myHook', function () {
            //当发生error时新增回调函数
        });
        Trigger::getInstance()->onException()->set('myHook', function () {

        });
    }

    function cache()
    {
        Cache::getInstance()->set('get', '节能=====aaa');
        $result = Cache::getInstance()->get('get');

        $this->response()->write($result);
    }

    function job()
    {
        $job = new Job();
        $job->setData("siam"); // 任意类型数据
        $job->setQueue("siam_queue");
        $jobId = Cache::getInstance()->putJob($job);
        var_dump($jobId);
    }

    function template()
    {
        try {
            $result = Render::getInstance()->render('a.html', ['title' => 'Test Template']);
            $this->response()->write($result);
        } catch (Exception $e) {
            Logger::getInstance()->error($e->getMessage());
        }

    }

    function check()
    {
        $data = [
            'name' => '',
            'age' => 25
        ];

        $valitor = new Validate();
        $valitor->addColumn('name', '名字不为空')->required('名字不为空')->lengthMin(10, '最小长度不小于10位');
        $bool = $valitor->validate($data);
        var_dump($bool ? "true" : $valitor->getError()->__toString());
    }

    public function redis()
    {
        /*@var \EasySwoole\RedisPool\Connection $redis */
        $redis = \EasySwoole\RedisPool\Redis::getInstance()->pool('redis')::defer();
        ($redis->set('name', '仙士可'));

        $redis = \EasySwoole\RedisPool\Redis::defer('redis');
        var_dump($redis->get('name'));

        $data = \EasySwoole\RedisPool\Redis::invoker('redis', function (\EasySwoole\RedisPool\Connection $redis) {
            return $redis->get('name');
        });
        var_dump($data);
        $data = \EasySwoole\RedisPool\Redis::getInstance()->pool('redis')::invoke(function (\EasySwoole\RedisPool\Connection $redis) {
            return $redis->get('name');
        });
        var_dump($data);

        //原生获取方式，getobj和recycleObj必须成对使用
        $redis = \EasySwoole\RedisPool\Redis::getInstance()->pool('redis')->getObj();
        var_dump($redis->get('name'));
        //回收
        \EasySwoole\RedisPool\Redis::getInstance()->pool('redis')->recycleObj($redis);
    }

    function atomic()
    {
        AtomicManager::getInstance()->add('second', 0);
        $atomic = AtomicManager::getInstance()->get('second');
        $atomic->add(1);
        $this->response()->write($atomic->get());
        // TODO: Implement index() method.

    }

    function consul()
    {
        $config = new Config([
            'IP' => '127.0.0.1',
            'port' => '8500',
            'version' => 'v1',
        ]);
        $this->consul = new Consul($config);

        $register = new Register([
            "datacenter" => "dc1",
            "id" => "40e4a748-2192-161a-0510-9bf59fe950b5",
            "node" => "foobar",
            "Address" => "172.30.11.119",
            "NodeMeta" => [
                "somekey" => "somevalue"
            ],
            "Service" => [
                "ID" => "EasySwoole-1",
                "Service" => "EasySwoole",
                "Tags" => [
                    "primary",
                    "v1",
                    "dev"
                ],
                "Address" => "172.30.11.119",
                "Meta" => [
                    "version" => "1.0"
                ],
                "Port" => 9501
            ],
            "Check" => [
                "Node" => "foobar",
                "CheckID" => "EasySwoole-1",
                "Name" => "EasySwoole health check",
                "Notes" => "Script based health check",
                "Status" => "passing",
                "ServiceID" => "EasySwoole-1",
                "Definition" => [
                    "HTTP" => "172.30.11.119:9501/health",
                    "Interval" => "1s",
                    "Timeout" => "1s",
                    "DeregisterCriticalServiceAfter" => "30s"
                ]
            ],
            "SkipNodeUpdate" => false
        ]);

        $this->consul->catalog()->register($register);
        $deregister = new Deregister(["node" => "foobar"]);
        $this->consul->catalog()->deRegister($deregister);
//        $deregister = new Deregister(["node" => "d62002ce5439"]);
//        $this->consul->catalog()->deRegister($deregister);

        $services = new Services();
        print_r($this->consul->catalog()->services($services));

        $service = new Service([
            'service' => 'EasySwoole',
            'dc' => 'dc1',
            'tag' => '',
            'near' => '',
            'node-meta' => '',
            'filter' => '',
        ]);
        print_r($this->consul->catalog()->service($service));


//        // Bootstrap ACLs
//        $bootstrap = new Bootstrap();
//        $this->consul->acl()->bootstrap($bootstrap);
//
//        // Check ACL Replication
//        $replication = new Replication();
//        $this->consul->acl()->replication($replication);

//        // Translate Rules
//        // Translate a Legacy Token's Rules
//        $translate = new Translate([
//            'accessor_id' => $accessor_id
//        ]);
//        $this->consul->acl()->translate($translate);
//
//        // Login to Auth Method
//        $login = new Login([
//            "authMethod" => $authMethod,
//            "bearerToken" => $bearerToken
//        ]);
//        $this->consul->acl()->login($login);
//
//        // Logout from Auth Method
//        $logout = new Logout([
//            'token' => $header['token']
//        ]);
//        $this->consul->acl()->logout($logout);
    }

    function zh() {
        $this->response()->withHeader('Content-type', 'text/html;charset=utf-8');
        $this->response()->write("中文乱码");
    }

    function config() {
        $config = \EasySwoole\EasySwoole\Config::getInstance()->getConf();

        $this->response()->write(json_encode($config));
    }

    function common() {
        $config = \EasySwoole\EasySwoole\Config::getInstance()->getConf('common');
        var_dump($config);

        $this->response()->write(json_encode($config));
    }

    function dynamic() {
        $config = \EasySwoole\EasySwoole\Config::getInstance()->getConf('dynamic');
        var_dump($config);

        $this->response()->write(json_encode($config));
    }

    function config1() {
        $redis = [
            'host'          => '127.0.0.1',
            'port'          => '6379',
            'auth'          => '',
            'db'            => 1,//选择数据库,默认为0
            'intervalCheckTime'    => 30 * 1000,//定时验证对象是否可用以及保持最小连接的间隔时间
            'maxIdleTime'          => 15,//最大存活时间,超出则会每$intervalCheckTime/1000秒被释放
            'maxObjectNum'         => 20,//最大创建数量
            'minObjectNum'         => 5,//最小创建数量 最小创建数量不能大于等于最大创建
        ];
        $this->response()->write(json_encode($redis));
    }

}