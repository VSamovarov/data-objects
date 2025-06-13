<?php

namespace Sam\DataObjects;

use Sam\DataObjects\Exceptions\InvalidDtoStyleException;
use ReflectionClass;
use ReflectionProperty;
use ReflectionParameter;
use ReflectionException;

/**
 * Extracts information about DTO class properties used in its construction for further usage
 * (serialization, validation, etc.).
 *
 * Features:
 * 1. Supports two DTO styles:
 *    - Promoted parameters (public function __construct(public int $id))
 *    - Legacy approach: public properties + regular constructor (public int $id; then __construct($id))
 *
 * 2. Unified interface:
 *    Returns DtoProperty[] with complete information regardless of DTO style:
 *    - Property name
 *    - Constructor parameter
 *    - ReflectionProperty (if available)
 *
 * 3. Auto-caching:
 *    Results are cached in static storage for improved performance on repeated calls
 *
 * 4. Error handling:
 *    Throws InvalidDtoStyleException if:
 *    - Constructor is missing
 *    - Constructor parameters don't match public properties (legacy style)
 *    - Mixed promoted and non-public properties are used
 *
 * 5. Structure validation:
 *    Legacy style strictly verifies that each constructor parameter matches
 *    a public property with the same name
 *
 * @final
 */
final class PropertyExtractor
{
    /** @var array<class-string, DtoProperty[]> */
    private array $cache = [];

    public function __construct(private bool $useCache = true)
    {
    }


    /**
     * Clears the property cache for a specific class or all classes.
     *
     * @param class-string|null $class The class to clear cache for, or null to clear all
     * @return void
     */
    public function clearCache(?string $class = null): void
    {
        if ($class !== null) {
            unset($this->cache[$class]);
        } else {
            $this->cache = [];
        }
    }

    /**
     * Extracts property information from a DTO class. Returns cached results if available.
     * Supports both promoted constructor parameters and legacy public properties styles.
     *
     * @param object|string $classOrObject The DTO class name or instance to extract properties from
     * @return DtoProperty[] Array of DtoProperty objects containing name, reflection and parameter info
     * @throws InvalidDtoStyleException If DTO structure is invalid (missing constructor, mixed styles)
     * @throws ReflectionException
     */
    public function getProperties(object|string $classOrObject): array
    {
        $className = is_object($classOrObject) ? $classOrObject::class : $classOrObject;

        if ($this->useCache && isset($this->cache[$className])) {
            return $this->cache[$className];
        }

        $properties = $this->extractProperties($className);

        if ($this->useCache) {
            $this->cache[$className] = $properties;
        }

        return $properties;

    }


    /**
     * Extracts and analyzes properties from a DTO class using reflection.
     *
     * This method handles two supported DTO styles:
     * 1. Constructor promoted properties:
     *    public function __construct(public int $id)
     *
     * 2. Regular public properties with constructor:
     *    public int $id;
     *    public function __construct(int $id)
     *
     * The extraction process:
     * 1. First checks for promoted properties in constructor
     * 2. If all parameters are promoted - returns just those
     * 3. Otherwise validates that each constructor parameter matches a public property
     *
     * @param class-string $className Name of the DTO class to analyze
     * @return DtoProperty[] Array of property descriptors containing:
     *         - Property name
     *         - ReflectionProperty instance
     *         - Constructor parameter information
     * @throws InvalidDtoStyleException When:
     * @throws ReflectionException
     *         - Constructor is missing
     *         - Mixed property styles are used
     *         - Constructor parameters don't match public properties
     */
    private function extractProperties(string $className): array
    {
        $class = new ReflectionClass($className);
        $constructor = $class->getConstructor();

        if (!$constructor) {
            throw new InvalidDtoStyleException("DTO '{$className}' must define a constructor.");
        }


        $parameters = $constructor->getParameters();
        $promoted = $this->extractPromotedProperties($class, $parameters);

        // all parameters promoted return them
        if (count($promoted) === count($parameters)) {
            return array_values($promoted);
        }

        return $this->extractPublicPropertiesMatchingConstructor($class, $parameters, $promoted);
    }


    /**
     * Extracts information about promoted constructor parameters.
     *
     * @param ReflectionClass $class The reflection of the DTO class being analyzed
     * @param ReflectionParameter[] $parameters Array of constructor parameters to check for promotion
     * @return array<string, DtoProperty> Map of parameter names to their DtoProperty representations
     * @throws ReflectionException|InvalidDtoStyleException
     */
    private function extractPromotedProperties(ReflectionClass $class, array $parameters): array
    {
        $result = [];

        foreach ($parameters as $param) {
            if ($param->isPromoted()) {
                $prop = $class->getProperty($param->getName());
                if (!$prop->isPublic()) {
                    throw new InvalidDtoStyleException(
                        "DTO '{$class->getName()}' has promoted constructor parameter '{$param->getName()}', but it is not declared as public."
                    );
                }
                $result[$param->getName()] = new DtoProperty($param->getName(), $prop, $param);
            }
        }
        return $result;
    }

    /**
     * Extracts and validates properties for legacy-style DTOs.
     *
     * Validates that:
     * - Each constructor parameter has a matching public property
     * - No mixing of promoted and regular properties occurs
     *
     * Creates DtoProperty instances combining information from both the property
     * and its corresponding constructor parameter.
     *
     * @param ReflectionClass $class The reflection of the DTO class being analyzed
     * @param ReflectionParameter[] $parameters Array of constructor parameters to process
     * @param array<string, DtoProperty> $promoted Already processed promoted properties if any
     * @return DtoProperty[] Array of property descriptors for the DTO
     * @throws InvalidDtoStyleException If property validation fails
     *
     */
    private function extractPublicPropertiesMatchingConstructor(ReflectionClass $class, array $parameters, array $promoted): array
    {
        $props = $class->getProperties(ReflectionProperty::IS_PUBLIC);
        $propMap = [];

        foreach ($props as $prop) {
            $propMap[$prop->getName()] = $prop;
        }

        $result = [];

        foreach ($parameters as $param) {
            $name = $param->getName();
            if (isset($promoted[$name])) {
                $result[] = $promoted[$name];
            } elseif (isset($propMap[$name])) {
                $result[] = new DtoProperty($name, $propMap[$name], $param);
            } else {
                throw new InvalidDtoStyleException(
                    "DTO '{$class->getName()}' uses unsupported mixed or private properties. Missing public property for '{$name}'."
                );
            }
        }

        return $result;
    }
}
