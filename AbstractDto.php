<?php

declare (strict_types=1);

namespace Sam\DataObjects;

use Sam\DataObjects\Contracts\AutoSerializable as AutoSerializableInterface;
use Sam\DataObjects\Contracts\CloneableWith as CloneableWithInterface;
use Sam\DataObjects\Contracts\Dto as DtoInterface;
use Sam\DataObjects\Exceptions\InvalidArgumentException;
use Sam\DataObjects\Traits\AutoSerializableTrait;
use Sam\DataObjects\Traits\CloneableWithTrait;
use Illuminate\Contracts\Container\BindingResolutionException;

abstract readonly class AbstractDto implements DtoInterface, AutoSerializableInterface, CloneableWithInterface
{
    use CloneableWithTrait;
    use AutoSerializableTrait;

    /**
     * @throws InvalidArgumentException
     * @throws BindingResolutionException
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @throws InvalidArgumentException
     * @throws BindingResolutionException
     */
    final public function toArray(): array
    {
        return $this->autoSerializable(new SerializableContext());
    }
}
