<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-14
 * Time: 10:20
 */

namespace App\Service;


use EasySwoole\Rpc\AbstractService;

class BaseService extends AbstractService
{

    public function serviceName(): string
    {

        return 'Demo.CarService';
    }
}