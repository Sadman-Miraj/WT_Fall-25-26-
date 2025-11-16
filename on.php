<!DOCTYPE html>
<html>  
<body>
    <center>
    <h1 id="pagetitle">Light Mode</h1>
    <button id="switchmotion" onclick="toggle()">Switch to Dark Mode</button>

    <script>
        function toggle() {
            var title = document.getElementById("pagetitle");
            var button = document.getElementById("switchmotion");
            var body = document.body;

            if (body.style.backgroundColor === "black") {
                body.style.backgroundColor = "white";
                title.style.color = "black";
                title.innerHTML = "Light Mode";
                button.innerHTML = "Switch to Dark Mode";
            } else {
                body.style.backgroundColor = "black";
                title.style.color = "white";
                title.innerHTML = "Dark Mode";
                button.innerHTML = "Switch to Light Mode";
            }
        }
    </script>
    </center>
</body>
</html>
