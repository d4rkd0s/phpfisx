# phpfisx — Roadmap

Status of the physics engine and what's next. See `CLAUDE.md` for architecture and dev workflow.

## Done

- Velocity integration + wall boundary reflection
- Friction (per-point damping)
- Point-vs-point collision with mass-weighted impulse
- Rigid bodies (box, circle) via PBD distance constraints
- Global restitution coefficient
- Point-vs-edge collision (soft body surface bouncing)
- Static collision surfaces (`addStaticLine`)
- Visual scene editor (drag-and-drop, select/move/delete)
- Per-shape materials (mass + restitution override per shape)
- Spawn zones
- PSR-4 autoloading, Pest test suite, GitHub Actions CI (PHP 8.1–8.3)
- Joints / hinges — `joint` entity connects two points (or a point to a fixed world anchor) with a single PBD distance constraint that leaves rotation free, unlike rigid multi-constraint shapes; full editor + playback support: a Joint tool in the toolbar (click two endpoints — shape or empty canvas — to connect them), dashed-orange rendering in both the editor canvas and `render.php`/`field.php`'s GD playback, and scene-JSON round-tripping through `render.php`
- Save / Load scene to browser localStorage — per-client only, no server-side persistence
- Legend/key in the editor UI showing what each shape color means
- Motion trails — optional checkbox in the Physics panel; when on, `field.php`'s playback draws a fading position history behind each point instead of a single dot
- Live mode — `live.php` streams the simulation over Server-Sent Events, one step at a time, so the editor can draw each frame on a canvas as it's calculated instead of waiting for a full run to finish and reload. The editor's new "⚡ Live" button runs this path; "▶ Run Simulation" still uses `render.php`'s pre-baked GD/PNG playback for the static, shareable HTML output. Scene parsing was pulled out of `render.php` into `field::fromScene()` so both endpoints build a field the same way.

## Next

- **Fluid / soft body mode** — mass-spring or SPH-based deformable simulation

## Later / exploratory

- Polygon-polygon collision (SAT) for non-box/circle shapes
- Force fields (radial gravity, wind, vortex)
- Spatial partitioning (quadtree / broad-phase AABB) for large particle counts
- Simulation presets/gallery (e.g. Pendulum, Newton's Cradle, fluid, particle fountain) — intentionally deferred until the editor/joint tooling matures further; not building the gallery itself yet, just tracking it here
- CLI runner for headless simulation/GIF generation without the web UI
