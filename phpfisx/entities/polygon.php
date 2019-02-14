<?php
namespace phpfisx\entities;

class polygon {
    public $pos;
    public $angle;
    public $offset;
    public $points;
    public $calcPoints;
    public $edges;
    public $normals;

    /**
     * @param vector $pos representing the origin of the polygon. (all other
     *   points are relative to this one)
     * @param array $points An array of vectors representing the points in the polygon,
     *   in counter-clockwise order.
     */
    public function __construct(vector $pos = null, $points = array()) {
        $this->pos = $pos ? $pos : new vector();
        $this->angle = 0;
        $this->offset = new vector();
        $this->setPoints($points);
    }

    /**
     * Allocate the vector arrays for the calculated properties
     * @param $points
     * @return $this
     */
    public function setPoints($points) {
        $lengthChanged = !$this->points || count($this->points) !== count($points);
        if ($lengthChanged) {
            $this->calcPoints = array();
            $this->edges = array();
            $this->normals = array();
            foreach ($points as &$point) {
                $this->calcPoints[] = new vector();
                $this->edges[] = new vector();
                $this->normals[] = new vector();
            }
        }
        $this->points = $points;
        $this->_recalc();
        return $this;
    }

    /**
     * Set the current rotation angle of the polygon.
     * @param $angle
     * @return $this
     */
    public function setAngle($angle) {
        $this->angle = $angle;
        $this->_recacl();
        return $this;
    }

    /**
     * Set the current offset to apply to the points before applying the angle rotation.
     * @param $offset
     * @return $this
     */
    public function setOffset($offset) {
        $this->offset = $offset;
        $this->_recacl();
        return $this;
    }

    /**
     * Rotates this polygon counter-clockwise around the origin of its local coordinate system.
     * @param $angle
     * @return $this
     */
    public function rotate($angle) {
        foreach ($this->points as &$point) {
            $point->rotate($angle);
        }
        $this->_recalc();
        return $this;
    }

    /**
     * Translate the original points of this polygin (relative to the local coordinate system) by the specified amounts. The offset translation will be applied on top of this translation.
     * @param $x
     * @param $y
     * @return $this
     */
    public function translate($x, $y) {
        foreach ($this->points as &$point) {
            $point->x += $x;
            $point->y += $y;
        }
        $this->_recalc();
        return $this;
    }

    /**
     * Computes the calculated collision polygon.
     * @return $this
     */
    public function _recalc() {
        $len = count($this->points);
        for ($i = 0; $i < $len; $i++) {
            $this->calcPoints[$i] = clone $this->points[$i];
            $this->calcPoints[$i]->x += $this->offset->x;
            $this->calcPoints[$i]->y += $this->offset->y;
            if ($this->angle != 0) {
                $this->calcPoints[$i]->rotate($this->angle);
            }
        }
        for ($i = 0; $i < $len; $i++) {
            $p1 = $this->calcPoints[$i];
            $p2 = $i < $len - 1 ? $this->calcPoints[$i + 1] : $this->calcPoints[0];
            $this->edges[$i] = clone $p2;
            $e = $this->edges[$i]->sub($p1);
            $this->normals[$i] = clone $e;
            $this->normals[$i]->perp()->normalize();
        }
        return $this;
    }

    // /**
    //  * Compute the axis-aligned bounding box.
    //  * @return box
    //  */
    // public function getAABB() {
    //     $len = count($this->calcPoints);
    //     $points = &$this->calcPoints;
    //     $xMin = $this->points[0]->x;
    //     $yMin = $points[0]->y;
    //     $xMax = $points[0]->x;
    //     $yMax = $points[0]->y;
    //     for ($i = 1; $i < $len; $i++) {
    //         $point = &$points[$i];
    //         if ($point->x < $xMin) {
    //           $xMin = $point->x;
    //         } else if ($point->x > $xMax) {
    //           $xMax = $point->x;
    //         }
    //         if ($point->y < $yMin) {
    //           $yMin = $point->y;
    //         } else if ($point->y > $yMax) {
    //           $yMax = $point->y;
    //         }
    //     }
    //     $pos = clone $this->pos;
    //     return (new box($pos->add(new vector($xMin, $yMin)), $xMax - $xMin, $yMax - $yMin))->toPolygon();
    // }
}