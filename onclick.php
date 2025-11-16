<!DOCTYPE html>
<html>
<head>
<style>
    body {
        background-color: white;
        transition: background-color 0.5s;
    }
    #l {
        color: black;
        transition: color 0.5s;
    }
</style>
</head>

<body id="b">
    <center>
        <h1 id="l">Light Mode</h1>
        <button onclick="myFunction()">Click Me </button>
    </center>

    <script>
        function myFunction() {
            var body = document.getElementById("b");
            var txt = document.getElementById("l");

            // Check current background color
            if (body.style.backgroundColor === "black" ) {
                body.style.backgroundColor = "white";
                txt.style.color = "black";
                txt.innerHTML = "Light Mode";
            } else {
                body.style.backgroundColor = "black";
                txt.style.color = "white";
                txt.innerHTML = "Dark Mode";
            }
        }
    </script>
</body>
</html>
