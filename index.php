<html>
<head>
<style>
iframe {
    margin: 0;
    padding: 0;
    border: none;
}
</style>
</head>
<body>
<iframe src="/run.php?step=1" width="500" height="500" id="system"></iframe>
<iframe src="/debug.php?step=1" width="500" height="800" id="dsystem"></iframe>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var i = 1;
    var num_of_steps = 25;
    document.getElementById('system').onload= function() {
        if(i < num_of_steps) {
            i++;
            document.getElementById('system').src = '/run.php?step=' + i;
        }
    };

    var di = 1;
    var dnum_of_steps = 25;
    document.getElementById('dsystem').onload= function() {
        if(di < dnum_of_steps) {
            di++;
            document.getElementById('dsystem').src = '/debug.php?step=' + di;
        }
    };
});
</script>
</body>
</html>