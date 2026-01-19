<?php
header("Content-Type: application/json");

$email = $_POST["email"] ?? "";
$response = [];

if (empty($email)) {
    $response = ["valid" => false, "message" => "Email is required"];
}
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response = ["valid" => false, "message" => "Invalid email format"];
}
else {
    // Simulated existing emails
    $existingEmails = ["test@example.com", "admin@example.com"];

    if (in_array($email, $existingEmails)) {
        $response = ["valid" => false, "message" => "Email already exists"];
    } else {
        $response = ["valid" => true, "message" => "Email is available"];
    }
}

echo json_encode($response);
