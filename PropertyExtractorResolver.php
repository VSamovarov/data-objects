<?php

namespace Sam\DataObjects;

use Illuminate\Support\Facades\App;

/**
 * Resolves and provides access to a shared instance of PropertyExtractor.
 *
 * This resolver serves as a lightweight and testable gateway to the application's
 * PropertyExtractor service. It acts as an adapter between decoupled consumers
 * (such as traits or standalone DTOs) and the Laravel service container,
 * allowing for consistent access to the PropertyExtractor across the codebase
 * without tightly coupling classes to the container.
 *
 * Key responsibilities:
 * - Lazily resolves PropertyExtractor via the Laravel container (App::make).
 * - Allows explicit override of the instance (e.g., for testing or customization).
 * - Provides a reset mechanism to clear the override and fall back to the container.
 *
 * Usage scenarios:
 * - Traits or utility classes needing a PropertyExtractor without constructor injection.
 * - Global access pattern where traditional DI is not feasible or practical.
 * - Testing scenarios where a mock PropertyExtractor must be injected temporarily.
 *
 * Example usage:
 * ```php
 * $extractor = PropertyExtractorResolver::get();
 * $properties = $extractor->getProperties($dto);
 *
 * // Override for testing:
 * PropertyExtractorResolver::set(new MockPropertyExtractor());
 *
 * // Reset after test:
 * PropertyExtractorResolver::reset();
 * ```
 */
class PropertyExtractorResolver
{
    /**
     * The explicitly set PropertyExtractor instance, if any.
     *
     * @var PropertyExtractor|null
     */
    protected static ?PropertyExtractor $override = null;

    /**
     * Get the current PropertyExtractor instance.
     *
     * If no override is set, it will be resolved from the Laravel container.
     *
     * @return PropertyExtractor
     */
    public static function get(): PropertyExtractor
    {
        return static::$override ?? App::make(PropertyExtractor::class);
    }

    /**
     * Explicitly set a custom PropertyExtractor instance.
     *
     * This is useful for injecting mocks or alternative implementations,
     * especially in testing environments.
     *
     * @param PropertyExtractor $extractor
     * @return void
     */
    public static function set(PropertyExtractor $extractor): void
    {
        static::$override = $extractor;
    }

    /**
     * Reset the resolver to default behavior.
     *
     * Removes any manually set instance and reverts to resolving from the container.
     *
     * @return void
     */
    public static function reset(): void
    {
        static::$override = null;
    }
}
