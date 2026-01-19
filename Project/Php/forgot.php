<?php
session_start();
include "../db/db.php";

$email = "";
$step = 1; // 1 = email verification, 2 = password reset
$message = "";
$messageType = "";
$token = "";