<?php

namespace BaksDev\SearchRedis\RediSearch;

interface CanBecomeArrayInterface
{
    public function toArray(): array;
}
