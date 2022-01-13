<?php

namespace app;

class Bar
{
    public function test(): int
    {
        return (new Foo)->test();
    }
}