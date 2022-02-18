<?php

use rebuild\Command\StartCommand;
use rebuild\Config\ConfigFactory;
use Symfony\Component\Console\Application;

require 'vendor/autoload.php';

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__,1));
//! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

//利用symfony的命令行插件增加start命令
$application = new Application;
//配置工厂加载配置
$config = new ConfigFactory();
$config = $config();

//遍历注册命令行
$commands = $config->get('commands');
foreach($commands as $command) {
    if($command === StartCommand::class){
        $application->add(new $command($config));
    }else{
        $application->add(new $command);
    }
}
//启动服务
$application->run();