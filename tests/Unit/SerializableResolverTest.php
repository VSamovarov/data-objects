<?php

use Sam\DataObjects\Exceptions\CircularReferenceException;
use Sam\DataObjects\PropertyExtractorResolver;
use Sam\DataObjects\SerializableResolver;
use Sam\DataObjects\SerializableContext;
use Sam\DataObjects\PropertyExtractor;
use Sam\DataObjects\Contracts\AutoSerializable;
use Sam\DataObjects\Contracts\Caster;
use Sam\DataObjects\Contracts\SkipsSerialization;
use Sam\DataObjects\Contracts\AttributeCaster;

beforeEach(function () {
    $extractor = new PropertyExtractor();
    PropertyExtractorResolver::set($extractor);
    $extractor->clearCache();
});

test('resolves simple DTO with no casters or skips', function () {
    $dto = new class () implements AutoSerializable {
        public function __construct(public string $name = 'John')
        {
        }

        public function autoSerializable(SerializableContext $context): array
        {
            return (new SerializableResolver([], PropertyExtractorResolver::get()))->resolve($this, $context);
        }
    };

    $result = (new SerializableResolver([], PropertyExtractorResolver::get()))->resolve($dto, new SerializableContext());
    expect($result)->toBe(['name' => 'John']);
});

test('applies global caster to scalar property', function () {
    $mockCaster = mock(Caster::class);
    $mockCaster->shouldReceive('isSupported')->andReturnTrue();
    $mockCaster->shouldReceive('cast')->andReturn('CASTED');

    $dto = new class () implements AutoSerializable {
        public function __construct(public string $value = 'original')
        {
        }

        public function autoSerializable(SerializableContext $context): array
        {
            return (new SerializableResolver([], PropertyExtractorResolver::get()))->resolve($this, $context);
        }
    };

    class TestCaster implements Caster
    {
        public function isSupported(mixed $value): bool
        {
            return true;
        }

        public function cast(mixed $value): mixed
        {
            return 'CASTED';
        }
    }

    $resolver = new SerializableResolver([TestCaster::class], PropertyExtractorResolver::get());
    $result = $resolver->resolve($dto, new SerializableContext());

    expect($result)->toBe(['value' => 'CASTED']);
});

test('applies attribute caster to property', function () {
    $dto = new class () implements AutoSerializable {
        public function __construct(
            #[ExampleCaster]
            public string $value = 'original'
        )
        {
        }

        public function autoSerializable(SerializableContext $context): array
        {
            return (new SerializableResolver([], PropertyExtractorResolver::get()))->resolve($this, $context);
        }
    };

    #[Attribute]
    class ExampleCaster implements AttributeCaster
    {
        public function isSupported(mixed $value): bool
        {
            return true;
        }

        public function cast(mixed $value): mixed
        {
            return strtoupper($value);
        }
    }

    $resolver = new SerializableResolver([], PropertyExtractorResolver::get());
    $result = $resolver->resolve($dto, new SerializableContext());

    expect($result)->toBe(['value' => 'ORIGINAL']);
});

test('skips property with SkipsSerialization if shouldSkip is true', function () {
    $dto = new class () implements AutoSerializable {
        public function __construct(
            #[AlwaysSkip]
            public string $secret = 'hidden',
            public string $visible = 'shown'
        )
        {
        }

        public function autoSerializable(SerializableContext $context): array
        {
            return (new SerializableResolver([], PropertyExtractorResolver::get()))->resolve($this, $context);
        }
    };

    #[Attribute]
    class AlwaysSkip implements SkipsSerialization
    {
        public function shouldSkip(mixed $value): bool
        {
            return true;
        }
    }

    $resolver = new SerializableResolver([], PropertyExtractorResolver::get());
    $result = $resolver->resolve($dto, new SerializableContext());

    expect($result)->toBe(['visible' => 'shown']);
});

test('handles nested AutoSerializable DTOs recursively', function () {
    $nested = new class () implements AutoSerializable {
        public function __construct(
            public string $field = 'nested'
        )
        {
        }

        public function autoSerializable(SerializableContext $context): array
        {
            return (new SerializableResolver([], PropertyExtractorResolver::get()))->resolve($this, $context);
        }
    };

    $dto = new class ($nested) implements AutoSerializable {
        public function __construct(public $child)
        {
        }

        public function autoSerializable(SerializableContext $context): array
        {
            return (new SerializableResolver([], PropertyExtractorResolver::get()))->resolve($this, $context);
        }
    };

    $resolver = new SerializableResolver([], PropertyExtractorResolver::get());
    $result = $resolver->resolve($dto, new SerializableContext());

    expect($result)->toBe([
        'child' => ['field' => 'nested']
    ]);
});

test('prevents infinite loop on circular references', function () {
    $dtoA = new class () implements AutoSerializable {
        public function __construct(public mixed $b = null)
        {
        }

        public function autoSerializable(SerializableContext $context): array
        {
            return (new SerializableResolver([], PropertyExtractorResolver::get()))->resolve($this, $context);
        }
    };

    $dtoB = new class () implements AutoSerializable {
        public function __construct(public mixed $a = null)
        {
        }

        public function autoSerializable(SerializableContext $context): array
        {
            return (new SerializableResolver([], PropertyExtractorResolver::get()))->resolve($this, $context);
        }
    };

    $dtoA->b = $dtoB;
    $dtoB->a = $dtoA;

    $resolver = new SerializableResolver([], PropertyExtractorResolver::get());
    $resolver->resolve($dtoA, new SerializableContext());
})->throws(CircularReferenceException::class);
