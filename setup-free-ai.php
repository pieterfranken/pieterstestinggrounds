<?php
/**
 * Free AI Setup Script for PTG Quiz System
 * Run this script to automatically configure free AI integration
 */

echo "🆓 PTG Free AI Setup Script\n";
echo "============================\n\n";

// Check if .env file exists
$envFile = '.env';
if (!file_exists($envFile)) {
    echo "❌ .env file not found. Please copy .env.example to .env first.\n";
    exit(1);
}

echo "🔍 Checking current AI configuration...\n";

// Read current .env
$envContent = file_get_contents($envFile);
$hasAIProvider = strpos($envContent, 'AI_PROVIDER=') !== false;

if ($hasAIProvider) {
    echo "✅ AI configuration already exists in .env\n";
} else {
    echo "➕ Adding AI configuration to .env...\n";
    
    // Add AI configuration
    $aiConfig = "\n# AI Configuration (Added by setup script)\n";
    $aiConfig .= "AI_PROVIDER=groq\n";
    $aiConfig .= "GROQ_API_KEY=\n";
    $aiConfig .= "OLLAMA_URL=http://localhost:11434/api/generate\n";
    $aiConfig .= "OLLAMA_MODEL=llama2\n";
    $aiConfig .= "HUGGINGFACE_API_KEY=\n";
    $aiConfig .= "TOGETHER_API_KEY=\n";
    $aiConfig .= "AI_QUIZ_GENERATION_ENABLED=true\n";
    $aiConfig .= "AI_EXPLANATIONS_ENABLED=true\n";
    $aiConfig .= "TEMPLATE_QUESTIONS_ENABLED=true\n";
    
    file_put_contents($envFile, $aiConfig, FILE_APPEND);
    echo "✅ AI configuration added to .env\n";
}

echo "\n🎯 Choose your FREE AI provider:\n";
echo "1. Groq (Recommended - 100 requests/day free)\n";
echo "2. Ollama (Completely free - runs locally)\n";
echo "3. HuggingFace (Free tier available)\n";
echo "4. Together AI (Free $25 credit)\n";
echo "5. Skip - use template questions only\n";

$choice = readline("\nEnter your choice (1-5): ");

switch ($choice) {
    case '1':
        setupGroq();
        break;
    case '2':
        setupOllama();
        break;
    case '3':
        setupHuggingFace();
        break;
    case '4':
        setupTogetherAI();
        break;
    case '5':
        setupTemplateOnly();
        break;
    default:
        echo "❌ Invalid choice. Exiting.\n";
        exit(1);
}

echo "\n🎉 Setup complete!\n";
echo "\n📋 Next steps:\n";
echo "1. Visit /ai-quiz in your browser\n";
echo "2. Enter a topic (e.g., 'JavaScript Programming')\n";
echo "3. Click 'Generate AI Quiz'\n";
echo "4. Enjoy your AI-powered quiz system!\n";

function setupGroq() {
    echo "\n🚀 Setting up Groq (FREE - 100 requests/day)...\n";
    echo "\n📝 Steps to get your Groq API key:\n";
    echo "1. Visit: https://console.groq.com/\n";
    echo "2. Sign up with your email\n";
    echo "3. Go to 'API Keys' section\n";
    echo "4. Create a new API key\n";
    echo "5. Copy the key (starts with 'gsk_')\n";
    
    $apiKey = readline("\nPaste your Groq API key (or press Enter to skip): ");
    
    if (!empty($apiKey)) {
        updateEnvValue('AI_PROVIDER', 'groq');
        updateEnvValue('GROQ_API_KEY', $apiKey);
        echo "✅ Groq configured successfully!\n";
        
        // Test the connection
        echo "🧪 Testing Groq connection...\n";
        if (testGroqConnection($apiKey)) {
            echo "✅ Groq connection successful!\n";
        } else {
            echo "⚠️  Groq connection failed. Please check your API key.\n";
        }
    } else {
        echo "⏭️  Skipped Groq setup. You can add the API key to .env later.\n";
        updateEnvValue('AI_PROVIDER', 'groq');
    }
}

function setupOllama() {
    echo "\n🏠 Setting up Ollama (100% FREE - runs locally)...\n";
    
    // Check if Ollama is installed
    $ollamaCheck = shell_exec('ollama --version 2>/dev/null');
    
    if ($ollamaCheck) {
        echo "✅ Ollama is already installed!\n";
        
        // Check if Ollama is running
        $ollamaRunning = @file_get_contents('http://localhost:11434/api/tags');
        
        if ($ollamaRunning !== false) {
            echo "✅ Ollama is running!\n";
            
            // Check available models
            $models = json_decode($ollamaRunning, true);
            if (!empty($models['models'])) {
                echo "✅ Available models: " . implode(', ', array_column($models['models'], 'name')) . "\n";
            } else {
                echo "📥 No models found. Downloading llama2...\n";
                echo "⏳ This may take a few minutes...\n";
                shell_exec('ollama pull llama2');
                echo "✅ llama2 model downloaded!\n";
            }
        } else {
            echo "🚀 Starting Ollama...\n";
            echo "💡 Run 'ollama serve' in another terminal to start Ollama\n";
        }
        
        updateEnvValue('AI_PROVIDER', 'ollama');
        echo "✅ Ollama configured successfully!\n";
        
    } else {
        echo "❌ Ollama not found. Please install it first:\n";
        echo "1. Visit: https://ollama.ai/\n";
        echo "2. Download and install Ollama\n";
        echo "3. Run: ollama pull llama2\n";
        echo "4. Run: ollama serve\n";
        echo "5. Re-run this setup script\n";
    }
}

function setupHuggingFace() {
    echo "\n🤗 Setting up HuggingFace (FREE tier)...\n";
    echo "\n📝 Steps to get your HuggingFace token:\n";
    echo "1. Visit: https://huggingface.co/\n";
    echo "2. Sign up for free\n";
    echo "3. Go to: https://huggingface.co/settings/tokens\n";
    echo "4. Create new token with 'Read' permission\n";
    echo "5. Copy the token (starts with 'hf_')\n";
    
    $apiKey = readline("\nPaste your HuggingFace token (or press Enter to skip): ");
    
    if (!empty($apiKey)) {
        updateEnvValue('AI_PROVIDER', 'huggingface');
        updateEnvValue('HUGGINGFACE_API_KEY', $apiKey);
        echo "✅ HuggingFace configured successfully!\n";
    } else {
        echo "⏭️  Skipped HuggingFace setup. You can add the token to .env later.\n";
    }
}

function setupTogetherAI() {
    echo "\n🤝 Setting up Together AI (FREE $25 credit)...\n";
    echo "\n📝 Steps to get your Together AI key:\n";
    echo "1. Visit: https://api.together.xyz/\n";
    echo "2. Sign up for free ($25 credit included)\n";
    echo "3. Get your API key from dashboard\n";
    echo "4. Copy the key\n";
    
    $apiKey = readline("\nPaste your Together AI key (or press Enter to skip): ");
    
    if (!empty($apiKey)) {
        updateEnvValue('AI_PROVIDER', 'together');
        updateEnvValue('TOGETHER_API_KEY', $apiKey);
        echo "✅ Together AI configured successfully!\n";
    } else {
        echo "⏭️  Skipped Together AI setup. You can add the key to .env later.\n";
    }
}

function setupTemplateOnly() {
    echo "\n📝 Setting up Template-only mode (No AI required)...\n";
    updateEnvValue('AI_PROVIDER', 'template');
    updateEnvValue('AI_QUIZ_GENERATION_ENABLED', 'false');
    updateEnvValue('TEMPLATE_QUESTIONS_ENABLED', 'true');
    echo "✅ Template-only mode configured!\n";
    echo "💡 Your quiz system will use smart templates instead of AI.\n";
}

function updateEnvValue($key, $value) {
    $envFile = '.env';
    $envContent = file_get_contents($envFile);
    
    // Check if key exists
    if (preg_match("/^{$key}=.*$/m", $envContent)) {
        // Update existing key
        $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envContent);
    } else {
        // Add new key
        $envContent .= "\n{$key}={$value}";
    }
    
    file_put_contents($envFile, $envContent);
}

function testGroqConnection($apiKey) {
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
    
    return $httpCode === 200;
}

echo "\n💡 Pro tip: You can always change your AI provider later by editing the AI_PROVIDER value in your .env file!\n";
?>
