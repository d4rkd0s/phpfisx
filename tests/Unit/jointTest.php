<?php
namespace Tests\Unit;

use phpfisx\entities\joint;
use phpfisx\entities\point;
use phpfisx\areas\field as phpfisx_field;

// Helper: make a free-standing point (no gravity, no friction, no collision)
function makeJointPoint(phpfisx_field $field, float $x, float $y, float $mass = 1.0): point {
    $p = new point($field, 0, bin2hex(random_bytes(4)), $x, $y, 0.0, 0.0, $mass);
    return $p;
}

it('auto-calculates rest length from initial positions (point-to-point)', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = makeJointPoint($field, 0.0, 0.0);
    $b = makeJointPoint($field, 30.0, 40.0); // distance = 50
    $j = new joint($a, $b);
    expect(round($j->getRestLength(), 5))->toBe(50.0);
});

it('accepts explicit rest length', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = makeJointPoint($field, 0.0, 0.0);
    $b = makeJointPoint($field, 0.0, 100.0);
    $j = new joint($a, $b, 80.0);
    expect($j->getRestLength())->toBe(80.0);
});

it('rest length of 0.0 produces a pin joint', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = makeJointPoint($field, 100.0, 100.0);
    $b = makeJointPoint($field, 130.0, 140.0);
    $j = new joint($a, $b, 0.0);
    $j->solve();
    $dist = sqrt(($b->getX() - $a->getX()) ** 2 + ($b->getY() - $a->getY()) ** 2);
    expect(round($dist, 5))->toBe(0.0);
});

// ---------------------------------------------------------------------------
// (1) Hinge pin: fixed distance held, free rotation (angle changes, distance constant)
// ---------------------------------------------------------------------------

it('holds a fixed distance from a fixed anchor while the point swings freely (pendulum)', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000], 0, 0, 1.0); // no gravity, no friction
    $a = makeJointPoint($field, 100.0, 0.0); // 100px to the right of the anchor at origin
    $j = new joint($a, null, -1.0, [0.0, 0.0]); // rest length auto = 100

    $startAngle = $j->getAngle();

    // Give the point a tangential velocity (perpendicular to the anchor line) and let it swing.
    $a->setVelocity(0.0, 20.0);

    for ($i = 0; $i < 20; $i++) {
        $a->integrate();
        $j->solve();
    }

    $endAngle = $j->getAngle();
    $dist     = sqrt(($a->getX() - 0.0) ** 2 + ($a->getY() - 0.0) ** 2);

    // Distance to anchor stays pinned at rest length...
    expect(round($dist, 3))->toBe(100.0);
    // ...but the angle around the anchor has changed — free rotation.
    expect(round($endAngle, 5))->not->toBe(round($startAngle, 5));
});

it('holds a fixed distance between two points while they swing freely relative to each other', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000], 0, 0, 1.0);
    $a = makeJointPoint($field, 500.0, 500.0);
    $b = makeJointPoint($field, 600.0, 500.0); // 100px apart
    $j = new joint($a, $b); // rest length auto = 100

    $startAngle = $j->getAngle();

    // Push b tangentially so the pair rotates about their shared connection.
    $b->setVelocity(0.0, 15.0);

    for ($i = 0; $i < 15; $i++) {
        $a->integrate();
        $b->integrate();
        $j->solve();
    }

    $endAngle = $j->getAngle();
    $dist     = sqrt(($b->getX() - $a->getX()) ** 2 + ($b->getY() - $a->getY()) ** 2);

    expect(round($dist, 3))->toBe(100.0);
    expect(round($endAngle, 5))->not->toBe(round($startAngle, 5));
});

it('solve reaches exact rest length after enough iterations (point-to-point)', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000]);
    $a = makeJointPoint($field, 100.0, 500.0);
    $b = makeJointPoint($field, 500.0, 500.0); // 400px apart
    $j = new joint($a, $b, 100.0); // rest = 100
    for ($i = 0; $i < 100; $i++) { $j->solve(); }
    $dx = $b->getX() - $a->getX();
    expect(round($dx, 1))->toBe(100.0);
});

// ---------------------------------------------------------------------------
// (2) Mass-weighted response
// ---------------------------------------------------------------------------

it('heavy point moves less than light point during solve (point-to-point)', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000]);
    $light = makeJointPoint($field, 500.0, 500.0, 1.0);
    $heavy = makeJointPoint($field, 600.0, 500.0, 100.0); // 100px apart
    $j = new joint($light, $heavy, 50.0);
    $heavy_before = $heavy->getX();
    $light_before = $light->getX();
    $j->solve();
    $heavy_delta = abs($heavy->getX() - $heavy_before);
    $light_delta = abs($light->getX() - $light_before);
    expect($light_delta)->toBeGreaterThan($heavy_delta);
});

// ---------------------------------------------------------------------------
// (3) Fixed-anchor joint: infinite-mass anchor never moves
// ---------------------------------------------------------------------------

it('a fixed-anchor joint never moves the anchor, only the point', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000]);
    $a = makeJointPoint($field, 150.0, 500.0); // 50px away from anchor
    $anchorBefore = [100.0, 500.0];
    $j = new joint($a, null, 30.0, $anchorBefore); // rest = 30, currently 50 apart

    $j->solve();

    expect($j->getAnchor())->toBe($anchorBefore); // anchor coordinate itself is immutable
    $dist = sqrt(($a->getX() - $anchorBefore[0]) ** 2 + ($a->getY() - $anchorBefore[1]) ** 2);
    expect(round($dist, 3))->toBe(30.0); // point absorbed 100% of the correction
});

it('anchor joint fully resolves distance in a single solve() call (weight = 1 for the point)', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000]);
    $a = makeJointPoint($field, 0.0, 0.0, 5.0); // mass shouldn't matter — anchor absorbs nothing
    $j = new joint($a, null, 40.0, [0.0, 100.0]); // currently 100 apart, rest 40

    $j->solve();

    $dist = sqrt(($a->getX() - 0.0) ** 2 + ($a->getY() - 100.0) ** 2);
    expect(round($dist, 5))->toBe(40.0);
});

it('anchor joint pulls the point directly toward the anchor', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000]);
    $a = makeJointPoint($field, 0.0, 0.0);
    $j = new joint($a, null, 0.0, [100.0, 0.0]); // pin joint to anchor

    $j->solve();

    expect(round($a->getX(), 5))->toBe(100.0);
    expect(round($a->getY(), 5))->toBe(0.0);
});

// ---------------------------------------------------------------------------
// (4) Edge cases mirrored from constraint tests
// ---------------------------------------------------------------------------

it('solve pulls apart points that are too close', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = makeJointPoint($field, 245.0, 250.0);
    $b = makeJointPoint($field, 255.0, 250.0); // 10px apart
    $j = new joint($a, $b, 50.0);
    $j->solve();
    $dx = $b->getX() - $a->getX();
    expect($dx)->toBeGreaterThan(10.0);
});

it('solve pushes together points that are too far', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = makeJointPoint($field, 200.0, 250.0);
    $b = makeJointPoint($field, 300.0, 250.0); // 100px apart
    $j = new joint($a, $b, 50.0);
    $j->solve();
    $dx = $b->getX() - $a->getX();
    expect($dx)->toBeLessThan(100.0);
});

it('exposes both endpoints and rest length', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = makeJointPoint($field, 0.0, 0.0);
    $b = makeJointPoint($field, 10.0, 0.0);
    $j = new joint($a, $b, 10.0);
    expect($j->getA())->toBe($a);
    expect($j->getB())->toBe($b);
    expect($j->getRestLength())->toBe(10.0);
    expect($j->isAnchored())->toBeFalse();
});

it('isAnchored is true only when constructed with a fixed anchor', function () {
    $field    = new phpfisx_field([0, 500, 0, 500]);
    $a        = makeJointPoint($field, 0.0, 0.0);
    $b        = makeJointPoint($field, 10.0, 0.0);
    $twoPoint = new joint($a, $b);
    $anchored = new joint($a, null, -1.0, [50.0, 50.0]);
    expect($twoPoint->isAnchored())->toBeFalse();
    expect($anchored->isAnchored())->toBeTrue();
    expect($anchored->getB())->toBeNull();
});

it('throws when constructed without a second point or an anchor', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = makeJointPoint($field, 0.0, 0.0);
    expect(fn() => new joint($a, null))->toThrow(\InvalidArgumentException::class);
});

it('solve is a no-op when points are already coincident', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = makeJointPoint($field, 50.0, 50.0);
    $b = makeJointPoint($field, 50.0, 50.0); // exactly on top of each other
    $j = new joint($a, $b, 10.0);
    $j->solve();
    expect($a->getX())->toBe(50.0);
    expect($a->getY())->toBe(50.0);
    expect($b->getX())->toBe(50.0);
    expect($b->getY())->toBe(50.0);
});

// ---------------------------------------------------------------------------
// field integration — addJoint()/addJointAnchor() wire into the step loop
// ---------------------------------------------------------------------------

it('field::addJoint registers a point-to-point joint solved during runFisx', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000], 0, 0, 1.0); // no gravity, no friction
    $a = makeJointPoint($field, 400.0, 500.0);
    $b = makeJointPoint($field, 600.0, 500.0); // 200px apart
    $field->addJoint($a, $b, 50.0); // rest = 50, currently far too stretched

    $ptsRef = new \ReflectionProperty($field, 'points');
    $ptsRef->setAccessible(true);
    $ptsRef->setValue($field, [$a, $b]);

    $pcRef = new \ReflectionProperty($field, 'pointCount');
    $pcRef->setAccessible(true);
    $pcRef->setValue($field, 2);

    $run = new \ReflectionMethod($field, 'runFisx');
    $run->setAccessible(true);
    $run->invoke($field);

    $dist = sqrt(($b->getX() - $a->getX()) ** 2 + ($b->getY() - $a->getY()) ** 2);
    expect(round($dist, 1))->toBe(50.0);

    $joints = $field->getJoints();
    expect($joints)->toHaveCount(1);
    expect($joints[0]->isAnchored())->toBeFalse();
});

it('field::addJointAnchor registers an anchored joint that pins the point but never moves the anchor', function () {
    $field = new phpfisx_field([0, 1000, 0, 1000], 0, 0, 1.0);
    $a = makeJointPoint($field, 500.0, 500.0);
    $field->addJointAnchor($a, 500.0, 400.0, 30.0); // anchor 100px above, rest = 30

    $ptsRef = new \ReflectionProperty($field, 'points');
    $ptsRef->setAccessible(true);
    $ptsRef->setValue($field, [$a]);

    $pcRef = new \ReflectionProperty($field, 'pointCount');
    $pcRef->setAccessible(true);
    $pcRef->setValue($field, 1);

    $run = new \ReflectionMethod($field, 'runFisx');
    $run->setAccessible(true);
    $run->invoke($field);

    $dist = sqrt(($a->getX() - 500.0) ** 2 + ($a->getY() - 400.0) ** 2);
    expect(round($dist, 1))->toBe(30.0);

    $joints = $field->getJoints();
    expect($joints)->toHaveCount(1);
    expect($joints[0]->isAnchored())->toBeTrue();
    expect($joints[0]->getAnchor())->toBe([500.0, 400.0]); // anchor untouched
});
