<?php
require_once('boot.php');
use phpfisx\areas\field as field;
$field = new field(array(0,500,0,500), 1000, 1);
$field->desiredPointCount(1);
$field->setStep($_GET['step']);
$field->debug();
