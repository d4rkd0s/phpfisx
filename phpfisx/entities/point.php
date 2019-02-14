<?php
namespace phpfisx\entities;

class point {
    public $id;
    public $x;
    public $y;
    public $z;
    private $field;

    public function __construct(\phpfisx\areas\field $field, int $seed = 0, string $existing_id = "", int $existing_x = 0, int $existing_y = 0) {
        $this->field = $field;
        if($seed !== 0) {
            $this->id = $this->uuid();
            srand($seed);
            $this->setCoords(
                rand(0, $this->field->getXMax()),
                rand(0, $this->field->getYMax())
            );
        } else {
            $this->id = $existing_id;
            $this->setCoords($existing_x, $existing_y);
        }
    }

    private function uuid() {
        return rand(10000,99999) . '-' . rand(10000,99999) . '-' . rand(10000,99999) . '-' . rand(10000,99999);
    }

    public function getVelocity() {

    }

    public function setVelocity() {
        
    }

    /**
     * applyForce - Applies force to the point
     *
     * @param integer $amount - Any number, indicates the "amount" of force to apply, no units yet
     * @param integer $direction - Direction from 0-360 to apply the force from
     * @param integer $step - The current fisx step
     * @return void
     */
    public function applyForce(int $amount, int $direction, int $step) {
        $new_x = $this->getX();
        $new_y = $this->getY();
        for ($i=0; $i < $step; $i++) { 
            // Calculate X,Y from Cos/Sin
            $new_x = $new_x + ($amount * (sin(deg2rad($direction))));
            $new_y = $new_y + ($amount * (cos(deg2rad($direction))));
            // Calculate distance between start/end for sanity check
            $distance = sqrt(pow($new_x - $this->getX(), 2) + pow($new_y - $this->getY(), 2));
            // Set end states
            $final_x = $new_x;
            $final_y = $new_y;
            // Set border
            // if x is pos out of bounds, limit x to max bounds
            if($new_x > $this->field->getXMax()) {
                $final_x = $this->field->getXMax() - $this->field->getBorder();
            }
            // if y is pos out of bounds, limit y to max bounds
            if($new_y > $this->field->getYMax()) {
                $final_y = $this->field->getYMax() - $this->field->getBorder();
            }
            // if x is neg out of bounds, limit x to min bounds
            if($new_x < 0) {
                $final_x = 0 + $this->field->getBorder();
            }
            // if y is neg out of bounds, limit y to min bounds
            if($new_y < 0) {
                $final_y = 0 + $this->field->getBorder();
            }
        }
        // Check distance traveled matches distance requested
        if (round($distance, 2) === round($amount * $step, 2)) {
            // Set new position of point
            $this->setCoords($final_x, $final_y);
        } else {
            throw new \Exception("Invalid vector movement");
        }
        
    }

    public function setCoords($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }

    public function getCoords() {
        return array($this->x, $this->y);
    }

    public function getX() {
        return $this->x;
    }

    public function getY() {
        return $this->y;
    }

    public function getID() {
        return $this->id;
    }
}