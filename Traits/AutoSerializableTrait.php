<?php

namespace Sam\DataObjects\Traits;

use Sam\DataObjects\Casters\ArrayableCaster;
use Sam\DataObjects\Casters\JsonSerializableCaster;
use Sam\DataObjects\Exceptions\InvalidArgumentException;
use Sam\DataObjects\SerializableContext;
use Sam\DataObjects\SerializableResolver;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Trait providing automatic serialization functionality for DTOs.
 * Implements property extraction and custom type casting for object serialization.
 */
trait AutoSerializableTrait
{
    use UsesPropertyExtractorTrait;


    /**
     * Get the list of caster classes used for serialization.
     *
     * @return array<class-string> Array of caster class names
     */
    protected function getCasts(): array
    {
        return [
            ArrayableCaster::class,
            JsonSerializableCaster::class,
        ];
    }


    /**
     * @throws InvalidArgumentException
     * @throws BindingResolutionException
     */
    public function autoSerializable(SerializableContext $context): mixed
    {
        $propertyExtractor = $this->getPropertyExtractor();
        return (new SerializableResolver($this->getCasts(), $propertyExtractor))->resolve($this, $context);
    }
}
