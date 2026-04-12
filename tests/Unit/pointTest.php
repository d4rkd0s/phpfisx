<?php
namespace Tests\Unit;

use phpfisx\entities\point;
use phpfisx\entities\vector;
use phpfisx\areas\field as phpfisx_field;

it('asserts true is true', function () {
    expect(true)->toBeTrue();
});

it('instantiates', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $point = new point($field, 42);
    expect($point)->toBeInstanceOf(point::class);
});

it('initializes with zero velocity', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $point = new point($field, 42);
    $vel = $point->getVelocity();
    expect($vel)->toBeInstanceOf(vector::class);
    expect($vel->x)->toBe(0);
    expect($vel->y)->toBe(0);
});

it('restores velocity from existing state', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $point = new point($field, 0, 'abc', 100.0, 200.0, 3.5, -1.2);
    expect($point->getX())->toBe(100.0);
    expect($point->getY())->toBe(200.0);
    expect($point->getVelocity()->x)->toBe(3.5);
    expect($point->getVelocity()->y)->toBe(-1.2);
});

it('applyForce accumulates into velocity', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $point = new point($field, 0, 'test', 250.0, 250.0);
    $point->applyForce(1, 0); // direction 0 = straight down
    $vel = $point->getVelocity();
    expect(round($vel->x, 5))->toBe(0.0);
    expect(round($vel->y, 5))->toBe(1.0);
});

it('applyForce accumulates over multiple calls', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $point = new point($field, 0, 'test', 250.0, 250.0);
    $point->applyForce(1, 0);
    $point->applyForce(1, 0);
    $point->applyForce(1, 0);
    expect(round($point->getVelocity()->y, 5))->toBe(3.0);
});

it('integrate advances position by velocity', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $point = new point($field, 0, 'test', 250.0, 250.0);
    $point->applyForce(5, 0); // downward
    $before_y = $point->getY();
    $point->integrate();
    expect($point->getY())->toBeGreaterThan($before_y);
});

it('integrate reflects velocity off bottom boundary', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $border = $field->getBorder();
    $point = new point($field, 0, 'test', 250.0, 500.0 - $border - 1.0);
    $point->applyForce(10, 0); // strong downward push into the wall
    $point->integrate();
    expect($point->getVelocity()->y)->toBeLessThan(0); // reversed
});

it('integrate reflects velocity off top boundary', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $border = $field->getBorder();
    $point = new point($field, 0, 'test', 250.0, (float)($border + 1));
    $point->setVelocity(0, -10); // strong upward velocity into the wall
    $point->integrate();
    expect($point->getVelocity()->y)->toBeGreaterThan(0); // reversed
});

it('setVelocity updates both components', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $point = new point($field, 0, 'test', 250.0, 250.0);
    $point->setVelocity(7.5, -3.2);
    expect($point->getVelocity()->x)->toBe(7.5);
    expect($point->getVelocity()->y)->toBe(-3.2);
});

it('friction damps velocity each step', function () {
    $field = new phpfisx_field([0, 500, 0, 500], 0, 4, 0.5); // gravity=0, friction=0.5
    $point = new point($field, 0, 'test', 250.0, 250.0, 10.0, 0.0); // vx=10
    $point->integrate();
    expect($point->getVelocity()->x)->toBe(5.0); // 10 * 0.5
});

it('friction=1 preserves velocity', function () {
    $field = new phpfisx_field([0, 500, 0, 500], 0, 4, 1.0); // no drag
    $point = new point($field, 0, 'test', 250.0, 250.0, 4.0, 0.0);
    $point->integrate();
    expect($point->getVelocity()->x)->toBe(4.0);
});

it('friction prevents unbounded velocity growth', function () {
    // Without friction, after 300 steps of gravity=1, vy would be 300.
    // With friction=0.98, terminal velocity = 1 / (1 - 0.98) = 50.
    // Use a field tall enough that the point never hits the floor.
    $field = new phpfisx_field([0, 100000, 0, 100000], 1, 4, 0.98);
    $point = new point($field, 0, 'test', 50000.0, 100.0);
    for ($i = 0; $i < 300; $i++) {
        $point->applyForce(1, 0);
        $point->integrate();
    }
    // vy must be well below the frictionless value of 300
    expect(abs($point->getVelocity()->y))->toBeLessThan(100.0);
});

it('toArray includes velocity components', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $point = new point($field, 0, 'test-id', 100.0, 200.0, 1.5, 2.5);
    $arr = $point->toArray();
    expect($arr)->toHaveKey('id');
    expect($arr)->toHaveKey('x');
    expect($arr)->toHaveKey('y');
    expect($arr)->toHaveKey('vx');
    expect($arr)->toHaveKey('vy');
    expect($arr['vx'])->toBe(1.5);
    expect($arr['vy'])->toBe(2.5);
});
