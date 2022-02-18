<?php

namespace rebuild\Config;

use Symfony\Component\Finder\Finder;

class ConfigFactory
{
    public function __invoke(): Config
    {
        $basePath     = BASE_PATH . "/config";
        $configFile   = $this->readConfig($basePath . '/config.php');
        $autoloadFile = $this->readPath([$basePath . '/autoload']);
        $configs      = array_merge_recursive($configFile, $autoloadFile);
        return new Config($configs);
    }

    /**
     * 读取config配置文件
     * @param string $string
     * @return array
     */
    protected function readConfig(string $string): array
    {
        $config = require $string;
        if(!is_array($config)) {
            return [];
        }
        return $config;
    }

    /**
     * 获取autoload文件夹配置
     * @param array $dirs
     * @return array
     */
    protected function readPath(array $dirs): array
    {
        $config = [];
        $finder = new Finder;
        $finder->files()->in($dirs)->name('*.php');
        foreach($finder as $fileInfo) {
            $key = $fileInfo->getBasename('.php');
            $config[$key] = require $fileInfo->getRealPath();
        }
        return $config;
    }
}