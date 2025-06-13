<?php

use Sam\DataObjects\SerializableContext;
use Sam\DataObjects\Exceptions\CircularReferenceException;

it('attaches and checks objects in context', function () {
    // Arrange
    $context = new SerializableContext();
    $object = new stdClass();

    // Act
    $context->attach($object);

    // Assert
    expect($context->has($object))->toBeTrue();
});

it('throws exception on circular reference detection', function () {
    // Arrange
    $context = new SerializableContext();
    $object = new stdClass();

    $context->attach($object);

    // Act & Assert
    $context->startCircularDetect($object);
})->throws(CircularReferenceException::class, 'Circular reference detected');

it('object to be detached from circular detection', function () {
    // Arrange
    $context = new SerializableContext();
    $object = new stdClass();
    $context->attach($object);

    // Act
    $context->endCircularDetect($object);

    // Assert
    expect($context->has($object))->toBeFalse();
});
