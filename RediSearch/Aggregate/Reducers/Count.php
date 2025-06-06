<?php

namespace BaksDev\SearchRedis\RediSearch\Aggregate\Reducers;

use BaksDev\SearchRedis\RediSearch\CanBecomeArrayInterface;

class Count implements CanBecomeArrayInterface
{
    use Aliasable;

    private $group;
    protected $reducerKeyword = 'COUNT';

    public function __construct(int $group)
    {
        $this->group = $group;
    }

    public function toArray(): array
    {
        return ['REDUCE', $this->reducerKeyword, $this->group, 'AS', empty($this->alias) ? 'count' : $this->alias];
    }
}
