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
    <meta name="msapplication-TileColor" content="#00a300">
    <meta name="msapplication-TileImage" content="/favicon/mstile-144x144.png">
    <meta name="msapplication-config" content="/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <style>
        iframe {
            margin: 0;
            padding: 0;
            border: none;
        }
        #status {
            font-size: 24px;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
</head>
<body style="background-color:gray;">
    <img src="/logo.png" style="padding:42px; height: 100px;"><br>
    <button id="renderbtn" onclick="start()">Render</button>
    <p id="status"></p>
    <iframe src="" width="1000" height="1000" id="system"></iframe>
    <script>
    var rendering = new Audio('/sounds/rendering.mp3');
    // loop rendering audio
    rendering.addEventListener('ended', function() {
        this.currentTime = 0;
        this.play();
    }, false);
    var done = new Audio('/sounds/done.mp3');
    function changeTitle(title) {
            document.title = title; 
            document.getElementById("status").innerHTML = title;
    }
    function start(){
        document.getElementById("renderbtn").remove();
        document.getElementById('system').src = 'render.php';
        changeTitle("Rendering... 🌀");
        // Play rendering sound
        rendering.play();
        document.getElementById('system').onload = function() {
            rendering.pause();
            console.log(document.getElementById('system').src);
            changeTitle("Rendered ✔️");
            done.play();
        };
    }
    changeTitle("Waiting...");
    </script>
</body>
</html>