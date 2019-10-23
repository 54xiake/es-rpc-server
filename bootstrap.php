<?php
//apollo配置
define('APOLLO_DEV_SERVER', 'http://127.0.0.1:8080');
define('APOLLO_PRO_SERVER', 'http://127.0.0.1:8080');
define('APOLLO_APP_ID', 'es-rpc-server');
//同步apollo配置
$commandArgs = $argv;
\EasySwoole\EasySwoole\Command\CommandContainer::getInstance()->set(new \App\Command\LoadApolloConfig());
\EasySwoole\EasySwoole\Command\CommandContainer::getInstance()->hook('LoadApolloConfig', $commandArgs);