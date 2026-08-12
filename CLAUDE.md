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
    joint.php            — PBD hinge: single distance constraint, rotation left free (see below)
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

- **Joints**: `joint` (`field::addJoint()`/`addJointAnchor()`) is a *single* PBD distance constraint applied in isolation — no bracing diagonals like `materializeBox()`/`materializeCircle()` — so the connected bodies stay free to rotate around it each step. `restLength = 0.0` gives a pin joint. Anchor mode (`addJointAnchor`) connects a point to a fixed `[x, y]` world coordinate instead of a second point; the anchor is treated as infinite mass and never moves, so 100% of the correction lands on the point (mirrors `constraint::solve()` with `mb → ∞`). Joints are solved in `field::solveConstraints()` alongside `$constraints` and round-trip through `field.json` (`joints` key) the same way constraints do. Drawn as a dashed orange line + small pivot dot at each end, in both the editor canvas (`index.php`) and playback (`field::visualize()`, via `imagesetstyle`/`IMG_COLOR_STYLED` since GD has no native dashed-line primitive).

- **Joint scene-parsing / deferred materialization**: a box/circle's actual PBD points don't exist until `field::materializeShapes()` runs at simulation step 1 — so a joint attaching to "the box at index 2" can't resolve to a real `point` object at scene-parse time. `field` mirrors the existing shapeBlueprints deferred-build pattern for this: `addJointBlueprint(?fromBlueprintIdx, fromX, fromY, ?toBlueprintIdx, toX, toY, restLength)` records the joint by **shapeBlueprints index** (not scene-shapes index — those differ once lines/spawn zones are interspersed) plus the raw click position; `materializeJoints()` (called right after `materializeShapes()`) resolves each endpoint to the *nearest materialized point* on that shape's `shapePoints[idx]` to the click (x, y) — this is how "which corner of the box" gets picked without the editor needing to know point topology. `shapeIndex === null` means a fixed world anchor at (x, y) instead. A joint blueprint with both endpoints anchored is skipped (nothing movable to connect). `render.php` maps scene-shapes indices → blueprint indices as it iterates (`box`/`circle` increment the counter, `line`/`spawn` don't), then resolves all `joint` entries in a second pass so a joint can reference a shape defined later in the array.

- **Scene JSON format** (render.php `?scene=...`):
  ```json
  {
    "settings": { "points":80, "steps":50, "gravity":1.0, "friction":0.98, "restitution":0.7, "trails":false },
    "shapes": [
      { "type":"box",    "cx":250, "cy":250, "w":60, "h":40, "mass":3.0, "restitution":-1 },
      { "type":"circle", "cx":250, "cy":150, "r":30, "n":10, "mass":1.5, "restitution":-1 },
      { "type":"line",   "x1":0,   "y1":300, "x2":200, "y2":400, "restitution":-1 },
      { "type":"spawn",  "x1":40,  "y1":20,  "x2":460, "y2":110 },
      { "type":"joint",  "from":{"shapeIndex":0,"x":280,"y":230}, "to":{"shapeIndex":1,"x":250,"y":150} }
    ]
  }
  ```
  A `joint`'s `from`/`to` each carry `shapeIndex` (index into this same `shapes` array, or `null`) **and** `x`/`y` — the click position. `x`/`y` is always present: when `shapeIndex` is set it's used to pick the nearest point on that shape once materialized; when `shapeIndex` is `null` it *is* the fixed anchor coordinate. Only `box`/`circle` shapes are valid joint attachment points in the editor — clicking a `line` (ramp) or empty canvas both produce an anchor endpoint, since ramps are already static/immovable.

- **Static-line collision detection is swept + proximity, not proximity-only**: `field::resolveStaticLineCollisions()` runs *after* `point::integrate()` each step and checks two things per point/line pair: (1) a swept/continuous test — does the segment from the point's pre-integrate position (`point::markPreIntegratePosition()`, captured every step regardless of whether constraints/joints exist) to its post-integrate position cross the static line segment this step? — and (2) the original proximity test — is the point's final position within `collisionRadius` of the line? Swept catches fast movers that would otherwise tunnel straight through a ramp in one step (velocity > ~`collisionRadius`/step); proximity catches slow/grazing contact that never actually crosses the line. Only one of the two applies a velocity/position response per line per point, via the shared `applyLineResponse()` helper, so they don't double up. Edge case: if a point crosses a static line but is already separating (`rvn >= 0`, e.g. it was already bounced by a different line/constraint earlier in the same step), neither pass applies a response — matches the pre-existing "only resolve if approaching" behavior, just extended to the swept path.

- **Motion trails**: `settings.trails` (bool) toggles `field::setTrailsEnabled()`. When on, `field::visualize()` calls `pushTrailHistory()` each step — a pure in-memory buffer (never persisted to `field.json`) capping each point's position history at `trailLength` (10) — and draws it as fading black dots (`imagecolorallocatealpha`) behind the solid point marker. Off by default.

- **Browser localStorage save/load** (`index.php`): "Save" persists `{ shapes, spawnZone, settings:{points,steps,gravity,friction,restitution,trails} }` to `localStorage` under the key `phpfisx.scene.v1` — explicitly per-browser/per-computer, never sent to the server. "Load" restores it verbatim, including slider positions and the trails checkbox.

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

### Known gaps

Entity classes (`point`, `vector`, `line`, `circle`, `polygon`, `box`) mostly expose public
properties rather than encapsulated getters/setters, and type hints are inconsistent across
the codebase. Both are fine for the current scope but worth tightening if the engine grows
past its current feature set.

---

## Roadmap

See `ROADMAP.md` for what's done and what's next.
