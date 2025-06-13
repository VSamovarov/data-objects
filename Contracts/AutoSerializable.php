<?php

namespace Sam\DataObjects\Contracts;

use Sam\DataObjects\SerializableContext;

// позволит явно отделить DTO, которые поддерживают автоматическую сериализацию, от других объектов
// и определяет поведение автоматической сериализации
interface AutoSerializable
{
    // внутренний метод, который реализует низкоуровневую автоматическую сериализацию,
    // и будет вызываться внутри публичного jsonSerialize()
    public function autoSerializable(SerializableContext $context): mixed;
}
