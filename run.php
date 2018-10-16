<?php
require_once('boot.php');
use phpfisx\areas\field as field;
$field = new field(array(0,500,0,500), 1, 1, 4);
$field->step($_GET['step']);
$field->visualize();
