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
        $this->x = rand($field->getBounds('x', 'min'), $field->getBounds('x', 'max'));
        $this->y = rand($field->getBounds('y', 'min'), $field->getBounds('y', 'max'));
    }

    private function uuid() {
        return rand(1000,9999) . rand(1000,9999) . rand(1000,9999) . rand(1000,9999);
    }

    public function getVelocity() {

    }

    public function setVelocity() {
        
    }

    public function applyForce() {
        
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