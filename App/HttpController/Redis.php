<?php
/**
 * Created by PhpStorm.
 * User: yugang
 * Date: 2019-11-08
 * Time: 11:45
 */

namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;

class Redis extends Controller
{

    function index()
    {
        // TODO: Implement index() method.
        $redis = \EasySwoole\RedisPool\Redis::defer('redis');
        $redis->set('key', 123);
        $result = $redis->get('key');
        $this->writeJson(1, $result);
    }

    function incr() {
        $redis = \EasySwoole\RedisPool\Redis::defer('redis');
        $result = $redis->incr('key');
        $this->writeJson(1, $result);
    }
}