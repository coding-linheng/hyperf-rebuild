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
        $config        = $this->config;
        $serverConfig  = $config->get('server');
        $serverFactory = new ServerFactory;
        $serverFactory->configure($serverConfig);
        $serverFactory->getServer()->start();
        return 1;
    }
}