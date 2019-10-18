<?php
/**
 * Created by PhpStorm.
 * User: yugang
 * Date: 2019-10-17
 * Time: 10:38
 */

namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;

class Apollo extends Controller
{

    function index()
    {
        go(function (){
            //配置apollo服务器信息
            $server = new \EasySwoole\Apollo\Server([
                'server'=>'http://127.0.0.1:8080',
                'appId'=>'es-rpc-server-1'
            ]);
            //创建apollo客户端
            $apollo = new \EasySwoole\Apollo\Apollo($server);
            //第一次同步
            var_dump( $apollo->sync('application'));
            //第二次同步，若服务端没有改变，那么返回的结果，isModify标记为fasle，并带有lastReleaseKey
            var_dump( $apollo->sync('application'));
        });
    }
}