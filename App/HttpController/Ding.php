<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-11-13
 * Time: 10:56
 */

namespace App\HttpController;


use App\Common\Dingding;
use EasySwoole\Http\AbstractInterface\Controller;

class Ding extends Controller
{

    function index()
    {
        // TODO: Implement index() method.
    }

    function send() {
        $ding = new Dingding();
        $data['content'] = 'test';
        $ding->sendMessage($data, 'test');
    }
}