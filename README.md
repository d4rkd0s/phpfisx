<img src="logo.png" alt="logo" width="200"/>

A physics engine written in PHP

<hr>

![Simple Points Animated GIF](simple_points.gif)

## Install / Use
Currently I have my latest "simulation" hard coded into the repo, but its easy to change. Anyways to see/run what I'm currently working on in the engine follow the steps below.

Install php first http://php.net/manual/en/install.php

Ensure you have `php_gd2` enabled for image rendering.

1. Clone the repo and go into the directory
`git clone https://github.com/d4rkd0s/phpfisx && cd phpfisx`

2. Run a local webserver with PHP
`php -S localhost:8000`

3. View in your browser, I use Chrome
http://localhost:8000/


### Got Yarn? Php in PATH? go FAST!!!

`yarn start`

Then visit: http://localhost:8000/

It should... work:

![Image explaining to run, yarn start, and the website is available via, localhost, on port, 8080](quick_start.png)

### How?

Using PHP, and some simple Object Oriented programming. A re-used random seed is used to calculate based on the current "step", data/points/variables, to produce the resulting math behind some **simple** physics. Visualizing it currently is being done with some iframe.onload and stepping through and requesting PNG images of the current "state" of the simulation. Each state is generated on the fly, and each request to phpfisx returns a single state. In the future I would I plan to "bake" states, so calculations can be ran once, for all states, and then a final Simulation can be played. In some smaller simulations a "live" view is what I'll be trying to achive allowing some simple things to be ran on the fly/adjusted.

### Why?

Theres plenty of complex, overbloated libraries in many languages. Most graphics / simulations are ran in compiled languages like C, C++, Java, Golang... etc. But I know what I wanted to achieve wasn't overly complex (at least when I started) and PHP is such a friendly language, it's ability to run on the fly without compiling and interoperability with the web. Makes is portable and easy to use, all you need is php (internal web server with `php -S`) and a browser.

### Why? (Asking Chat GPT 😂)

Prompt: `What would you want to run a physics simulation in PHP? What would be the benefits of using PHP as your selected language?`

Answer:

*There are a few benefits to using PHP for running a physics simulation:*

*- PHP is a general-purpose programming language, so it has a lot of built-in features and functions that you can use to build your simulation.*

*- PHP is a server-side language, so you can easily set up a web-based interface for your simulation. This can make it easy for users to interact with your simulation and view the results.*

*- PHP is relatively easy to learn and use, so it may be a good choice for someone who is just starting out with programming or who is not an experienced software developer.*


### Reporting Issues

Please use the standard GitHub issue tracker.

Report issues here: https://github.com/d4rkd0s/phpfisx/issues/new/choose

Select Bug or Feature.

![Image showcasing the different issue choices, bug, and, feature, which are available at, https://github.com/d4rkd0s/phpfisx/issues/new/choose](bug-feature-image.png)

### Planned Features

- [x] 2D Scale (1px = 1 meter)
- [x] 2D Fields (2d structure with bounds x,y)
- [x] 2D Points (Point's live in fields)
- [x] 2D Visualizer (built in PHP GD Image Library)
- [x] 2D Gravity
- [x] 2D Force
- [X] 2D Disk Stored States (.gitignore'd `field.json` file in root of repo)
- [-] 2D Mass (Basic implementation with gravity)
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
