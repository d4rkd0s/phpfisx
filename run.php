<?php
require_once('boot.php');
use phpfisx\areas\field as field;
$field = new field(array(0,500,0,500), 500, 9.86);
// $field->debug();
$field->visualize($_GET['step']);
