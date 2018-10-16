<?php
namespace phpfisx\entities;

class circle {
    public $pos;
    public $r;

    /**
     * @param vector $pos A vector representing the position of the center of the circle
     * @param int $r The radius of the circle
     */
    public function __construct(vector $pos = null, $r = 0){
        $this->pos = $pos ? $pos : new vector();
        $this->r = $r;
    }

    /**
     * Compute axis aligned bounding box
     * @return box
     */
    public function getAABB(){
        $r = $this->r;
        $pos = clone $this->pos;
        $corner = $pos->sub(new vector($r, $r));
        return (new box($corner, $r*2, $r*2))->toPolygon();
    }
}