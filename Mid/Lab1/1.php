<!DOCTYPE html>
<html>
<head>
    <title>My Web Page</title>

    <style>
        #head
        {
            color: blue;
            text-align: center;
            font-size: 22px;
        }

    .fn
    {
        font-family: Arial, sans-serif;
        font-size: 16px;
        right: 200px;
    }
    .fn1
    {
        width: 300px;
        height: 25px;
    }
    #reg
    {
        width: 310px;
        height: 30px;
        background-color: blue;
        color: white;
        font-size: 14px;
        
        margin-top: 20px;
    }
    #form
    {
        
        width: 400px;
        height: auto;
        margin: auto;
        padding: 20px;
        margin-top: 50px;
        background-color: beige;
    }
    </style>
</head>
<body>
<div id="form">
    <h1 id="head">Clinic Patient Registration </h1>
    <center>
    <table>
        <tr>
            <td><p class="fn">Full Name:</p>
            <input class="fn1" type="text" name="full_name"></td>
        </tr>
        <tr>
            <td><p class="fn">Age:</p><input class="fn1" type="number" name="age"></td>
        </tr>
        <tr>
            <td><p class="fn">Phone Number:</p><input class="fn1" type="number" name="phone_number"></td>
            
            </td>
        </tr>

        <tr>
            <td><p class="fn">Email Address:</p><input class="fn1" type="email" name="email"></td>
            
            </td>
        </tr>
        <tr>
            <td><p class="fn">Insurance Provider:</p>
            <select class="fn1" name="" id="">
                <option >Select Provider</option>
                <option >A</option>
                <option >B</option>
                <option >C</option>
                <option >D </option>
            </select>
            </td>
        </tr>

<tr>
            <td><p class="fn">Insurance Policy Number:</p><input class="fn1" type="number" name="insurance_policy_number"></td>
        </tr>

            <!-- additional info-->
             <tr><td colspan="2"><h1 id="head">Additional Information</h1></td></tr>
             
             <tr>
            <td><p class="fn">Username:</p>
            <input class="fn1" type="text" name="username"></td>
        </tr>

        <tr>
            <td><p class="fn">Password:</p>
            <input class="fn1" type="password" name="password"></td>
        </tr>

        <tr>
            <td><p class="fn">Confirm Password:</p>
            <input class="fn1" type="password" name="confirm_password"></td>
        </tr>
    </table>
    <input id="reg" type="submit" value="Register">
    </center>
    </div>
</body>
</html>