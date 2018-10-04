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
    
});
</script>
</body>
</html>