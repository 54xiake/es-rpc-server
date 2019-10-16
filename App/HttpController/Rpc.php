<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-11
 * Time: 14:31
 */

namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;

class Rpc extends Controller
{

    function index()
    {
        $this->response()->write('hello rpc');
    }
}