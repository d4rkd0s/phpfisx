<?php
namespace Tests\Unit;

use phpfisx\entities\point;
use phpfisx\areas\field as phpfisx_field;

it('asserts true is true', function () {
    $this->assertTrue(true);
    expect(true)->toBeTrue();
});

it('instantiates', function () {
    $field = new phpfisx_field(array(0,500,0,500));
    $point = new point($field, 42);
    expect($point)->toBeInstanceOf(point::class);
});