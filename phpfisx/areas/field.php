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
    private float $friction;
    private float $collisionRadius;

    private $step;
    private $steps;

    private $border;

    private $pointCount;

    /**
     * @param array $bounds   [x_min, x_max, y_min, y_max]
     * @param int   $gravity  Downward acceleration added to velocity per step
     * @param int   $border   Wall thickness in pixels
     * @param float $friction        Velocity multiplier per step (0–1). 1 = no drag, 0 = instant stop.
     *                               Default 0.98 gives a terminal velocity of ~50px/step under gravity=1.
     * @param float $collisionRadius Minimum distance (px) before two points are treated as colliding.
     */
    public function __construct($bounds, $gravity = 1, $border = 4, float $friction = 0.98, float $collisionRadius = 5.0) {
        $this->x_min          = $bounds[0];
        $this->x_max          = $bounds[1];
        $this->y_min          = $bounds[2];
        $this->y_max          = $bounds[3];
        $this->border         = $border;
        $this->friction       = $friction;
        $this->collisionRadius = $collisionRadius;
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

    public function getFriction(): float {
        return $this->friction;
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
        return $this->y_max;
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
        foreach ($this->points as $point) {
            foreach ($forces as $force) {
                if (in_array($this->step, $force['steps'])) {
                    $point->applyForce($force['amount'], $force['direction']);
                }
            }
        }
    }

    private function turbulence($amount = 1) {
        foreach ($this->points as $point) {
            $point->applyForce(round(rand(0, $amount)), round(rand(1, $this->TURBULENCE_LEVEL)));
        }
    }

    private function applyGravity() {
        foreach ($this->points as $point) {
            $point->applyForce($this->getGravity(), 0);
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
        fwrite($fp, json_encode([
            "step"   => $this->getStep(),
            "points" => array_map(fn($p) => $p->toArray(), $this->points),
            "lines"  => $this->lines,
        ]));
        fclose($fp);
    }

    private function loadFromDisk() {
        $disk = json_decode(file_get_contents('field.json'), true);
        $points = [];
        foreach ($disk['points'] as $raw) {
            $points[] = new point(
                $this, 0,
                $raw['id'],
                $raw['x'],
                $raw['y'],
                $raw['vx']   ?? 0.0,
                $raw['vy']   ?? 0.0,
                $raw['mass'] ?? 1.0
            );
        }
        $this->points = $points;
        $lines = [];
        foreach ($disk['lines'] as $raw) {
            $lines[] = new line($this, 0, $raw['id'], $raw['start_x'], $raw['start_y'], $raw['end_x'], $raw['end_y']);
        }
        $this->lines = $lines;
    }

    public function runFisx() {
        $this->turbulence();
        $this->applyGravity();
        $this->resolvePointCollisions();
        foreach ($this->points as $point) {
            $point->integrate();
        }
    }

    /**
     * resolvePointCollisions — Elastic impulse response for overlapping point pairs.
     *
     * For every pair (i, j): if their distance is less than collisionRadius,
     * apply a mass-weighted impulse along the collision normal and push them apart.
     * Complexity is O(n²) — fine for hundreds of points; spatial hashing would be
     * needed for thousands.
     */
    private function resolvePointCollisions(): void {
        $n      = count($this->points);
        $radius = $this->collisionRadius;

        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $a = $this->points[$i];
                $b = $this->points[$j];

                $dx = $a->getX() - $b->getX();
                $dy = $a->getY() - $b->getY();
                $dist = sqrt($dx * $dx + $dy * $dy);

                if ($dist >= $radius || $dist < 0.0001) {
                    continue;
                }

                // Collision normal (unit vector from b → a)
                $nx = $dx / $dist;
                $ny = $dy / $dist;

                // Relative velocity along normal
                $va  = $a->getVelocity();
                $vb  = $b->getVelocity();
                $rvn = ($va->x - $vb->x) * $nx + ($va->y - $vb->y) * $ny;

                // Only resolve if points are approaching
                if ($rvn >= 0) {
                    continue;
                }

                // Elastic impulse: J = -2 * rvn / (1/ma + 1/mb)
                $ma = $a->getMass();
                $mb = $b->getMass();
                $J  = -2.0 * $rvn / (1.0 / $ma + 1.0 / $mb);

                $va->x += ($J / $ma) * $nx;
                $va->y += ($J / $ma) * $ny;
                $vb->x -= ($J / $mb) * $nx;
                $vb->y -= ($J / $mb) * $ny;

                // Positional correction — push apart to eliminate overlap
                $overlap = ($radius - $dist) / 2.0;
                $a->setCoords($a->getX() + $nx * $overlap, $a->getY() + $ny * $overlap);
                $b->setCoords($b->getX() - $nx * $overlap, $b->getY() - $ny * $overlap);
            }
        }
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
        $border = 2;
        $frames = [];

        for ($step = 1; $step <= $this->steps; $step++) {
            $this->setStep($step);
            $this->calculate();

            $gd    = imagecreatetruecolor($this->x_max, $this->y_max);
            $white = imagecolorallocate($gd, 255, 255, 255);
            $gray  = imagecolorallocate($gd, 245, 245, 245);
            $black = imagecolorallocate($gd, 0, 0, 0);

            imagefilledrectangle($gd, 0, 0, $this->getXMax(), $this->getYMax(), $black);
            imagefilledrectangle($gd, $border, $border, $this->getXMax() - $border * 1.5, $this->getYMax() - $border * 1.5, $white);

            foreach ($this->points as $point) {
                $px = round($point->getX());
                $py = round($point->getY());
                imagesetpixel($gd, $px,     $py - 1, $black);
                imagesetpixel($gd, $px - 1, $py,     $black);
                imagesetpixel($gd, $px,     $py,     $black);
                imagesetpixel($gd, $px + 1, $py,     $black);
                imagesetpixel($gd, $px,     $py + 1, $black);
            }

            foreach ($this->lines as $line) {
                imageline($gd, $line->getStartX(), $line->getStartY(), $line->getEndX(), $line->getEndY(), $black);
            }

            imagefilledrectangle($gd, $border + 1, $border + 1, round(60 + strlen((string)$step)), 20, $gray);
            imagestring($gd, 4, 4, 4, 'step ' . $step, $black);

            ob_start();
            imagepng($gd);
            $frames[] = base64_encode(ob_get_clean());
            imagedestroy($gd);
        }

        header('Content-Type: text/html; charset=utf-8');
        echo $this->renderAnimation($frames);
    }

    private function renderAnimation(array $frames): string {
        $framesJson = json_encode($frames);
        $total      = count($frames);
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { background: #fff; display: flex; flex-direction: column; align-items: center; }
  canvas { display: block; image-rendering: pixelated; width: 100%; max-width: 500px; }
  .bar { display: flex; align-items: center; gap: 8px; padding: 6px 4px; font: 12px monospace; width: 100%; max-width: 500px; }
  .bar span { min-width: 60px; color: #555; }
  input[type=range] { flex: 1; accent-color: #333; }
  button { padding: 2px 10px; font: 12px monospace; cursor: pointer; }
</style>
</head>
<body>
<canvas id="c" width="500" height="500"></canvas>
<div class="bar">
  <button id="btn">⏸</button>
  <input type="range" id="scrub" min="0" max="{$total}" value="0" step="1">
  <span id="lbl">step 1 / {$total}</span>
</div>
<script>
(function() {
  var frames = {$framesJson};
  var total  = frames.length;
  var canvas = document.getElementById('c');
  var ctx    = canvas.getContext('2d');
  var scrub  = document.getElementById('scrub');
  var lbl    = document.getElementById('lbl');
  var btn    = document.getElementById('btn');
  var cur    = 0;
  var playing = true;
  var timer;

  function draw(i) {
    var img = new Image();
    img.onload = function() { ctx.drawImage(img, 0, 0, 500, 500); };
    img.src = 'data:image/png;base64,' + frames[i];
    scrub.value = i;
    lbl.textContent = 'step ' + (i + 1) + ' / ' + total;
  }

  function tick() {
    draw(cur);
    cur = (cur + 1) % total;
  }

  function startLoop() { timer = setInterval(tick, 80); }
  function stopLoop()  { clearInterval(timer); }

  scrub.addEventListener('input', function() {
    cur = parseInt(this.value);
    draw(cur);
  });

  btn.addEventListener('click', function() {
    playing = !playing;
    btn.textContent = playing ? '⏸' : '▶';
    playing ? startLoop() : stopLoop();
  });

  draw(0);
  startLoop();
})();
</script>
</body>
</html>
HTML;
    }

}
