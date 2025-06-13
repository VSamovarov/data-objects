<?php

declare(strict_types=1);

namespace Sam\DataObjects\Attributes;

use Attribute;
use Sam\DataObjects\Contracts\AttributeCaster;
use Sam\DataObjects\Exceptions\InvalidArgumentException;
use DateTimeInterface;

/**
 * Class AsStringTime
 *
 * Converts an object implementing DateTimeInterface into a formatted string.
 * Useful for serializing time objects (e.g., for API responses or DTOs).
 *
 * ### Example usage:
 * ```php
 * use Sam\DataObjects\Attributes\AsStringTime;
 *
 * class EventDto
 * {
 *     #[AsStringTime('H:i:s')]
 *     public DateTimeImmutable $startTime;
 *
 *     #[AsStringTime('Y-m-d')]
 *     public DateTimeImmutable $eventDate;
 *
 *     #[AsStringTime] // By default ISO 8601
 *     public DateTimeImmutable $updatedAt;
 * }
 * ```
 *
 * In this example, if `$startTime` contains a `DateTimeImmutable` object representing 14:30:00,
 * it will be serialized to the string `"14:30:00"`.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class AsStringTime implements AttributeCaster
{
    public function __construct(
        private string $format = DateTimeInterface::ATOM,
    )
    {
    }

    public function cast(mixed $value): mixed
    {
        if (!$value instanceof DateTimeInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expected instance of DateTimeInterface, got %s',
                get_debug_type($value)
            ));
        }

        return $value->format($this->format);
    }

    public function isSupported(mixed $value): bool
    {
        return true;
    }
}
