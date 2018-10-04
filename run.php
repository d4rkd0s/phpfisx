<?php
require_once('boot.php');
use phpfisx\areas\field as field;
$field = new field(array(0,250,0,250), 1, 5);
// $field->debug();
$field->visualize($_GET['step']);
