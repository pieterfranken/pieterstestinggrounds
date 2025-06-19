<?php
/**
 * Simple Groq API Test
 */

// Load environment
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$apiKey = $_ENV['GROQ_API_KEY'] ?? '';

echo "🧪 Simple Groq API Test\n";
echo "======================\n\n";

if (empty($apiKey)) {
    echo "❌ No Groq API key found in .env\n";
    exit(1);
}

echo "✅ API Key found: " . substr($apiKey, 0, 10) . "...\n\n";

// Test 1: Check if we can reach Groq
echo "1️⃣ Testing basic connectivity...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.groq.com/openai/v1/models');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For Windows compatibility

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
    exit(1);
}

if ($httpCode === 200) {
    echo "✅ Groq API is reachable! (HTTP $httpCode)\n";
    $models = json_decode($response, true);
    echo "📋 Available models: " . count($models['data']) . "\n\n";
} else {
    echo "❌ Groq API error (HTTP $httpCode)\n";
    echo "Response: $response\n";
    exit(1);
}

// Test 2: Generate a simple question
echo "2️⃣ Testing question generation...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.groq.com/openai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$data = [
    'model' => 'llama3-70b-8192',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You are a quiz creator. Generate exactly one multiple choice question about JavaScript programming.'
        ],
        [
            'role' => 'user',
            'content' => 'Create 1 JavaScript question with 4 options (A, B, C, D) and indicate the correct answer.'
        ]
    ],
    'temperature' => 0.7,
    'max_tokens' => 500
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
    exit(1);
}

if ($httpCode === 200) {
    $result = json_decode($response, true);
    if (isset($result['choices'][0]['message']['content'])) {
        $content = $result['choices'][0]['message']['content'];
        echo "✅ Question generated successfully!\n";
        echo "📝 Generated content:\n";
        echo "---\n";
        echo $content . "\n";
        echo "---\n\n";
        
        echo "🎉 Groq API is working perfectly!\n";
        echo "💡 The issue is likely in the October CMS integration.\n";
    } else {
        echo "❌ Unexpected response format\n";
        echo "Response: $response\n";
    }
} else {
    echo "❌ Question generation failed (HTTP $httpCode)\n";
    echo "Response: $response\n";
}

echo "\n📋 Summary:\n";
echo "- API Key: Valid\n";
echo "- Connectivity: Working\n";
echo "- Question Generation: " . ($httpCode === 200 ? "Working" : "Failed") . "\n";
echo "\n💡 Next step: Check October CMS HTTP client configuration\n";
?>
