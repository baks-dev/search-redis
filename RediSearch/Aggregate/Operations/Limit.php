<?php

namespace BaksDev\SearchRedis\RediSearch\Aggregate\Operations;

use BaksDev\SearchRedis\RediSearch\CanBecomeArrayInterface;

class Limit implements CanBecomeArrayInterface
{
    private $offset;
    private $pageSize;

    public function __construct(int $offset, int $pageSize)
    {
        $this->offset = $offset;
        $this->pageSize = $pageSize;
    }

    public function toArray(): array
    {
        return ['LIMIT', $this->offset, $this->pageSize];
    }
}
