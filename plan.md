# phpfisx Improvement Plan

This document outlines bugs to fix, improvements to make, and features to add to the phpfisx physics engine. Items are organized by priority and complexity.

---

## Progress Update (2025-10-18)

### ✅ Completed Today

**Phase 1: Critical Bugs & Quick Wins - COMPLETE!**
- ✅ Fixed all 4 critical bugs (getYMax, rotate, polygon typos, package.json)
- ✅ Enabled PSR-4 autoloading
- ✅ Improved UUID generation (now uses cryptographically random bytes)
- ✅ Created images directory with .gitkeep
- ✅ Fixed composer dependencies (switched to sybio/gif-creator)
- ✅ Fixed test infrastructure (Pest.php now properly loads classes)
- ✅ Enabled point instantiation test
- ✅ All tests passing (3/3)

**Time Invested:** ~2 hours
**Impact:** Foundation is now solid - no more critical bugs, proper autoloading, tests work!

### 🚧 Next Session Priorities

1. Complete Phase 2: Add comprehensive test coverage
2. Start Phase 3: Code quality improvements (type hints, docblocks)
3. Begin Phase 4: Implement velocity system

---

## Phase 1: Critical Bugs & Quick Wins ✅ COMPLETE

### 1.1 Bug Fixes (HIGH PRIORITY) ✅
These are actual bugs that will cause incorrect behavior:

- [x] **Fix `getYMax()` method** (`phpfisx/areas/field.php:83`)
  - Currently returns `$this->x_max` instead of `$this->y_max`
  - Impact: Any code using getYMax gets wrong value
  - Effort: 1 minute

- [x] **Fix `rotate()` return statement** (`phpfisx/entities/vector.php:34`)
  - Currently returns `this` instead of `$this`
  - Impact: PHP fatal error when rotate is called
  - Effort: 1 minute

- [x] **Fix typos in polygon.php** (`phpfisx/entities/polygon.php:55,66`)
  - `_recacl()` should be `_recalc()`
  - Impact: Fatal error when calling setAngle or setOffset
  - Effort: 2 minutes

- [x] **Fix package.json syntax error** (`package.json:12`)
  - Unclosed string in "start" script
  - Current: `"start": "yarn install && echo 'Visit: https://localhost:8000; && php -S localhost:8000"`
  - Should be: `"start": "yarn install && echo 'Visit: http://localhost:8000' && php -S localhost:8000"`
  - Impact: yarn start fails
  - Effort: 1 minute

### 1.2 Quick Improvements ✅

- [x] **Enable PSR-4 autoloading**
  - Update composer.json with autoload section
  - Remove manual require_once statements from boot.php
  - Benefits: Cleaner code, faster loading, IDE autocomplete
  - Effort: 30 minutes

- [x] **Fix UUID generation**
  - Replace custom uuid() method with proper implementation
  - Use `ramsey/uuid` composer package OR PHP's built-in functions
  - Locations: `point.php:26`, `line.php:31`
  - Effort: 15 minutes

- [x] **Add .phpunit.result.cache to .gitignore**
  - Already listed in .gitignore but verify it's working
  - Effort: 1 minute

- [x] **Create images directory**
  - Add empty .gitkeep file to ensure directory exists
  - Prevents errors on first run
  - Effort: 2 minutes

---

## Phase 2: Testing Infrastructure (In Progress)

The test suite is set up but mostly disabled. Let's fix that!

### 2.1 Fix Existing Tests ✅

- [x] **Resolve autoloading issue in tests**
  - Fix "Class phpfisx\areas\field not found" error
  - Update tests/Pest.php to properly load boot.php or use PSR-4
  - Effort: 15 minutes

- [x] **Enable point instantiation test** (`tests/Unit/pointTest.php:12-16`)
  - Uncomment and verify it works
  - Effort: 5 minutes

- [x] **Add composer test script**
  - Added `composer test` command that runs Pest
  - All 3 tests now passing

### 2.2 Add Comprehensive Tests

- [ ] **Vector class tests**
  - Test all mathematical operations (add, sub, scale, rotate, etc.)
  - Test edge cases (zero vectors, normalization, etc.)
  - Coverage target: 80%+
  - Effort: 2-3 hours

- [ ] **Point class tests**
  - Test force application
  - Test boundary constraints
  - Test collision detection
  - Test coordinate getters/setters
  - Effort: 2 hours

- [ ] **Field class tests**
  - Test point generation
  - Test line generation
  - Test gravity application
  - Test state persistence (mock file system)
  - Test step sequence validation
  - Effort: 3-4 hours

- [ ] **Line/Circle/Polygon tests**
  - Test geometry calculations
  - Test collision detection
  - Test transformations
  - Effort: 2 hours each

- [ ] **Add integration tests**
  - Test full simulation runs
  - Test GIF generation
  - Test state save/load cycle
  - Effort: 2-3 hours

- [ ] **Set up CI/CD**
  - Add GitHub Actions workflow
  - Run tests on push/PR
  - Generate coverage reports
  - Effort: 1 hour

---

## Phase 3: Code Quality & Architecture

### 3.1 Improve Encapsulation

- [ ] **Make entity properties private**
  - Change public properties to private in: point, vector, line, circle, polygon, box
  - Add proper getters/setters where needed
  - Benefits: Better encapsulation, easier to add validation
  - Effort: 2-3 hours

### 3.2 Add Type Hints

- [ ] **Add parameter and return type hints**
  - Go through all classes and add strict types
  - Add declare(strict_types=1) to all files
  - Benefits: Better IDE support, catch bugs earlier
  - Effort: 3-4 hours

### 3.3 Improve Documentation

- [ ] **Add comprehensive docblocks**
  - Document all public methods
  - Add @param and @return tags
  - Add class-level descriptions
  - Effort: 3-4 hours

- [ ] **Translate Chinese comments to English** (`vector.php`)
  - Lines 26, 45, 79, 91, 103, 115, 129, 143
  - Keep both languages or choose English for consistency
  - Effort: 30 minutes

### 3.4 Error Handling

- [ ] **Add file I/O error handling**
  - Check file_exists before reading
  - Handle fopen failures in persistToDisk/resetDisk
  - Add try/catch blocks where appropriate
  - Effort: 1-2 hours

- [ ] **Add validation**
  - Validate field bounds in constructor
  - Validate force amounts and directions
  - Validate step sequences
  - Effort: 2 hours

### 3.5 Configuration System

- [ ] **Create Config class**
  - Move hard-coded values to configuration
  - Support .env file or config.php
  - Configurable values:
    - Default gravity
    - Turbulence level
    - Border size
    - Image quality
    - GIF frame duration
  - Effort: 2-3 hours

### 3.6 Logging System

- [ ] **Add logging**
  - Use Monolog or similar
  - Log simulation steps
  - Log errors
  - Log performance metrics
  - Effort: 2 hours

---

## Phase 4: Missing Physics Features

### 4.1 Velocity System (HIGH PRIORITY)

- [ ] **Implement proper velocity**
  - Add velocity vector to point class
  - Implement getVelocity/setVelocity methods
  - Update applyForce to modify velocity instead of position directly
  - Apply velocity to position each step
  - Effort: 3-4 hours

### 4.2 Mass & Inertia

- [ ] **Add mass property to points**
  - Add mass parameter to point constructor
  - Modify force application: F = ma
  - Heavier objects should accelerate slower
  - Effort: 2-3 hours

### 4.3 Friction

- [ ] **Implement friction forces**
  - Add friction coefficient to field
  - Apply friction opposite to velocity
  - Friction = coefficient * velocity
  - Effort: 2-3 hours

### 4.4 Advanced Collision Detection

- [ ] **Point-to-point collision**
  - Detect when points overlap
  - Apply collision response (bounce)
  - Consider elastic vs inelastic collisions
  - Effort: 4-6 hours

- [ ] **Polygon collision detection**
  - Implement SAT (Separating Axis Theorem)
  - Detect polygon-polygon intersections
  - Calculate collision normals and depths
  - Effort: 8-12 hours

- [ ] **Collision resolution**
  - Implement impulse-based physics
  - Handle restitution (bounciness)
  - Handle friction during collisions
  - Effort: 6-8 hours

### 4.5 Materials

- [ ] **Material system**
  - Create Material class
  - Properties: density, friction, restitution, color
  - Assign materials to entities
  - Use in collision calculations
  - Effort: 3-4 hours

---

## Phase 5: Enhanced Visualization

### 5.1 Rendering Improvements

- [ ] **Add color support**
  - Allow custom colors for points
  - Color-code by velocity, mass, material, etc.
  - Effort: 1-2 hours

- [ ] **Render velocity vectors**
  - Draw arrows showing direction/magnitude
  - Toggle on/off
  - Effort: 2 hours

- [ ] **Render polygons properly**
  - Fill polygons with color
  - Draw polygon outlines
  - Support transparency
  - Effort: 2-3 hours

- [ ] **Add grid/axis lines**
  - Optional coordinate grid
  - Labeled axes
  - Effort: 2 hours

- [ ] **Performance HUD**
  - Display FPS, entity count
  - Physics calculation time
  - Memory usage
  - Effort: 2-3 hours

### 5.2 Export Options

- [ ] **Support different output formats**
  - PNG sequence (already partial support)
  - MP4 video (using FFmpeg)
  - WebM
  - Individual frames download
  - Effort: 3-4 hours

- [ ] **Adjustable quality settings**
  - Configurable image size
  - GIF optimization
  - Frame rate control
  - Effort: 2 hours

---

## Phase 6: Simulation Capabilities

### 6.1 Pre-baked Simulations

- [ ] **Implement "bake" mode**
  - Calculate all steps upfront
  - Store all frames
  - Quick playback without recalculation
  - Benefits: Faster previewing, editing
  - Effort: 4-5 hours

- [ ] **Save/load simulations**
  - Export simulation definition to JSON
  - Import and replay simulations
  - Share simulation files
  - Effort: 3-4 hours

### 6.2 Interactive Controls

- [ ] **Real-time parameter adjustment**
  - Sliders for gravity, friction, etc.
  - Live updates without full restart
  - Effort: 4-6 hours (requires refactoring)

- [ ] **Pause/play/step controls**
  - Pause simulation mid-run
  - Step forward one frame at a time
  - Scrub timeline
  - Effort: 3-4 hours

- [ ] **Mouse interaction**
  - Click to add points
  - Drag to apply forces
  - Draw lines/polygons
  - Effort: 6-8 hours

### 6.3 Simulation Presets

- [ ] **Create example simulations**
  - Gravity well
  - Bouncing balls
  - Pendulum
  - Fluid simulation
  - Particle fountain
  - Each preset as a separate class
  - Effort: 2-3 hours each

- [ ] **Simulation library/gallery**
  - Browse available simulations
  - One-click run
  - Effort: 3-4 hours

---

## Phase 7: Advanced Features

### 7.1 Spatial Optimization

- [ ] **Implement quadtree**
  - Speed up collision detection
  - Only check nearby entities
  - Essential for large simulations (1000+ entities)
  - Effort: 8-12 hours

- [ ] **Broad phase collision detection**
  - AABB checks before detailed collision
  - Spatial hashing
  - Effort: 6-8 hours

### 7.2 Constraints & Joints

- [ ] **Distance constraints**
  - Keep two points at fixed distance (springs, rods)
  - Effort: 4-6 hours

- [ ] **Hinge joints**
  - Connect objects with rotation
  - Effort: 6-8 hours

- [ ] **Spring system**
  - Hooke's law: F = -kx
  - Dampening
  - Effort: 3-4 hours

### 7.3 Force Fields

- [ ] **Implement force fields**
  - Radial gravity (attract/repel from point)
  - Directional wind
  - Vortex forces
  - Custom force field shapes
  - Effort: 4-6 hours

### 7.4 Soft Body Physics

- [ ] **Deformable objects**
  - Mass-spring systems
  - Pressure simulation
  - Effort: 12-16 hours

### 7.5 Fluid Simulation

- [ ] **Particle-based fluids**
  - SPH (Smoothed Particle Hydrodynamics)
  - Surface tension
  - Viscosity
  - Effort: 20-30 hours

---

## Phase 8: 3D Support

### 8.1 3D Foundation

- [ ] **Create 3D equivalents**
  - vector3 class (x, y, z)
  - point3 class
  - Space class (3D field)
  - Effort: 8-12 hours

- [ ] **3D math operations**
  - Cross product
  - 3D rotations (quaternions)
  - Effort: 6-8 hours

### 8.2 3D Rendering

- [ ] **Implement 3D projection**
  - Orthographic projection
  - Perspective projection
  - Camera controls
  - Effort: 12-16 hours

- [ ] **3D primitive rendering**
  - Lines, planes, cubes
  - Spheres, cylinders
  - Effort: 8-12 hours

### 8.3 3D File Import

- [ ] **STL file parser**
  - Binary and ASCII formats
  - Convert to polygon meshes
  - Effort: 6-8 hours

- [ ] **OBJ file parser**
  - Load vertices, faces, normals
  - Material support
  - Effort: 8-10 hours

---

## Phase 9: Performance & Scalability

### 9.1 Optimization

- [ ] **Profile physics calculations**
  - Identify bottlenecks
  - Use Xdebug profiler
  - Effort: 2-3 hours

- [ ] **Optimize critical paths**
  - Cache expensive calculations
  - Reduce object allocations
  - Use references where appropriate
  - Effort: 4-6 hours

### 9.2 Alternative Rendering

- [ ] **WebGL rendering**
  - Replace GD with browser-based rendering
  - Much faster, real-time capable
  - Canvas API fallback
  - Effort: 20-30 hours

- [ ] **Server-sent events**
  - Stream simulation state to browser
  - Browser renders in real-time
  - Effort: 6-8 hours

### 9.3 Parallelization

- [ ] **Multi-threaded physics** (requires pthreads extension)
  - Split field into regions
  - Calculate each region in parallel
  - Effort: 12-16 hours

---

## Phase 10: Developer Experience

### 10.1 CLI Tools

- [ ] **Create CLI command**
  - Run simulations from command line
  - Generate GIFs without web UI
  - Batch processing
  - Uses Symfony Console component
  - Effort: 4-6 hours

### 10.2 API

- [ ] **REST API**
  - Trigger simulations via HTTP
  - Query simulation state
  - Download results
  - Effort: 6-8 hours

### 10.3 Documentation

- [ ] **API documentation**
  - Generate with phpDocumentor
  - Host on GitHub Pages
  - Effort: 3-4 hours

- [ ] **Tutorial series**
  - Getting started guide
  - Creating custom simulations
  - Advanced physics concepts
  - Effort: 8-12 hours

- [ ] **Example gallery**
  - Visual showcase of capabilities
  - Code samples
  - Live demos
  - Effort: 6-8 hours

---

## Recommended Priority Order

### Sprint 1 (Week 1): Foundation
1. Fix all bugs (Phase 1.1)
2. Enable PSR-4 autoloading (Phase 1.2)
3. Fix test infrastructure (Phase 2.1)
4. Add basic vector/point tests (Phase 2.2)

### Sprint 2 (Week 2): Testing & Quality
1. Complete test coverage (Phase 2.2)
2. Add type hints (Phase 3.2)
3. Improve error handling (Phase 3.4)
4. Add docblocks (Phase 3.3)

### Sprint 3 (Week 3): Core Physics
1. Implement velocity system (Phase 4.1)
2. Add mass/inertia (Phase 4.2)
3. Implement friction (Phase 4.3)
4. Basic point-to-point collision (Phase 4.4)

### Sprint 4 (Week 4): Visualization
1. Enhanced rendering (Phase 5.1)
2. Export options (Phase 5.2)
3. Configuration system (Phase 3.5)

### Sprint 5+: Advanced Features
- Choose based on interest and goals
- Collision resolution, materials, 3D support, etc.

---

## Effort Summary

| Phase | Estimated Hours |
|-------|----------------|
| Phase 1: Bugs & Quick Wins | 2-3 |
| Phase 2: Testing | 15-20 |
| Phase 3: Code Quality | 15-20 |
| Phase 4: Physics Features | 30-40 |
| Phase 5: Visualization | 15-20 |
| Phase 6: Simulation Capabilities | 25-35 |
| Phase 7: Advanced Features | 50-70 |
| Phase 8: 3D Support | 40-60 |
| Phase 9: Performance | 40-60 |
| Phase 10: Developer Experience | 25-35 |
| **TOTAL** | **257-363 hours** |

---

## Notes

- This plan is ambitious and comprehensive
- Focus on phases 1-4 first for a solid foundation
- Each phase can be tackled independently
- Contributions welcome - great for open source collaboration
- Consider creating GitHub issues for each task
- Some advanced features (fluid sim, soft bodies) may warrant separate libraries

## Quick Start Checklist

For immediate impact, complete these tasks first:

- [ ] Fix getYMax() bug
- [ ] Fix vector.php return bug
- [ ] Fix polygon.php typos
- [ ] Fix package.json syntax
- [ ] Enable PSR-4 autoloading
- [ ] Fix and enable existing tests
- [ ] Add images/.gitkeep
- [ ] Implement velocity system
- [ ] Add basic collision response

**Time: ~8-12 hours for massive improvement!**
