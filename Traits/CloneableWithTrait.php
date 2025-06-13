<?php

/**
 * Provides functionality to create modified copies of DTO objects with new property values.
 *
 * This trait supports creating copies of read-only DTOs by generating new instances with modified properties,
 * rather than modifying the existing object. It works with constructor-based property initialization.
 *
 * The implementation uses positional arguments via ReflectionClass::newInstanceArgs() rather than named arguments.
 *
 * Limitations:
 * - Does not support private properties that are not declared through the constructor
 *
 * Example usage:
 * ```php
 * $dto = new UserDto(id: 1, name: 'Alice');
 * $admin = $dto->cloneWith(['role' => 'admin']); // Creates new instance with modified role
 * $copy = $dto->clone(); // Creates exact copy
 * ```
 */

namespace Sam\DataObjects\Traits;

use Sam\DataObjects\Exceptions\InvalidDtoStyleException;
use Sam\DataObjects\Exceptions\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

trait CloneableWithTrait
{
    use UsesPropertyExtractorTrait;

    /**
     * Clones the current instance with the provided overrides, allowing selective
     * modification of properties while preserving others.
     *
     * @param array $overrides An associative array of property names and their
     *                         respective override values.
     * @return static A new instance of the class with the specified overrides applied.
     *
     * @throws InvalidArgumentException If named parameters are not used for overrides.
     * @throws InvalidArgumentException If a value for a required parameter is not provided.
     * @throws InvalidArgumentException If the override array contains unmapped parameters.
     * @throws ReflectionException
     * @throws InvalidDtoStyleException
     */
    public function cloneWith(array $overrides): static
    {
        if (count($overrides) > 0 && array_is_list($overrides)) {
            throw new InvalidArgumentException(
                'The method `cloneWith()` supports only named parameters.'
            );
        }

        $class = $this::class;

        $propertyExtractor = $this->getPropertyExtractor();
        $properties = $propertyExtractor->getProperties($this);
        $args = [];

        foreach ($properties as $property) {
            $name = $property->name();

            if (array_key_exists($name, $overrides)) {
                $args[$name] = $overrides[$name];
            } elseif (property_exists($this, $name)) {
                $args[$name] = $this->{$name};
            } elseif ($property->parameter?->isDefaultValueAvailable()) {
                $args[$name] = $property->defaultValue();
            } else {
                throw new InvalidArgumentException("The value for the parameter is not conveyed `$name`.");
            }
        }

        $unmappedParameters = array_diff_key($overrides, $args);
        if ($unmappedParameters) {
            throw new InvalidArgumentException(
                sprintf(
                    'The parameters "%s" are not present in the DTO and cannot be used for cloning.',
                    implode('", "', array_keys($unmappedParameters))
                )
            );
        }
        return (new ReflectionClass($class))->newInstanceArgs($args);
    }

    /**
     * Creates a clone of the current instance without applying any overrides to its properties.
     *
     * Delegates to the `cloneWith` method with an empty array, ensuring the cloned
     * instance maintains the same property values as the original instance.
     *
     * @return static A new instance that is an exact duplicate of the current instance.
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws InvalidDtoStyleException
     */
    public function clone(): static
    {
        return $this->cloneWith([]);
    }
}
