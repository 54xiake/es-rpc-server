<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-11
 * Time: 19:51
 */

namespace App\Service;


use EasySwoole\EasySwoole\Config;
use EasySwoole\Rpc\AbstractService;

class CarService extends AbstractService
{
    public function version():string
    {
        return '2.0';
    }

    public function serviceName(): string
    {
        return Config::getInstance()->getConf('SERVER_NAME').'.CarService';
    }

    public function getCarInfo() {
        $this->response()->setResult([
            'name'=>'宝马X5',
            'brand'=>'宝马'
        ]);
    }
}