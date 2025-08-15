<?php

$API_KEY = 'O5mTtC6sSxzsXNWf3x0mXFtbOUcvSZQm';
$url = "https://api.sms-man.com/control/countries?token=${API_KEY}";

// Initialize cURL session
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Decode JSON response to an associative array
$data = json_decode($response, true);

// Check if API response is valid
if (!is_array($data)) {
    die(json_encode(["success" => false, "error_msg" => "Invalid API response"]));
}

// Return the countries as a JSON response
header('Content-Type: application/json');
echo json_encode($data);
?>