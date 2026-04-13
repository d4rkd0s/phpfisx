<?php
namespace phpfisx\areas;

use \phpfisx\entities\point as point;
use \phpfisx\entities\line as line;
use \phpfisx\entities\constraint as constraint;

class field {
    private $TURBULENCE_LEVEL = 1000;

    private $x_min = 0;
    private $x_max = 0;
    private $y_min = 0;
    private $y_max = 0;

    private $valid = false;
    private $points = array();
    private $lines = array();
    private array $constraints = [];
    private array $shapeBlueprints = [];
    private array $staticLines = [];
    private ?array $spawnZone = null;
    private $gravity;
    private float $friction;
    private float $collisionRadius;
    private float $restitution;

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
     * @param float $restitution     Bounciness coefficient for all collisions (0 = dead stop, 1 = perfectly elastic).
     */
    public function __construct($bounds, $gravity = 1, $border = 4, float $friction = 0.98, float $collisionRadius = 5.0, float $restitution = 0.7) {
        $this->x_min           = $bounds[0];
        $this->x_max           = $bounds[1];
        $this->y_min           = $bounds[2];
        $this->y_max           = $bounds[3];
        $this->border          = $border;
        $this->friction        = $friction;
        $this->collisionRadius = $collisionRadius;
        $this->restitution     = max(0.0, min(1.0, $restitution));
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

    public function getRestitution(): float {
        return $this->restitution;
    }

    /**
     * Add an immovable collision surface (wall, ramp, platform).
     * Points bounce off it with the field's restitution coefficient.
     */
    public function addStaticLine(float $x1, float $y1, float $x2, float $y2): void {
        $this->staticLines[] = [$x1, $y1, $x2, $y2];
    }

    /**
     * Constrain random particle spawning to a rectangle.
     * Without this, particles spawn anywhere inside the field bounds.
     */
    public function setSpawnZone(float $x1, float $y1, float $x2, float $y2): void {
        $this->spawnZone = [
            min($x1, $x2), min($y1, $y2),
            max($x1, $x2), max($y1, $y2),
        ];
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
        if ($this->spawnZone) {
            [$x1, $y1, $x2, $y2] = $this->spawnZone;
            for ($i = 0; $i < $this->pointCount; $i++) {
                srand($i + 77777); // deterministic but distinct from default seed
                $x = $x1 + ($x2 - $x1) * (rand(0, 10000) / 10000.0);
                $y = $y1 + ($y2 - $y1) * (rand(0, 10000) / 10000.0);
                $this->points[$i] = new point($this, 0, $this->newUuid(), $x, $y);
            }
        } else {
            for ($i = 0; $i < $this->pointCount; $i++) {
                $this->points[$i] = new point($this, $i);
            }
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
        // Only apply to loose points (indices 0..pointCount-1).
        // Rigid body corner points start at index $this->pointCount.
        for ($i = 0; $i < $this->pointCount; $i++) {
            if (isset($this->points[$i])) {
                $this->points[$i]->applyForce(round(rand(0, $amount)), round(rand(1, $this->TURBULENCE_LEVEL)));
            }
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
            "step"        => $this->getStep(),
            "points"      => array_map(fn($p) => $p->toArray(), $this->points),
            "lines"       => $this->lines,
            "constraints" => array_map(fn($c) => [
                'a_id'     => $c->getA()->getID(),
                'b_id'     => $c->getB()->getID(),
                'rest'     => $c->getRestLength(),
                'boundary' => $c->isBoundary(),
            ], $this->constraints),
        ]));
        fclose($fp);
    }

    private function loadFromDisk() {
        $disk   = json_decode(file_get_contents('field.json'), true);
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

        // Rebuild constraints from serialized topology using point IDs
        $pointMap = [];
        foreach ($this->points as $p) {
            $pointMap[$p->getID()] = $p;
        }
        $this->constraints = [];
        foreach ($disk['constraints'] ?? [] as $rc) {
            if (isset($pointMap[$rc['a_id']], $pointMap[$rc['b_id']])) {
                $this->constraints[] = new constraint(
                    $pointMap[$rc['a_id']],
                    $pointMap[$rc['b_id']],
                    $rc['rest'],
                    $rc['boundary'] ?? true
                );
            }
        }

        $lines = [];
        foreach ($disk['lines'] as $raw) {
            $lines[] = new line($this, 0, $raw['id'], $raw['start_x'], $raw['start_y'], $raw['end_x'], $raw['end_y']);
        }
        $this->lines = $lines;
    }

    // -------------------------------------------------------------------------
    // Rigid bodies — blueprint registration and materialization
    // -------------------------------------------------------------------------

    /**
     * Register a box to be spawned at simulation start.
     * Call before visualize(). The box is materialized on step 1.
     */
    public function spawnBox(float $cx, float $cy, float $w, float $h, float $mass = 1.0): void {
        $this->shapeBlueprints[] = [
            'type' => 'box', 'cx' => $cx, 'cy' => $cy,
            'w'    => $w,    'h'  => $h,  'mass' => $mass,
        ];
    }

    /**
     * Register a circle (polygon approximation) to be spawned at simulation start.
     */
    public function spawnCircle(float $cx, float $cy, float $r, int $n = 8, float $mass = 1.0): void {
        $this->shapeBlueprints[] = [
            'type' => 'circle', 'cx' => $cx, 'cy' => $cy,
            'r'    => $r,       'n'  => $n,  'mass' => $mass,
        ];
    }

    private function materializeShapes(): void {
        $this->constraints = [];
        foreach ($this->shapeBlueprints as $bp) {
            match ($bp['type']) {
                'box'    => $this->materializeBox($bp['cx'], $bp['cy'], $bp['w'], $bp['h'], $bp['mass']),
                'circle' => $this->materializeCircle($bp['cx'], $bp['cy'], $bp['r'], $bp['n'], $bp['mass']),
            };
        }
    }

    private function materializeBox(float $cx, float $cy, float $w, float $h, float $mass): void {
        $hw = $w / 2.0;
        $hh = $h / 2.0;

        $a = $this->makePoint($cx - $hw, $cy - $hh, $mass); // top-left
        $b = $this->makePoint($cx + $hw, $cy - $hh, $mass); // top-right
        $c = $this->makePoint($cx - $hw, $cy + $hh, $mass); // bottom-left
        $d = $this->makePoint($cx + $hw, $cy + $hh, $mass); // bottom-right

        // Edges (4) — boundary surface; diagonals (2) — internal structural braces only
        $this->constraints[] = new constraint($a, $b, -1.0, true);
        $this->constraints[] = new constraint($c, $d, -1.0, true);
        $this->constraints[] = new constraint($a, $c, -1.0, true);
        $this->constraints[] = new constraint($b, $d, -1.0, true);
        $this->constraints[] = new constraint($a, $d, -1.0, false); // diagonal
        $this->constraints[] = new constraint($b, $c, -1.0, false); // diagonal
    }

    private function materializeCircle(float $cx, float $cy, float $r, int $n, float $mass): void {
        $pts = [];
        for ($i = 0; $i < $n; $i++) {
            $angle = (2.0 * M_PI * $i) / $n;
            $pts[] = $this->makePoint($cx + $r * cos($angle), $cy + $r * sin($angle), $mass);
        }

        // Ring constraints between adjacent perimeter points — boundary surface
        for ($i = 0; $i < $n; $i++) {
            $this->constraints[] = new constraint($pts[$i], $pts[($i + 1) % $n], -1.0, true);
        }

        // Cross constraints (diameters) for rigidity — internal only, never collide
        $half = (int)($n / 2);
        for ($i = 0; $i < $half; $i++) {
            $this->constraints[] = new constraint($pts[$i], $pts[$i + $half], -1.0, false);
        }
    }

    /** Create a point, add it to the field, and return it. */
    private function makePoint(float $x, float $y, float $mass = 1.0): point {
        $p = new point($this, 0, $this->newUuid(), $x, $y);
        $p->setMass($mass);
        $this->points[] = $p;
        return $p;
    }

    private function newUuid(): string {
        return bin2hex(random_bytes(8));
    }

    // -------------------------------------------------------------------------
    // Constraint solving
    // -------------------------------------------------------------------------

    /**
     * Run constraint iterations after each integration step.
     * More iterations = stiffer shapes, more CPU cost.
     */
    private function solveConstraints(int $iterations = 5): void {
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($this->constraints as $c) {
                $c->solve();
            }
        }
    }

    /**
     * resolveStaticLineCollisions — Bounce ALL points off immovable line surfaces.
     *
     * Static lines have infinite mass, so the impulse formula simplifies:
     *   dv = -(1+e) * (v·n)   (the point's normal-component velocity gets reflected)
     * Mass cancels entirely — heavier particles bounce just as high as lighter ones.
     */
    private function resolveStaticLineCollisions(): void {
        if (empty($this->staticLines)) return;

        $e      = $this->restitution;
        $radius = $this->collisionRadius;

        foreach ($this->points as $p) {
            $px = $p->getX();
            $py = $p->getY();
            $vp = $p->getVelocity();

            foreach ($this->staticLines as [$x1, $y1, $x2, $y2]) {
                $abx = $x2 - $x1; $aby = $y2 - $y1;
                $ab2 = $abx * $abx + $aby * $aby;
                if ($ab2 < 0.0001) continue;

                $apx = $px - $x1; $apy = $py - $y1;
                $t   = max(0.0, min(1.0, ($apx * $abx + $apy * $aby) / $ab2));
                $cpx = $x1 + $t * $abx;
                $cpy = $y1 + $t * $aby;

                $dx   = $px - $cpx;
                $dy   = $py - $cpy;
                $dist = sqrt($dx * $dx + $dy * $dy);

                if ($dist >= $radius || $dist < 0.0001) continue;

                $nx = $dx / $dist;
                $ny = $dy / $dist;

                // Relative velocity along normal (wall is stationary)
                $rvn = $vp->x * $nx + $vp->y * $ny;
                if ($rvn >= 0) continue; // already separating

                // Infinite-mass wall: mass cancels → dv = -(1+e)*rvn
                $dv = -(1.0 + $e) * $rvn;
                $vp->x += $dv * $nx;
                $vp->y += $dv * $ny;

                // Push point clear of the surface
                $overlap = $radius - $dist;
                $p->setCoords($px + $nx * $overlap, $py + $ny * $overlap);
                $px = $p->getX();
                $py = $p->getY();
            }
        }
    }

    public function runFisx() {
        $this->turbulence();       // loose points only (see below)
        $this->applyGravity();     // all points
        $this->resolvePointCollisions();
        $this->resolveEdgeCollisions();
        $this->resolveStaticLineCollisions();
        foreach ($this->points as $point) {
            $point->integrate();
        }
        if (!empty($this->constraints)) {
            foreach ($this->points as $point) {
                $point->savePreviousPosition();
            }
            $this->solveConstraints();
            foreach ($this->points as $point) {
                $point->addConstraintVelocity();
            }
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

                // Impulse: J = -(1+e) * rvn / (1/ma + 1/mb)
                $ma = $a->getMass();
                $mb = $b->getMass();
                $J  = -(1.0 + $this->restitution) * $rvn / (1.0 / $ma + 1.0 / $mb);

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

    /**
     * resolveEdgeCollisions — Bounce loose points off rigid body boundary edges.
     *
     * For each loose point (indices 0..pointCount-1) and each boundary constraint:
     * finds the closest point on the edge segment, and if the point is within
     * collisionRadius applies a restitution impulse along the surface normal,
     * distributed to the point and both edge endpoints weighted by (1-t) and t.
     */
    private function resolveEdgeCollisions(): void {
        if (empty($this->constraints)) return;

        $e      = $this->restitution;
        $radius = $this->collisionRadius;

        for ($i = 0; $i < $this->pointCount; $i++) {
            if (!isset($this->points[$i])) continue;

            $p  = $this->points[$i];
            $px = $p->getX();
            $py = $p->getY();
            $vp = $p->getVelocity();
            $mp = $p->getMass();

            foreach ($this->constraints as $c) {
                if (!$c->isBoundary()) continue;

                $ea  = $c->getA();
                $eb  = $c->getB();
                $ax  = $ea->getX(); $ay = $ea->getY();
                $bx  = $eb->getX(); $by = $eb->getY();

                // Parametric closest point on segment AB
                $abx = $bx - $ax; $aby = $by - $ay;
                $ab2 = $abx * $abx + $aby * $aby;
                if ($ab2 < 0.0001) continue; // degenerate edge

                $apx = $px - $ax; $apy = $py - $ay;
                $t   = max(0.0, min(1.0, ($apx * $abx + $apy * $aby) / $ab2));
                $cpx = $ax + $t * $abx;
                $cpy = $ay + $t * $aby;

                $dx   = $px - $cpx;
                $dy   = $py - $cpy;
                $dist = sqrt($dx * $dx + $dy * $dy);

                if ($dist >= $radius || $dist < 0.0001) continue;

                // Normal pointing from edge toward point
                $nx = $dx / $dist;
                $ny = $dy / $dist;

                // Edge contact velocity (interpolated between endpoints)
                $va  = $ea->getVelocity();
                $vb  = $eb->getVelocity();
                $vcx = (1.0 - $t) * $va->x + $t * $vb->x;
                $vcy = (1.0 - $t) * $va->y + $t * $vb->y;

                // Relative velocity of point w.r.t. edge contact, along normal
                $rvn = ($vp->x - $vcx) * $nx + ($vp->y - $vcy) * $ny;

                // Only resolve if approaching
                if ($rvn >= 0) continue;

                // Effective inverse mass accounts for point + both edge endpoints
                $ma         = $ea->getMass();
                $mb         = $eb->getMass();
                $invEffMass = 1.0 / $mp
                            + (1.0 - $t) * (1.0 - $t) / $ma
                            + $t * $t / $mb;

                $J = -(1.0 + $e) * $rvn / $invEffMass;

                // Apply impulse to loose point
                $vp->x += ($J / $mp) * $nx;
                $vp->y += ($J / $mp) * $ny;

                // Distribute impulse to edge endpoints
                $va->x -= ($J * (1.0 - $t) / $ma) * $nx;
                $va->y -= ($J * (1.0 - $t) / $ma) * $ny;
                $vb->x -= ($J * $t / $mb) * $nx;
                $vb->y -= ($J * $t / $mb) * $ny;

                // Positional correction — push point fully out of edge
                $overlap = $radius - $dist;
                $p->setCoords($px + $nx * $overlap, $py + $ny * $overlap);
                // Refresh cached position for remaining edge checks this step
                $px = $p->getX();
                $py = $p->getY();
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
            $this->materializeShapes();
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
            $blue  = imagecolorallocate($gd, 30, 80, 200);
            $red   = imagecolorallocate($gd, 210, 50, 35);

            imagefilledrectangle($gd, 0, 0, $this->getXMax(), $this->getYMax(), $black);
            imagefilledrectangle($gd, $border, $border, $this->getXMax() - $border * 1.5, $this->getYMax() - $border * 1.5, $white);

            // Static surfaces — drawn thick and red so they stand out
            imagesetthickness($gd, 3);
            foreach ($this->staticLines as [$lx1, $ly1, $lx2, $ly2]) {
                imageline($gd, (int)round($lx1), (int)round($ly1), (int)round($lx2), (int)round($ly2), $red);
            }
            imagesetthickness($gd, 1);

            // Draw only boundary edges of rigid bodies (skip internal diagonal braces)
            foreach ($this->constraints as $c) {
                if (!$c->isBoundary()) continue;
                imageline($gd,
                    (int)round($c->getA()->getX()), (int)round($c->getA()->getY()),
                    (int)round($c->getB()->getX()), (int)round($c->getB()->getY()),
                    $blue
                );
            }

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
