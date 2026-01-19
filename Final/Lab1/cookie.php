<?php
setcookie("user","miraj",time()+86400,"/");
print_r($_COOKIE);

setcookie("fruit","apple",time()+3600);
echo "<br>";
if(isset($_COOKIE["fruit"])){

    echo "The cookie is ". $_COOKIE["fruit"];
}
else{
    echo "The cookie is not set";
}
?>