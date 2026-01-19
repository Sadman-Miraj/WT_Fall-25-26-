<?php
$error=[];
$sucess="";

if($_SERVER["REQUEST_METHOD"]=="POST"){
    if(empty($_POST["name"])){
        $error["name"]="Name is required";
    }
    elseif (!preg_match("/^[A-Za-z]{3,}$/",$_POST["name"])){
        $error["name"]="Name must be at least 3 characters and contain only letters and spaces";


    }

    if(empty($_POST["email"])){
        $error["email"]="Email is required";
    }
    elseif(!filter_var($_POST["email"],FILTER_VALIDATE_EMAIL)){
        $error["email"]="Invalid email format";
    }

    if(empty($_POST["phone"])){
        $error["phone"]="Phone number is required";
    }
    elseif(!preg_match("/^\d{3}-\d{3}-\d{4}$/",$_POST['phone'])){
        $error['phone']="Phone must be in xxx-xxx-xxxx format";
    }

    if(empty($_POST["subject"])){
        $error["subject"]="Please select a subject";
    }

    if(empty($_POST["message"])){
        $error["message"]="Message is required";
    }
    elseif(strlen($_POST['message'])<20){
        $error["message"]="Message must be at least 20 characters long";
    }

    if(empty($error)){
        $success="Form submitted successfully!";
    }
}
?>
<form action="" method="post">
    <table>
        <tr>
            <td><label for="name">Name:</label></td>
            <td><input type="text" name="name" >
        <?php echo $error["name"]??"";?></td>
        </tr>
        <tr>
            <td><label for="email">Email:</label></td>
            <td><input type="text" name="email" >
        <?php echo $error["email"]??"";?></td>
        </tr>
        <tr>
            <td><label for="phone">Phone:</label></td>
            <td><input type="text" name="phone" placeholder="123-456-7890">
        <?php echo $error["phone"]??"";?></td>
        </tr>
        <tr>
            <td>
                <label for="subject">Subject:</label>
                
            </td>
            <td>
                <select name="" id="">
                    <option value="">Select</option>
                    <option value="support">Support</option>
                    <option value="sales">Sales</option>
                </select>
                <?php echo $error ["subject"]??"";?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="message">Message:</label>
                
            </td>
            <td>
                <textarea name="message" id="" cols="30" rows="10"></textarea>
                <?php echo $error["message"]??"";?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="submit">Submit</button>
            </td>
    </table>
</form>