<?php

namespace Sam\DataObjects\Traits;

use Sam\DataObjects\PropertyExtractor;
use Sam\DataObjects\PropertyExtractorResolver;

/**
 * Provides a reusable method to access the PropertyExtractor instance.
 * Centralizes retrieval via PropertyExtractorResolver for consistency and flexibility.
 * Allows easy sharing of PropertyExtractor logic across multiple traits or classes.
 */
trait UsesPropertyExtractorTrait
{
    protected function getPropertyExtractor(): PropertyExtractor
    {
        return PropertyExtractorResolver::get();
    }
}
