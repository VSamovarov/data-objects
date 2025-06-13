<?php

declare(strict_types=1);

namespace Sam\DataObjects;

use Sam\DataObjects\Contracts\Caster;
use Sam\DataObjects\Contracts\CasterRegistry as CasterRegistryInterface;
use Sam\DataObjects\Exceptions\InvalidArgumentException;

final class CasterRegistry implements CasterRegistryInterface
{
    /**
     * @var Caster[]
     */
    private readonly array $casters;

    /**
     * @param array<Caster|class-string<Caster>> $casters
     * @throws InvalidArgumentException
     */
    public function __construct(array $casters = [])
    {
        $this->casters = $this->normalizeCasters($casters);
    }

    /**
     * Applies supported custers sequentially.
     */
    public function applyCasters(mixed $value): mixed
    {
        foreach ($this->casters as $caster) {
            if ($caster->isSupported($value)) {
                $value = $caster->cast($value);
            }
        }
        return $value;
    }

    /**
     * Converts an array of classes and objects into an array of Casterings.
     *
     * @param array<Caster|class-string<Caster>> $casters
     * @return Caster[]
     * @throws InvalidArgumentException
     */
    private function normalizeCasters(array $casters): array
    {
        $normalized = [];

        foreach ($casters as $index => $caster) {
            $instance = match (true) {
                is_string($caster) => $this->instantiateFromClassName($caster, $index),
                is_object($caster) && $caster instanceof Caster => $caster,
                default => throw new InvalidArgumentException("Invalid caster at index {$index}. Expected class-string or Caster instance."),
            };

            $normalized[] = $instance;
        }

        return $normalized;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function instantiateFromClassName(string $className, int $index): Caster
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Caster class '{$className}' does not exist at index {$index}.");
        }

        $instance = new $className();

        if (!$instance instanceof Caster) {
            $type = get_class($instance);
            throw new InvalidArgumentException("Class '{$className}' must implement Caster interface, got {$type}.");
        }

        return $instance;
    }
}
