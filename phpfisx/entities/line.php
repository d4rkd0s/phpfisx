<?php
namespace phpfisx\entities;

class line {
    public $id;
    public $start_x;
    public $start_y;
    public $start_z;
    public $end_x;
    public $end_y;
    public $end_z;
    private $field;

    public function __construct(\phpfisx\areas\field $field, int $seed = 0, string $existing_id = "", int $existing_start_x = 0, int $existing_start_y = 0, int $existing_end_x = 0, int $existing_end_y = 0) {
        $this->field = $field;
        if($seed !== 0) {
            $this->id = $this->uuid();
            srand($seed);
            $this->setCoords(
                rand(0, $this->field->getXMax()),
                rand(0, $this->field->getYMax()),
                rand(0, $this->field->getXMax()),
                rand(0, $this->field->getYMax())
            );
        } else {
            $this->id = $existing_id;
            $this->setCoords($existing_start_x, $existing_start_y, $existing_end_x, $existing_end_y);
        }
    }

    private function uuid() {
        return rand(10000,99999) . '-' . rand(10000,99999) . '-' . rand(10000,99999) . '-' . rand(10000,99999);
    }

    public function setCoords($start_x, $start_y, $end_x, $end_y) {
        $this->start_x = $start_x;
        $this->start_y = $start_y;
        $this->end_x = $end_x;
        $this->end_y = $end_y;
    }
    
    public function isOnLine(int $x, int $y) {
        $x1 = $this->start_x;
        $y1 = $this->start_y;
        $x2 = $this->end_x;
        $y2 = $this->end_y;
        $x3 = $x;
        $y3 = $y;
        $x4 = $this->end_x;
        $y4 = $this->end_y;
        $denominator = (($y4 - $y3) * ($x2 - $x1)) - (($x4 - $x3) * ($y2 - $y1));
        $numerator_a = (($x4 - $x3) * ($y1 - $y3)) - (($y4 - $y3) * ($x1 - $x3));
        $numerator_b = (($x2 - $x1) * ($y1 - $y3)) - (($y2 - $y1) * ($x1 - $x3));
        if($denominator==0) { return false; }
        $ua = $numerator_a / $denominator;
        $ub = $numerator_b / $denominator;
        if ($ua >= 0 && $ua <= 1 && $ub >= 0 && $ub <= 1) {
            return true;
        } else {
            return false;
        }
    }

    public function getCoords() {
        return array($this->getStartX(), $this->getStartY(), $this->getEndX(), $this->getEndY());
    }

    public function getStartX() {
        return $this->start_x;
    }

    public function getStartY() {
        return $this->start_y;
    }

    public function getEndX() {
        return $this->end_x;
    }

    public function getEndY() {
        return $this->end_y;
    }

    public function getID() {
        return $this->id;
    }
}