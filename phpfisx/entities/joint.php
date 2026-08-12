<?php
namespace phpfisx\entities;

/**
 * A Position-Based Dynamics (PBD) joint — a rotating connection between two
 * points, or between a point and a fixed world-space anchor.
 *
 * Unlike `constraint` (used to brace rigid body shapes with multiple
 * interlocking distance constraints + diagonals so the shape can't rotate
 * internally), a `joint` is a *single* distance constraint applied in
 * isolation between exactly one pair of connection points. With nothing
 * else holding the two bodies' relative angle fixed, the bodies remain free
 * to swing/rotate around the joint each step — this is what makes it a
 * hinge rather than a rigid brace. Passing `restLength = 0.0` produces a
 * pin joint (both points/anchor coincide, free rotation about a point).
 *
 * Two connection modes:
 *  - Point-to-point: connects `a` and `b`, both movable, mass-weighted
 *    correction identical to `constraint::solve()`.
 *  - Point-to-anchor: connects `a` to a fixed world-space [x, y] coordinate.
 *    The anchor never moves (equivalent to infinite mass) — all positional
 *    correction is applied to `a`.
 */
class joint {
    private point $a;
    private ?point $b;
    private ?array $anchor; // [x, y] fixed world point, or null when $b is set
    private float $restLength;

    /**
     * @param point      $a          First connection point (always movable).
     * @param ?point     $b          Second connection point. Pass null when anchoring to a fixed point.
     * @param float      $restLength Pass -1.0 (default) to auto-calculate from current distance.
     * @param ?array     $anchor     Fixed world-space [x, y] target. Required (and only used) when $b is null.
     */
    public function __construct(point $a, ?point $b, float $restLength = -1.0, ?array $anchor = null) {
        if ($b === null && $anchor === null) {
            throw new \InvalidArgumentException('joint requires either a second point or a fixed anchor');
        }

        $this->a      = $a;
        $this->b      = $b;
        $this->anchor = $b === null ? $anchor : null;

        if ($restLength >= 0.0) {
            $this->restLength = $restLength;
        } else {
            [$tx, $ty] = $this->targetXY();
            $dx = $tx - $a->getX();
            $dy = $ty - $a->getY();
            $this->restLength = sqrt($dx * $dx + $dy * $dy);
        }
    }

    public function isAnchored(): bool { return $this->anchor !== null; }

    public function getA(): point { return $this->a; }
    public function getB(): ?point { return $this->b; }
    public function getAnchor(): ?array { return $this->anchor; }
    public function getRestLength(): float { return $this->restLength; }

    /**
     * The current [x, y] this joint is pulling `a` toward — either the live
     * position of `b`, or the fixed anchor coordinate.
     */
    private function targetXY(): array {
        return $this->anchor !== null ? $this->anchor : [$this->b->getX(), $this->b->getY()];
    }

    /**
     * Angle (radians) from `a` to its connection target. Free to change
     * step-to-step — that's the "hinge" behavior — while solve() keeps the
     * distance pinned at restLength.
     */
    public function getAngle(): float {
        [$tx, $ty] = $this->targetXY();
        return atan2($ty - $this->a->getY(), $tx - $this->a->getX());
    }

    /**
     * Correct positions so the connection is exactly restLength apart.
     * Rotation around the joint is left entirely unconstrained.
     */
    public function solve(): void {
        if ($this->anchor !== null) {
            $this->solveAnchor();
        } else {
            $this->solveTwoPoints();
        }
    }

    private function solveTwoPoints(): void {
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

    private function solveAnchor(): void {
        [$ax, $ay] = $this->anchor;
        $dx   = $ax - $this->a->getX();
        $dy   = $ay - $this->a->getY();
        $dist = sqrt($dx * $dx + $dy * $dy);

        if ($dist < 0.0001) {
            return;
        }

        $delta = ($dist - $this->restLength) / $dist;

        // Anchor has infinite mass — it never moves, so `a` absorbs 100% of
        // the correction (mirrors constraint::solve() with mb -> infinity).
        $this->a->setCoords(
            $this->a->getX() + $delta * $dx,
            $this->a->getY() + $delta * $dy
        );
    }
}
