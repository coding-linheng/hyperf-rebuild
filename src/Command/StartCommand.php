<?php

namespace rebuild\Command;

use rebuild\Config\Config;
use rebuild\Server\ServerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends Command
{

    protected Config $config;

    public function __construct(Config $config)
    {
        parent::__construct();
        $this->config = $config;
    }


    protected function configure()
    {
        $this->setName('start')->setDescription('启动服务')->setHelp("This command allows you to create users...");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //获取server服务配置
        $config        = $this->config;
        $serverConfig  = $config->get('server');
        $serverFactory = new ServerFactory;
        //进行server服务初始化/事件注册
        $serverFactory->configure($serverConfig);
        //启动服务
        $serverFactory->getServer()->start();
        return 1;
    }
}