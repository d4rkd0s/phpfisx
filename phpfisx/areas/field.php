<?php
namespace phpfisx\areas;

use \phpfisx\entities\point as point;
use \phpfisx\entities\line as line;

class field {
    private $TURBULENCE_LEVEL = 1000;

    private $x_min = 0;
    private $x_max = 0;
    private $y_min = 0;
    private $y_max = 0;

    private $valid = false;
    private $points = array();
    private $lines = array();
    private $gravity;

    private $step;
    private $steps;

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

    public function getLines() {
        return $this->lines;
    }

    public function desiredPointCount(int $num) {
        $this->pointCount = $num;
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

    public function setSteps(int $steps) {
        $this->steps = $steps;
    }

    public function getSteps() {
        return $this->steps;
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

    public function getXMax() {
        return $this->x_max;
    }

    public function getYMax() {
        return $this->x_max;
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

    public function generateLines() {
        for ($i=0; $i < 3; $i++) { 
            $this->lines[$i] = new line($this, $i);
        }
    }

    private function applyForces(array $forces) {
        // Run physics on each point
        foreach ($this->points as $point) {
            // Random Light Force
            foreach($forces as $force) {
                if(in_array($this->step, $force['steps'])){
                    $point->applyForce($force['amount'], $force['direction'], $this->getStep());
                }
            }
        }
    }

    private function turbulence($amount = 1) {
        foreach ($this->points as $point) {
            $point->applyForce(round(rand(0,$amount)), round(rand(1,$this->TURBULENCE_LEVEL)), $this->getStep());
        }
    }

    private function applyGravity() {
        foreach ($this->points as $point) {
            // $this->getGravity()*($this->step*$this->getGravity())
            $point->applyForce($this->getGravity(), round(0), $this->getStep());
        }
    }

    private function checkCollisions() {
        foreach ($this->points as $point) {
            $point->checkCollisions($this->lines);
        }
    }

    private function resetDisk() {
        $fp = fopen('field.json', 'w');
        fwrite($fp, json_encode(array(
            "step" => 0,
            "points" => array(),
            "lines" => array()
        )));
        fclose($fp);
    }

    private function persistToDisk() {
        $fp = fopen('field.json', 'w');
        fwrite($fp, json_encode(array(
            "step" => $this->getStep(),
            "points" => $this->points,
            "lines" => $this->lines
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
        $lines = array();
        foreach ($disk['lines'] as $raw_line) {
            array_push($lines, new line($this, 0, $raw_line['id'], $raw_line['start_x'], $raw_line['start_y'], $raw_line['end_x'], $raw_line['end_y']));
        }
        $this->lines = $lines;
    }

    public function runFisx() {
        // $forces = array(
        //     [
        //         "ids"=>"all",
        //         "force"=>"linear",
        //         "direction"=>90,
        //         "amount"=>1,
        //         "steps"=>[1,2,3,7,8,9,13,14,15,19,20,21,25]
        //     ]
        // );
        // $this->applyForces($forces);
        $this->turbulence(); 
        $this->applyGravity();
        $this->checkCollisions();
    }

    public function calculate() {
        // Ensure disk is setup
        if(!file_exists('field.json')) { $this->resetDisk(); }

        // if the step is the first step, clear the field, build the points, and run fisx
        if($this->getStep() === 1 || $this->getStep() === 0) {
            $this->resetDisk();
            $this->generatePoints();
            // $this->generateLines();
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
        $frames = array();
        $durations = array();
        for ($step=1; $step <= $this->steps; $step++) { 
            $this->setStep($step);
            $this->calculate();
            $gd = imagecreatetruecolor($this->x_max, $this->y_max);
            $border = 2;
            $white = imagecolorallocate($gd, 255, 255, 255);
            $gray = imagecolorallocate($gd, 245, 245, 245);
            $black = imagecolorallocate($gd, 0, 0, 0);
            $red = imagecolorallocate($gd, 255, 0, 0);
            $green = imagecolorallocate($gd, 0, 255, 0);
            $blue = imagecolorallocate($gd, 0, 0, 255);
            // Set frame
            imagefilledrectangle($gd, 0, 0, $this->getXMax(), $this->getYMax(), $black);
            // Set background
            imagefilledrectangle($gd, $border, $border, $this->getXMax() - $border*1.5, $this->getYMax() - $border*1.5, $white);
            # Fill in points
            foreach ($this->points as $point) {
                $pointx = round($point->getX());
                $pointy = round($point->getY());
                imagesetpixel($gd, $pointx, $pointy-1, $black);
                imagesetpixel($gd, $pointx-1, $pointy, $black);
                imagesetpixel($gd, $pointx, $pointy, $black);
                imagesetpixel($gd, $pointx+1, $pointy, $black);
                imagesetpixel($gd, $pointx, $pointy+1, $black);
            }
            # Fill in lines
            foreach ($this->lines as $line) {
                imageline($gd, $line->getStartX(), $line->getStartY(), $line->getEndX(), $line->getEndY(), $black);
            }
            // Set text background
            imagefilledrectangle($gd, $border+1, $border+1, round(60+strlen(strval($this->step))), 20, $gray);
            // Set details
            imagestring($gd, 4, 4, 4, 'time ' . $step, $black);
            
            // header('Content-Type: image/png');
            array_push($frames, "images/image" . strval($step) . ".png");
            array_push($durations, 1);
            imagepng($gd, "images/image" . strval($step) . ".png", 0, 0);
        }


        $anim = new \GifCreator\AnimGif();
        $anim->create($frames, $durations, 0);
        header('Content-type: image/gif');
        header('Content-Disposition: filename="render.gif"');
        $gifBinary = $anim->get();
        echo $gifBinary;
    }

}
