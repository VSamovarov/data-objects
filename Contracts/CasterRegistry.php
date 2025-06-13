<?php

namespace Sam\DataObjects\Contracts;

interface CasterRegistry
{
    public function applyCasters(mixed $value): mixed;
}
