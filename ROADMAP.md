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

## Next

- **Live mode** — WebSocket or SSE streaming so simulations play without a full page reload between steps
- **Joints / hinges** — connect bodies with rotation instead of rigid PBD constraints
- **Fluid / soft body mode** — mass-spring or SPH-based deformable simulation

## Later / exploratory

- Polygon-polygon collision (SAT) for non-box/circle shapes
- Force fields (radial gravity, wind, vortex)
- Spatial partitioning (quadtree / broad-phase AABB) for large particle counts
- Simulation presets/gallery (pendulum, fluid, particle fountain)
- CLI runner for headless simulation/GIF generation without the web UI
