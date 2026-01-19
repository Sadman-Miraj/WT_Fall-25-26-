<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$errors = [];

// NAME
if (empty($data["name"]) || !preg_match("/^[A-Za-z ]{3,}$/", $data["name"])) {
    $errors["name"] = "Name must be at least 3 letters";
}

// EMAIL
if (empty($data["email"]) || !filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
    $errors["email"] = "Invalid email";
}

// PHONE
if (empty($data["phone"]) || !preg_match("/^\d{3}-\d{3}-\d{4}$/", $data["phone"])) {
    $errors["phone"] = "Phone must be xxx-xxx-xxxx";
}

// SUBJECT
if (empty($data["subject"])) {
    $errors["subject"] = "Subject is required";
}

// MESSAGE
if (empty($data["message"]) || strlen($data["message"]) < 20) {
    $errors["message"] = "Message must be at least 20 characters";
}

// If errors exist
if (!empty($errors)) {
    echo json_encode([
        "success" => false,
        "errors" => $errors
    ]);
    exit;
}

// Simulated DB insert
$reference = "REF-" . rand(10000, 99999);

// Simulated auto-reply email
$autoReply = "Thank you {$data['name']}, we received your message.";

echo json_encode([
    "success" => true,
    "reference" => $reference
]);
