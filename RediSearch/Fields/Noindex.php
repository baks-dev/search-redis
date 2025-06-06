<?php

namespace BaksDev\SearchRedis\RediSearch\Fields;

trait Noindex
{
    protected $isNoindex = false;

    public function isNoindex(): bool
    {
        return $this->isNoindex;
    }

    public function setNoindex(bool $noindex)
    {
        $this->isNoindex = $noindex;
        return $this;
    }
}
