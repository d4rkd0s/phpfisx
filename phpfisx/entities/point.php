<?php
namespace phpfisx\entities;

class point {
    public $id;
    public $x;
    public $y;
    public $z;
    public $mode;

    public function __construct(\phpfisx\areas\field $field, int $seed) {
        $this->id = $this->uuid();
        srand($seed);
        $this->setCoords(10,10);
        // $this->x = rand($field->getBounds('x', 'min'), $field->getBounds('x', 'max'));
        // $this->y = rand($field->getBounds('y', 'min'), $field->getBounds('y', 'max'));
    }

    private function uuid() {
        return rand(1000,9999) . rand(1000,9999) . rand(1000,9999) . rand(1000,9999);
    }

    public function getVelocity() {

    }

    public function setVelocity() {
        
    }

    public function applyForce($amount, $direction) {

        // Get current position x,y
        error_log("Amount: " . $amount);
        error_log("Angle: " . $direction);
        error_log("Old X: " . $this->getX());
        error_log("Old Y: " . $this->getY());

        error_log("test1: " . sin(deg2rad($direction))); 
        error_log("test2: " . cos(deg2rad($direction))); 

        $new_x = $this->getX() + ($amount * (sin(deg2rad($direction))));
        $new_y = $this->getY() + ($amount * (cos(deg2rad($direction))));
        error_log("New X: " . $new_x);
        error_log("New Y: " . $new_y);


        // Calculate distance between start/end
        $distance = sqrt(pow($new_x - $this->getX(), 2) + pow($new_y - $this->getY(), 2));
        error_log("Distance traveled: " . $distance);

        // Calculate force direction resulting location
        $this->setCoords($new_x, $new_y);

        // Move x

        // if out of bounds, limit to bounds

        // Move y

        // if out of bounds, limit to bounds



        // $new_y = $point->getY() + $this->getGravity() * $step;
        // if($new_y > $this->y_max) {
        //     $point->setCoords($point->getX(), $this->getBounds("y", "max") - 4);
        //     // $this->setPointVal($point["id"], $point["x"], $this->y_max - 4);
        // } else {
        //     $point->setCoords($point->getX(), $new_y);
        //     // $this->setPointVal($point["id"], $point["x"], $new_y);
        // }
    }

    public function setCoords($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }

    public function getCoords() {
        return arrat($this->x, $this->y);
    }

    public function getX() {
        return $this->x;
    }

    public function getY() {
        return $this->y;
    }
}