<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-16
 * Time: 09:44
 */

namespace App\Common;


use EasySwoole\EasySwoole\Config;

class DB
{
    private $container = [];

    private static $instance;

    public static function getInstance()
    {
        if(!isset(self::$instance)){
            self::$instance = new DB();
        }
        return self::$instance;
    }

    function dbCon():\EasySwoole\Mysqli\Client
    {
        $cid = \co::getCid();
        if(!isset($this->container[$cid])){
            $configData = Config::getInstance()->getConf('MYSQL');
            $config = new \EasySwoole\Mysqli\Config($configData);
            $client = new \EasySwoole\Mysqli\Client($config);
            $client->connect();
            $this->container[$cid] = $client;
            defer(function (){
                $this->destroy();
            });
        }
        return $this->container[$cid];
    }

    function destroy()
    {
        $cid = \co::getCid();
        if(!isset($this->container[$cid])){
            unset($this->container[$cid]);
        }
    }
}