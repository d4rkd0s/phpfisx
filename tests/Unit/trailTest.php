<?php
namespace Tests\Unit;

use phpfisx\areas\field as phpfisx_field;
use phpfisx\entities\point;

// pushTrailHistory() is the pure-state half of the motion-trails feature —
// it just maintains a per-point capped position history. The GD drawing
// half (fading dots) isn't practical to unit test, so we test the buffer
// behavior directly via reflection, mirroring the style used elsewhere
// in this suite (resolvePointCollisions, resolveEdgeCollisions, etc).

function invokePushTrailHistory(phpfisx_field $field): void {
    $m = new \ReflectionMethod($field, 'pushTrailHistory');
    $m->setAccessible(true);
    $m->invoke($field);
}

function getTrailHistory(phpfisx_field $field): array {
    $p = new \ReflectionProperty($field, 'trailHistory');
    $p->setAccessible(true);
    return $p->getValue($field);
}

function setFieldPoints(phpfisx_field $field, array $points): void {
    $p = new \ReflectionProperty($field, 'points');
    $p->setAccessible(true);
    $p->setValue($field, $points);
}

it('defaults trails to disabled', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    expect($field->getTrailsEnabled())->toBeFalse();
});

it('setTrailsEnabled toggles the flag', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $field->setTrailsEnabled(true);
    expect($field->getTrailsEnabled())->toBeTrue();
});

it('pushTrailHistory records each point\'s current position', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = new point($field, 0, 'a', 10.0, 20.0);
    setFieldPoints($field, [$a]);

    invokePushTrailHistory($field);

    $history = getTrailHistory($field);
    expect($history['a'])->toBe([[10.0, 20.0]]);
});

it('pushTrailHistory accumulates across multiple calls', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = new point($field, 0, 'a', 0.0, 0.0);
    setFieldPoints($field, [$a]);

    invokePushTrailHistory($field);
    $a->setCoords(5.0, 5.0);
    invokePushTrailHistory($field);
    $a->setCoords(10.0, 10.0);
    invokePushTrailHistory($field);

    $history = getTrailHistory($field);
    expect($history['a'])->toBe([[0.0, 0.0], [5.0, 5.0], [10.0, 10.0]]);
});

it('pushTrailHistory caps history at trailLength, dropping the oldest', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = new point($field, 0, 'a', 0.0, 0.0);
    setFieldPoints($field, [$a]);

    $lengthRef = new \ReflectionProperty($field, 'trailLength');
    $lengthRef->setAccessible(true);
    $lengthRef->setValue($field, 3);

    for ($i = 0; $i < 6; $i++) {
        $a->setCoords((float)$i, 0.0);
        invokePushTrailHistory($field);
    }

    $history = getTrailHistory($field);
    // Only the last 3 positions survive (3, 4, 5), oldest dropped
    expect($history['a'])->toBe([[3.0, 0.0], [4.0, 0.0], [5.0, 0.0]]);
});

it('pushTrailHistory tracks multiple points independently', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = new point($field, 0, 'a', 1.0, 1.0);
    $b = new point($field, 0, 'b', 2.0, 2.0);
    setFieldPoints($field, [$a, $b]);

    invokePushTrailHistory($field);

    $history = getTrailHistory($field);
    expect($history['a'])->toBe([[1.0, 1.0]]);
    expect($history['b'])->toBe([[2.0, 2.0]]);
});
