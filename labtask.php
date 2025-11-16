<!Doctype html>
<html lang="en">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Festival</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <center>
        <div class="container">
        <h2>Participant Registration Section</h2>
<table>
        <form onsubmit="return validateForm();">


        <tr>
           <td> <label>Name:</label></td>
            <td><input type="text" id="name" oninput="resetErrors()">
            <div class="error" id="name-error"></div></td>
</tr>

        <tr>
           <td> <label>Email:</label></td>
            <td><input type="text" id="email" oninput="resetErrors()">
            <div class="error" id="email-error"></div></td>
</tr>

        <tr>
           <td> <label>Password:</label></td>
            <td><input type="password" id="password" oninput="resetErrors()">
            <div class="error" id="password-error"></div></td>
</tr>

        <tr>
           <td> <label>Confirm Password:</label></td>
            <td><input type="password" id="cpass" oninput="resetErrors()">
            <div class="error" id="cpass-error"></div></td>
</tr>



</table>
<button type="submit">Submit</button>
        </form>
        </center>


    <!-- JavaScript for form validation -->


    <script>
function validateForm() {

    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const pass = document.getElementById("password").value;
    const cpass = document.getElementById("cpass").value;

    const nEr = document.getElementById("name-error");
    const eEr = document.getElementById("email-error");
    const pEr = document.getElementById("password-error");
    const cEr = document.getElementById("cpass-error");


    nEr.textContent = "";
    eEr.textContent = "";
    pEr.textContent = "";
    cEr.textContent = "";

    let valid = true;

    // -------- NAME --------
    if (name === "") {
        nEr.textContent = "Name cannot be empty.";
        valid = false;
    } else if (/\d/.test(name)) {
        nEr.textContent = "Name cannot contain numbers.";
        valid = false;
    } else if (!/^[A-Za-z ]+$/.test(name)) {
        nEr.textContent = "Name must contain only letters.";
        valid = false;
    }

    // -------- EMAIL  --------
    if (email === "") {
        eEr.textContent = "Email cannot be empty.";
        valid = false;
    } else if (!email.includes("@")) {
        eEr.textContent = "Email must contain @ symbol.";
        valid = false;
    }

    // -------- PASSWORD  --------
    if (pass === "") {
        pEr.textContent = "Password cannot be empty.";
        valid = false;
    }

    if (cpass === "") {
        cEr.textContent = "Confirm your password.";
        valid = false;
    } else if (pass !== cpass) {
        cEr.textContent = "Passwords do not match.";
        valid = false;
    }

    // -------- FINAL  --------
    if (!valid) return false;

   

    return false;
}


// Clear errors on typing
function resetErrors() {
    document.getElementById("name-error").textContent = "";
    document.getElementById("email-error").textContent = "";
    document.getElementById("password-error").textContent = "";
    document.getElementById("cpass-error").textContent = "";
}
</script>
    </div>
</body>
</html>