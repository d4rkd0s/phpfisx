<?php
require_once('boot.php');

use phpfisx\areas\field as field;
$field = new field(array(0,500,0,500));
$field->desiredPointCount(1000);
$field->setSteps(50);
$field->visualize();
