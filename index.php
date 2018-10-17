<html>
<head>
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
</style>
</head>
<body>
<img src="/logo.png" style="padding:42px; height: 100px;">
<br>
<iframe src="/run.php?step=1" width="500" height="500" id="system"></iframe>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var num_of_steps = 25;

    var i = 1;
    document.getElementById('system').onload= function() {
        if(i < num_of_steps) {
            i++;
            document.getElementById('system').src = '/run.php?step=' + i;
        }
    };
});
</script>
</body>
</html>