<?php
include "../db/db.php";

$name = $location = $issue = $type = "";
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $issue = trim($_POST["issue"] ?? "");
    $type = $_POST["type"] ?? "";
    
    if (empty($name)) {
        $message = "Name is required.";
        $messageType = "error";
    } elseif (empty($location)) {
        $message = "Current location is required.";
        $messageType = "error";
    } elseif (empty($issue)) {
        $message = "Please describe the issue.";
        $messageType = "error";
    } elseif (empty($type) || !in_array($type, ['towing', 'battery', 'flat'])) {
        $message = "Please select a valid service type.";
        $messageType = "error";
    } else {
        $sql = "INSERT INTO emergency (name, location, issue, type) VALUES ('$name', '$location', '$issue', '$type')";
        
        if($conn->query($sql)) {
            $message = "Emergency service request received! Our team will contact you shortly for your $type service.";
            $messageType = "success";
            $name = $location = $issue = $type = "";
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
    <title>Automobiles Solution - Emergency Service</title>
    <link rel="stylesheet" href="../css/emergency.css">

</head>
<body>
    <h2 class="servh">Emergency Service Details</h2>
    
    <?php if (!empty($message)): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="service-form" id="emerg">
        <form method="post" action="">
            <fieldset>
                <legend>Emergency Service Booking</legend>
                
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" 
                       value="<?php echo htmlspecialchars($name); ?>" required>
                
                <label for="location">Current Location:</label>
                <input type="text" id="location" name="location" 
                       value="<?php echo htmlspecialchars($location); ?>" required>
                
                <label for="issue">Describe the Issue:</label>
                <textarea id="issue" name="issue" required><?php echo htmlspecialchars($issue); ?></textarea>
                
                <label for="type">Service Type:</label>
                <select id="type" name="type" required>
                    <option value="" disabled <?php echo ($type == '') ? 'selected' : ''; ?>>Select emergency type</option>
                    <option value="towing" <?php echo ($type == 'towing') ? 'selected' : ''; ?>>Towing Service</option>
                    <option value="battery" <?php echo ($type == 'battery') ? 'selected' : ''; ?>>Battery Jumpstart</option>
                    <option value="flat" <?php echo ($type == 'flat') ? 'selected' : ''; ?>>Flat Tire Change</option>
                </select>
                
                <input type="submit" value="Request Emergency Service" class="submit-btn emergency-btn">
            </fieldset>
        </form>
    </div>
</body>
</html>
<?php
$conn->close();
?>