<?php

namespace app\Controller;

class HelloController
{
    /**
     * @path /hello/index
     * @return string
     */
    public function index(): string
    {
        return 'hello world';
    }

    public function hyperf(): string
    {
        return 'hello hyperf';
    }
}