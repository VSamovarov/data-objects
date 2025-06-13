<?php

declare(strict_types=1);

namespace Sam\DataObjects\Casters;

use Sam\DataObjects\Contracts\Caster as CasterInterface;
use Sam\DataObjects\Exceptions\InvalidArgumentException;
use JsonSerializable;

final class JsonSerializableCaster implements CasterInterface
{
    public function isSupported(mixed $value): bool
    {
        return $value instanceof JsonSerializable;
    }

    /**
     * @param mixed $value
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function cast(mixed $value): mixed
    {
        if (!($value instanceof JsonSerializable)) {
            throw new InvalidArgumentException('Value must be an instance of JsonSerializable');
        }
        return $value->jsonSerialize();
    }
}
