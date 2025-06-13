<?php

use Sam\DataObjects\PropertyExtractor;
use Sam\DataObjects\PropertyExtractorResolver;
use Illuminate\Support\Facades\App;

beforeEach(function () {
    PropertyExtractorResolver::reset();
});

it('resolves the default PropertyExtractor from the container', function () {
    $propertyExtractor = new PropertyExtractor();

    App::shouldReceive('make')
        ->once()
        ->with(PropertyExtractor::class)
        ->andReturn($propertyExtractor);

    $extractor = PropertyExtractorResolver::get();

    expect($extractor)->toBe($propertyExtractor);

});

it('allows overriding the PropertyExtractor instance', function () {
    $propertyExtractor = new PropertyExtractor();

    PropertyExtractorResolver::set($propertyExtractor);
    $extractor = PropertyExtractorResolver::get();

    expect($extractor)->toBe($propertyExtractor);

});

it('resets to the default container behavior after override', function () {
    $propertyExtractor = new PropertyExtractor();
    $anotherMockExtractor = new PropertyExtractor();

    PropertyExtractorResolver::set($propertyExtractor);
    expect(PropertyExtractorResolver::get())->toBe($propertyExtractor);

    PropertyExtractorResolver::reset();

    App::shouldReceive('make')
        ->once()
        ->with(PropertyExtractor::class)
        ->andReturn($anotherMockExtractor);

    $extractor = PropertyExtractorResolver::get();

    expect($extractor)->toBe($anotherMockExtractor);
});
