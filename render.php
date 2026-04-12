<?php
require_once('boot.php');

use phpfisx\areas\field as field;

$points  = max(1,   min(500, (int)(  $_GET['points']   ?? 100)));
$steps   = max(1,   min(200, (int)(  $_GET['steps']    ?? 40)));
$gravity = max(0.0, min(20.0, (float)($_GET['gravity'] ?? 1.0)));
$friction = max(0.0, min(1.0, (float)($_GET['friction'] ?? 0.98)));

$field = new field([0, 500, 0, 500], $gravity, 4, $friction);
$field->desiredPointCount($points);
$field->setSteps($steps);
$field->visualize();
