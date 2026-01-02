<!DOCTYPE html>
<html>
<head>
    <title>Automobiles Solution</title>
    <link rel="stylesheet" href="../css/book.css">
</head>
<body>
    <!----------------------------------services---------------------------------->
<h2 class="servh">Our Services</h2>
<div id="ser">
    <div class="service-item">
 <button id="regular">Regular</button>


    </div>
    <div class="service-item">
  <button id="home">Home</button>

    </div>
    <div class="service-item">
    <button id="emergency">Emergency</button>
    </div>
</div>

<!----------------------------------template for regular---------------------------------->
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
        <input type="text" id="coupon" name="coupon"><br><br>
        <button id="coupon Now">Apply</button>

        <input type="submit" value="Book Now">
        </legend>
    </form>
</div>

<!----------------------------------template for home---------------------------------->
<div id="homeService">

    <form action="index.php" method="post">
        <legend>Home Service Booking
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br><br>
        <label for="address">Service Address:</label>
        <input type="text" id="address" name="address" required><br><br>
        <label for="date">Preferred Date:</label>
        <input type="date" id="date" name="date" required><br><br>
        
        
        <label for="type">Service Type:</label>
        <select id="type" name="type">
            <option value="oil">Oil Change</option>
            <option value="tire">Tire Rotation</option>
            <option value="brake">Brake Inspection</option>
        </select>

        <label for="coupon">Coupon Code:</label>
        <input type="text" id="coupon" name="coupon"><br><br>
        <button id="coupon Now">Apply</button>

        <input type="submit" value="Book Now">
        </legend>
    </form>
</div>
<!----------------------------------template for emergency---------------------------------->

<footer>
</footer>
</body>
</html>