<?php
namespace Tests\Unit;

use phpfisx\entities\vector;

it('instantiates', function () {
    $vector = new vector();
    $this->assertInstanceOf(vector::class, $vector);
});