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

    private $step;

    private $border;

    private $pointCount;

    public function __construct($bounds, $gravity = 1, $border = 4) {
        $this->x_min = $bounds[0];
        $this->x_max = $bounds[1];
        $this->y_min = $bounds[2];
        $this->y_max = $bounds[3];
        $this->border = $border;
        $this->ensureFieldSpace();
        $this->setGravity($gravity);
    }

    public function getBorder() {
        return $this->border;
    }

    private function setGravity($gravity) {
        $this->gravity = $gravity;
    }

    public function desiredPointCount(int $num) {
        $this->pointCount = $num;
    }

    private function getGravity() {
        return $this->gravity;
    }

    public function setStep(int $step) {
        $this->step = $step;
    }

    public function getStep() {
        return $this->step;
    }

    public function getLastStep() {
        $disk = json_decode(file_get_contents('field.json'), true);
        return $disk['step'];
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

    public function generatePoints() {
        for ($i=0; $i < $this->pointCount; $i++) { 
            $this->points[$i] = new point($this, $i);
        }
    }

    private function applyForces(array $forces) {
        // Run physics on each point
        foreach ($this->points as $point) {
            // Random Light Force
            foreach($forces as $force) {
                if(in_array($this->step, $force['steps'])){
                    $point->applyForce($force['amount'], $force['direction'], $this->step);
                }
            }
        }
    }

    private function applyGravity() {
         foreach ($this->points as $point) {
            $point->applyForce($this->getGravity()*($this->step*$this->getGravity()), round(0), $this->step);
        }
    }

    private function checkCollisions() {
        return true;
    }

    private function resetDisk() {
        $fp = fopen('field.json', 'w');
        fwrite($fp, json_encode(array(
            "step" => 0,
            "points" => array()
        )));
        fclose($fp);
    }

    private function persistToDisk() {
        $fp = fopen('field.json', 'w');
        fwrite($fp, json_encode(array(
            "step" => $this->getStep(),
            "points" => $this->points
        )));
        fclose($fp);
    }

    private function loadFromDisk() {
        $disk = json_decode(file_get_contents('field.json'), true);
        $points = array();
        foreach ($disk['points'] as $raw_point) {
            array_push($points, new point($this, 0, $raw_point['id'], $raw_point['x'], $raw_point['y']));
        }
        $this->points = $points;
    }

    private function initDisk() {
        if(!file_exists('field.json')) {
            $this->resetDisk();
        }
    }

    public function runFisx() {
        $forces = array(
            [
                "ids"=>"all",
                "force"=>"linear",
                "direction"=>170,
                "amount"=>10,
                "steps"=>[1,2,3]
            ]
        );
        $this->applyForces($forces);
        $this->applyGravity();
        $this->checkCollisions();
    }

    public function calculate() {
        // Ensure disk is setup
        $this->initDisk();

        // if the step is the first step, clear the field, build the points, and run fisx
        if($this->getStep() === 1) {
            $this->resetDisk();
            $this->generatePoints();
        } 
        // if the step is n and n-1 = last step, then load points from file
        else if($this->getStep()-1 === $this->getLastStep()) {
            $this->loadFromDisk();
        }
        // if the step is n and n-1 != last step, then throw exception (request out of sequence)
        else if($this->getStep()-1 !== $this->getLastStep()) {
            throw new \Exception('request out of sequence req step: ' . $this->getStep() . " laststep: " . $this->getLastStep());
        }

        // Run physics calculations
        $this->runFisx();

        // Save to disk
        $this->persistToDisk();
    }

    public function visualize() {
        $this->calculate();

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
        imagefilledrectangle($gd, $border+1, $border+1, round(60+strlen(strval($this->step))), 20, $gray);

        // Set details
        imagestring($gd, 4, 4, 4, 'time ' . $this->step, $black);
        
        header('Content-Type: image/png');
        imagepng($gd);
    }

}