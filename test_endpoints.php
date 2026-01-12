<?php

// Test script to verify all endpoints are responding correctly
// Usage: php test_endpoints.php

$baseUrl = 'http://localhost:8000';
$endpoints = [
    '/' => 302, // Redirect to login
    '/login' => 200,
    '/health-check' => 200, // We'll add this route
    '/api/webhooks/bounces/postal' => 405, // Method Not Allowed (GET)
];

echo "🧪 Starting Endpoint Tests...\n\n";

$hasErrors = false;

foreach ($endpoints as $path => $expectedStatus) {
    $url = $baseUrl . $path;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === $expectedStatus) {
        echo "✅ [{$httpCode}] {$path}\n";
    } else {
        echo "❌ [{$httpCode}] {$path} (Expected: {$expectedStatus})\n";
        $hasErrors = true;
    }
}

if ($hasErrors) {
    echo "\n⚠️  Some tests failed!\n";
    exit(1);
} else {
    echo "\n🎉 All endpoints passed!\n";
    exit(0);
}
