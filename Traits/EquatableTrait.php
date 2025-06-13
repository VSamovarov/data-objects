<?php

namespace Sam\DataObjects\Traits;

use Sam\DataObjects\Contracts\Equatable;

/**
 * Trait EquatableTrait
 *
 * Implements functionality for checking equality of objects belonging to the same class.
 * Uses the values of object properties for comparison. This makes the trait particularly useful
 * for Data Transfer Objects (DTOs), where comparisons are typically value-based.
 *
 * Key features:
 * - Compares objects that implement the Equatable interface.
 * - Supports comparison of both primitive values and nested objects/arrays.
 * - Prioritizes the `equals` method if it's implemented on property objects.
 */
trait EquatableTrait
{
    use UsesPropertyExtractorTrait;

    /**
     */
    public function equals(mixed $other): bool
    {
        // Return false if $other isn't an instance of the current class
        if (!$other instanceof static) {
            return false;
        }

        // Use the PropertyExtractor to retrieve the properties of the current object
        $propertyExtractor = $this->getPropertyExtractor();
        $properties = $propertyExtractor->getProperties($this);

        // Compare each property one by one
        foreach ($properties as $property) {
            $thisValue = $property->getValue($this); // Value from the current object
            $otherValue = $property->getValue($other); // Value from the other object

            // If the types of the values don't match, objects are not equal
            if (gettype($thisValue) !== gettype($otherValue)) {
                return false;
            }

            // If both values are objects but their classes differ, objects are not equal
            if (is_object($thisValue) && get_class($thisValue) !== get_class($otherValue)) {
                return false;
            }

            // If the value implements Equatable or has an equals method, use it for comparison
            if ($thisValue instanceof Equatable || is_object($thisValue) && method_exists($thisValue, 'equals')) {
                if (!$thisValue->equals($otherValue)) {
                    return false;
                }
            } else {
                // If equals is not available, perform strict comparison or compare serialized values
                if ($thisValue !== $otherValue && serialize($thisValue) !== serialize($otherValue)) {
                    return false;
                }
            }
        }

        // Return true if all properties are equal
        return true;
    }
}
