<?php

namespace BaksDev\SearchRedis\RediSearch\Aggregate\Operations;

use BaksDev\SearchRedis\RediSearch\CanBecomeArrayInterface;

class Filter implements CanBecomeArrayInterface
{
    public $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    public function toArray(): array
    {
        return ['FILTER', $this->expression];
    }
}
