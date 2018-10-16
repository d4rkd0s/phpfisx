<?php
namespace phpfisx\areas;

use \phpfisx\entities\point as point;

class field {
    private $x_min = 0;
    private $x_max = 0;
    private $y_min = 0;
    private $y_max = 0;

    private $valid = false;
    private $points = array();
    private $gravity;

    private $border;

    public function __construct($bounds, $num_of_points, $gravity, $border = 4) {
        $this->x_min = $bounds[0];
        $this->x_max = $bounds[1];
        $this->y_min = $bounds[2];
        $this->y_max = $bounds[3];
        $this->border = $border;
        $this->ensureFieldSpace();
        $this->generatePoints($num_of_points);
        $this->setGravity($gravity);
    }

    public function getBorder() {
        return $this->border;
    }

    private function setGravity($gravity) {
        $this->gravity = $gravity;
    }

    private function getGravity() {
        return $this->gravity;
    }

    public function getBounds($axis, $type) {
        switch ($axis) {
            case 'x':
                if ($type === "min") { return $this->x_min; }
                else if ($type === "max") { return $this->x_max; }
                break;
            case 'y':
                if ($type === "min") { return $this->y_min;}
                else if ($type === "max") { return $this->y_max; }
            break;
            default: return 0; break;
        }
    }

    private function ensureFieldSpace() {
        // Check that there is space in X
        if(abs($this->x_min - $this->x_max) > 0) {
            // Check that there is space in Y
            if(abs($this->y_min - $this->y_max) > 0) {
                $this->valid = true;
            }
        }
    }

    public function generatePoints($num) {
        for ($i=0; $i < $num; $i++) { 
            $this->points[$i] = new point($this, $i);
        }
    }

    public function runFisx($step) {
        // Run physics on each point
        foreach ($this->points as $point) {
            // Random Light Force
            // $point->applyForce(1, round(rand(0,360)), $step);
            $point->applyForce(10, 170, $step);
            // Gravity
            // $point->applyForce($this->getGravity()*($step*$this->getGravity()), round(0), $step);
        }
    }

    public function debug($step) {
        $this->runFisx($step);
        echo json_encode(array(
            'type' => 'field',
            'validity' => ($this->valid ? 'valid' : 'invalid'),
            'bounds' => array(
                'x_min' => $this->x_min,
                'x_max' => $this->x_max,
                'y_min' => $this->y_min,
                'y_max' => $this->y_max
            ),
            'points' => $this->points
        ));        
    }

    public function visualize($step) {
        $this->runFisx($step);

        $gd = imagecreatetruecolor($this->x_max, $this->y_max);

        $border = 2;

        $white = imagecolorallocate($gd, 255, 255, 255);
        $gray = imagecolorallocate($gd, 245, 245, 245);
        $black = imagecolorallocate($gd, 0, 0, 0);
        $red = imagecolorallocate($gd, 255, 0, 0);
        $blue = imagecolorallocate($gd, 0, 0, 255);
        
        // Set frame
        imagefilledrectangle($gd, 0, 0, $this->getBounds('x', 'max'), $this->getBounds('y', 'max'), $black);

        // Set background
        imagefilledrectangle($gd, $border, $border, $this->getBounds('x', 'max') - $border*1.5, $this->getBounds('y', 'max') - $border*1.5, $white);

        foreach ($this->points as $point) {
            $pointx = round($point->getX());
            $pointy = round($point->getY());
            imagesetpixel($gd, $pointx, $pointy-1, $black);
            imagesetpixel($gd, $pointx-1, $pointy, $black);
            imagesetpixel($gd, $pointx, $pointy, $black);
            imagesetpixel($gd, $pointx+1, $pointy, $black);
            imagesetpixel($gd, $pointx, $pointy+1, $black);
        }

        
        // Set text background
        imagefilledrectangle($gd, $border+1, $border+1, round(60+strlen(strval($step))), 20, $gray);

        // Set details
        imagestring($gd, 4, 4, 4, 'time ' . $step, $black);
        
        header('Content-Type: image/png');
        imagepng($gd);
    }

}