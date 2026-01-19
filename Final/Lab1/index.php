<form id="contactForm">
    Name: <input type="text" name="name"><span id="err_name"></span><br><br>

    Email: <input type="text" name="email"><span id="email_status"></span><br><br>

    Phone: <input type="text" name="phone" placeholder="123-456-7890">
    <span id="err_phone"></span><br><br>

    Subject:
    <select name="subject">
        <option value="">Select</option>
        <option value="support">Support</option>
        <option value="sales">Sales</option>
    </select>
    <span id="err_subject"></span><br><br>

    Message:<br>
    <textarea name="message"></textarea>
    <span id="err_message"></span><br><br>

    <button type="submit">Submit</button>
</form>

<p id="status"></p>

<script>
// EMAIL VALIDATION ON BLUR
document.querySelector("[name=email]").addEventListener("blur", function () {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "validate_email.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function () {
        let res = JSON.parse(this.responseText);
        document.getElementById("email_status").innerText = res.message;
    };

    xhr.send("email=" + encodeURIComponent(this.value));
});

// FORM SUBMIT
document.getElementById("contactForm").addEventListener("submit", function (e) {
    e.preventDefault();

    document.getElementById("status").innerText = "Submitting...";

    let formData = new FormData(this);
    let data = {};
    formData.forEach((v, k) => data[k] = v);

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "submit_form.php", true);
    xhr.timeout = 5000;
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onload = function () {
        let res = JSON.parse(this.responseText);

        if (res.success) {
            document.getElementById("status").innerText =
                "Success! Reference Number: " + res.reference;
            document.getElementById("contactForm").reset();
        } else {
            document.getElementById("status").innerText = "Validation errors";

            document.getElementById("err_name").innerText = res.errors.name ?? "";
            document.getElementById("err_phone").innerText = res.errors.phone ?? "";
            document.getElementById("err_subject").innerText = res.errors.subject ?? "";
            document.getElementById("err_message").innerText = res.errors.message ?? "";
        }
    };

    xhr.ontimeout = function () {
        document.getElementById("status").innerText = "Request timed out (5s)";
    };

    xhr.send(JSON.stringify(data));
});
</script>
