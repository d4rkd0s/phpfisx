<html>
<head>
    <title>phpfisx</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="manifest" href="/favicon/site.webmanifest">
    <link rel="shortcut icon" href="/favicon/favicon.ico">
    <meta name="theme-color" content="#ffffff">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #1a1a1a;
            color: #e0e0e0;
            font-family: 'Courier New', Courier, monospace;
            padding: 24px;
        }

        header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }
        header img { height: 48px; }
        header h1 { font-size: 26px; color: #5bd565; letter-spacing: 3px; }

        /* ── Toolbar ── */
        .toolbar {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        .sep { width: 1px; height: 26px; background: #3a3a3a; margin: 0 2px; }

        .tbtn {
            background: #242424;
            border: 1px solid #3a3a3a;
            border-radius: 4px;
            color: #bbb;
            font-family: inherit;
            font-size: 12px;
            padding: 6px 14px;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: background 0.1s, border-color 0.1s, color 0.1s;
            user-select: none;
        }
        .tbtn:hover { background: #2e2e2e; border-color: #555; color: #ddd; }
        .tbtn.active { background: #5bd565; border-color: #5bd565; color: #111; font-weight: bold; }
        .tbtn.danger { border-color: #5a2020; color: #c06060; }
        .tbtn.danger:hover { background: #2a1818; }

        /* ── Main layout ── */
        .main {
            display: flex;
            gap: 20px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        /* ── Canvas frame ── */
        .canvas-frame {
            position: relative;
            flex-shrink: 0;
        }
        #editor {
            display: block;
            border: 1px solid #333;
            border-radius: 4px;
            cursor: crosshair;
            background: #fff;
        }
        #sim-iframe {
            display: none;
            position: absolute;
            top: 0; left: 0;
            width: 500px;
            height: 500px;
            border: 1px solid #333;
            border-radius: 4px;
        }
        #live-canvas {
            display: none;
            position: absolute;
            top: 0; left: 0;
            border: 1px solid #333;
            border-radius: 4px;
            background: #fff;
        }
        .canvas-hint {
            margin-top: 6px;
            font-size: 11px;
            color: #555;
            text-align: center;
            min-height: 14px;
        }

        /* ── Settings panel ── */
        .panel {
            background: #242424;
            border: 1px solid #333;
            border-radius: 6px;
            padding: 20px;
            width: 248px;
            flex-shrink: 0;
        }
        .panel h2 {
            font-size: 11px;
            letter-spacing: 2px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        .row { margin-bottom: 13px; }
        .row label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #bbb;
            margin-bottom: 4px;
        }
        .row label span { color: #5bd565; font-weight: bold; min-width: 36px; text-align: right; }
        input[type=range] { width: 100%; accent-color: #5bd565; cursor: pointer; }

        #run-btn {
            width: 100%;
            margin-top: 6px;
            padding: 11px;
            background: #5bd565;
            color: #111;
            font-family: inherit;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.15s;
        }
        #run-btn:hover { background: #72e57d; }
        #run-btn:disabled { background: #383838; color: #666; cursor: default; }

        #back-btn {
            width: 100%;
            margin-top: 6px;
            padding: 8px;
            background: transparent;
            color: #777;
            font-family: inherit;
            font-size: 12px;
            border: 1px solid #3a3a3a;
            border-radius: 4px;
            cursor: pointer;
            display: none;
            transition: background 0.1s;
        }
        #back-btn:hover { background: #2a2a2a; color: #bbb; }

        .status {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
            text-align: center;
            min-height: 16px;
        }
        .status.running { color: #f0a500; }
        .status.done    { color: #5bd565; }

        .info {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #2e2e2e;
            font-size: 11px;
            color: #555;
            line-height: 1.8;
        }
        .info strong { color: #888; }

        /* ── Property panel (selected shape) ── */
        #prop-panel {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #2e2e2e;
            display: none;
        }
        #prop-panel h3 {
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #5bd565;
            margin-bottom: 12px;
        }
        .prop-reset {
            font-size: 10px;
            color: #555;
            cursor: pointer;
            float: right;
            margin-top: 2px;
        }
        .prop-reset:hover { color: #888; }

        /* ── Legend ── */
        .legend {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 14px;
            font-size: 11px;
            color: #888;
        }
        .legend .item { display: flex; align-items: center; gap: 5px; }
        .legend i {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 2px;
            flex-shrink: 0;
        }
        .legend i.dash {
            background: transparent;
            border: 1.5px dashed #ff9600;
            border-radius: 0;
        }

        /* ── Trails checkbox row ── */
        .row.checkbox-row { display: flex; align-items: center; gap: 7px; margin-bottom: 13px; }
        .row.checkbox-row label { margin: 0; font-size: 12px; color: #bbb; cursor: pointer; }
        .row.checkbox-row input[type=checkbox] { accent-color: #5bd565; cursor: pointer; width: 14px; height: 14px; }
    </style>
</head>
<body>

<header>
    <img src="/logo.png" alt="phpfisx">
    <h1>phpfisx</h1>
</header>

<!-- Toolbar -->
<div class="toolbar">
    <button class="tbtn active" data-tool="box"    title="[B]">□ Box</button>
    <button class="tbtn"        data-tool="circle" title="[C]">○ Circle</button>
    <button class="tbtn"        data-tool="line"   title="[L]">╱ Ramp</button>
    <button class="tbtn"        data-tool="spawn"  title="[Z]">◈ Spawn Zone</button>
    <button class="tbtn"        data-tool="joint"  title="[J]">┄ Joint</button>
    <div class="sep"></div>
    <button class="tbtn"        data-tool="select" title="[V]">↖ Select</button>
    <div class="sep"></div>
    <button class="tbtn" id="save-btn" title="Save scene to this browser">💾 Save</button>
    <button class="tbtn" id="load-btn" title="Load scene saved in this browser">📂 Load</button>
    <div class="sep"></div>
    <button class="tbtn danger" id="clear-btn">✕ Clear</button>
</div>

<!-- Legend -->
<div class="legend">
    <span class="item"><i style="background:#1e50c8"></i> Box / Circle</span>
    <span class="item"><i style="background:#cc3020"></i> Ramp</span>
    <span class="item"><i style="background:rgba(91,213,101,0.55)"></i> Spawn Zone</span>
    <span class="item"><i class="dash"></i> Joint</span>
</div>

<!-- Main area -->
<div class="main">

    <div class="canvas-frame">
        <canvas id="editor" width="500" height="500"></canvas>
        <canvas id="live-canvas" width="500" height="500"></canvas>
        <iframe id="sim-iframe"></iframe>
        <div class="canvas-hint" id="hint">Drag to size · Click for default · [B/C/L/Z/V] switch tool</div>
    </div>

    <div class="panel">
        <h2>Physics</h2>

        <div class="row">
            <label>Points <span id="npoints-v">80</span></label>
            <input type="range" id="npoints" min="5" max="300" value="80"
                   oninput="document.getElementById('npoints-v').textContent=this.value">
        </div>
        <div class="row">
            <label>Steps <span id="nsteps-v">50</span></label>
            <input type="range" id="nsteps" min="5" max="150" value="50"
                   oninput="document.getElementById('nsteps-v').textContent=this.value">
        </div>
        <div class="row">
            <label>Gravity <span id="gravity-v">1.0</span></label>
            <input type="range" id="gravity" min="0" max="10" step="0.1" value="1.0"
                   oninput="document.getElementById('gravity-v').textContent=parseFloat(this.value).toFixed(1)">
        </div>
        <div class="row">
            <label>Friction <span id="friction-v">0.98</span></label>
            <input type="range" id="friction" min="0.80" max="1.00" step="0.01" value="0.98"
                   oninput="document.getElementById('friction-v').textContent=parseFloat(this.value).toFixed(2)">
        </div>
        <div class="row">
            <label>Bounciness <span id="bounce-v">0.70</span></label>
            <input type="range" id="bounce" min="0.00" max="1.00" step="0.05" value="0.70"
                   oninput="document.getElementById('bounce-v').textContent=parseFloat(this.value).toFixed(2)">
        </div>

        <div class="row checkbox-row">
            <input type="checkbox" id="trails">
            <label for="trails">Motion trails</label>
        </div>

        <button id="live-btn">⚡ Live</button>
        <button id="run-btn">▶ Run Simulation</button>
        <button id="back-btn">◀ Back to Editor</button>
        <p class="status" id="status">Ready</p>

        <div class="info" id="shape-info">No shapes placed yet.</div>

        <!-- Property editor — visible when a shape is selected with Select tool -->
        <div id="prop-panel">
            <h3 id="prop-title">Shape Properties</h3>
            <div class="row" id="prop-mass-row">
                <label>Mass
                    <span id="prop-mass-v">3.0</span>
                </label>
                <input type="range" id="prop-mass" min="0.5" max="20" step="0.5" value="3.0">
            </div>
            <div class="row" id="prop-bounce-row">
                <label>Bounciness
                    <span id="prop-bounce-v">Auto</span>
                    <span class="prop-reset" id="prop-bounce-reset" title="Reset to global default">↺ global</span>
                </label>
                <input type="range" id="prop-bounce" min="0.00" max="1.00" step="0.05" value="0.70">
            </div>
        </div>
    </div>

</div>

<audio id="snd-render" src="/sounds/rendering.mp3" loop></audio>
<audio id="snd-done"   src="/sounds/done.mp3"></audio>

<script>
(function () {
    // ─── State ─────────────────────────────────────────────────────────────
    // shapes: {type:'box', cx,cy,w,h,mass,restitution} |
    //         {type:'circle', cx,cy,r,mass,restitution} |
    //         {type:'line', x1,y1,x2,y2,restitution} |
    //         {type:'joint', from:{shapeIndex|null,x,y}, to:{shapeIndex|null,x,y}}
    // restitution: -1 = use field global; 0–1 = per-shape override
    // joint endpoints: shapeIndex null = fixed world anchor at (x,y); shapeIndex set =
    //   pivot on that shape, (x,y) is the click position used to pick the nearest
    //   materialized point on that body once the simulation runs (see field.php).
    const SAVE_KEY = 'phpfisx.scene.v1';
    let shapes     = [
        { type:'box',    cx:370, cy:340, w:90,  h:65,  mass:3.0, restitution:-1 },
        { type:'circle', cx:140, cy:360, r:38,  mass:1.5,        restitution:-1 },
        { type:'line',   x1:20,  y1:200, x2:210, y2:330,        restitution:-1 },
        { type:'line',   x1:480, y1:190, x2:290, y2:310,        restitution:-1 },
    ];
    let spawnZone  = { x1:40, y1:20, x2:460, y2:110 };
    let tool       = 'box';
    let drag       = null;   // { sx, sy } while dragging
    let sel        = null;   // selected index (or 'spawn')
    let moveOff    = null;   // { dx, dy } for drag-moving
    let mouse      = { x:0, y:0 };
    let mode       = 'edit'; // 'edit' | 'run'
    let jointDraft = null;   // { shapeIndex, x, y } — first endpoint clicked, awaiting the second

    // ─── Elements ───────────────────────────────────────────────────────────
    const canvas     = document.getElementById('editor');
    const ctx        = canvas.getContext('2d');
    const iframe     = document.getElementById('sim-iframe');
    const liveCanvas = document.getElementById('live-canvas');
    const liveCtx    = liveCanvas.getContext('2d');
    const hint       = document.getElementById('hint');
    const liveBtn    = document.getElementById('live-btn');
    const runBtn     = document.getElementById('run-btn');
    const backBtn    = document.getElementById('back-btn');
    const status     = document.getElementById('status');
    const info       = document.getElementById('shape-info');
    let   liveSource = null; // active EventSource while a Live playback is running

    // ─── Tool buttons ────────────────────────────────────────────────────────
    document.querySelectorAll('.tbtn[data-tool]').forEach(btn => {
        btn.addEventListener('click', () => setTool(btn.dataset.tool));
    });
    document.getElementById('clear-btn').addEventListener('click', () => {
        shapes = []; spawnZone = null; sel = null; drag = null; jointDraft = null;
        refresh(); draw();
    });

    document.getElementById('save-btn').addEventListener('click', () => {
        const data = {
            shapes, spawnZone,
            settings: {
                points:      +document.getElementById('npoints').value,
                steps:       +document.getElementById('nsteps').value,
                gravity:     +document.getElementById('gravity').value,
                friction:    +document.getElementById('friction').value,
                restitution: +document.getElementById('bounce').value,
                trails:      document.getElementById('trails').checked,
            },
        };
        localStorage.setItem(SAVE_KEY, JSON.stringify(data));
        flashHint('Saved to this browser ✓');
    });

    document.getElementById('load-btn').addEventListener('click', () => {
        const raw = localStorage.getItem(SAVE_KEY);
        if (!raw) { flashHint('Nothing saved yet'); return; }
        let data;
        try { data = JSON.parse(raw); } catch (e) { flashHint('Saved scene is corrupted'); return; }

        shapes    = Array.isArray(data.shapes) ? data.shapes : [];
        spawnZone = data.spawnZone || null;
        if (data.settings) {
            setSliderValue('npoints',  data.settings.points,      'npoints-v',  v => v);
            setSliderValue('nsteps',   data.settings.steps,       'nsteps-v',   v => v);
            setSliderValue('gravity',  data.settings.gravity,     'gravity-v',  v => parseFloat(v).toFixed(1));
            setSliderValue('friction', data.settings.friction,    'friction-v', v => parseFloat(v).toFixed(2));
            setSliderValue('bounce',   data.settings.restitution, 'bounce-v',   v => parseFloat(v).toFixed(2));
            document.getElementById('trails').checked = !!data.settings.trails;
        }
        sel = null; jointDraft = null; drag = null;
        refresh(); draw();
        flashHint('Loaded from this browser ✓');
    });

    function setSliderValue(id, val, labelId, fmt) {
        if (val === undefined || val === null) return;
        document.getElementById(id).value = val;
        document.getElementById(labelId).textContent = fmt(val);
    }

    let flashTimer = null;
    function flashHint(msg) {
        clearTimeout(flashTimer);
        hint.textContent = msg;
        flashTimer = setTimeout(updateHint, 1600);
    }

    function setTool(t) {
        tool = t;
        jointDraft = null;
        document.querySelectorAll('.tbtn[data-tool]').forEach(b =>
            b.classList.toggle('active', b.dataset.tool === t));
        canvas.style.cursor = t === 'select' ? 'default' : 'crosshair';
        updateHint();
        draw();
    }

    document.addEventListener('keydown', e => {
        if (e.target.tagName === 'INPUT') return;
        const map = { b:'box', c:'circle', l:'line', z:'spawn', j:'joint', v:'select' };
        if (map[e.key]) { setTool(map[e.key]); return; }
        if (e.key === 'Escape' && jointDraft) { jointDraft = null; draw(); return; }
        if ((e.key === 'Delete' || e.key === 'Backspace') && sel !== null) {
            if (sel === 'spawn') spawnZone = null;
            else shapes.splice(sel, 1);
            sel = null;
            refresh(); draw();
        }
    });

    // ─── Canvas coordinate helper ────────────────────────────────────────────
    function pos(e) {
        const r = canvas.getBoundingClientRect();
        return { x: (e.clientX - r.left) * (500 / r.width),
                 y: (e.clientY - r.top)  * (500 / r.height) };
    }

    // ─── Mouse events ────────────────────────────────────────────────────────
    canvas.addEventListener('mousedown', e => {
        if (mode !== 'edit') return;
        const p = pos(e);
        if (tool === 'select') {
            sel = hitTest(p.x, p.y);
            if (sel !== null) {
                const s = sel === 'spawn' ? spawnZone : shapes[sel];
                const isLineLike = sel === 'spawn' || shapes[sel]?.type === 'line' || shapes[sel]?.type === 'joint';
                const cx = isLineLike ? (s.x1 !== undefined ? (s.x1 + s.x2) / 2 : (s.from.x + s.to.x) / 2) : s.cx;
                const cy = isLineLike ? (s.y1 !== undefined ? (s.y1 + s.y2) / 2 : (s.from.y + s.to.y) / 2) : s.cy;
                moveOff = { dx: p.x - cx, dy: p.y - cy };
                drag = { sx: p.x, sy: p.y, moving: true };
            }
        } else if (tool === 'joint') {
            const hit = hitTestJointable(p.x, p.y);
            const endpoint = { shapeIndex: hit, x: p.x, y: p.y };
            if (!jointDraft) {
                jointDraft = endpoint;
            } else {
                shapes.push({ type: 'joint', from: jointDraft, to: endpoint });
                jointDraft = null;
                sel = null;
                refresh();
            }
        } else {
            drag = { sx: p.x, sy: p.y };
        }
        draw();
    });

    canvas.addEventListener('mousemove', e => {
        if (mode !== 'edit') return;
        const p = pos(e);
        mouse = p;
        if (drag?.moving && tool === 'select' && sel !== null) {
            const nx = p.x - moveOff.dx, ny = p.y - moveOff.dy;
            if (sel === 'spawn') {
                const hw = (spawnZone.x2 - spawnZone.x1) / 2,
                      hh = (spawnZone.y2 - spawnZone.y1) / 2;
                spawnZone = { x1: nx-hw, y1: ny-hh, x2: nx+hw, y2: ny+hh };
            } else {
                const s = shapes[sel];
                if (s.type === 'box' || s.type === 'circle') {
                    s.cx = nx; s.cy = ny;
                } else if (s.type === 'line') {
                    const mx = (s.x1+s.x2)/2, my = (s.y1+s.y2)/2;
                    const ddx = nx - mx, ddy = ny - my;
                    s.x1 += ddx; s.y1 += ddy; s.x2 += ddx; s.y2 += ddy;
                } else if (s.type === 'joint') {
                    // Only free (unattached) endpoints move with the drag — an
                    // endpoint pinned to a shape stays pinned to that shape's pivot.
                    const mx = (s.from.x+s.to.x)/2, my = (s.from.y+s.to.y)/2;
                    const ddx = nx - mx, ddy = ny - my;
                    if (s.from.shapeIndex === null) { s.from.x += ddx; s.from.y += ddy; }
                    if (s.to.shapeIndex   === null) { s.to.x   += ddx; s.to.y   += ddy; }
                }
            }
        }
        draw();
    });

    canvas.addEventListener('mouseup', e => {
        if (mode !== 'edit') return;
        const p = pos(e);
        if (!drag) return;
        if (tool === 'select') {
            drag = null; moveOff = null;
            refresh(); draw(); return;
        }
        const { sx, sy } = drag;
        const ddx = p.x - sx, ddy = p.y - sy;
        const dist = Math.hypot(ddx, ddy);
        drag = null;

        if (tool === 'box') {
            const w = dist > 12 ? Math.max(20, Math.abs(ddx)) : 70;
            const h = dist > 12 ? Math.max(20, Math.abs(ddy)) : 50;
            shapes.push({ type:'box', cx: sx + ddx/2, cy: sy + ddy/2,
                          w, h, mass:3.0, restitution:-1 });

        } else if (tool === 'circle') {
            shapes.push({ type:'circle', cx: sx, cy: sy,
                          r: Math.max(12, dist > 10 ? dist : 32), mass:1.5, restitution:-1 });

        } else if (tool === 'line') {
            if (dist > 8)
                shapes.push({ type:'line', x1:sx, y1:sy, x2:p.x, y2:p.y, restitution:-1 });

        } else if (tool === 'spawn') {
            if (dist > 12)
                spawnZone = { x1:Math.min(sx,p.x), y1:Math.min(sy,p.y),
                              x2:Math.max(sx,p.x), y2:Math.max(sy,p.y) };
            else
                spawnZone = { x1:sx-160, y1:sy-25, x2:sx+160, y2:sy+25 };
        }

        sel = null;
        refresh(); draw();
    });

    canvas.addEventListener('mouseleave', () => {
        if (drag && !drag.moving) drag = null;
        draw();
    });

    // ─── Hit testing ─────────────────────────────────────────────────────────
    function hitTest(x, y) {
        for (let i = shapes.length - 1; i >= 0; i--) {
            const s = shapes[i];
            if (s.type === 'box') {
                if (x >= s.cx-s.w/2-6 && x <= s.cx+s.w/2+6 &&
                    y >= s.cy-s.h/2-6 && y <= s.cy+s.h/2+6) return i;
            } else if (s.type === 'circle') {
                if (Math.hypot(x-s.cx, y-s.cy) <= s.r + 8) return i;
            } else if (s.type === 'line') {
                if (segDist(x, y, s.x1, s.y1, s.x2, s.y2) < 8) return i;
            } else if (s.type === 'joint') {
                if (segDist(x, y, s.from.x, s.from.y, s.to.x, s.to.y) < 8) return i;
            }
        }
        if (spawnZone && x >= spawnZone.x1 && x <= spawnZone.x2 &&
            y >= spawnZone.y1 && y <= spawnZone.y2) return 'spawn';
        return null;
    }

    // Only box/circle shapes are valid joint attachment points — a click on a
    // ramp (already-static) or empty canvas becomes a fixed anchor instead.
    function hitTestJointable(x, y) {
        for (let i = shapes.length - 1; i >= 0; i--) {
            const s = shapes[i];
            if (s.type !== 'box' && s.type !== 'circle') continue;
            if (s.type === 'box') {
                if (x >= s.cx-s.w/2-6 && x <= s.cx+s.w/2+6 &&
                    y >= s.cy-s.h/2-6 && y <= s.cy+s.h/2+6) return i;
            } else if (Math.hypot(x-s.cx, y-s.cy) <= s.r + 8) {
                return i;
            }
        }
        return null;
    }

    function segDist(px, py, x1, y1, x2, y2) {
        const abx = x2-x1, aby = y2-y1, ab2 = abx*abx+aby*aby;
        if (ab2 < 1e-6) return Math.hypot(px-x1, py-y1);
        const t = Math.max(0, Math.min(1, ((px-x1)*abx+(py-y1)*aby)/ab2));
        return Math.hypot(px-(x1+t*abx), py-(y1+t*aby));
    }

    // ─── Drawing ─────────────────────────────────────────────────────────────
    const C_BOX    = 'rgba(30,80,200,0.18)';
    const C_BOX_S  = '#1e50c8';
    const C_SEL    = 'rgba(255,150,0,0.25)';
    const C_SEL_S  = '#ff9600';
    const C_LINE   = '#cc3020';
    const C_SPAWN  = 'rgba(91,213,101,0.10)';
    const C_SPAWN_S= 'rgba(91,213,101,0.55)';
    const C_JOINT  = '#ff9600';

    function draw() {
        ctx.clearRect(0, 0, 500, 500);

        // Field background
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, 500, 500);
        ctx.strokeStyle = '#222';
        ctx.lineWidth = 3;
        ctx.strokeRect(1.5, 1.5, 497, 497);

        // Spawn zone
        if (spawnZone) {
            const isSel = sel === 'spawn';
            const { x1, y1, x2, y2 } = spawnZone;
            ctx.fillStyle = isSel ? 'rgba(91,213,101,0.2)' : C_SPAWN;
            ctx.fillRect(x1, y1, x2-x1, y2-y1);
            ctx.strokeStyle = isSel ? '#5bd565' : C_SPAWN_S;
            ctx.lineWidth = 1.5;
            ctx.setLineDash([6,4]);
            ctx.strokeRect(x1, y1, x2-x1, y2-y1);
            ctx.setLineDash([]);
            ctx.fillStyle = isSel ? '#5bd565' : C_SPAWN_S;
            ctx.font = '10px Courier New';
            ctx.fillText('spawn zone', x1+5, y1+13);
        }

        // Shapes
        shapes.forEach((s, i) => drawShape(s, i === sel));

        // Drag preview
        if (drag && !drag.moving) drawPreview(drag.sx, drag.sy, mouse.x, mouse.y);

        // Pending joint — first endpoint placed, awaiting the second click
        if (tool === 'joint' && jointDraft) {
            ctx.save();
            ctx.globalAlpha = 0.6;
            ctx.strokeStyle = C_JOINT;
            ctx.lineWidth = 2;
            ctx.setLineDash([6, 4]);
            ctx.beginPath();
            ctx.moveTo(jointDraft.x, jointDraft.y);
            ctx.lineTo(mouse.x, mouse.y);
            ctx.stroke();
            ctx.setLineDash([]);
            dot(ctx, jointDraft.x, jointDraft.y, C_JOINT);
            ctx.restore();
        }
    }

    function drawShape(s, selected) {
        ctx.save();
        const fill   = selected ? C_SEL   : C_BOX;
        const stroke = selected ? C_SEL_S : C_BOX_S;

        if (s.type === 'box') {
            ctx.fillStyle   = fill;
            ctx.strokeStyle = stroke;
            ctx.lineWidth   = selected ? 2 : 1.5;
            ctx.fillRect  (s.cx-s.w/2, s.cy-s.h/2, s.w, s.h);
            ctx.strokeRect(s.cx-s.w/2, s.cy-s.h/2, s.w, s.h);
            dot(ctx, s.cx, s.cy, stroke);

        } else if (s.type === 'circle') {
            ctx.fillStyle   = fill;
            ctx.strokeStyle = stroke;
            ctx.lineWidth   = selected ? 2 : 1.5;
            ctx.beginPath(); ctx.arc(s.cx, s.cy, s.r, 0, Math.PI*2);
            ctx.fill(); ctx.stroke();
            dot(ctx, s.cx, s.cy, stroke);

        } else if (s.type === 'line') {
            ctx.strokeStyle = selected ? C_SEL_S : C_LINE;
            ctx.lineWidth   = selected ? 3 : 2.5;
            ctx.lineCap     = 'round';
            ctx.beginPath();
            ctx.moveTo(s.x1, s.y1); ctx.lineTo(s.x2, s.y2);
            ctx.stroke();
            dot(ctx, s.x1, s.y1, ctx.strokeStyle);
            dot(ctx, s.x2, s.y2, ctx.strokeStyle);

        } else if (s.type === 'joint') {
            // Dashed orange line + small pivot dot at each end — visually
            // distinct from the solid blue/red rigid-constraint look, and
            // matches the dashed orange rendering field.php draws in playback.
            ctx.strokeStyle = selected ? C_SEL_S : C_JOINT;
            ctx.lineWidth   = selected ? 3 : 2;
            ctx.lineCap     = 'round';
            ctx.setLineDash(selected ? [] : [6, 4]);
            ctx.beginPath();
            ctx.moveTo(s.from.x, s.from.y); ctx.lineTo(s.to.x, s.to.y);
            ctx.stroke();
            ctx.setLineDash([]);
            dot(ctx, s.from.x, s.from.y, ctx.strokeStyle);
            dot(ctx, s.to.x,   s.to.y,   ctx.strokeStyle);
        }
        ctx.restore();
    }

    function dot(ctx, x, y, color) {
        ctx.fillStyle = color;
        ctx.beginPath(); ctx.arc(x, y, 3, 0, Math.PI*2); ctx.fill();
    }

    function drawPreview(sx, sy, ex, ey) {
        const dx = ex-sx, dy = ey-sy;
        ctx.save();
        ctx.globalAlpha = 0.5;
        ctx.setLineDash([5,4]);
        if (tool === 'box') {
            ctx.strokeStyle = C_BOX_S; ctx.lineWidth = 1.5;
            ctx.strokeRect(sx, sy, dx, dy);
        } else if (tool === 'circle') {
            ctx.strokeStyle = C_BOX_S; ctx.lineWidth = 1.5;
            ctx.beginPath(); ctx.arc(sx, sy, Math.hypot(dx,dy), 0, Math.PI*2); ctx.stroke();
        } else if (tool === 'line') {
            ctx.strokeStyle = C_LINE; ctx.lineWidth = 2.5; ctx.lineCap = 'round';
            ctx.beginPath(); ctx.moveTo(sx,sy); ctx.lineTo(ex,ey); ctx.stroke();
        } else if (tool === 'spawn') {
            ctx.strokeStyle = '#5bd565'; ctx.lineWidth = 1.5;
            ctx.strokeRect(Math.min(sx,ex), Math.min(sy,ey), Math.abs(dx), Math.abs(dy));
        }
        ctx.setLineDash([]);
        ctx.restore();
    }

    // ─── Run / Live / back ──────────────────────────────────────────────────────
    function buildScene() {
        return {
            settings: {
                points:      +document.getElementById('npoints').value,
                steps:       +document.getElementById('nsteps').value,
                gravity:     +document.getElementById('gravity').value,
                friction:    +document.getElementById('friction').value,
                restitution: +document.getElementById('bounce').value,
                trails:      document.getElementById('trails').checked,
            },
            shapes: [
                ...shapes,
                ...(spawnZone ? [{ type:'spawn', ...spawnZone }] : []),
            ],
        };
    }

    function stopLive() {
        if (liveSource) {
            liveSource.close();
            liveSource = null;
        }
        liveCanvas.style.display = 'none';
    }

    runBtn.addEventListener('click', () => {
        stopLive();
        const scene = buildScene();

        mode = 'run';
        canvas.style.display = 'none';
        hint.style.display   = 'none';
        iframe.style.display = 'block';
        runBtn.disabled      = true;
        liveBtn.disabled     = true;
        backBtn.style.display= 'block';
        status.className     = 'status running';
        status.textContent   = 'Rendering…';
        document.getElementById('snd-render').play().catch(() => {});

        iframe.onload = () => {
            document.getElementById('snd-render').pause();
            document.getElementById('snd-render').currentTime = 0;
            document.getElementById('snd-done').play().catch(() => {});
            runBtn.disabled  = false;
            liveBtn.disabled = false;
            status.className = 'status done';
            status.textContent = 'Done';
        };

        iframe.src = 'render.php?scene=' + encodeURIComponent(JSON.stringify(scene));
    });

    // Live mode — streams the simulation via Server-Sent Events (live.php)
    // and draws each incoming frame straight onto a canvas, instead of
    // waiting for render.php to finish every step and ship one big HTML
    // blob. render.php/the iframe stays as-is for the "generate one static
    // playback page" use case; this is the interactive alternative.
    liveBtn.addEventListener('click', () => {
        stopLive();
        const scene = buildScene();

        mode = 'run';
        canvas.style.display     = 'none';
        hint.style.display       = 'none';
        iframe.style.display     = 'none';
        liveCanvas.style.display = 'block';
        runBtn.disabled          = true;
        liveBtn.disabled         = true;
        backBtn.style.display    = 'block';
        status.className         = 'status running';
        status.textContent       = 'Streaming…';
        document.getElementById('snd-render').play().catch(() => {});

        let bounds      = { width: 500, height: 500 };
        let staticLines = [];

        liveSource = new EventSource('live.php?scene=' + encodeURIComponent(JSON.stringify(scene)));

        liveSource.addEventListener('init', e => {
            const data = JSON.parse(e.data);
            bounds      = { width: data.width, height: data.height };
            staticLines = data.staticLines;
            liveCanvas.width  = bounds.width;
            liveCanvas.height = bounds.height;
        });

        liveSource.addEventListener('step', e => {
            drawLiveFrame(JSON.parse(e.data), bounds, staticLines);
        });

        liveSource.addEventListener('done', () => {
            stopLive();
            document.getElementById('snd-render').pause();
            document.getElementById('snd-render').currentTime = 0;
            document.getElementById('snd-done').play().catch(() => {});
            runBtn.disabled  = false;
            liveBtn.disabled = false;
            status.className = 'status done';
            status.textContent = 'Done';
        });

        liveSource.onerror = () => {
            stopLive();
            document.getElementById('snd-render').pause();
            runBtn.disabled  = false;
            liveBtn.disabled = false;
            status.className = 'status';
            status.textContent = 'Stream error';
        };
    });

    function drawLiveFrame(frame, bounds, staticLines) {
        liveCtx.fillStyle = '#fff';
        liveCtx.fillRect(0, 0, bounds.width, bounds.height);

        // Static surfaces — thick red, matching render.php's GD playback
        liveCtx.strokeStyle = '#d23223';
        liveCtx.lineWidth   = 3;
        for (const [x1, y1, x2, y2] of staticLines) {
            liveCtx.beginPath();
            liveCtx.moveTo(x1, y1);
            liveCtx.lineTo(x2, y2);
            liveCtx.stroke();
        }

        // Rigid body boundary edges — blue
        liveCtx.strokeStyle = '#1e50c8';
        liveCtx.lineWidth   = 1;
        for (const [ax, ay, bx, by] of frame.edges) {
            liveCtx.beginPath();
            liveCtx.moveTo(ax, ay);
            liveCtx.lineTo(bx, by);
            liveCtx.stroke();
        }

        // Joints — dashed orange line + pivot dots, matching render.php
        liveCtx.strokeStyle = '#ff9600';
        liveCtx.setLineDash([4, 3]);
        for (const [ax, ay, bx, by] of frame.joints) {
            liveCtx.beginPath();
            liveCtx.moveTo(ax, ay);
            liveCtx.lineTo(bx, by);
            liveCtx.stroke();
            dot(liveCtx, ax, ay, '#ff9600');
            dot(liveCtx, bx, by, '#ff9600');
        }
        liveCtx.setLineDash([]);

        // Loose/free points — black
        liveCtx.fillStyle = '#000';
        for (const [x, y] of frame.points) {
            liveCtx.fillRect(x - 1, y - 1, 3, 3);
        }

        status.textContent = 'step ' + frame.step;
    }

    backBtn.addEventListener('click', () => {
        mode = 'edit';
        stopLive();
        iframe.style.display = 'none';
        iframe.src           = '';
        canvas.style.display = 'block';
        hint.style.display   = '';
        backBtn.style.display= 'none';
        runBtn.disabled      = false;
        liveBtn.disabled     = false;
        status.className     = 'status';
        status.textContent   = 'Ready';
        document.getElementById('snd-render').pause();
        draw();
    });

    // ─── Helpers ──────────────────────────────────────────────────────────────
    const HINTS = {
        box:    'Drag to size · Click for default (70×50)',
        circle: 'Drag to set radius · Click for default (r=32)',
        line:   'Drag to draw immovable ramp / wall',
        spawn:  'Drag to set particle spawn zone (one at a time)',
        joint:  'Click first endpoint (shape or empty canvas) · Click second to connect · Esc to cancel',
        select: 'Click to select · Drag to move · Delete to remove',
    };

    function updateHint() {
        hint.textContent = HINTS[tool] || '';
    }

    function refresh() {
        const boxes   = shapes.filter(s => s.type === 'box').length;
        const circles = shapes.filter(s => s.type === 'circle').length;
        const lines   = shapes.filter(s => s.type === 'line').length;
        const joints  = shapes.filter(s => s.type === 'joint').length;
        const parts = [];
        if (boxes)   parts.push(`${boxes} box${boxes>1?'es':''}`);
        if (circles) parts.push(`${circles} circle${circles>1?'s':''}`);
        if (lines)   parts.push(`${lines} ramp${lines>1?'s':''}`);
        if (joints)  parts.push(`${joints} joint${joints>1?'s':''}`);
        if (!parts.length) parts.push('no shapes');
        parts.push(spawnZone ? '✓ spawn zone' : 'no spawn zone');
        info.innerHTML = '<strong>Scene:</strong> ' + parts.join(' · ');
        showProps();
    }

    // ─── Property panel ──────────────────────────────────────────────────────
    const propPanel      = document.getElementById('prop-panel');
    const propTitle      = document.getElementById('prop-title');
    const propMassRow    = document.getElementById('prop-mass-row');
    const propBounceRow  = document.getElementById('prop-bounce-row');
    const propMassSlider = document.getElementById('prop-mass');
    const propMassVal    = document.getElementById('prop-mass-v');
    const propBounce     = document.getElementById('prop-bounce');
    const propBounceVal  = document.getElementById('prop-bounce-v');
    const propReset      = document.getElementById('prop-bounce-reset');

    function showProps() {
        if (sel === null || sel === 'spawn' || tool !== 'select') {
            propPanel.style.display = 'none';
            return;
        }
        const s = shapes[sel];
        if (!s) { propPanel.style.display = 'none'; return; }

        propPanel.style.display = 'block';

        // Title
        const labels = { box:'Box', circle:'Circle', line:'Ramp', joint:'Joint' };
        propTitle.textContent = (labels[s.type] || s.type) + ' Properties';

        // Mass and bounciness don't apply to static lines or joints (a joint
        // has no material of its own — it just pins two points/anchors together)
        const isMaterial = (s.type !== 'line' && s.type !== 'joint');
        propMassRow.style.display   = isMaterial ? '' : 'none';
        propBounceRow.style.display = isMaterial ? '' : 'none';
        if (isMaterial) {
            propMassSlider.value = s.mass ?? 3.0;
            propMassVal.textContent = parseFloat(propMassSlider.value).toFixed(1);

            // Bounciness (-1 = auto/global)
            const hasCustomBounce = (s.restitution ?? -1) >= 0;
            propBounce.value = hasCustomBounce ? s.restitution : +document.getElementById('bounce').value;
            updateBounceLabel();
        }
    }

    function updateBounceLabel() {
        const s = sel !== null && sel !== 'spawn' ? shapes[sel] : null;
        if (!s) return;
        const isCustom = (s.restitution ?? -1) >= 0;
        propBounceVal.textContent = isCustom
            ? parseFloat(propBounce.value).toFixed(2)
            : 'Auto';
        propBounceVal.style.color = isCustom ? '#5bd565' : '#888';
        propReset.style.display   = isCustom ? '' : 'none';
    }

    propMassSlider.addEventListener('input', () => {
        if (sel === null || sel === 'spawn') return;
        shapes[sel].mass = parseFloat(propMassSlider.value);
        propMassVal.textContent = parseFloat(propMassSlider.value).toFixed(1);
    });

    propBounce.addEventListener('input', () => {
        if (sel === null || sel === 'spawn') return;
        shapes[sel].restitution = parseFloat(propBounce.value);
        updateBounceLabel();
    });

    propReset.addEventListener('click', () => {
        if (sel === null || sel === 'spawn') return;
        shapes[sel].restitution = -1;
        propBounce.value = +document.getElementById('bounce').value;
        updateBounceLabel();
    });

    // ─── Init ─────────────────────────────────────────────────────────────────
    updateHint();
    refresh();
    draw();
})();
</script>
</body>
</html>
