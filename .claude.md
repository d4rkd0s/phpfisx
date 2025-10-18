# phpfisx - PHP Physics Engine

## Project Overview
phpfisx is a 2D physics simulation engine written in PHP. It focuses on simplicity and web portability, leveraging PHP's ability to run without compilation and integrate seamlessly with web browsers.

## Architecture

### Core Components

#### Namespaces
- `phpfisx\areas\` - Simulation spaces and environments
- `phpfisx\entities\` - Physical objects and geometric primitives
- `phpfisx\` - Core simulation interfaces

#### Key Classes

**Areas:**
- `field` - The main 2D simulation space with boundary management, physics calculations, and state persistence

**Entities:**
- `point` - Fundamental particle with position, forces, and collision detection
- `vector` - Mathematical vector operations (add, sub, scale, rotate, project, reflect, etc.)
- `line` - Linear segment with collision detection capabilities
- `circle` - Circular shape with AABB computation
- `polygon` - Complex shapes with rotation, translation, edges, and normals
- `box` - Rectangular bounds with polygon conversion

#### Interfaces
- `simulation` - Base interface for simulation types (create, run methods)

### Physics System

**Current Features:**
- Gravity application (downward force)
- Custom force vectors with direction (0-360 degrees) and magnitude
- Turbulence/randomness injection
- Basic boundary collision (points stay within field bounds)
- Line-point collision detection
- State persistence to disk (field.json)

**Physics Loop (runFisx):**
1. Apply turbulence (random forces)
2. Apply gravity
3. Check collisions with lines
4. Update positions based on accumulated forces

### Visualization

**Rendering Pipeline:**
- Uses PHP GD library for image generation
- Generates PNG frames for each simulation step
- Compiles frames into animated GIF using AnimGif library
- Visual elements: border, background, points (5-pixel cross), lines, timestep label

**Current Approach:**
- Sequential rendering: calculates one step, renders, calculates next
- Real-time generation via iframe in browser
- Audio feedback (rendering.mp3 loops during generation, done.mp3on completion)

### State Management

**Persistence:**
- Serializes field state to `field.json` (gitignored)
- Stores: current step, points array, lines array
- Validates step sequence to prevent out-of-order requests
- Resets on step 0 or 1

**File Structure:**
```json
{
  "step": <number>,
  "points": [{"id": "...", "x": ..., "y": ...}, ...],
  "lines": [{"id": "...", "start_x": ..., "start_y": ..., "end_x": ..., "end_y": ...}, ...]
}
```

## Current Limitations

### Known Issues
1. **Bug in field.php:83** - `getYMax()` returns `x_max` instead of `y_max`
2. **Bug in vector.php:34** - Returns `this` instead of `$this`
3. **Typos in polygon.php** - `_recacl()` should be `_recalc()` (lines 55, 66)
4. **Incomplete velocity system** - Stub methods in point.php (getVelocity, setVelocity)
5. **Weak UUID generation** - Uses rand() instead of proper UUID algorithm
6. **package.json syntax error** - Line 12 has unclosed string
7. **No autoloading** - Uses manual require_once statements
8. **Minimal test coverage** - Pest is set up but tests are mostly disabled/incomplete

### Missing Features (From Roadmap)
- Proper 2D velocity implementation
- Complete mass/inertia system
- Comprehensive collision detection (point-point, polygon-polygon)
- Friction forces
- Material properties
- Adjustable scale (1px = configurable distance)
- Live unstepped simulations
- 3D space support

### Architecture Gaps
- No PSR-4 autoloading
- No configuration system (all values hard-coded)
- Public properties without encapsulation
- Mixed language comments (Chinese/English in vector.php)
- Limited type hinting
- No comprehensive error handling
- No logging system
- Hard-coded simulation parameters

## Development Setup

### Prerequisites
- PHP 8.0+ with `php_gd2` extension
- Composer
- Yarn (optional, for convenience scripts)

### Installation
```bash
git clone https://github.com/d4rkd0s/phpfisx
cd phpfisx
composer install
```

### Running
```bash
php -S localhost:8000
# OR
yarn start
```

### Testing
```bash
composer test  # Runs Pest tests
```

### File Structure
```
phpfisx/
├── phpfisx/
│   ├── areas/
│   │   └── field.php         # Main simulation space
│   ├── entities/
│   │   ├── point.php         # Particle entity
│   │   ├── vector.php        # Vector math
│   │   ├── line.php          # Line segments
│   │   ├── circle.php        # Circles
│   │   ├── polygon.php       # Polygons
│   │   └── box.php           # Bounding boxes
│   ├── simulation.php        # Interface
│   └── simulation_2d.php     # 2D implementation (stub)
├── tests/
│   ├── Pest.php
│   └── Unit/
│       ├── pointTest.php
│       └── vectorTest.php
├── boot.php                  # Bootstrap file
├── index.php                 # Web UI
├── render.php               # Rendering endpoint
├── composer.json
├── package.json
└── README.md
```

### Generated Files (gitignored)
- `field.json` - Simulation state
- `images/*.png` - Frame renders
- `vendor/` - Composer dependencies

## Code Style Notes

- PHP 8.0 target
- Namespaced architecture
- OOP paradigm
- Camelcase for methods
- Lowercase for properties
- Mix of public/private visibility (inconsistent)
- Some properties exposed publicly (bad practice)

## External Dependencies

- `lunakid/anim-gif` - GIF animation creation (fork of animgif)
- `pestphp/pest` - Testing framework (dev dependency)

## Common Workflows

### Creating a New Simulation
1. Edit `render.php`
2. Set field bounds: `new field(array(x_min, x_max, y_min, y_max))`
3. Set point count: `$field->desiredPointCount(100)`
4. Set steps: `$field->setSteps(40)`
5. Call `$field->visualize()`

### Adding a New Entity Type
1. Create class in `phpfisx/entities/`
2. Add namespace `phpfisx\entities`
3. Implement constructor with field reference
4. Add to `boot.php` require_once list
5. Implement visualization in `field.php` visualize method

### Modifying Physics
Edit `field.php` -> `runFisx()` method to add/remove force applications

## Design Philosophy

**Why PHP?**
- No compilation required
- Easy web integration
- Low barrier to entry
- Runs anywhere with PHP installed
- Simple debugging

**Tradeoffs:**
- Slower than compiled languages (C++, Rust, etc.)
- Not suitable for real-time complex simulations
- Limited by single-threaded execution
- GD library limitations compared to OpenGL/WebGL

## Future Vision

The project aims to evolve from simple 2D particle simulations to:
- Full 2D rigid body physics
- Material and friction modeling
- 3D space support
- STL/OBJ file imports
- "Baked" pre-calculated simulations
- Live interactive adjustments
- WebGL-based rendering (potentially)
