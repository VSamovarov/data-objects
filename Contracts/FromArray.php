<?php

namespace Sam\DataObjects\Contracts;

interface FromArray
{
    /**
     * Creates an object from an array of data.
     *
     * @param array<string, mixed> $data Data to fill out an object.
     * @return static A copy of the implementing class.
     */
    public static function fromArray(array $data): static;

}
