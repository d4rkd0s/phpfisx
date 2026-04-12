<?php
namespace Tests\Unit;

use phpfisx\entities\vector;

it('instantiates', function () {
    $vector = new vector();
    expect($vector)->toBeInstanceOf(vector::class);
});

it('instantiates with x and y values', function () {
    $v = new vector(3, 4);
    expect($v->x)->toBe(3);
    expect($v->y)->toBe(4);
});

it('add combines two vectors', function () {
    $v = new vector(1, 2);
    $v->add(new vector(3, 4));
    expect($v->x)->toBe(4);
    expect($v->y)->toBe(6);
});

it('sub subtracts two vectors', function () {
    $v = new vector(5, 7);
    $v->sub(new vector(2, 3));
    expect($v->x)->toBe(3);
    expect($v->y)->toBe(4);
});

it('scale multiplies components uniformly', function () {
    $v = new vector(2, 3);
    $v->scale(2);
    expect($v->x)->toBe(4);
    expect($v->y)->toBe(6);
});

it('len returns correct magnitude', function () {
    $v = new vector(3, 4);
    expect($v->len())->toBe(5.0);
});

it('normalize produces unit vector', function () {
    $v = new vector(3, 4);
    $v->normalize();
    expect(round($v->len(), 10))->toBe(1.0);
});

it('dot product is correct', function () {
    $a = new vector(2, 3);
    $b = new vector(4, 5);
    expect($a->dot($b))->toBe(23);
});

it('rotate 90 degrees is correct', function () {
    $v = new vector(1, 0);
    $v->rotate(M_PI / 2); // 90° CCW
    expect(round($v->x, 5))->toEqual(0.0);
    expect(round($v->y, 5))->toEqual(1.0);
});

it('rotate 180 degrees reverses the vector', function () {
    $v = new vector(1, 0);
    $v->rotate(M_PI);
    expect(round($v->x, 5))->toEqual(-1.0);
    expect(round($v->y, 5))->toEqual(0.0);
});

it('copy returns a new independent vector', function () {
    $v = new vector(3, 4);
    $copy = $v->copy();
    expect($copy->x)->toBe(3);
    expect($copy->y)->toBe(4);
    expect($copy)->not->toBe($v);
    // Mutating copy does not affect original
    $copy->x = 99;
    expect($v->x)->toBe(3);
});

it('reverse negates both components', function () {
    $v = new vector(5, -3);
    $v->reverse();
    expect($v->x)->toBe(-5);
    expect($v->y)->toBe(3);
});
