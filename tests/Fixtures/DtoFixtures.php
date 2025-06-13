<?php

#[Attribute(Attribute::TARGET_PROPERTY)]
class TestAttribute
{
    public function __construct(public string $value)
    {
    }
}

final class SampleDto
{
    public function __construct(
        #[TestAttribute('meta')]
        public string $name = 'default',
        public ?int   $age = null,
    )
    {
    }
}

final class PromotedDto
{
    public function __construct(
        #[TestAttribute('meta')]
        public int    $id,
        public string $name,
    )
    {
    }
}

final class LegacyDto
{
    #[TestAttribute('meta')]
    public int $id;
    public string $name;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}

final class MixedDto
{
    public int $id;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
    }
}

final class NoConstructorDto
{
}

class InvalidPromotedDto
{
    public function __construct(private int $id)
    {
    }
}

class SampleDtoWithDefaults
{
    public function __construct(
        public string $foo = 'default',
        public ?int   $bar = null,
    )
    {
    }
}

class SimpleMixed
{
    public function __construct(
        public mixed $mixed = null,
    )
    {
    }
}
