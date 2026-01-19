<?php
session_start();

// Set cookie if not already set
if (!isset($_COOKIE['visitor']) && !isset($_SESSION['user_id'])) {
    setcookie('visitor', 'true', time() + (86400 * 30), "/"); // 30 days
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userInitial = $isLoggedIn ? strtoupper(substr($userName, 0, 1)) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automobiles Solution</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
<div id="top">
    <div class="top-left">
        <a id="logo-link" href="index.php">AS</a>
    </div>

    <div class="top-center">
        Automobiles Solution
    </div>

    <div class="top-right">
        <p>
            <?php if ($isLoggedIn): ?>
                <div class="dropdown">
                    <div class="user-profile">
                        <div class="user-icon"><?php echo $userInitial; ?></div>
                        <a href="profile.php" class="user-name"><?php echo htmlspecialchars($userName); ?></a>
                    </div>
                    <div class="dropdown-content">
                        <a href="profile.php">Profile</a>
                        <hr>
                        <a href="logout.php" class="logout-link">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php">Login</a> | 
                <a href="signup.php">Sign Up</a>
            <?php endif; ?>
        </p>
    </div>
</div>

<!---------------------------------center----------------------------------->
<div id="center">
    <h1 class="welcome">Welcome to Automobiles Solution</h1>
    <?php if ($isLoggedIn): ?>
        <div class="welcome-message">
            Welcome back, <strong><?php echo htmlspecialchars($userName); ?></strong>!
        </div>
    <?php endif; ?>
    <p class="info">Lorem ipsum dolor sit amet, consectetur adipisicing elit. 
        Illum explicabo dolores, qui eveniet alias magni fugit. Porro, quaerat. 
        Ut laborum, consectetur sequi explicabo repellat dicta earum sed praesentium 
        nobis commodi architecto incidunt magnam, vel assumenda cum harum dolorum 
        consequatur quasi eos amet quis rerum? Ratione quibusdam quo dolorem commodi 
        rem repellendus nostrum inventore, ab voluptatibus dolore vel nemo deserunt 
        voluptate. Obcaecati reiciendis non hic blanditiis, eius exercitationem? 
        Voluptatum error nihil repellat culpa eaque. Illo, omnis quisquam dignissimos 
        libero deserunt odit rerum nam cum expedita, dolores et nemo nostrum asperiores 
        explicabo voluptate fuga debitis? Omnis esse nobis laudantium perferendis voluptas
        
        
        odit totam vel facilis id. Asperiores mollitia iusto, delectus, fugit reprehenderit 
        possimus omnis praesentium blanditiis, sequi ut eveniet excepturi
         dicta veritatis consequatur deserunt illum hic. Dolore mollitia alias unde 
         perspiciatis explicabo temporibus perferendis enim aut repudiandae commodi 
         
         
         Aperiam, adipisci deleniti corrupti id, iure earum nobis labore facilis 
         asperiores odit voluptatum repudiandae laboriosam sunt totam eaque doloremque? 
         Alias reprehenderit tempore suscipit eum sunt eos corporis omnis.</p>
</div>

<!----------------------------------services---------------------------------->
<h2 class="servh">Our Services</h2>
<div id="ser">
    <div class="service-item">
        <h3 class="sert" ><a  href="regular.php"  style="text-decoration:none; color:black;">Regular</a> </h3>
        <p class="serp">
Routine vehicle maintenance and checkups to keep your car running smoothly. 
Ideal for scheduled servicing, inspections, and minor repairs.
        </p>
    </div>
    <div class="service-item">
        <h3 class="sert"><a  href="emergency.php"  style="text-decoration:none; color:black;">Emergency</a></h3>
        <p class="serp">
24/7 roadside assistance for breakdowns, accidents, or urgent repairs.
Fast response times to get you back on the road quickly and safely.
        </p>
    </div>
    <div class="service-item">
        <h3 class="sert"><a  href="home.php"  style="text-decoration:none; color:black;">Home</a></h3>
        <p class="serp">
Convenient vehicle servicing at your home or workplace.
Professional mechanics come to you for oil changes, tire rotations, and more.

        </p>
    </div>
</div>

<h1 id="book">
    <a href="book.php" style="color: white; text-decoration: none;">Book Now</a>
</h1>
<!----------------------------------INVENTORY---------------------------------->

<h2 class="invh">Our Inventory</h2>
<div id="inv">
    <div class="light">
        <img src="../image/index/light.jpeg" alt="Sedan">
        <h3 class="invt">LIGHT</h3>
</div>
    <div class="light">
        <img src="../image/index/battery.jpeg" alt="SUV">
        <h3 class="invt">BATTERY</h3>
    </div>
    <div class="light">
        <img src="../image/index/shock.jpg" alt="Truck">
        <h3 class="invt">SHOCK ABSORBER</h3>
    </div>
    <div class="light">
        <img src="../image/index/wheel.jpeg" alt="Coupe">
        <h3 class="invt">WHEEL</h3>
    </div>
</div>
<h1 id="buy">
    <a href="customer_inventory.php" style="color: white; text-decoration: none;">BUY Now</a>
</h1>
        

<!----------------------------------footer---------------------------------->
<footer>
<div id="finfo">

    <div class="fp">
        <h3 class="abou">About us</h3>
        <p class="ap">
            Automobiles Solution is your trusted partner for all your vehicle maintenance 
            and repair needs. With a team of experienced mechanics and a commitment to 
            customer satisfaction, we provide top-notch services to keep your car in 
            optimal condition.

        </p>
    </div>
    <div class="fp">
        <h3 class="contact">Contact Us</h3>
        <p class="cp">
            Phone: +123-456-7890<br>
            Email: info@automobilessolution.com<br>
            Address: 123 Auto Street, Car City, Country

        </p>
    </div>
    <div class="fp">
        <h3 class="report">Report</h3>
        <p class="rp">
            For any issues or feedback regarding our services, please reach out to us 
            at:
            <a href="report.php">Report</a>
        </p>
        <div id="social">
            <a href="facebook.com"><img id="fb" src="../image/index/Fb.png" alt="Fb"></a>
            <a href="twitter.com"><img id="tw" src="../image/index/Tw.png" alt="Tw"></a>
            <a href="instagram.com"><img id="ig" src="../image/index/Ig.png" alt="Ig"></a>
        </div>
    </div>

</div>
</footer>

<script src="../js/index.js"></script>
</body>
</html>