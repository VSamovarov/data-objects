<?php

declare(strict_types=1);

namespace Sam\DataObjects\Attributes;

use Sam\DataObjects\Contracts\SkipsSerialization;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Skip implements SkipsSerialization
{
    public function shouldSkip(mixed $value): bool
    {
        return true;
    }
}
