<?php

use Sam\DataObjects\PropertyExtractor;
use Sam\DataObjects\PropertyExtractorResolver;
use Sam\DataObjects\Traits\FromArrayTrait;
use Sam\DataObjects\Contracts\FromArray;
use Sam\DataObjects\Exceptions\InvalidArgumentException;

class TestDto implements FromArray
{
    use FromArrayTrait;

    public function __construct(
        public string     $name,
        public int        $age,
        public ?NestedDto $nested = null,
        public ?SimpleDto $simple = null,
    )
    {
    }
}

class NestedDto implements FromArray
{
    use FromArrayTrait;

    public function __construct(
        public string $title,
        public string $description,
        public mixed  $mixed = null,
    )
    {
    }
}

class SimpleDto
{
    public function __construct(
        public string $key,
        public int    $value,
        public mixed  $mixed = null,
    )
    {
    }
}

beforeEach(function () {
    $extractor = new PropertyExtractor();
    PropertyExtractorResolver::set($extractor);
    $extractor->clearCache();
});

it('creates a DTO object from a valid data array', function () {
    // Arrange
    $data = [
        'name' => 'John Doe',
        'age' => 30,
        'nested' => [
            'title' => 'Some Title',
            'description' => 'Some Description',
            'mixed' => [1, 2, 3],
        ],
        'simple' => null
    ];

    // Act
    /** @var TestDto $result */
    $result = TestDto::fromArray($data);
    // Assert
    expect($result)->toBeInstanceOf(TestDto::class)
        ->and($result->name)->toBe('John Doe')
        ->and($result->age)->toBe(30)
        ->and($result->nested)->toBeInstanceOf(NestedDto::class)
        ->and($result->nested->title)->toBe('Some Title')
        ->and($result->nested->description)->toBe('Some Description')
        ->and($result->nested->mixed)->toBe([1, 2, 3])
        ->and($result->simple)->toBeNull();
});

it('throws an exception for missing a required property', function () {
    // Arrange
    $data = [
        'name' => 'John Doe',
        // 'age' is missing
    ];

    // Act & Assert
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("Missing required property 'age' for DTO TestDto");

    TestDto::fromArray($data);
});

it('handles optional properties correctly', function () {
    // Arrange
    $data = [
        'name' => 'John Doe',
        'age' => 30,
        // 'nested' is optional and missing
    ];

    // Act
    /** @var TestDto $result */
    $result = TestDto::fromArray($data);

    // Assert
    expect($result)->toBeInstanceOf(TestDto::class)
        ->and($result->name)->toBe('John Doe')
        ->and($result->age)->toBe(30)
        ->and($result->nested)->toBeNull();
});

it('recursively handles nested DTOs', function () {
    // Arrange
    $nestedData = [
        'title' => 'Some Title',
        'description' => 'Some Description'
    ];
    $data = [
        'name' => 'John Doe',
        'age' => 30,
        'nested' => $nestedData,
    ];

    // Act
    /** @var TestDto $result */
    $result = TestDto::fromArray($data);

    // Assert
    expect($result->nested)->not()->toBeNull()
        ->and($result->nested->title)->toBe($nestedData['title'])
        ->and($result->nested->description)->toBe($nestedData['description']);
});

it('creates an object for a class without FromArray interface using PropertyExtractorResolver', function () {
    // Arrange
    $data = [
        'name' => 'John Doe',
        'age' => 30,
        'simple' => [
            'key' => 'example-key',
            'value' => 123
        ]
    ];
    // Act
    /** @var TestDto $result */
    $result = TestDto::fromArray($data);

    // Assert
    expect($result->simple)->toBeInstanceOf(SimpleDto::class)
        ->and($result->simple->key)->toBe('example-key')
        ->and($result->simple->value)->toBe(123);
});

it('throws an exception for missing properties in class without FromArray interface', function () {
    // Arrange
    $data = [
        'name' => 'John Doe',
        'age' => 30,
        'simple' => [
            'key' => 'example-key'
        ]
    ];

    // Act & Assert
    $this->expectException(InvalidArgumentException::class);
    // Act
    /** @var TestDto $result */
    $result = TestDto::fromArray($data);
});

it('We transmit the parameter to the object of the correct type', function () {
    $data = [
        'name' => 'John Doe',
        'age' => 30,
        'simple' => new SimpleDto('example-key', 99)
    ];

    $result = TestDto::fromArray($data);
    expect($result->simple)->toBeInstanceOf(SimpleDto::class)
        ->and($result->simple->key)->toBe('example-key')
        ->and($result->simple->value)->toBe(99);
});

it('throws an exception for parameter is not that type', function () {
    $data = [
        'name' => 'John Doe',
        'age' => 30,
        'simple' => new stdClass(),
    ];

    $this->expectException(TypeError::class);
    TestDto::fromArray($data);
});
