<?php

declare(strict_types=1);

namespace Sam\DataObjects\Casters;

use Sam\DataObjects\Contracts\Caster as CasterInterface;
use Sam\DataObjects\Exceptions\InvalidArgumentException;
use Illuminate\Contracts\Support\Arrayable;

final class ArrayableCaster implements CasterInterface
{
    public function isSupported(mixed $value): bool
    {
        return $value instanceof Arrayable;
    }

    public function cast(mixed $value): mixed
    {
        if (!($value instanceof Arrayable)) {
            throw new InvalidArgumentException('Value must be an instance of Arrayable');
        }
        return $value->toArray();
    }
}
