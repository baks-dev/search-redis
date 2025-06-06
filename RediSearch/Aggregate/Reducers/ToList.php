<?php

namespace BaksDev\SearchRedis\RediSearch\Aggregate\Reducers;

class ToList extends AbstractFieldNameReducer
{
    protected $reducerKeyword = 'TOLIST';

    public function toArray(): array
    {
        return ['REDUCE', $this->reducerKeyword, '1', $this->fieldName, 'AS', $this->makeAlias()];
    }
}
