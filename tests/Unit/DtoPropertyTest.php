<?php

use Sam\DataObjects\DtoProperty;

require_once __DIR__ . '/../Fixtures/DtoFixtures.php';


it('exposes all reflection metadata correctly', function () {
    $reflection = new ReflectionProperty(SampleDto::class, 'name');
    $property = new DtoProperty('name', $reflection);
    expect($property->name())->toBe('name');
    expect($property->reflectionProperty())->toBeInstanceOf(ReflectionProperty::class);
    expect($property->typeName())->toBe('string');
    expect($property->isNullable())->toBeFalse();

    $attributes = $property->attributes(TestAttribute::class);
    expect($attributes)->toBeArray()->and($attributes)->toHaveCount(1);
    expect($attributes[0]->newInstance())->toBeInstanceOf(TestAttribute::class);
    $arguments = $attributes[0]->getArguments();
    expect($arguments[0])->toBe('meta');
});

it('correctly detects nullable', function () {
    $reflection = new ReflectionProperty(SampleDto::class, 'age');
    $property = new DtoProperty('age', $reflection);

    expect($property->typeName())->toBe('int');
    expect($property->isNullable())->toBeTrue();

    expect($property->attributes())->toBe([]);
});

it('returns default values correctly', function () {
    $constructor = new ReflectionClass(SampleDtoWithDefaults::class);
    $params = $constructor->getConstructor()->getParameters();


    $fooParam = $params[0];
    $barParam = $params[1];

    $fooProp = $constructor->getProperty('foo');
    $barProp = $constructor->getProperty('bar');

    $fooDtoProp = new DtoProperty('foo', $fooProp, $fooParam);
    $barDtoProp = new DtoProperty('bar', $barProp, $barParam);

    expect($fooDtoProp->defaultValue())->toBe('default');
    expect($barDtoProp->defaultValue())->toBeNull();
});

it('retrieves the value of a property from the object', function () {
    $sampleDto = new SampleDto(name: 'TestName', age: 25);

    $reflection = new ReflectionProperty(SampleDto::class, 'name');
    $property = new DtoProperty('name', $reflection);

    $value = $property->getValue($sampleDto);

    expect($value)->toBe('TestName');
});
