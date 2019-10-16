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

class Health extends Controller
{

    function index()
    {
        // TODO: Implement index() method.
//        $this->response()->write('hello world');
        $this->writeJson(200, [], 'success');
    }
}