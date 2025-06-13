<?php

use Sam\DataObjects\CasterRegistry;
use Sam\DataObjects\Contracts\Caster;
use Sam\DataObjects\Exceptions\InvalidArgumentException;
use Mockery as m;

beforeEach(function () {
    // Очистка моков перед каждым тестом
    m::close();
});

it('applies single caster if supported', function () {
    // Arrange
    $caster = m::mock(Caster::class);
    $caster->shouldReceive('isSupported')->with('42')->andReturnTrue();
    $caster->shouldReceive('cast')->with('42')->andReturn(42);

    $registry = new CasterRegistry([$caster]);

    // Act
    $result = $registry->applyCasters('42');

    // Assert
    expect($result)->toBe(42);
});

it('applies multiple casters in order if supported', function () {
    // Arrange
    $caster1 = m::mock(Caster::class);
    $caster1->shouldReceive('isSupported')->with('raw')->andReturnTrue();
    $caster1->shouldReceive('cast')->with('raw')->andReturn('intermediate');

    $caster2 = m::mock(Caster::class);
    $caster2->shouldReceive('isSupported')->with('intermediate')->andReturnTrue();
    $caster2->shouldReceive('cast')->with('intermediate')->andReturn('final');

    $registry = new CasterRegistry([$caster1, $caster2]);

    // Act
    $result = $registry->applyCasters('raw');

    // Assert
    expect($result)->toBe('final');
});

it('skips casters if none support the value', function () {
    // Arrange
    $caster = m::mock(Caster::class);
    $caster->shouldReceive('isSupported')->with(123)->andReturnFalse();

    $registry = new CasterRegistry([$caster]);

    // Act
    $result = $registry->applyCasters(123);

    // Assert
    expect($result)->toBe(123);
});

it('throws exception if caster class does not exist', function () {
    // Act & Assert
    new CasterRegistry(['App\\Fake\\NonExistentCaster']);
})->throws(InvalidArgumentException::class, "Caster class 'App\\Fake\\NonExistentCaster' does not exist");

it('throws exception if class does not implement Caster interface', function () {
    // Arrange
    class NotACaster
    {
    }

    // Act & Assert
    new CasterRegistry([NotACaster::class]);
})->throws(InvalidArgumentException::class, "Class 'NotACaster' must implement Caster interface");
