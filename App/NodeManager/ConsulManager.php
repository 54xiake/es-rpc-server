<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-14
 * Time: 14:53
 */

namespace App\NodeManager;


use EasySwoole\Component\Pool\PoolConf;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\Consul\Config;
use EasySwoole\Consul\Consul;
use EasySwoole\Rpc\NodeManager\NodeManagerInterface;
use EasySwoole\Rpc\ServiceNode;

class ConsulManager implements NodeManagerInterface
{
    protected $consulKey;
    /** @var Channel */
    protected $channel;

    function __construct(string $host, $port = 8500, $auth = null, string $hashKey = '__rpcNodes', int $maxRedisNum = 10)
    {
        $this->consulKey = $hashKey;
        PoolManager::getInstance()->registerAnonymous('__rpcConsul', function (PoolConf $conf) use ($host, $port, $auth, $maxRedisNum) {
            $config = new Config([
                'IP' => '127.0.0.1',
                'port' => '8500',
                'version' => 'v1',
            ]);
            $consul = new Consul($config);
            $consul->connect();

            return $consul;
        });
    }

    function getServiceNodes(string $serviceName, ?string $version = null): array
    {
        // TODO: Implement getServiceNodes() method.
    }

    function getServiceNode(string $serviceName, ?string $version = null): ?ServiceNode
    {
        // TODO: Implement getServiceNode() method.
    }

    function deleteServiceNode(ServiceNode $serviceNode): bool
    {
        // TODO: Implement deleteServiceNode() method.
    }

    function serviceNodeHeartBeat(ServiceNode $serviceNode): bool
    {
        // TODO: Implement serviceNodeHeartBeat() method.
    }
}