<!DOCTYPE html>
<html>
<head>
    <title>Automobiles Solution</title>
    <link rel="stylesheet" href="../css/regular.css">
</head>
<body>
    <!----------------------------------regular service details---------------------------------->  
<h2 class="servh">Regular Service Details</h2>
<div id="reg">
    <form action="index.php" method="post">
        <legend>Regular Service Booking
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br><br>
        <label for="date">Preferred Date:</label>
        <input type="date" id="date" name="date" required><br><br>
        
        
        <label for="type">Service Type:</label>
        <select id="type" name="type">
            <option value="oil">Oil Change</option>
            <option value="tire">Tire Rotation</option>
            <option value="brake">Brake Inspection</option>
        </select>

        <label for="coupon">Coupon Code:</label>
        <input type="text" id="coupon" name="coupon">
        <button id="coupon">Apply</button>

        <input  type="submit" value="Book Now">
        </legend>
    </form>
</div>
</body>
</html>