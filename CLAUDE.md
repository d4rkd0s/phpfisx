# phpfisx — CLAUDE.md

2D particle physics engine in PHP. Position-Based Dynamics (PBD) with rigid bodies, restitution, static surfaces, and a browser-based scene editor.

---

## Architecture

```
phpfisx/
  areas/field.php        — simulation world: spawn, step, visualize
  entities/
    point.php            — particle with mass, velocity, boundary reflection
    constraint.php       — PBD distance constraint between two points
    vector.php           — 2D math helpers
render.php               — HTTP endpoint: runs simulation, returns HTML animation
index.php                — browser scene editor (canvas drag-and-drop)
boot.php                 — autoload + namespace bootstrap
tests/Unit/              — Pest v1 unit tests (50 tests)
```

### Key design decisions

- **Restitution sentinel `-1.0`**: Per-shape and per-constraint restitution defaults to `-1.0`, meaning "inherit from field global." Any value `>= 0.0` overrides. This flows from `field::addStaticLine()` → `staticLines[]` tuple index 4, and from `constraint::$restitution` → `getConstraintRestitution()`.

- **`isBoundary` flag on constraints**: Boundary constraints (outer edges) participate in point-vs-edge collision detection. Internal structural braces (box diagonals, circle diameter) are marked `isBoundary=false` — they stiffen the shape without acting as collision surfaces. Always set this correctly when constructing constraints manually.

- **Static lines don't persist to `field.json`**: The entire simulation runs inside a single PHP request within `visualize()`. `$this->staticLines` lives in memory across all steps — no need to serialize it.

- **Spawn zone**: `setSpawnZone(x1,y1,x2,y2)` constrains initial particle placement. Uses `srand($i + 77777)` for determinism distinct from the full-field default seeding.

- **Scene JSON format** (render.php `?scene=...`):
  ```json
  {
    "settings": { "points":80, "steps":50, "gravity":1.0, "friction":0.98, "restitution":0.7 },
    "shapes": [
      { "type":"box",    "cx":250, "cy":250, "w":60, "h":40, "mass":3.0, "restitution":-1 },
      { "type":"circle", "cx":250, "cy":150, "r":30, "n":10, "mass":1.5, "restitution":-1 },
      { "type":"line",   "x1":0,   "y1":300, "x2":200, "y2":400, "restitution":-1 },
      { "type":"spawn",  "x1":40,  "y1":20,  "x2":460, "y2":110 }
    ]
  }
  ```

---

## Dev Workflow

### Run tests
```bash
./vendor/bin/pest
# or
composer test
```

Pre-commit hook runs Pest automatically — commits are blocked on failure.

### PHP version
Targets PHP 8.1+. CI matrix: 8.1, 8.2, 8.3 via GitHub Actions.

### Local server
```bash
php -S localhost:8080
# open http://localhost:8080/
```

### Test conventions
- Framework: **Pest v1** (`pestphp/pest ^1.21`)
- Use `round($val, N)->toBe(expected)` — `toBeCloseTo()` is not available in v1
- Private properties/methods accessed via `ReflectionProperty` / `ReflectionMethod`
- When injecting `staticLines` directly via reflection, use 5-element tuples: `[x1, y1, x2, y2, restitution]`

---

## Roadmap (current state)

- [x] Velocity integration + wall boundary reflection  
- [x] Friction (per-point damping)  
- [x] Point-vs-point collision with mass-weighted impulse  
- [x] Rigid bodies (box, circle) via PBD distance constraints  
- [x] Global restitution coefficient  
- [x] Point-vs-edge collision (soft body surface bouncing)  
- [x] Static collision surfaces (`addStaticLine`)  
- [x] Visual scene editor (drag-and-drop, Select/move/delete)  
- [x] Per-shape materials (mass + restitution override per shape)  
- [x] Spawn zone  
- [ ] Live mode (WebSocket or SSE streaming — no page reload between steps)  
- [ ] Joints / hinges  
- [ ] Fluid / soft body mode  
