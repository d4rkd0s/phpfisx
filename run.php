<?php
require_once('boot.php');
use phpfisx\areas\field as field;
$field = new field(array(0,500,0,500));
$field->desiredPointCount(2500);
$field->setStep($_GET['step']);
$field->visualize();
