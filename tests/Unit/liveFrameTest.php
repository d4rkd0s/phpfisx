<?php
namespace Tests\Unit;

use phpfisx\areas\field as phpfisx_field;
use phpfisx\entities\point;

// Covers the pure step-serialization functions that live.php's SSE loop
// (`serializeFrame()`, `serializeStaticScene()`) and the shared scene-parsing
// builder (`fromScene()`) rely on. No HTTP, no SSE transport, no GD — see
// CLAUDE.md "Live mode" for why the transport (headers/flush/sleep) is
// deliberately kept out of field.php and therefore out of this test file.

it('serializeStaticScene reports bounds, total steps, and rounded static lines', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $field->setSteps(42);
    $field->addStaticLine(1.005, 2.0, 300.999, 4.0);

    $snapshot = $field->serializeStaticScene();

    expect($snapshot['width'])->toBe(500);
    expect($snapshot['height'])->toBe(500);
    expect($snapshot['totalSteps'])->toBe(42);
    expect($snapshot['staticLines'])->toHaveCount(1);
    expect($snapshot['staticLines'][0])->toBe([1.01, 2.0, 301.0, 4.0]);
});

it('serializeFrame reports the current step and point positions', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $field->setStep(3);

    $a = new point($field, 0, 'a', 10.001, 20.0);
    $b = new point($field, 0, 'b', 30.0, 40.004);

    $ref = new \ReflectionProperty($field, 'points');
    $ref->setAccessible(true);
    $ref->setValue($field, [$a, $b]);

    $frame = $field->serializeFrame();

    expect($frame['step'])->toBe(3);
    expect($frame['points'])->toBe([[10.0, 20.0], [30.0, 40.0]]);
    expect($frame['edges'])->toBe([]);
    expect($frame['joints'])->toBe([]);
});

it('serializeFrame includes only boundary constraint edges, not internal braces', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $field->desiredPointCount(0);
    $field->setSteps(1);
    $field->spawnBox(100, 100, 40, 40, 1.0);

    $field->setStep(1);
    $ref = new \ReflectionMethod($field, 'calculate');
    $ref->setAccessible(true);
    $ref->invoke($field);

    $frame = $field->serializeFrame();

    // A box has 4 boundary edges + 2 internal diagonal braces (isBoundary=false).
    // Only the 4 boundary edges should appear in the frame.
    expect($frame['edges'])->toHaveCount(4);
});

it('serializeFrame reports a joint between two points as a 4-tuple', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = new point($field, 0, 'a', 0.0, 0.0);
    $b = new point($field, 0, 'b', 10.0, 0.0);
    $field->addJoint($a, $b, 10.0);

    $frame = $field->serializeFrame();

    expect($frame['joints'])->toBe([[0.0, 0.0, 10.0, 0.0]]);
});

it('serializeFrame reports an anchored joint using the fixed anchor coordinate', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $a = new point($field, 0, 'a', 5.0, 5.0);
    $field->addJointAnchor($a, 50.0, 60.0, 10.0);

    $frame = $field->serializeFrame();

    expect($frame['joints'])->toBe([[5.0, 5.0, 50.0, 60.0]]);
});

it('fromScene builds a field with settings, shapes, and a joint from scene JSON', function () {
    $scene = [
        'settings' => ['points' => 5, 'steps' => 7, 'gravity' => 2.0, 'friction' => 0.9, 'restitution' => 0.5],
        'shapes'   => [
            ['type' => 'box', 'cx' => 100, 'cy' => 100, 'w' => 40, 'h' => 40, 'mass' => 2.0],
            ['type' => 'circle', 'cx' => 300, 'cy' => 100, 'r' => 20, 'n' => 8, 'mass' => 1.0],
            ['type' => 'line', 'x1' => 0, 'y1' => 490, 'x2' => 500, 'y2' => 490],
            ['type' => 'joint', 'from' => ['shapeIndex' => 0, 'x' => 119, 'y' => 81], 'to' => ['shapeIndex' => 1, 'x' => 281, 'y' => 100]],
        ],
    ];

    $field = phpfisx_field::fromScene($scene);

    expect($field->getSteps())->toBe(7);
    expect($field->getFriction())->toBe(0.9);

    $blueprintsRef = new \ReflectionProperty($field, 'shapeBlueprints');
    $blueprintsRef->setAccessible(true);
    expect($blueprintsRef->getValue($field))->toHaveCount(2);

    expect($field->getJointBlueprints())->toHaveCount(1);
});

it('fromScene defaults to sensible settings when scene JSON is empty', function () {
    $field = phpfisx_field::fromScene([]);

    expect($field->getSteps())->toBe(50);
    expect($field->getFriction())->toBe(0.98);
});
