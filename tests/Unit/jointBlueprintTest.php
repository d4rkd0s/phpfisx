<?php
namespace Tests\Unit;

use phpfisx\areas\field as phpfisx_field;

// Covers the deferred joint-materialization pipeline that render.php relies
// on when parsing a 'joint' scene shape (see CLAUDE.md "Scene JSON format"):
// addJointBlueprint() records a joint by shape-blueprint index + click
// position, and materializeJoints() (invoked from calculate() on step 1,
// after materializeShapes()) resolves each endpoint to the nearest actual
// point on that shape, or a fixed anchor.

it('resolves a shape-to-shape joint blueprint to the nearest point on each body', function () {
    $field = new phpfisx_field([0, 500, 0, 500], 0);
    $field->spawnBox(100, 100, 40, 40, 1.0);    // blueprint 0: corners at (80,80) (120,80) (80,120) (120,120)
    $field->spawnBox(300, 100, 40, 40, 1.0);    // blueprint 1: corners at (280,80) (320,80) (280,120) (320,120)

    // Click near the top-right corner of box 0, and the top-left corner of box 1
    $field->addJointBlueprint(0, 119.0, 81.0, 1, 281.0, 81.0);

    $field->desiredPointCount(0);
    $field->setSteps(1);

    $field->setStep(1);
    $ref = new \ReflectionMethod($field, 'calculate');
    $ref->setAccessible(true);
    $ref->invoke($field);

    $joints = $field->getJoints();
    expect($joints)->toHaveCount(1);
    expect($joints[0]->isAnchored())->toBeFalse();
    expect($joints[0]->getA()->getX())->toBe(120.0);
    expect($joints[0]->getA()->getY())->toBe(80.0);
    expect($joints[0]->getB()->getX())->toBe(280.0);
    expect($joints[0]->getB()->getY())->toBe(80.0);
});

it('resolves a shape-to-anchor joint blueprint into an anchored joint', function () {
    $field = new phpfisx_field([0, 500, 0, 500], 0);
    $field->spawnBox(100, 100, 40, 40, 1.0); // blueprint 0

    // "to" endpoint has no shapeIndex — it's a fixed world anchor
    $field->addJointBlueprint(0, 81.0, 81.0, null, 10.0, 10.0);

    $field->desiredPointCount(0);
    $field->setSteps(1);

    $field->setStep(1);
    $ref = new \ReflectionMethod($field, 'calculate');
    $ref->setAccessible(true);
    $ref->invoke($field);

    $joints = $field->getJoints();
    expect($joints)->toHaveCount(1);
    expect($joints[0]->isAnchored())->toBeTrue();
    expect($joints[0]->getAnchor())->toBe([10.0, 10.0]);
    expect($joints[0]->getA()->getX())->toBe(80.0);
    expect($joints[0]->getA()->getY())->toBe(80.0);
});

it('skips a joint blueprint with two fixed anchors — nothing movable to connect', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $field->addJointBlueprint(null, 0.0, 0.0, null, 50.0, 50.0);

    $field->desiredPointCount(0);
    $field->setSteps(1);

    $field->setStep(1);
    $ref = new \ReflectionMethod($field, 'calculate');
    $ref->setAccessible(true);
    $ref->invoke($field);

    expect($field->getJoints())->toHaveCount(0);
});

it('getJointBlueprints returns registered blueprints before materialization', function () {
    $field = new phpfisx_field([0, 500, 0, 500]);
    $field->addJointBlueprint(0, 1.0, 2.0, null, 3.0, 4.0, 25.0);

    $blueprints = $field->getJointBlueprints();
    expect($blueprints)->toHaveCount(1);
    expect($blueprints[0]['fromIdx'])->toBe(0);
    expect($blueprints[0]['toIdx'])->toBeNull();
    expect($blueprints[0]['rest'])->toBe(25.0);
});
