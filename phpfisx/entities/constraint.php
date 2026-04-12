<?php
namespace phpfisx\entities;

/**
 * A Position-Based Dynamics (PBD) distance constraint between two points.
 *
 * Each step, solve() pushes the two endpoints toward their rest length.
 * Corrections are mass-weighted: lighter points move more than heavier ones.
 * Run several iterations per step (field::solveConstraints) for stability.
 */
class constraint {
    private point $a;
    private point $b;
    private float $restLength;

    /**
     * @param float $restLength Pass -1.0 (default) to auto-calculate from current distance.
     */
    public function __construct(point $a, point $b, float $restLength = -1.0) {
        $this->a = $a;
        $this->b = $b;

        if ($restLength >= 0.0) {
            $this->restLength = $restLength;
        } else {
            $dx = $b->getX() - $a->getX();
            $dy = $b->getY() - $a->getY();
            $this->restLength = sqrt($dx * $dx + $dy * $dy);
        }
    }

    /**
     * Correct positions so the two points are exactly restLength apart.
     */
    public function solve(): void {
        $dx   = $this->b->getX() - $this->a->getX();
        $dy   = $this->b->getY() - $this->a->getY();
        $dist = sqrt($dx * $dx + $dy * $dy);

        if ($dist < 0.0001) {
            return;
        }

        $delta    = ($dist - $this->restLength) / $dist;
        $ma       = $this->a->getMass();
        $mb       = $this->b->getMass();
        $invTotal = 1.0 / ($ma + $mb);

        $cx = $delta * $dx;
        $cy = $delta * $dy;

        $this->a->setCoords(
            $this->a->getX() + ($mb * $invTotal) * $cx,
            $this->a->getY() + ($mb * $invTotal) * $cy
        );
        $this->b->setCoords(
            $this->b->getX() - ($ma * $invTotal) * $cx,
            $this->b->getY() - ($ma * $invTotal) * $cy
        );
    }

    public function getA(): point { return $this->a; }
    public function getB(): point { return $this->b; }
    public function getRestLength(): float { return $this->restLength; }
}
