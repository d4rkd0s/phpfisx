<html>
<head>
    <title>phpfisx</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="manifest" href="/favicon/site.webmanifest">
    <link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#5bd565">
    <link rel="shortcut icon" href="/favicon/favicon.ico">
    <meta name="apple-mobile-web-app-title" content="phpfisx">
    <meta name="application-name" content="phpfisx">
    <meta name="theme-color" content="#ffffff">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #1a1a1a;
            color: #e0e0e0;
            font-family: 'Courier New', Courier, monospace;
            padding: 32px;
        }

        header {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 32px;
        }

        header img { height: 60px; }

        header h1 {
            font-size: 28px;
            color: #5bd565;
            letter-spacing: 2px;
        }

        .panel {
            display: flex;
            gap: 32px;
            flex-wrap: wrap;
            align-items: flex-start;
        }

        .controls {
            background: #242424;
            border: 1px solid #333;
            border-radius: 6px;
            padding: 24px;
            min-width: 260px;
        }

        .controls h2 {
            font-size: 13px;
            letter-spacing: 2px;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .control-row {
            margin-bottom: 18px;
        }

        .control-row label {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #ccc;
            margin-bottom: 6px;
        }

        .control-row label span {
            color: #5bd565;
            font-weight: bold;
            min-width: 40px;
            text-align: right;
        }

        input[type=range] {
            width: 100%;
            accent-color: #5bd565;
            cursor: pointer;
        }

        button#runbtn {
            width: 100%;
            margin-top: 8px;
            padding: 12px;
            background: #5bd565;
            color: #111;
            font-family: inherit;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.15s;
        }

        button#runbtn:hover { background: #72e57d; }
        button#runbtn:disabled { background: #444; color: #777; cursor: default; }

        .status {
            font-size: 13px;
            color: #888;
            margin-top: 12px;
            min-height: 18px;
            text-align: center;
        }

        .status.running { color: #f0a500; }
        .status.done    { color: #5bd565; }

        .output {
            flex: 1;
            min-width: 520px;
        }

        .output iframe {
            display: block;
            border: 1px solid #333;
            border-radius: 6px;
            background: #fff;
            width: 520px;
            height: 520px;
        }

        .output .placeholder {
            width: 520px;
            height: 520px;
            border: 1px dashed #444;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <header>
        <img src="/logo.png" alt="phpfisx logo">
        <h1>phpfisx</h1>
    </header>

    <div class="panel">
        <div class="controls">
            <h2>Parameters</h2>

            <div class="control-row">
                <label>Points <span id="points-val">100</span></label>
                <input type="range" id="points" min="5" max="300" value="100"
                       oninput="document.getElementById('points-val').textContent = this.value">
            </div>

            <div class="control-row">
                <label>Steps <span id="steps-val">40</span></label>
                <input type="range" id="steps" min="5" max="150" value="40"
                       oninput="document.getElementById('steps-val').textContent = this.value">
            </div>

            <div class="control-row">
                <label>Gravity <span id="gravity-val">1.0</span></label>
                <input type="range" id="gravity" min="0" max="10" step="0.1" value="1.0"
                       oninput="document.getElementById('gravity-val').textContent = parseFloat(this.value).toFixed(1)">
            </div>

            <div class="control-row">
                <label>Friction <span id="friction-val">0.98</span></label>
                <input type="range" id="friction" min="0.80" max="1.00" step="0.01" value="0.98"
                       oninput="document.getElementById('friction-val').textContent = parseFloat(this.value).toFixed(2)">
            </div>

            <button id="runbtn" onclick="run()">Run Simulation</button>
            <p class="status" id="status">Ready</p>
        </div>

        <div class="output" id="output">
            <div class="placeholder" id="placeholder">simulation output will appear here</div>
        </div>
    </div>

    <audio id="snd-rendering" src="/sounds/rendering.mp3" loop></audio>
    <audio id="snd-done"      src="/sounds/done.mp3"></audio>

    <script>
        function run() {
            var btn    = document.getElementById('runbtn');
            var status = document.getElementById('status');
            var output = document.getElementById('output');

            var params = new URLSearchParams({
                points:   document.getElementById('points').value,
                steps:    document.getElementById('steps').value,
                gravity:  document.getElementById('gravity').value,
                friction: document.getElementById('friction').value,
            });

            // Remove old iframe / placeholder
            output.innerHTML = '';

            var iframe = document.createElement('iframe');
            iframe.width  = 520;
            iframe.height = 520;
            output.appendChild(iframe);

            btn.disabled = true;
            status.className = 'status running';
            status.textContent = 'Rendering...';
            document.getElementById('snd-rendering').play().catch(function(){});

            iframe.onload = function() {
                document.getElementById('snd-rendering').pause();
                document.getElementById('snd-rendering').currentTime = 0;
                document.getElementById('snd-done').play().catch(function(){});
                btn.disabled = false;
                status.className = 'status done';
                status.textContent = 'Done';
            };

            iframe.src = 'render.php?' + params.toString();
        }
    </script>
</body>
</html>
