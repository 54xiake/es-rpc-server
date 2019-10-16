<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-11
 * Time: 14:27
 */

namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\AbstractRouter;
use FastRoute\RouteCollector;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        //开启全局模式拦截
        //全局模式拦截下,路由将只匹配Router.php中的控制器方法响应,将不会执行框架的默认解析
        $this->setGlobalMode(false);

        $routeCollector->get('/user', '/index.html');
        $routeCollector->get('/rpc', '/Rpc/index');

        $routeCollector->get('/', function (Request $request, Response $response) {
            $response->write('this router index');
        });
        $routeCollector->get('/test', function (Request $request, Response $response) {
            $response->write('this router test');
            return '/a';//重新定位到/a方法
        });
        $routeCollector->get('/user/{id:\d+}', function (Request $request, Response $response) {
            $response->write("this is router user ,your id is {$request->getQueryParam('id')}");//获取到路由匹配的id
            return false;//不再往下请求,结束此次响应
        });

        // 拦截GET方法
        $routeCollector->addRoute('GET', '/router1', '/Index');

        // 拦截POST方法
        $routeCollector->addRoute('POST', '/router2', '/Index');

        // 拦截多个方法
        $routeCollector->addRoute(['GET', 'POST'], '/router', '/Index');

//        $this->setMethodNotAllowCallBack(function (Request $request,Response $response){
//            $response->write('未找到处理方法');
//            return false;//结束此次响应
//        });
//        $this->setRouterNotFoundCallBack(function (Request $request,Response $response){
//            $response->write('未找到路由匹配');
//            return 'index';//重定向到index路由
//        });

    }
}