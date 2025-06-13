<?php

declare (strict_types=1);

namespace Sam\DataObjects;

use ReflectionAttribute;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionParameter;
use ReflectionException;
use ReflectionUnionType;

/**
 * The DtoProperty class encapsulates information about a DTO property,
 * based on its constructor parameter and (optionally) a declared public property.
 *
 * It provides a unified interface for accessing property metadata,
 * regardless of whether it's declared using promoted properties or traditional public properties.
 *
 * This is used in mechanisms such as serialization, deserialization, validation,
 * documentation generation, and DTO logging.
 *
 * @internal
 */
final readonly class DtoProperty
{
    public function __construct(
        public string               $name,
        public ReflectionProperty   $property,
        public ?ReflectionParameter $parameter = null,
    )
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the ReflectionParameter of the constructor parameter
     * corresponding to this property.
     *
     * @return ReflectionParameter
     */
    public function reflectionParameter(): ReflectionParameter
    {
        return $this->parameter;
    }

    /**
     * Returns the property if the property is explicitly declared in the class.
     *
     * May be null if the DTO uses promoted properties only.
     *
     * @return ReflectionProperty|null
     */
    public function reflectionProperty(): ?ReflectionProperty
    {
        return $this->property;
    }

    /**
     * @return ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType|null
     */
    public function type()
    {
        return $this->parameter?->getType() ?? $this->property->getType();
    }

    public function typeName(): ?string
    {
        return $this->type()?->getName();
    }

    public function isNullable(): bool
    {
        return $this->type()?->allowsNull() ?? true;
    }

    /**
     * @param string|null $attributeClass
     * @return ReflectionAttribute[]
     */
    public function attributes(string $attributeClass = null): array
    {
        return $this->property->getAttributes($attributeClass);
    }

    /**
     * Returns the default value for the designer parameter if it is set.
     *
     * @return mixed|null
     * @throws ReflectionException
     */
    public function defaultValue(): mixed
    {
        if ($this->parameter?->isDefaultValueAvailable()) {
            return $this->parameter->getDefaultValue();
        }

        return null;
    }

    /**
     * Checks if the property is optional.
     * A property is considered optional if it has a default value or is nullable.
     *
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->parameter?->isDefaultValueAvailable() || $this->isNullable();
    }

    /**
     * Gets property value from the given object.
     *
     * @param object $obj
     * @return mixed
     */
    public function getValue(object $obj): mixed
    {
        return $this->property->getValue($obj);
    }
}
