<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Validation</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 20px;
        }
        .container {
            width: 400px;
            margin: auto;
            padding: 25px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #777;
            border-radius: 4px;
        }
        .error {
            color: red;
            font-size: 0.9em;
            margin-top: 3px;
        }
        button {
            margin-top: 20px;
            width: 100%;
            padding: 10px;
            background: green;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1em;
        }
        button:hover {
            background: darkgreen;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Registration Form</h2>

        <form onsubmit="return validateForm();">

            <label>Name:</label>
            <input type="text" id="name" oninput="resetErrors()">
            <div class="error" id="name-error"></div>

            <label>Address:</label>
            <input type="text" id="address" oninput="resetErrors()">
            <div class="error" id="address-error"></div>

            <label>Email:</label>
            <input type="text" id="email" oninput="resetErrors()">
            <div class="error" id="email-error"></div>

            <label>Password:</label>
            <input type="password" id="password" oninput="resetErrors()">
            <div class="error" id="password-error"></div>

            <label>Subject / Course:</label>
            <select id="subject" onchange="resetErrors()">
                <option value="">-- Select --</option>
                <option value="Web Development">Web Development</option>
                <option value="JavaScript">JavaScript</option>
                <option value="Digital Marketing">Digital Marketing</option>
            </select>
            <div class="error" id="subject-error"></div>

            <label>
                <input type="checkbox" id="agree" onclick="resetErrors()">
                I agree to the above information
            </label>
            <div class="error" id="agree-error"></div>

            <button type="submit">Submit</button>

        </form>
    </div>

    <!-- ================= SCRIPT ================= -->

    <script>
        function validateForm() {
            const name = document.getElementById("name").value;
            const addr = document.getElementById("address").value;
            const email = document.getElementById("email").value;
            const pass = document.getElementById("password").value;
            const sub = document.getElementById("subject").value;
            const agree = document.getElementById("agree").checked;

            const nameErr = document.getElementById("name-error");
            const addrErr = document.getElementById("address-error");
            const emailErr = document.getElementById("email-error");
            const passErr = document.getElementById("password-error");
            const subErr = document.getElementById("subject-error");
            const agreeErr = document.getElementById("agree-error");

            // Clear errors
            nameErr.textContent = "";
            addrErr.textContent = "";
            emailErr.textContent = "";
            passErr.textContent = "";
            subErr.textContent = "";
            agreeErr.textContent = "";

            let isValid = true;

            if (name === "" || /\d/.test(name)) {
                nameErr.textContent = "Please enter your name properly.";
                isValid = false;
            }

            if (addr === "") {
                addrErr.textContent = "Please enter your address.";
                isValid = false;
            }

            if (email === "" || !email.includes("@") || !email.includes(".")) {
                emailErr.textContent = "Please enter a valid email address.";
                isValid = false;
            }

            if (pass === "" || pass.length < 6) {
                passErr.textContent = "Please enter a password with at least 6 characters.";
                isValid = false;
            }

            if (sub === "") {
                subErr.textContent = "Please select your course.";
                isValid = false;
            }

            if (!agree) {
                agreeErr.textContent = "Please agree to the above information.";
                isValid = false;
            }

            if (isValid) {
                alert("Form submitted successfully!");
                return true;
            }
            else {
                return false;
            }
        }

        function resetErrors() {
            document.getElementById("name-error").textContent = "";
            document.getElementById("address-error").textContent = "";
            document.getElementById("email-error").textContent = "";
            document.getElementById("password-error").textContent = "";
            document.getElementById("subject-error").textContent = "";
            document.getElementById("agree-error").textContent = "";
        }
    </script>

</body>
</html>
