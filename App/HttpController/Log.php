<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-11-06
 * Time: 16:45
 */

namespace App\HttpController;


use App\Common\Log\Logger;
use EasySwoole\Http\AbstractInterface\Controller;

class Log extends Controller
{
    function index()
    {
        Logger::getInstance()->log('=========aaaa', Logger::LOG_LEVEL_ERROR);
        Logger::getInstance()->console('=========aaaabbb', Logger::LOG_LEVEL_INFO);
        Logger::getInstance()->console('=========aaaabbb', Logger::LOG_LEVEL_NOTICE);
        Logger::getInstance()->console('=========aaaabbb', Logger::LOG_LEVEL_WARNING);
        Logger::getInstance()->console('=========aaaabbb', Logger::LOG_LEVEL_ERROR);
    }
}