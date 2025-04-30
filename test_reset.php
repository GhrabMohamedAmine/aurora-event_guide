<?php
// Simple test script to validate if reset_password.php is working

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Simulate a POST request
$ch = curl_init('http://localhost/aminghrab/user/controller/reset_password.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'action' => 'send_reset_code',
    'email' => 'test@example.com'
]);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$error = curl_error($ch);
curl_close($ch);

// Output the results
echo "HTTP Code: $httpCode\n";
echo "Content-Type: $contentType\n";
if ($error) {
    echo "cURL Error: $error\n";
}
echo "Response:\n$response\n";

// Try to decode the JSON response
$decodedResponse = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
}
else {
    echo "Decoded Response: ";
    print_r($decodedResponse);
}
?> 