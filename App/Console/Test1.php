<?php
/**
 * Created by PhpStorm.
 * User: 54xiake
 * Date: 2019-10-11
 * Time: 13:33
 */

namespace App\Console;


use EasySwoole\Console\Console;
use EasySwoole\Console\ModuleInterface;

class Test1 implements ModuleInterface
{

    public function moduleName(): string
    {
        return 'test1';
    }

    public function exec(array $arg, int $fd, Console $console)
    {
        return 'this is test exec';
    }

    public function help(array $arg, int $fd, Console $console)
    {
        return 'this is test help';
    }
}
