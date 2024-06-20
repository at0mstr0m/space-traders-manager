<?php

declare(strict_types=1);

namespace Tests\Feature\App\Macros;

use Illuminate\Foundation\Testing\WithFaker;

uses(WithFaker::class);

test('sectorSymbol', function (mixed $faker) {
    expect($faker->sectorSymbol())
        ->toBeString()
        ->toHaveLength(2)
        ->toBe('X1');
})->with([
    fn () => fake(),
    fn () => $this->faker,
]);

test('systemSymbol', function (mixed $faker) {
    $generated = $faker->systemSymbol();
    expect($generated)
        ->toBeString()
        ->toStartWith('X1-');
    expect(strlen($generated))
        ->toBeBetween(6, 7);
})->with([
    fn () => fake(),
    fn () => $this->faker,
]);

test('waypointSuffix', function (mixed $faker) {
    $generated = $faker->waypointSuffix();
    expect($generated)
        ->toBeString()
        ->toMatch('/[A-Z][1-9][0-9]?/');
    expect(strlen($generated))
        ->toBeBetween(2, 3);
})->with([
    fn () => fake(),
    fn () => $this->faker,
]);

test('waypointSymbol', function (mixed $faker) {
    $generated = $faker->waypointSymbol();
    expect($generated)
        ->toBeString()
        ->toMatch('/X1-[A-Z][A-Z]?[1-9]{2}-[A-Z][1-9][0-9]?/');
    expect(strlen($generated))
        ->toBeBetween(9, 11);
})->with([
    fn () => fake(),
    fn () => $this->faker,
]);

test('waypointSymbols', function (mixed $faker) {
    $count = 5;
    $generated = $faker->waypointSymbols($count);
    expect($generated)
        ->toBeArray()
        ->toHaveLength($count)
        ->sequence(
            fn (mixed $symbol) => $symbol
                ->toBeString()
                ->toMatch('/X1-[A-Z][A-Z]?[1-9]{2}-[A-Z][1-9][0-9]?/')
        );
})->with([
    fn () => fake(),
    fn () => $this->faker,
]);
