<?php

use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseHas;

use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Tests\Fakes\AbstractData\AbstractData;
use Spatie\LaravelData\Tests\Fakes\AbstractData\AbstractDataA;

use Spatie\LaravelData\Tests\Fakes\AbstractData\AbstractDataB;

use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts;

use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCustomCollectionCasts;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithDefaultCasts;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataCollection;

beforeEach(function () {
    DummyModelWithCasts::migrate();
});

it('can save a data collection', function () {
    DummyModelWithCasts::create([
        'data_collection' => SimpleData::collect([
            'Hello',
            'World',
        ], DataCollection::class),
    ]);

    assertDatabaseHas(DummyModelWithCasts::class, [
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);
});

it('can save a data object as an array', function () {
    DummyModelWithCasts::create([
        'data_collection' => [
            ['string' => 'Hello'],
            ['string' => 'World'],
        ],
    ]);

    assertDatabaseHas(DummyModelWithCasts::class, [
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);
});

it('can save a data object as an array from a collection', function () {
    DummyModelWithCasts::create([
        'data_collection' => collect([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);

    assertDatabaseHas(DummyModelWithCasts::class, [
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);
});

it('can load a data object', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts $model */
    $model = DummyModelWithCasts::first();

    expect($model->data_collection)->toEqual(new DataCollection(SimpleData::class, [
        new SimpleData('Hello'),
        new SimpleData('World'),
    ]));
});

it('can save a null as a value', function () {
    DummyModelWithCasts::create([
        'data_collection' => null,
    ]);

    assertDatabaseHas(DummyModelWithCasts::class, [
        'data_collection' => null,
    ]);
});

it('can load null as a value', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data_collection' => null,
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts $model */
    $model = DummyModelWithCasts::first();

    expect($model->data_collection)->toBeNull();
});

it('can save a custom data collection', function () {
    DummyModelWithCustomCollectionCasts::create([
        'data_collection' => [
            ['string' => 'Hello'],
            ['string' => 'World'],
        ],
    ]);

    assertDatabaseHas(DummyModelWithCustomCollectionCasts::class, [
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ], JSON_PRETTY_PRINT),
    ]);
});

it('retrieves custom data collection', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCustomCollectionCasts $model */
    $model = DummyModelWithCustomCollectionCasts::first();

    expect($model->data_collection)->toEqual(new SimpleDataCollection(
        SimpleData::class,
        [
            new SimpleData('Hello'),
            new SimpleData('World'),
        ]
    ));
});

it('loads a custom data collection when nullable argument used and value is null in database', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data' => null,
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithDefaultCasts $model */
    $model = DummyModelWithDefaultCasts::first();

    expect($model->data_collection)
        ->toBeInstanceOf(SimpleDataCollection::class)
        ->toBeEmpty();
});

it('can use an abstract data collection with multiple children', function () {
    $abstractA = new AbstractDataA('A\A');
    $abstractB = new AbstractDataB('B\B');

    $modelId = DummyModelWithCasts::create([
        'abstract_collection' => [$abstractA, $abstractB],
    ])->id;

    $model = DummyModelWithCasts::find($modelId);

    expect($model->abstract_collection)
        ->toBeInstanceOf(DataCollection::class)
        ->each->toBeInstanceOf(AbstractData::class);

    expect($model->abstract_collection[0])->toBeInstanceOf(AbstractDataA::class);
    expect($model->abstract_collection[1])->toBeInstanceOf(AbstractDataB::class);
});
