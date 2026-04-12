<?php
namespace Tests\Unit;

use phpfisx\entities\constraint;
use phpfisx\entities\point;
use phpfisx\areas\field as phpfisx_field;

// Helper: make a free-standing point (no gravity, no friction, no collision)
function makePoint(phpfisx_field $field, float $x, float $y, float $mass = 1.0): point {
    $p = new point($field, 0, bin2hex(random_bytes(4)), $x, $y, 0.0, 0.0, $mass);
    return $p;
}

it('auto-calculates rest length from initial positions', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = makePoint($field, 0.0, 0.0);
    $b = makePoint($field, 30.0, 40.0); // distance = 50
    $c = new constraint($a, $b);
    expect(round($c->getRestLength(), 5))->toBe(50.0);
});

it('accepts explicit rest length', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = makePoint($field, 0.0, 0.0);
    $b = makePoint($field, 0.0, 100.0);
    $c = new constraint($a, $b, 80.0);
    expect($c->getRestLength())->toBe(80.0);
});

it('solve pulls apart points that are too close', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = makePoint($field, 245.0, 250.0);
    $b = makePoint($field, 255.0, 250.0); // 10px apart
    $c = new constraint($a, $b, 50.0);   // rest = 50
    $c->solve();
    $dx = $b->getX() - $a->getX();
    expect($dx)->toBeGreaterThan(10.0); // pushed apart toward 50
});

it('solve pushes together points that are too far', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = makePoint($field, 200.0, 250.0);
    $b = makePoint($field, 300.0, 250.0); // 100px apart
    $c = new constraint($a, $b, 50.0);   // rest = 50
    $c->solve();
    $dx = $b->getX() - $a->getX();
    expect($dx)->toBeLessThan(100.0); // pulled toward 50
});

it('solve reaches exact rest length after enough iterations', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000]);
    $a = makePoint($field, 100.0, 500.0);
    $b = makePoint($field, 500.0, 500.0); // 400px apart
    $c = new constraint($a, $b, 100.0);  // rest = 100
    for ($i = 0; $i < 100; $i++) { $c->solve(); }
    $dx = $b->getX() - $a->getX();
    expect(round($dx, 1))->toBe(100.0);
});

it('heavy point moves less than light point during solve', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000]);
    $light = makePoint($field, 500.0, 500.0, 1.0);
    $heavy = makePoint($field, 600.0, 500.0, 100.0); // 100px apart
    $c = new constraint($light, $heavy, 50.0);
    $heavy_before = $heavy->getX();
    $light_before = $light->getX();
    $c->solve();
    $heavy_delta = abs($heavy->getX() - $heavy_before);
    $light_delta = abs($light->getX() - $light_before);
    expect($light_delta)->toBeGreaterThan($heavy_delta);
});

it('exposes both endpoints and rest length', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = makePoint($field, 0.0, 0.0);
    $b = makePoint($field, 10.0, 0.0);
    $c = new constraint($a, $b, 10.0);
    expect($c->getA())->toBe($a);
    expect($c->getB())->toBe($b);
    expect($c->getRestLength())->toBe(10.0);
});
