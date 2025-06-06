<?php

namespace BaksDev\SearchRedis\RediSearch\Aggregate\Operations;

class GroupBy extends AbstractFieldNameOperation
{
    public function __construct(array $fieldNames)
    {
        parent::__construct('GROUPBY', $fieldNames);
    }
}
