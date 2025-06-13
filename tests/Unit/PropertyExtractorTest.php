<?php

use Sam\DataObjects\PropertyExtractor;
use Sam\DataObjects\PropertyExtractorResolver;
use Sam\DataObjects\DtoProperty;
use Sam\DataObjects\Exceptions\InvalidDtoStyleException;

require_once __DIR__ . '/../Fixtures/DtoFixtures.php';

beforeEach(function () {
    $extractor = new PropertyExtractor();
    PropertyExtractorResolver::set($extractor);
    $extractor->clearCache();
});

afterEach(fn() => PropertyExtractorResolver::reset());

it('extracts properties from DTO with promoted constructor parameters', function () {
    $extractor = PropertyExtractorResolver::get();
    $props = $extractor->getProperties(PromotedDto::class);
    expect($props)->toHaveCount(2);
    expect($props[0])->toBeInstanceOf(DtoProperty::class);
    expect($props[0]->name())->toBe('id');
    expect($props[1]->name())->toBe('name');
    expect($props[1]->typeName())->toBe('string');
    expect($props[0]->attributes(TestAttribute::class)[0]->newInstance())->toBeInstanceOf(TestAttribute::class);
});

it('extracts properties from DTO with public properties', function () {
    $extractor = PropertyExtractorResolver::get();
    $props = $extractor->getProperties(LegacyDto::class);
    expect($props)->toHaveCount(2);
    expect($props[0])->toBeInstanceOf(DtoProperty::class);
    expect($props[0]->name())->toBe('id');
    expect($props[1]->name())->toBe('name');
    expect($props[1]->typeName())->toBe('string');
    expect($props[0]->attributes(TestAttribute::class)[0]->newInstance())->toBeInstanceOf(TestAttribute::class);
});

it('throws if DTO has no constructor', function () {
    $extractor = PropertyExtractorResolver::get();
    expect(fn() => $extractor->getProperties(NoConstructorDto::class))
        ->toThrow(InvalidDtoStyleException::class, "DTO 'NoConstructorDto' must define a constructor.");
});

it('throws if DTO uses mixed style', function () {
    $extractor = PropertyExtractorResolver::get();
    expect(fn() => $extractor->getProperties(MixedDto::class))
        ->toThrow(InvalidDtoStyleException::class, "DTO 'MixedDto' uses unsupported mixed or private properties.");
});

it('uses cache for repeated calls', function () {
    $extractor = PropertyExtractorResolver::get();
    $props1 = $extractor->getProperties(PromotedDto::class);
    $props2 = $extractor->getProperties(PromotedDto::class);

    expect($props1)->toBe($props2);
});

it('recreates cache after clearing', function () {
    $extractor = PropertyExtractorResolver::get();
    $props1 = $extractor->getProperties(PromotedDto::class);
    $extractor->clearCache();
    $props2 = $extractor->getProperties(PromotedDto::class);

    expect($props1)->not->toBe($props2); // cache was dropped, the second challenge created a new object
});

it('throws if promoted property is not public', function () {
    $extractor = PropertyExtractorResolver::get();

    expect(fn() => $extractor->getProperties(InvalidPromotedDto::class))
        ->toThrow(
            InvalidDtoStyleException::class,
            "DTO 'InvalidPromotedDto' has promoted constructor parameter 'id', but it is not declared as public."
        );
});
