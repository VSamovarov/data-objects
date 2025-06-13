<?php

namespace Sam\DataObjects\Contracts;

/**
 * The `SkipsSerialization` interface allows conditional exclusion of properties
 * during the serialization of a DTO into an array or JSON.
 *
 * This interface can be implemented by:
 * - Attributes applied to DTO properties (e.g. #[SkipIfNull])
 * - Cast classes that implement value transformation and also determine
 *   if a value should be skipped during serialization
 *
 * Implementing classes define custom logic to determine whether a property
 * should be excluded—for example, if the value is `null`, empty, or based
 * on more complex logic.
 *
 * Example (used as an attribute):
 * --------------------------------
 * #[SkipIfNull]
 * public ?string $email;
 *
 * class SkipIfNull implements SkipsSerialization {
 *     public function shouldSkip(mixed $value): bool {
 *         return $value === null;
 *     }
 * }
 *
 * Example (used inside a Cast):
 * -----------------------------
 * class NullToEmptyStringCast implements Cast, SkipsSerialization {
 *     public function isSupported(mixed $value): bool {
 *         return is_null($value);
 *     }
 *
 *     public function cast(mixed $value): mixed {
 *         return '';
 *     }
 *
 *     public function shouldSkip(mixed $value): bool {
 *         return false; // Always included after cast
 *     }
 * }
 */
interface SkipsSerialization
{
    /**
     * Determines whether the given property should be excluded from serialization.
     *
     * @param mixed $value The value of the property.
     * @return bool Returns `true` if the property should be skipped; otherwise, `false`.
     */
    public function shouldSkip(mixed $value): bool;
}
