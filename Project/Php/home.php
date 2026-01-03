<?php
include "../db/db.php";

$name = $address = $date = $type = "";
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $date = $_POST["date"] ?? "";
    $type = $_POST["type"] ?? "";
    
    if (empty($name)) {
        $message = "Name is required.";
        $messageType = "error";
    } elseif (empty($address)) {
        $message = "Service address is required.";
        $messageType = "error";
    } elseif (empty($date)) {
        $message = "Preferred date is required.";
        $messageType = "error";
    } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
        $message = "Cannot select a past date.";
        $messageType = "error";
    } elseif (empty($type) || !in_array($type, ['oil', 'tire', 'brake'])) {
        $message = "Please select a valid service type.";
        $messageType = "error";
    } else {
        $sql = "INSERT INTO home (name, address, date, type) VALUES ('$name', '$address', '$date', '$type')";
        
        if($conn->query($sql)) {
            $message = "Thank you, $name! Your $type service is booked for $date at your address.";
            $messageType = "success";
            $name = $address = $date = $type = "";
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
    <title>Automobiles Solution - Home Service</title>
    <link rel="stylesheet" href="../css/home.css">
</head>
<body>
    <h2 class="servh">Home Service Details</h2>
    
    <?php if (!empty($message)): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="service-form">
        <form method="post" action="">
            <fieldset>
                <legend>Home Service Booking</legend>
                
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" 
                       value="<?php echo htmlspecialchars($name); ?>" required>
                
                <label for="address">Service Address:</label>
                <input type="text" id="address" name="address" 
                       value="<?php echo htmlspecialchars($address); ?>" required>
                
                <label for="date">Preferred Date:</label>
                <input type="date" id="date" name="date" 
                       value="<?php echo $date; ?>" 
                       min="<?php echo date('Y-m-d'); ?>" required>
                
                <label for="type">Service Type:</label>
                <select id="type" name="type" required>
                    <option value="" disabled <?php echo ($type == '') ? 'selected' : ''; ?>>Select service type</option>
                    <option value="oil" <?php echo ($type == 'oil') ? 'selected' : ''; ?>>Oil Change</option>
                    <option value="tire" <?php echo ($type == 'tire') ? 'selected' : ''; ?>>Tire Rotation</option>
                    <option value="brake" <?php echo ($type == 'brake') ? 'selected' : ''; ?>>Brake Inspection</option>
                </select>
                
                <input type="submit" value="Book Now" class="submit-btn">
            </fieldset>
        </form>
    </div>
    
    <script>
        document.getElementById('date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
<?php
$conn->close();
?>