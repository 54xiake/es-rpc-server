<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-11
 * Time: 11:20
 */

namespace App\Process;


use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\FastCache\Cache;
use Swoole\Timer;

class TestJob extends AbstractProcess
{

    protected function run($arg)
    {
        Timer::tick(1000, function () {
            $this->runJob();
        });
    }

    protected function runJob() {
        $job = Cache::getInstance()->getJob('siam_queue');// Job对象或者null
        if ($job === null){
            echo "没有任务\n";
        }else{
            // 执行业务逻辑
            var_dump($job);
            // 执行完了要删除或者重发，否则超时会自动重发
            Cache::getInstance()->deleteJob($job);
        }
    }
}