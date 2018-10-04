# phpfisx
A basic Physics engine in PHP because why not

![Simple Points Animated GIF](simple_points.gif)

### How?

Using PHP, and some simple Object Oriented programming. A re-used random seed is used to calculate based on the current "step", data/points/variables, to produce the resulting math behind some **simple** physics. Visualizing it currently is being done with some iframe.onload and stepping through and requesting PNG images of the current "state" of the simulation. Each state is generated on the fly, and each request to phpfisx returns a single state. In the future I would I plan to "bake" states, so calculations can be ran once, for all states, and then a final Simulation can be played. In some smaller simulations a "live" view is what I'll be trying to achive allowing some simply things to be ran on the fly/adjusted.

### Why?

Theres plenty of complex, overbloated libraries in many languages. Mostly graphics / simulations are ran in compiled languages like C, C++, Java, Golang... etc. But I know what I wanted to achive wasn't overly complex (at least when I started) and PHP is such a friendly language, it's ability to run on the fly without compiling and interoperability with the web. Makes is portable and easy to use, all you need is php (internal web server with `php -S`) and a browser.


### Planned Features

- [x] 2D Scale (1px = 1 meter)
- [x] 2D Fields (2d structure with bounds x,y)
- [x] 2D Points (Point's live in fields)
- [x] 2D Visualizer (built in PHP GD Image Library)
- [x] 2D Gravity
- [ ] 2D Mass
- [ ] 2D Velocity
- [ ] 2D Lines
- [ ] 2D Collision Detection
- [ ] 2D Polygons
- [ ] 2D Friction
- [ ] 2D Materials
- [ ] 2D Adjustable Scale
- [ ] Live system unstepped
- [ ] 3D Spaces
- [ ] 3D Points
- [ ] 3D Lines
- [ ] 3D Polygons
- [ ] 3D Shapes
- [ ] 3D .stl imports https://en.wikipedia.org/wiki/STL_(file_format)
- [ ] 3D .obj imports http://paulbourke.net/dataformats/obj/
