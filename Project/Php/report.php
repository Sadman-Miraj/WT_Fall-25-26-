```php
<?php
$name = $service_date = $service_type = $feedback = "";
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"] ?? "");
    $service_date = $_POST["service_date"] ?? "";
    $service_type = $_POST["service_type"] ?? "";
    $feedback = trim($_POST["feedback"] ?? "");
    
    if (empty($name)) {
        $message = "Name is required.";
        $messageType = "error";
    } elseif (empty($service_date)) {
        $message = "Service date is required.";
        $messageType = "error";
    } elseif (strtotime($service_date) > strtotime(date('Y-m-d'))) {
        $message = "Service date cannot be in the future.";
        $messageType = "error";
    } elseif (empty($service_type) || !in_array($service_type, ['regular', 'home', 'emergency'])) {
        $message = "Please select a valid service type.";
        $messageType = "error";
    } elseif (empty($feedback)) {
        $message = "Feedback is required.";
        $messageType = "error";
    } else {
        $message = "Report submitted successfully!";
        $messageType = "success";
        $name = $service_date = $service_type = $feedback = "";
    }
}
?>
```
<!DOCTYPE html>
<html>
<head>
    <title>Automobiles Solution</title>
    <link rel="stylesheet" href="../css/report.css">
</head>
<body>
    <h2 class="servh">Service Report</h2>
    <div class="report-form" id="report">
        <form method="post" action="process_report.php">
            <fieldset>
                <legend>Service Report Submission</legend>
                
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required><br><br>
                
                <label for="service_date">Service Date:</label>
                <input type="date" id="service_date" name="service_date" required><br><br>
                
                <label for="service_type">Service Type:</label>
                <select id="service_type" name="service_type" required>
                    <option value="">Select a service</option>
                    <option value="regular">Regular</option>
                    <option value="home">Home</option>
                    <option value="emergency">Emergency</option>
                </select><br><br>
                
                <label for="feedback">Feedback:</label><br>
                <textarea id="feedback" name="feedback" rows="4" cols="50" required></textarea><br><br>
                
                <input type="submit" value="Submit Report" class="submit-btn">
            </fieldset>
        </form>
    </div>