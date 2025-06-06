<?php

namespace BaksDev\SearchRedis\RediSearch\Aggregate\Operations;

use BaksDev\SearchRedis\RediSearch\CanBecomeArrayInterface;

class Apply implements CanBecomeArrayInterface
{
    public $expression;
    public $asFieldName;

    public function __construct(string $expression, string $asFieldName)
    {
        $this->expression = $expression;
        $this->asFieldName = $asFieldName;
    }

    public function toArray(): array
    {
        return ['APPLY', $this->expression, 'AS', $this->asFieldName];
    }
}
