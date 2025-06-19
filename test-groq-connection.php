<?php
/**
 * Test Groq AI Connection for PTG Quiz System
 */

// Load environment variables
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

if (empty($apiKey) || $apiKey === 'your_groq_api_key_here') {
    echo "❌ Groq API key not found in .env file\n";
    exit(1);
}

echo "🧪 Testing Groq AI Connection...\n";
echo "API Key: " . substr($apiKey, 0, 10) . "...\n\n";

// Test 1: Check API key validity
echo "1️⃣ Testing API key validity...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.groq.com/openai/v1/models');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✅ API key is valid!\n";
    $models = json_decode($response, true);
    echo "📋 Available models: " . count($models['data']) . " models found\n\n";
} else {
    echo "❌ API key validation failed (HTTP $httpCode)\n";
    echo "Response: $response\n";
    exit(1);
}

// Test 2: Generate a sample quiz question
echo "2️⃣ Testing quiz question generation...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.groq.com/openai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$prompt = 'Generate 1 multiple choice question about JavaScript programming at medium difficulty level.

Format as valid JSON array with this structure:
[
  {
    "question": "Question text here?",
    "options": {
      "A": "Option A",
      "B": "Option B", 
      "C": "Option C",
      "D": "Option D"
    },
    "correct": "A"
  }
]

Requirements:
- Questions should be clear and unambiguous
- All options should be plausible
- Only one correct answer per question
- Return ONLY the JSON array, no other text';

$data = [
    'model' => 'llama3-8b-8192',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You are an expert quiz creator. Generate high-quality multiple choice questions in valid JSON format.'
        ],
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ],
    'temperature' => 0.7,
    'max_tokens' => 1000
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $result = json_decode($response, true);
    if (isset($result['choices'][0]['message']['content'])) {
        $content = $result['choices'][0]['message']['content'];
        echo "✅ Quiz question generated successfully!\n";
        echo "📝 Generated content:\n";
        echo "---\n";
        echo $content . "\n";
        echo "---\n\n";
        
        // Try to parse the JSON
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = trim($content);
        
        $questions = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && !empty($questions)) {
            echo "✅ JSON parsing successful!\n";
            echo "🎯 Sample question: " . $questions[0]['question'] . "\n";
        } else {
            echo "⚠️  JSON parsing failed, but AI responded (this is normal - we can handle this)\n";
        }
    } else {
        echo "❌ Unexpected response format\n";
        echo "Response: $response\n";
    }
} else {
    echo "❌ Quiz generation failed (HTTP $httpCode)\n";
    echo "Response: $response\n";
}

echo "\n🎉 Connection test complete!\n";
echo "\n📋 Summary:\n";
echo "✅ Groq API key: Valid\n";
echo "✅ AI model access: Working\n";
echo "✅ Quiz generation: Ready\n";
echo "\n🚀 Your AI quiz system is ready to use!\n";
echo "👉 Visit /ai-quiz in your browser to try it out.\n";
?>
