<?php return array (
  'MYSQL' => 
  array (
    'host' => '127.0.0.1',
    'port' => 3306,
    'user' => 'root',
    'password' => 'asdfghjkl',
    'database' => 'easyswoole',
    'timeout' => 5,
    'charset' => 'utf8mb4',
    'POOL_MAX_NUM' => 20,
    'POOL_TIME_OUT' => 1,
  ),
  'REDIS' => 
  array (
    'host' => '127.0.0.1',
    'port' => '6379',
    'auth' => '',
    'db' => 1,
    'intervalCheckTime' => 30000,
    'maxIdleTime' => 15,
    'maxObjectNum' => 20,
    'minObjectNum' => 5,
  ),
  'MAIN_SERVER' => 
  array (
    'LISTEN_ADDRESS' => '0.0.0.0',
    'PORT' => 9501,
    'SERVER_TYPE' => 2,
    'SOCK_TYPE' => 1,
    'RUN_MODEL' => 2,
    'SETTING' => 
    array (
      'worker_num' => 8,
      'reload_async' => true,
      'max_wait_time' => 3,
    ),
    'TASK' => 
    array (
      'workerNum' => 4,
      'maxRunningNum' => 128,
      'timeout' => 15,
    ),
  ),
  'SERVER_NAME' => 'EasySwoole',
  'TEMP_DIR' => NULL,
  'LOG_DIR' => NULL,
);