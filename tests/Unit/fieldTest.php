<?php
namespace Tests\Unit;

use phpfisx\areas\field as phpfisx_field;
use phpfisx\entities\constraint;
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

it('exposes restitution', function () {
    $field = new phpfisx_field([0, 500, 0, 500], 1, 4, 0.98, 5.0, 0.5);
    expect($field->getRestitution())->toBe(0.5);
});

it('restitution 0 kills relative velocity on collision', function () {
    // e=0 means no bounce — relative velocity along normal should be 0 after
    $field = new phpfisx_field([0, 1000, 0, 1000], 0, 0, 1.0, 10.0, 0.0);

    $a = new point($field, 0, 'a', 498.0, 500.0,  5.0, 0.0);
    $b = new point($field, 0, 'b', 502.0, 500.0, -5.0, 0.0);

    $ref = new \ReflectionProperty($field, 'points');
    $ref->setAccessible(true);
    $ref->setValue($field, [$a, $b]);

    $resolve = new \ReflectionMethod($field, 'resolvePointCollisions');
    $resolve->setAccessible(true);
    $resolve->invoke($field);

    // With e=0: relative velocity along normal = 0 (stick together)
    $rvn = ($a->getVelocity()->x - $b->getVelocity()->x); // both on x-axis
    expect(round(abs($rvn), 5))->toBe(0.0);
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

it('edge collision: point moving into horizontal edge gets bounced back', function () {
    // Field with no gravity, full restitution, large collision radius
    $field = new phpfisx_field([0, 1000, 0, 1000], 0, 0, 1.0, 10.0, 1.0);

    // Horizontal edge at y=200, spanning x=100 to x=300
    $ea = new point($field, 0, 'ea', 100.0, 200.0, 0.0, 0.0, 100.0); // heavy — barely moves
    $eb = new point($field, 0, 'eb', 300.0, 200.0, 0.0, 0.0, 100.0);

    // Loose point above the edge, moving downward toward it (from y=195 → approaching y=200)
    $p = new point($field, 0, 'p', 200.0, 195.0, 0.0, 5.0, 1.0); // vy=5 = moving down

    // Set pointCount so resolveEdgeCollisions treats only $p as loose
    $pcRef = new \ReflectionProperty($field, 'pointCount');
    $pcRef->setAccessible(true);
    $pcRef->setValue($field, 1); // only index 0 ($p) is loose

    $ptsRef = new \ReflectionProperty($field, 'points');
    $ptsRef->setAccessible(true);
    $ptsRef->setValue($field, [$p, $ea, $eb]);

    $c = new constraint($ea, $eb, -1.0, true);
    $cRef = new \ReflectionProperty($field, 'constraints');
    $cRef->setAccessible(true);
    $cRef->setValue($field, [$c]);

    $resolve = new \ReflectionMethod($field, 'resolveEdgeCollisions');
    $resolve->setAccessible(true);
    $resolve->invoke($field);

    // Point should now be moving upward (bounced)
    expect($p->getVelocity()->y)->toBeLessThan(0);
});

it('edge collision: no response when point moving away from edge', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000], 0, 0, 1.0, 10.0, 1.0);

    $ea = new point($field, 0, 'ea', 100.0, 200.0, 0.0, 0.0, 100.0);
    $eb = new point($field, 0, 'eb', 300.0, 200.0, 0.0, 0.0, 100.0);

    // Point moving upward (away from edge below)
    $p = new point($field, 0, 'p', 200.0, 195.0, 0.0, -5.0, 1.0); // vy=-5 = moving up

    $pcRef = new \ReflectionProperty($field, 'pointCount');
    $pcRef->setAccessible(true);
    $pcRef->setValue($field, 1);

    $ptsRef = new \ReflectionProperty($field, 'points');
    $ptsRef->setAccessible(true);
    $ptsRef->setValue($field, [$p, $ea, $eb]);

    $c = new constraint($ea, $eb, -1.0, true);
    $cRef = new \ReflectionProperty($field, 'constraints');
    $cRef->setAccessible(true);
    $cRef->setValue($field, [$c]);

    $resolve = new \ReflectionMethod($field, 'resolveEdgeCollisions');
    $resolve->setAccessible(true);
    $resolve->invoke($field);

    // Velocity unchanged — moving away, no impulse applied
    expect($p->getVelocity()->y)->toBe(-5.0);
});
