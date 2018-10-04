<?php
namespace phpfisx\entities;

class point {
    public $id;
    public $x;
    public $y;
    public $z;
    private $field;

    public function __construct(\phpfisx\areas\field $field, int $seed) {
        $this->id = $this->uuid();
        $this->field = $field;
        srand($seed);
        $this->setCoords(
            rand($this->field->getBounds('x', 'min'), $this->field->getBounds('x', 'max')),
            rand($this->field->getBounds('y', 'min'), $this->field->getBounds('y', 'max'))
        );
    }

    private function uuid() {
        return rand(10000,99999) . '-' . rand(10000,99999) . '-' . rand(10000,99999) . '-' . rand(10000,99999);
    }

    public function getVelocity() {

    }

    public function setVelocity() {
        
    }

    public function applyForce($amount, $direction, $step) {
        $new_x = $this->getX();
        $new_y = $this->getY();
        for ($i=0; $i < $step; $i++) { 
            // Calculate X,Y from Cos/Sin
            $new_x = $new_x + ($amount * (sin(deg2rad($direction))));
            $new_y = $new_y + ($amount * (cos(deg2rad($direction))));
        }
        // Calculate distance between start/end for sanity check
        $distance = sqrt(pow($new_x - $this->getX(), 2) + pow($new_y - $this->getY(), 2));
        // Check distance traveled matches distance requested
        if (round($distance, 2) === round($amount * $step, 2)) {
            // Set end states
            $final_x = $new_x;
            $final_y = $new_y;
            // Set border
            $border = 4;
            // if x is pos out of bounds, limit x to max bounds
            if($new_x > $this->field->getBounds("x", "max")) {
                $final_x = $this->field->getBounds("x", "max") - $border;
            }
            // if y is pos out of bounds, limit y to max bounds
            if($new_y > $this->field->getBounds("y", "max")) {
                $final_y = $this->field->getBounds("y", "max") - $border;
            }
            // if x is neg out of bounds, limit x to min bounds
            if($new_x < $this->field->getBounds("x", "min")) {
                $final_x = $this->field->getBounds("x", "min") + $border;
            }
            // if y is neg out of bounds, limit y to min bounds
            if($new_y < $this->field->getBounds("y", "min")) {
                $final_y = $this->field->getBounds("y", "min") + $border;
            }
            // Set new position of point
            $this->setCoords($final_x, $final_y);
        } else {
            throw new \Exception("Invalid vector movement");
        }
        
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