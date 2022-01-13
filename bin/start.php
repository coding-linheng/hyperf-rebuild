<?php

use rebuild\Command\StartCommand;
use rebuild\Config\ConfigFactory;
use Symfony\Component\Console\Application;

require 'vendor/autoload.php';

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__,1));
//! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

$application = new Application;
$config = new ConfigFactory();
$config = $config();

$commands = $config->get('commands');
foreach($commands as $command) {
    if($command === StartCommand::class){
        $application->add(new $command($config));
    }else{
        $application->add(new $command);
    }
}
$application->run();