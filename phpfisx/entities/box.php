<?php
namespace phpfisx\entities;

class box {
    public $pos;
    public $w;
    public $h;
    /**
     * @param vector $pos A vector representing the bottom-left of the box (i.e. the smallest x and smallest y value).
     * @param int $w The width of the box.
     * @param int $h The height of the box.
     */
    public function __construct(vector $pos = null, $w = 0, $h = 0){
        $this->pos = $pos ? $pos : new vector();
        $this->w = $w;
        $this->h = $h;
    }
    /**
     * Returns a new polygon whose edges are the edges of the box.
     * @return polygon
     */
    public function toPolygon(){
        return new polygon(new vector($this->pos->x, $this->pos->y),
            array(
                new vector(), new vector($this->w, 0),
                new vector($this->w,$this->h), new vector(0,$this->h)
            )
        );
    }
}