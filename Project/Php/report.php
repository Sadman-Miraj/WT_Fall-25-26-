<?php
include "../db/db.php";

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
        $sql = "INSERT INTO report (name, date, type, feedback) VALUES ('$name', '$service_date', '$service_type', '$feedback')";
        
        if($conn->query($sql)) {
            $message = "Report submitted successfully!";
            $messageType = "success";
            $name = $service_date = $service_type = $feedback = "";
        } else {
            $message = "Database error: " . $conn->error;
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Automobiles Solution</title>
    <link rel="stylesheet" href="../css/report.css">
</head>
<body>
    <h2 class="servh">Service Report</h2>
    
    <?php if (!empty($message)): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="report-form" id="report">
        <form method="post" action="">
            <fieldset>
                <legend>Service Report Submission</legend>
                
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                
                <label for="service_date">Service Date:</label>
                <input type="date" id="service_date" name="service_date" value="<?php echo $service_date; ?>" max="<?php echo date('Y-m-d'); ?>" required>
                
                <label for="service_type">Service Type:</label>
                <select id="service_type" name="service_type" required>
                    <option value="" disabled <?php echo ($service_type == '') ? 'selected' : ''; ?>>Select a service</option>
                    <option value="regular" <?php echo ($service_type == 'regular') ? 'selected' : ''; ?>>Regular</option>
                    <option value="home" <?php echo ($service_type == 'home') ? 'selected' : ''; ?>>Home</option>
                    <option value="emergency" <?php echo ($service_type == 'emergency') ? 'selected' : ''; ?>>Emergency</option>
                </select>
                
                <label for="feedback">Feedback:</label>
                <textarea id="feedback" name="feedback" rows="4" required><?php echo htmlspecialchars($feedback); ?></textarea>
                
                <input type="submit" value="Submit Report" class="submit-btn">
            </fieldset>
        </form>
    </div>
    
    <script>
        document.getElementById('service_date').max = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
<?php
$conn->close();
?>
