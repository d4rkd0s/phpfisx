<img src="logo.png" alt="phpfisx logo" width="180"/>

# phpfisx — PHP Physics Engine

[![CI](https://github.com/d4rkd0s/phpfisx/actions/workflows/ci.yml/badge.svg)](https://github.com/d4rkd0s/phpfisx/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A 2D physics simulation engine written in PHP — because why not.

Runs locally with just `php -S` and a browser. No compilation, no external services, no WebGL. Pure PHP + HTML5 canvas playback.

![Simulation demo](simple_points.gif)

---

## Features

- **Position-Based Dynamics** — distance constraints hold rigid bodies together across multiple solver iterations per step
- **Rigid bodies** — spawn boxes and circles; each shape is a network of PBD constraints with mass-weighted correction
- **Joints / hinges** — connect two points, or a point to a fixed anchor, with a single constraint that leaves rotation free
- **Point-vs-edge and static-surface collision** — loose particles bounce off shape surfaces and ramps/walls, with swept detection so fast movers don't tunnel through in one step
- **Elastic point-point collision** — mass-weighted impulse response between particles
- **Gravity + custom forces** — directional force with magnitude and degree-based direction
- **Friction / velocity damping** — per-field drag coefficient applied each step
- **Visual scene editor** — drag-and-drop shapes, ramps, spawn zones, and joints on an HTML5 canvas, with save/load to browser storage
- **Two playback modes** — pre-baked PNG frames with a scrub bar, or Live mode, which streams each step over Server-Sent Events and draws it as it's calculated
- **PSR-4 autoloading**, PHP 8.1+, Pest test suite, GitHub Actions CI

---

## Quickstart

```bash
# Clone and install
git clone https://github.com/d4rkd0s/phpfisx
cd phpfisx
composer install

# Run the dev server
php -S localhost:8000

# Open in browser
open http://localhost:8000
```

**Requirements:** PHP 8.1+ with `ext-gd` enabled, Composer.

**Got Yarn?**
```bash
yarn start   # starts php -S localhost:8000
```

---

## How It Works

Each simulation is a **field** containing **points** (particles) and optional **constraints** (rigid body edges).

**Physics loop per step:**
1. Apply turbulence (random micro-forces to loose particles)
2. Apply gravity (downward force, scaled by mass)
3. Resolve point-point collisions (elastic impulse)
4. Resolve point-vs-edge collisions (impulse vs rigid body surfaces)
5. Integrate velocity → position (with wall reflection + friction)
6. Solve PBD constraints (N iterations — keeps rigid bodies stiff)
7. Feed constraint position deltas back into velocity

`render.php` pre-calculates the full simulation server-side, renders each frame to a PNG via PHP GD, and base64-encodes the set into a self-contained HTML page with a canvas player. `live.php` runs the same physics loop but streams each step to the browser as a Server-Sent Event as soon as it's calculated, so the editor can draw it straight onto a canvas without waiting for the whole run to finish.

---

## Architecture

```
phpfisx/
├── phpfisx/
│   ├── areas/
│   │   └── field.php          # Simulation space, physics loop, scene parsing, frame serialization, GD rendering
│   └── entities/
│       ├── point.php           # Particle — position, velocity, mass, integrate
│       ├── constraint.php      # PBD distance constraint (boundary or internal)
│       ├── joint.php           # PBD hinge — single constraint, rotation left free
│       ├── vector.php          # 2D vector math
│       └── line.php            # Line segment entity
├── tests/Unit/                 # Pest test suite (87 tests)
├── render.php                  # HTTP endpoint — runs sim, returns pre-baked animated HTML
├── live.php                    # HTTP endpoint — streams sim steps over Server-Sent Events
├── index.php                   # Scene editor UI (canvas drag-and-drop, Physics panel, Run/Live playback)
├── boot.php                    # Composer autoload bootstrap
└── composer.json
```

---

## Reporting Issues

Use the [GitHub issue tracker](https://github.com/d4rkd0s/phpfisx/issues/new/choose) — select **Bug** or **Feature**.

---

## Roadmap

See [ROADMAP.md](ROADMAP.md) for what's done and what's next.

---

## License

[MIT](LICENSE) — Logan Schmidt
