<?php
namespace Tests\Unit;

use phpfisx\areas\field as phpfisx_field;
use phpfisx\entities\point;

it('instantiates with defaults', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    expect($field)->toBeInstanceOf(phpfisx_field::class);
});

it('exposes friction', function () {
    $field = new phpfisx_field([0, 500, 0, 500], 1, 4, 0.95);
    expect($field->getFriction())->toBe(0.95);
});

it('collision: equal-mass head-on exchange velocities', function () {
    // Two equal-mass points moving directly toward each other.
    // After elastic collision they should swap velocities.
    $field = new phpfisx_field([0, 1000, 0, 1000], 0, 0, 1.0, 10.0);

    // Place them 4px apart — well inside collision radius of 10
    $a = new point($field, 0, 'a', 498.0, 500.0,  5.0, 0.0); // moving right
    $b = new point($field, 0, 'b', 502.0, 500.0, -5.0, 0.0); // moving left

    // Inject into field via reflection so resolvePointCollisions can act
    $ref = new \ReflectionProperty($field, 'points');
    $ref->setAccessible(true);
    $ref->setValue($field, [$a, $b]);

    // Trigger collision resolution directly
    $resolve = new \ReflectionMethod($field, 'resolvePointCollisions');
    $resolve->setAccessible(true);
    $resolve->invoke($field);

    // After elastic collision, velocities should be swapped (a goes left, b goes right)
    expect($a->getVelocity()->x)->toBeLessThan(0);
    expect($b->getVelocity()->x)->toBeGreaterThan(0);
});

it('collision: no response when points moving apart', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000], 0, 0, 1.0, 10.0);

    // Points overlapping but moving away from each other
    $a = new point($field, 0, 'a', 498.0, 500.0, -5.0, 0.0); // moving left
    $b = new point($field, 0, 'b', 502.0, 500.0,  5.0, 0.0); // moving right

    $ref = new \ReflectionProperty($field, 'points');
    $ref->setAccessible(true);
    $ref->setValue($field, [$a, $b]);

    $resolve = new \ReflectionMethod($field, 'resolvePointCollisions');
    $resolve->setAccessible(true);
    $resolve->invoke($field);

    // Velocities unchanged — no impulse when separating
    expect($a->getVelocity()->x)->toBe(-5.0);
    expect($b->getVelocity()->x)->toBe(5.0);
});

it('collision: heavy point barely deflected by light one', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000], 0, 0, 1.0, 10.0);

    $light = new point($field, 0, 'light', 498.0, 500.0,  5.0, 0.0);
    $heavy = new point($field, 0, 'heavy', 502.0, 500.0, -5.0, 0.0);
    $heavy->setMass(100.0);

    $ref = new \ReflectionProperty($field, 'points');
    $ref->setAccessible(true);
    $ref->setValue($field, [$light, $heavy]);

    $resolve = new \ReflectionMethod($field, 'resolvePointCollisions');
    $resolve->setAccessible(true);
    $resolve->invoke($field);

    // Heavy point should barely change; light point bounces back hard
    $heavyDelta = abs($heavy->getVelocity()->x - (-5.0));
    $lightDelta = abs($light->getVelocity()->x - 5.0);
    expect($lightDelta)->toBeGreaterThan($heavyDelta);
});
