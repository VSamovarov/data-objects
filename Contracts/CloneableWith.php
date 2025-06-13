<?php

namespace Sam\DataObjects\Contracts;

interface CloneableWith
{
    /**
     * Creates a copy of the current object with a reduction of these properties.
     *
     * @param array $overrides Associative array: [name => value]
     * @return static
     */
    public function cloneWith(array $overrides): static;

    /**
     * Creates an exact copy of the current object unchanged.
     *
     * @return static
     */
    public function clone(): static;
}
