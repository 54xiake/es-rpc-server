<?php
////同步apollo配置
$commandArgs = $argv;
array_shift($commandArgs);
\EasySwoole\EasySwoole\Command\CommandContainer::getInstance()->set(new \App\Command\LoadApolloConfig());
\EasySwoole\EasySwoole\Command\CommandContainer::getInstance()->hook('LoadApolloConfig', $commandArgs);