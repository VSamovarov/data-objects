<?php

namespace Sam\DataObjects\Contracts;

interface Equatable
{

    /**
     * Checks equality with another object of the same class.
     *
     * @param static $other Object of the same class to compare with
     */
    public function equals(mixed $other): bool;
}
