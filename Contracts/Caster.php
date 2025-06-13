<?php

declare(strict_types=1);

/**
 * Interface Caster
 *
 * Defines a contract for implementing custom data transformation (casting).
 * Classes implementing this interface should determine if they can handle a specific property
 * and provide appropriate logic to transform or handle the value.
 */

namespace Sam\DataObjects\Contracts;

use Sam\DataObjects\Exceptions\InvalidArgumentException;

interface Caster
{
    /**
     * Determines if the current caster supports the given property.
     *
     * This method should verify whether the property matches the criteria
     * required for the caster to process it (e.g., type checking or specific instance).
     *
     * @param mixed $value
     * @return bool Returns true if the caster can handle the given property, false otherwise.
     */

    public function isSupported(mixed $value): bool;

    /**
     * Performs the casting/transformation of the given value.
     *
     * This method should contain the logic for transforming the provided value
     * into the desired format or structure. It is expected to throw exceptions
     * if the value is invalid or casting is not possible.
     *
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public function cast(mixed $value): mixed;
}
