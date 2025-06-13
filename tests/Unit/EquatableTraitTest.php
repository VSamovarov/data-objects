<?php

use Sam\DataObjects\Contracts\Equatable;
use Sam\DataObjects\PropertyExtractor;
use Sam\DataObjects\PropertyExtractorResolver;
use Sam\DataObjects\Traits\EquatableTrait;


class SimpleDtoWithEquatableTrait implements Equatable
{
    use EquatableTrait;

    public function __construct(
        public string     $foo,
        public mixed      $mixed,
        public ?Equatable $equatable = null,
        public mixed      $mixed2 = null,
    )
    {
    }
}


beforeEach(function () {
    $extractor = new PropertyExtractor();
    PropertyExtractorResolver::set($extractor);
    $extractor->clearCache();
});

it('returns true for objects with identical properties', function () {
    // Arrange
    $dto1 = new SimpleDtoWithEquatableTrait(
        'John',
        (object)['a' => 1, 'b' => 2],
        new SimpleDtoWithEquatableTrait('Marta', new SampleDto('John', 30), null, 1),
        null
    );
    $dto2 = new SimpleDtoWithEquatableTrait(
        'John',
        (object)['a' => 1, 'b' => 2],
        new SimpleDtoWithEquatableTrait('Marta', new SampleDto('John', 30), null, 1),
        null
    );

    // Act
    $result = $dto1->equals($dto2);

    // Assert
    expect($result)->toBeTrue();
});

it('returns false for objects with different properties', function () {
    // Arrange
    $dto1 = new SimpleDtoWithEquatableTrait('John', 30);
    $dto2 = new SimpleDtoWithEquatableTrait('Jane', 25);

    // Act
    $result = $dto1->equals($dto2);

    // Assert
    expect($result)->toBeFalse();
});

it('returns false when comparing objects of different classes', function () {
    // Arrange
    $dto1 = new SimpleDtoWithEquatableTrait('John', 30);
    $dto2 = new SampleDto('John', 30);

    // Act
    $result = $dto1->equals($dto2);

    // Assert
    expect($result)->toBeFalse();
});

it('returns false when comparing with null', function () {
    // Arrange
    $dto = new SimpleDtoWithEquatableTrait('John', 30);

    // Act
    $result = $dto->equals(null);

    // Assert
    expect($result)->toBeFalse();
});

it('returns false when comparing with a non-object', function () {
    // Arrange
    $dto = new SimpleDtoWithEquatableTrait('John', 30);

    // Act
    $result = $dto->equals('this is a string');

    // Assert
    expect($result)->toBeFalse();
});


it('returns false for a DTO with no constructor against an object of a different structure', function () {
    // Arrange
    $dto1 = new SimpleDtoWithEquatableTrait('John', 30);
    $dto2 = new NoConstructorDto();

    // Act
    $result = $dto1->equals($dto2);

    // Assert
    expect($result)->toBeFalse();
});

it('returns false when comparing with nested objects', function () {
    // Arrange
    $dto1 = new SimpleDtoWithEquatableTrait('John', new SimpleMixed(new SimpleMixed([1, 2])));
    $dto2 = new SimpleDtoWithEquatableTrait('John', new SimpleMixed(new SimpleMixed([1, 2])));

    // Act
    $result = $dto1->equals($dto2);

    // Assert
    expect($result)->toBeTrue();
});
