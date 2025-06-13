<?php

declare(strict_types=1);

namespace Sam\DataObjects;

use Sam\DataObjects\Contracts\AttributeCaster;
use Sam\DataObjects\Contracts\AutoSerializable as AutoSerializableInterface;
use Sam\DataObjects\Contracts\Caster;
use Sam\DataObjects\Contracts\SkipsSerialization;
use Sam\DataObjects\Exceptions\InvalidArgumentException;

final class SerializableResolver
{
    private CasterRegistry $globalCasterRegistry;
    private PropertyExtractor $propertyExtractor;

    /**
     * @param array<class-string<Caster>|Caster> $globalCasters
     * @throws InvalidArgumentException
     */
    public function __construct(array $globalCasters, PropertyExtractor $propertyExtractor)
    {
        $this->globalCasterRegistry = new CasterRegistry($globalCasters);
        $this->propertyExtractor = $propertyExtractor;
    }

    public function resolve(AutoSerializableInterface $object, SerializableContext $context): mixed
    {
        $context->startCircularDetect($object);

        try {
            $properties = $this->propertyExtractor->getProperties($object);
            $result = [];

            foreach ($properties as $dtoProp) {
                $propertyName = $dtoProp->name();
                $value = $dtoProp->reflectionProperty()->getValue($object);

                if ($this->shouldSkipProperty($value, $dtoProp)) {
                    continue;
                }

                $attributeCasters = $this->getCastersFromAttributes($dtoProp);
                if (!empty($attributeCasters)) {
                    $value = (new CasterRegistry($attributeCasters))->applyCasters($value);
                }

                $result[$propertyName] = $this->resolveRecursively($value, $context);
            }

            return $result;
        } finally {
            $context->endCircularDetect($object);
        }
    }

    private function resolveRecursively(mixed $value, SerializableContext $context): mixed
    {
        if ($value instanceof AutoSerializableInterface) {
            return $value->autoSerializable($context);
        }

        if (is_iterable($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                $result[$key] = $this->resolveRecursively($item, $context);
            }
            return $result;
        }

        return $this->globalCasterRegistry->applyCasters($value);
    }

    private function shouldSkipProperty(mixed $value, DtoProperty $dtoProp): bool
    {
        foreach ($dtoProp->attributes() as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance instanceof SkipsSerialization && $instance->shouldSkip($value, $dtoProp)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return list<Caster>
     */
    private function getCastersFromAttributes(DtoProperty $dtoProp): array
    {
        $casters = [];
        foreach ($dtoProp->attributes() as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance instanceof AttributeCaster) {
                $casters[] = $instance;
            }
        }
        return $casters;
    }
}
