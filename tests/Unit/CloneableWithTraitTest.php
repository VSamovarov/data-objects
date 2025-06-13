<?php

use Sam\DataObjects\PropertyExtractor;
use Sam\DataObjects\Traits\CloneableWithTrait;
use Sam\DataObjects\Exceptions\InvalidArgumentException;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

beforeEach(function () {

    // Create a fixture class using the CloneableWithTrait
    $this->fixtureClass = new class (1, 'Alice') {
        use CloneableWithTrait;


        /**
         * !!!!! Override getPropertyExtractor method to avoid mocking and container dependency
         */
        protected function getPropertyExtractor(): PropertyExtractor
        {
            return new PropertyExtractor(true);
        }

        public function __construct(
            public int     $id,
            public string  $name,
            public ?string $role = null
        )
        {
        }
    };
});

it('creates an exact copy using clone()', function () {
    // Arrange
    $original = $this->fixtureClass;

    // Act
    $copy = $original->clone();

    // Assert
    expect($copy)->not->toBe($original); // Ensure a new instance is created
    expect($copy->id)->toBe($original->id);
    expect($copy->name)->toBe($original->name);
    expect($copy->role)->toBe($original->role);
});

it('creates a modified copy using cloneWith()', function () {
    // Arrange
    $original = $this->fixtureClass;

    // Act
    $modified = $original->cloneWith(['role' => 'Admin']);

    // Assert
    expect($modified)->not->toBe($original); // Ensure a new instance is created
    expect($modified->id)->toBe($original->id);
    expect($modified->name)->toBe($original->name);
    expect($modified->role)->toBe('Admin'); // Ensure the role has been updated
});

it('throws an exception if cloneWith receives a list instead of an associative array', function () {
    // Arrange
    $original = $this->fixtureClass;

    // Act & Assert
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The method `cloneWith()` supports only named parameters.');
    $original->cloneWith([1, 2, 3]); // Invalid input
});

it('uses default values of optional properties in cloneWith()', function () {
    // Arrange
    $defaultClass = new class (1, 'Bob', 'Guest') {
        use CloneableWithTrait;

        /**
         * !!!!! Override getPropertyExtractor method to avoid mocking and container dependency
         */
        protected function getPropertyExtractor(): PropertyExtractor
        {
            return new PropertyExtractor(true);
        }

        public function __construct(
            public int     $id,
            public string  $name,
            public ?string $role = 'Guest'
        )
        {
        }
    };

    // Act
    $copy = $defaultClass->cloneWith(['id' => 2]);

    // Assert
    expect($copy->id)->toBe(2); // Ensure `id` was updated
    expect($copy->name)->toBe('Bob'); // Ensure `name` remains the same
    expect($copy->role)->toBe('Guest'); // Ensure `role` uses default value
});

it('throws an exception when accessing a non-existent property in cloneWith()', function () {
    // Arrange
    $original = $this->fixtureClass;

    // Act & Assert
    $this->expectException(InvalidArgumentException::class);
    $original->cloneWith(['nonexistentProperty' => 'value']);
});
