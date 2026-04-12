<?php
namespace phpfisx\entities;

class point {
    public $id;
    public $x;
    public $y;
    public $z;
    private $field;
    private vector $velocity;

    public function __construct(
        \phpfisx\areas\field $field,
        int $seed = 0,
        string $existing_id = "",
        float $existing_x = 0.0,
        float $existing_y = 0.0,
        float $existing_vx = 0.0,
        float $existing_vy = 0.0
    ) {
        $this->field = $field;
        $this->velocity = new vector(0, 0);

        if ($seed !== 0) {
            $this->id = $this->uuid();
            srand($seed);
            $this->setCoords(
                rand(0, $this->field->getXMax()),
                rand(0, $this->field->getYMax())
            );
        } else {
            $this->id = $existing_id;
            $this->setCoords($existing_x, $existing_y);
            $this->velocity = new vector($existing_vx, $existing_vy);
        }
    }

    private function uuid(): string {
        return sprintf('%s-%s-%s-%s',
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(4))
        );
    }

    public function getVelocity(): vector {
        return $this->velocity;
    }

    public function setVelocity(float $vx, float $vy): void {
        $this->velocity->x = $vx;
        $this->velocity->y = $vy;
    }

    /**
     * applyForce — Accumulates a force into this point's velocity.
     *
     * Forces are expressed as a magnitude and a direction in degrees.
     * Direction 0 = downward (positive Y), 90 = rightward (positive X).
     * Velocity is applied to position each step via integrate().
     *
     * @param float $amount   Force magnitude
     * @param int   $direction Direction in degrees (0–360)
     */
    public function applyForce(float $amount, int $direction): void {
        $this->velocity->x += $amount * sin(deg2rad($direction));
        $this->velocity->y += $amount * cos(deg2rad($direction));
    }

    /**
     * integrate — Advances position by the current velocity.
     *
     * When a boundary is hit, the relevant velocity component is reversed
     * so the point bounces. Call this once per simulation step, after all
     * forces have been applied.
     */
    public function integrate(): void {
        $new_x = $this->x + $this->velocity->x;
        $new_y = $this->y + $this->velocity->y;

        $border = $this->field->getBorder();
        $x_max  = $this->field->getXMax() - $border;
        $y_max  = $this->field->getYMax() - $border;

        if ($new_x >= $x_max) {
            $new_x = $x_max;
            $this->velocity->x *= -1;
        } elseif ($new_x <= $border) {
            $new_x = $border;
            $this->velocity->x *= -1;
        }

        if ($new_y >= $y_max) {
            $new_y = $y_max;
            $this->velocity->y *= -1;
        } elseif ($new_y <= $border) {
            $new_y = $border;
            $this->velocity->y *= -1;
        }

        $this->setCoords($new_x, $new_y);
    }

    public function checkCollisions($lines): bool {
        foreach ($lines as $line) {
            if ($line->isOnLine($this->getX(), $this->getY())) {
                return true;
            }
        }
        return false;
    }

    public function setCoords($x, $y): void {
        $this->x = $x;
        $this->y = $y;
    }

    public function getCoords(): array {
        return [$this->x, $this->y];
    }

    public function getX(): float {
        return $this->x;
    }

    public function getY(): float {
        return $this->y;
    }

    public function getID(): string {
        return $this->id;
    }

    /**
     * toArray — Serializes the point state including velocity.
     * Used by field::persistToDisk() for clean round-trip saves.
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'x'  => $this->x,
            'y'  => $this->y,
            'vx' => $this->velocity->x,
            'vy' => $this->velocity->y,
        ];
    }
}
