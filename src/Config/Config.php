<?php

namespace rebuild\Config;

use rebuild\Contract\ConfigInterface;

class Config implements ConfigInterface
{
    protected array $config = [];

    public function __construct(array $configs)
    {
        $this->config = $configs;
    }

    public function get(string $key, $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function has(string $keys): bool
    {
        return isset($this->config[$keys]);
    }

    public function set(string $key, $value)
    {
        $this->config[$key] = $value;
    }

}