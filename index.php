<!DOCTYPE html>
<html>  
<head>
    <title>Lab 1 - Dark/Light Mode Toggle</title>
</head>
<body>
    <h2>Welcome to Lab 1</h2>
    <p>Click the button below to toggle between Dark Mode and Light Mode.</p>
    <a href="onclick.php"><button>Go to Dark/Light Mode Toggle</button></a>
    <form action="/submit" method="post">
  <label>Name:</label>
  <input type="text" name="username">
  <input type="submit" value="Submit">
</form>
<fieldset>
  <legend>Personal Info</legend>
  <label>Name:</label>
  <input type="text">
</fieldset>
<form action="/action_page.php">
  <fieldset>
    <legend>Personalia:</legend>
    <label for="fname">First name:</label>
    <input type="text" id="fname" name="fname"><br><br>
    <label for="lname">Last name:</label>
    <input type="text" id="lname" name="lname"><br><br>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email"><br><br>
    <label for="birthday">Birthday:</label>
    <input type="date" id="birthday" name="birthday"><br><br>
    <input type="submit" value="Submit">
  </fieldset>
</form>
</body>
</html>