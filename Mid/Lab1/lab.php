<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Festival Registration</title>
 
</head>
<center>
<body>

    <div class="container">
        <h2>Participant Registration</h2>
        <form onsubmit="return validateForm();">
            <table>
                <tr>
                    <td><label for="name">Name:</label></td>
                    <td>
                        <input type="text" id="name" oninput="resetErrors()">
                        <div class="error" id="name-error"></div>
                    </td>
                </tr>
                <tr>
                    <td><label for="email">Email:</label></td>
                    <td>
                        <input type="text" id="email" oninput="resetErrors()">
                        <div class="error" id="email-error"></div>
                    </td>
                </tr>
                <tr>
                    <td><label for="phone">Phone Number:</label></td>
                    <td>
                        <input type="text" id="phone" oninput="resetErrors()">
                        <div class="error" id="phone-error"></div>
                    </td>
                </tr>
                <tr>
                    <td><label for="password">Password:</label></td>
                    <td>
                        <input type="password" id="password" oninput="resetErrors()">
                        <div class="error" id="password-error"></div>
                    </td>
                </tr>
                <tr>
                    <td><label for="cpass">Confirm Password:</label></td>
                    <td>
                        <input type="password" id="cpass" oninput="resetErrors()">
                        <div class="error" id="cpass-error"></div>
                    </td>
                </tr>
            </table>
            <button type="submit">Register</button>
        </form>
        
        <!-- Success message container -->
        <div id="success-message"></div>
        
        <!-- Activity Selection Section -->
        <div class="activity-section">
            <h2>Activity Selection</h2>
            <div class="activity-input-container">
                <input type="text" id="activity-name" placeholder="Enter activity name">
                <button id="add-activity">Add Activity</button>
            </div>
            <ul class="activity-list" id="activity-list">
                <!-- Activities will be added here dynamically -->
            </ul>
        </div>
    </div>

    <script>
        // Form validation function
        function validateForm() {
            const name = document.getElementById("name").value;
            const email = document.getElementById("email").value;
            const phone = document.getElementById("phone").value;
            const pass = document.getElementById("password").value;
            const cpass = document.getElementById("cpass").value;

            const nEr = document.getElementById("name-error");
            const eEr = document.getElementById("email-error");
            const phEr = document.getElementById("phone-error");
            const pEr = document.getElementById("password-error");
            const cEr = document.getElementById("cpass-error");

            // Clear previous errors
            nEr.textContent = "";
            eEr.textContent = "";
            phEr.textContent = "";
            pEr.textContent = "";
            cEr.textContent = "";

            let valid = true;

            // -------- NAME VALIDATION --------
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

            // -------- EMAIL VALIDATION --------
            if (email === "") {
                eEr.textContent = "Email cannot be empty.";
                valid = false;
            } else if (!email.includes("@")) {
                eEr.textContent = "Email must contain @ symbol.";
                valid = false;
            } else if (!/^\S+@\S+\.\S+$/.test(email)) {
                eEr.textContent = "Please enter a valid email address.";
                valid = false;
            }

            // -------- PHONE VALIDATION --------
            if (phone === "") {
                phEr.textContent = "Phone number cannot be empty.";
                valid = false;
            } else if (!/^\d+$/.test(phone)) {
                phEr.textContent = "Phone number must contain only digits.";
                valid = false;
            } else if (phone.length < 10) {
                phEr.textContent = "Phone number must be at least 10 digits.";
                valid = false;
            }

            // -------- PASSWORD VALIDATION --------
            if (pass === "") {
                pEr.textContent = "Password cannot be empty.";
                valid = false;
            } else if (pass.length < 6) {
                pEr.textContent = "Password must be at least 6 characters long.";
                valid = false;
            }

            if (cpass === "") {
                cEr.textContent = "Confirm your password.";
                valid = false;
            } else if (pass !== cpass) {
                cEr.textContent = "Passwords do not match.";
                valid = false;
            }

            // -------- FINAL SUBMISSION --------
            if (!valid) return false;

            // Show success message
            const box = document.getElementById("success-message");
            box.style.display = "block";
            box.innerHTML = `
                <strong>Registration Successful!</strong><br><br>
                <b>Name:</b> ${name}<br>
                <b>Email:</b> ${email}<br>
                <b>Phone:</b> ${phone}
            `;

            // Reset form
            document.querySelector("form").reset();

            return false; // Prevent page reload
        }

        // Clear errors on typing
        function resetErrors() {
            document.getElementById("name-error").textContent = "";
            document.getElementById("email-error").textContent = "";
            document.getElementById("phone-error").textContent = "";
            document.getElementById("password-error").textContent = "";
            document.getElementById("cpass-error").textContent = "";
        }

        // Activity Selection Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const addActivityBtn = document.getElementById('add-activity');
            const activityInput = document.getElementById('activity-name');
            const activityList = document.getElementById('activity-list');
            
            // Add activity when button is clicked
            addActivityBtn.addEventListener('click', function() {
                const activityName = activityInput.value.trim();
                
                if (activityName === '') {
                    alert('Please enter an activity name');
                    return;
                }
                
                // Create new activity item
                const activityItem = document.createElement('li');
                activityItem.className = 'activity-item';
                
                // Create activity text
                const activityText = document.createElement('span');
                activityText.textContent = activityName;
                
                // Create remove button
                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-btn';
                removeBtn.textContent = 'Remove';
                
                // Add event listener to remove button
                removeBtn.addEventListener('click', function() {
                    activityList.removeChild(activityItem);
                });
                
                // Append elements to activity item
                activityItem.appendChild(activityText);
                activityItem.appendChild(removeBtn);
                
                // Add activity to list
                activityList.appendChild(activityItem);
                
                // Clear input
                activityInput.value = '';
                activityInput.focus();
            });
            
            // Allow adding activity by pressing Enter key
            activityInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    addActivityBtn.click();
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</center>
</html>