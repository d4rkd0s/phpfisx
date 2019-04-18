<?php
require_once('boot.php');

use phpfisx\areas\field as field;
$field = new field(array(0,500,0,500));
$field->desiredPointCount(500);
$field->setSteps(25);
$field->visualize();
