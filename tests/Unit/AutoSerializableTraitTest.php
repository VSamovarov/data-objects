<?php

use Sam\DataObjects\Contracts\AutoSerializable;
use Sam\DataObjects\PropertyExtractor;
use Sam\DataObjects\PropertyExtractorResolver;
use Sam\DataObjects\SerializableContext;
use Sam\DataObjects\Traits\AutoSerializableTrait;

class TestAutoSerializable implements AutoSerializable
{
    use AutoSerializableTrait;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    protected function getPropertyExtractor(): PropertyExtractor
    {
        return PropertyExtractorResolver::get();
    }
}

beforeEach(function () {
    PropertyExtractorResolver::reset();
});

it('serializes data correctly using autoSerializable', function () {
    // Arrange
    $data = ['key1' => 'value1', 'key2' => 'value2'];
    $context = new SerializableContext();

    // Create a real copy of Propertyextractor and install it in resolver
    $propertyExtractor = new PropertyExtractor();
    PropertyExtractorResolver::set($propertyExtractor);

    // Test object using a trait
    $object = new TestAutoSerializable($data);

    // Act
    $result = $object->autoSerializable($context);

    // Assert
    expect($result)->toBeArray()
        ->toMatchArray(['data' => $data]);
});

it('returns correct casts', function () {
    // Arrange
    $object = new TestAutoSerializable([]);

    // Act
    $getCastsMethod = new ReflectionMethod($object, 'getCasts');
    $casts = $getCastsMethod->invoke($object);

    // Assert
    expect($casts)->toBeArray()
        ->toContain(
            Sam\DataObjects\Casters\ArrayableCaster::class,
            Sam\DataObjects\Casters\JsonSerializableCaster::class
        );
});
