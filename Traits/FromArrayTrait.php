<?php

namespace Sam\DataObjects\Traits;

use Sam\DataObjects\Contracts\FromArray;
use Sam\DataObjects\DtoProperty;
use Sam\DataObjects\Exceptions\InvalidArgumentException;
use Sam\DataObjects\Exceptions\InvalidDtoStyleException;
use Sam\DataObjects\PropertyExtractorResolver;
use ReflectionClass;
use ReflectionException;

trait FromArrayTrait
{
    /**
     * Creates an object from an array of data.
     *
     * @param array<string, mixed> $data Data to fill out an object.
     * @return static A copy of the implementing class.
     * @throws ReflectionException|InvalidArgumentException|InvalidDtoStyleException
     */
    public static function fromArray(array $data): static
    {
        $className = static::class;
        $propertyExtractor = PropertyExtractorResolver::get();
        $properties = $propertyExtractor->getProperties($className);

        self::validateProperties($properties, $data);

        $args = static::mapPropertiesToArguments($properties, $data);
        return static::createInstance($className, $args);
    }

    /**
     * Maps properties of the class to constructor arguments.
     *
     * @param array<DtoProperty> $properties
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws InvalidArgumentException|ReflectionException
     */
    private static function mapPropertiesToArguments(array $properties, array $data): array
    {
        $args = [];

        foreach ($properties as $property) {
            $name = $property->name();
            if (array_key_exists($name, $data)) {
                $value = static::getValueForProperty($property, $data[$name]);
            } elseif ($property->isOptional()) {
                $value = $property->defaultValue();
            } else {
                throw new InvalidArgumentException("Missing required property '{$name}' for DTO " . static::class);
            }

            if ($value === null && !$property->isNullable()) {
                throw new InvalidArgumentException("'{$name}' property should not be Nullable for DTO " . static::class);
            }

            $args[$name] = $value;
        }
        return $args;
    }


    /**
     * @param DtoProperty $property
     * @param mixed $value
     * @return mixed
     */
    private static function getValueForProperty(DtoProperty $property, mixed $value): mixed
    {
        $parameterType = $property->type();
        if ($value !== null && $parameterType && !$parameterType->isBuiltin()) {
            return static::resolveComplexType($property, $value);
        }
        return $value;
    }

    /**
     * Resolves a complex type for a given parameter.
     *
     * @param DtoProperty $property
     * @param mixed $value
     * @return mixed
     */
    private static function resolveComplexType(DtoProperty $property, mixed $value): mixed
    {
        $typeName = $property->typeName();

        if (is_subclass_of($typeName, FromArray::class)) {
            return $typeName::fromArray($value);
        }
        return static::createObjectWithPropertyExtractor($typeName, $value);
    }

    /**
     * Creates an object using PropertyExtractor if it does not implement FromArray.
     *
     * @param string $typeName
     * @param array<string, mixed> $value
     * @return object
     * @throws InvalidArgumentException|ReflectionException|InvalidDtoStyleException
     */
    private static function createObjectWithPropertyExtractor(string $typeName, mixed $value): object
    {

        if (static::isParametricArray($value)) {
            $nestedProperties = PropertyExtractorResolver::get()->getProperties($typeName);
            $nestedArgs = static::mapPropertiesToArguments($nestedProperties, $value);
            return static::createInstance($typeName, $nestedArgs);
        }

        if (is_object($value) && $value instanceof $typeName) {
            return $value;
        }

        return new $typeName($value);
    }

    /**
     * Creates a new instance of a class using reflection.
     *
     * @param string $className
     * @param array<string, mixed> $args
     * @return object
     * @throws ReflectionException
     */
    private static function createInstance(string $className, array $args): object
    {
        $reflection = new ReflectionClass($className);
        return $reflection->newInstanceArgs($args);
    }

    /**
     * @param array $properties
     * @param array $data
     * @return void
     * @throws InvalidArgumentException
     */
    public static function validateProperties(array $properties, array $data): void
    {
        $propertyNames = array_map(fn($property) => $property->name(), $properties);
        $dataKeys = array_keys($data);
        $diff = array_diff($dataKeys, $propertyNames);
        if (!empty($diff)) {
            throw new InvalidArgumentException("Unknown properties '" . implode(', ', $diff) . "' for DTO " . static::class);
        }
    }

    /**
     * Checks if the given value is a parametric (associative) array.
     *
     * Returns true if the array has non-sequential or non-integer keys.
     *
     * @param mixed $data The value to check.
     * @return bool True if it's an associative array, false otherwise.
     */
    public static function isParametricArray(mixed $data): bool
    {
        return is_array($data) && array_keys($data) !== range(0, count($data) - 1);
    }
}
