<?php namespace PTG\Quiz\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI Quiz Service for generating questions and providing intelligent features
 */
class AIQuizService
{
    protected $provider;
    protected $config;

    public function __construct($provider = null)
    {
        $this->provider = $provider ?: env('AI_PROVIDER', 'groq'); // Default to groq
        $this->config = $this->getProviderConfig($this->provider);
    }

    /**
     * Set the AI provider dynamically
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
        $this->config = $this->getProviderConfig($this->provider);
        return $this;
    }

    /**
     * Get configuration for different AI providers
     */
    protected function getProviderConfig($provider)
    {
        $configs = [
            // FREE OPTIONS
            'huggingface' => [
                'api_key' => env('HUGGINGFACE_API_KEY'), // Free tier available
                'api_url' => 'https://api-inference.huggingface.co/models/microsoft/DialoGPT-large',
                'model' => 'microsoft/DialoGPT-large',
                'free' => true
            ],
            'ollama' => [
                'api_url' => env('OLLAMA_URL', 'http://localhost:11434/api/generate'),
                'model' => env('OLLAMA_MODEL', 'llama2'),
                'free' => true // Completely free, runs locally
            ],
            'groq' => [
                'api_key' => env('GROQ_API_KEY'), // Free tier: 100 requests/day
                'api_url' => 'https://api.groq.com/openai/v1/chat/completions',
                'model' => 'llama3-70b-8192',
                'free' => true
            ],
            'together' => [
                'api_key' => env('TOGETHER_API_KEY'), // Free $25 credit
                'api_url' => 'https://api.together.xyz/v1/chat/completions',
                'model' => 'meta-llama/Llama-2-7b-chat-hf',
                'free' => true
            ],

            // PAID OPTIONS (for comparison)
            'openai' => [
                'api_key' => env('OPENAI_API_KEY'),
                'api_url' => 'https://api.openai.com/v1/chat/completions',
                'model' => 'gpt-3.5-turbo',
                'free' => false
            ],
            'anthropic' => [
                'api_key' => env('ANTHROPIC_API_KEY'),
                'api_url' => 'https://api.anthropic.com/v1/messages',
                'model' => 'claude-3-haiku-20240307',
                'free' => false
            ]
        ];

        return $configs[$provider] ?? $configs['huggingface'];
    }

    /**
     * Generate quiz questions based on a topic
     */
    public function generateQuestions($topic, $count = 10, $difficulty = 'medium')
    {
        $maxRetries = 10; // Increased retry limit
        $baseDelay = 1; // Base delay in seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                Log::info("AI Quiz Generation Attempt {$attempt}", ['topic' => $topic, 'count' => $count, 'difficulty' => $difficulty]);

                // Try AI generation
                $questions = $this->callAIProvider($topic, $count, $difficulty);
                if (!empty($questions)) {
                    Log::info('AI Quiz Generation Success', ['questions_count' => count($questions), 'attempt' => $attempt]);
                    return $questions;
                }
            } catch (Exception $e) {
                Log::error("AI Quiz Generation Error (Attempt {$attempt}): " . $e->getMessage());

                // If this is the last attempt, fall back to templates
                if ($attempt === $maxRetries) {
                    Log::warning('All AI attempts failed after ' . $maxRetries . ' tries, falling back to templates');
                    break;
                }

                // Progressive delay: 1s, 2s, 3s, etc. (capped at 5s)
                $delay = min($baseDelay * $attempt, 5);
                Log::info("Waiting {$delay} seconds before retry...");
                sleep($delay);
            }
        }

        // Fallback to template-based generation only after all retries exhausted
        Log::warning('Using template questions as fallback');
        return $this->generateTemplateQuestions($topic, $count, $difficulty);
    }

    /**
     * Call the configured AI provider
     */
    protected function callAIProvider($topic, $count, $difficulty)
    {
        switch ($this->provider) {
            case 'ollama':
                return $this->callOllama($topic, $count, $difficulty);
            case 'huggingface':
                return $this->callHuggingFace($topic, $count, $difficulty);
            case 'groq':
                return $this->callGroq($topic, $count, $difficulty);
            case 'together':
                return $this->callTogether($topic, $count, $difficulty);
            case 'openai':
                return $this->callOpenAI($topic, $count, $difficulty);
            default:
                throw new Exception('Unsupported AI provider: ' . $this->provider);
        }
    }

    /**
     * Generate explanations for quiz answers
     */
    public function generateExplanation($question, $correctAnswer, $userAnswer, $options)
    {
        try {
            // Try to generate AI explanation first
            return $this->generateAIExplanation($question, $correctAnswer, $userAnswer, $options);
        } catch (Exception $e) {
            Log::error('AI Explanation Error: ' . $e->getMessage());
            // Fallback to simple explanation
            return $this->getSimpleExplanation($question, $correctAnswer, $userAnswer, $options);
        }
    }

    /**
     * Generate AI-powered explanation
     */
    protected function generateAIExplanation($question, $correctAnswer, $userAnswer, $options)
    {
        $correctOption = $options[$correctAnswer] ?? 'Unknown';
        $userOption = $userAnswer ? ($options[$userAnswer] ?? 'Unknown') : 'No answer';
        $isCorrect = ($userAnswer === $correctAnswer);

        // Create context-aware prompt based on whether user was correct
        if ($isCorrect) {
            $prompt = "The user correctly answered this question: \"{$question}\"\n\n";
            $prompt .= "They selected '{$correctOption}' (option {$correctAnswer}), which is correct.\n\n";
            $prompt .= "Provide a positive, educational explanation that:\n";
            $prompt .= "1. Confirms they got it right\n";
            $prompt .= "2. Explains why their answer is correct\n";
            $prompt .= "3. Adds interesting educational context\n";
            $prompt .= "4. Is encouraging and congratulatory\n";
            $prompt .= "5. Is 2-3 sentences long\n";
        } else {
            $prompt = "The user answered this question incorrectly: \"{$question}\"\n\n";
            $prompt .= "They selected '{$userOption}' (option {$userAnswer}), but the correct answer is '{$correctOption}' (option {$correctAnswer}).\n\n";
            $prompt .= "All options were:\n";
            foreach ($options as $key => $option) {
                $prompt .= "- {$key}: {$option}\n";
            }
            $prompt .= "\nProvide a helpful, educational explanation that:\n";
            $prompt .= "1. Starts with something like 'Ah, unfortunately you got it wrong' or 'Not quite right this time'\n";
            $prompt .= "2. States 'the right answer is actually [correct answer]'\n";
            $prompt .= "3. Explains why the correct answer is right\n";
            $prompt .= "4. Briefly explains why their choice was incorrect\n";
            $prompt .= "5. Is encouraging and supportive (not discouraging)\n";
            $prompt .= "6. Helps them learn for next time\n";
            $prompt .= "7. Is 2-3 sentences long\n";
            $prompt .= "8. NEVER use the word 'indeed' in your response\n";
        }

        // Use cURL for explanation generation
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config['api_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->config['api_key'],
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = [
            'model' => $this->config['model'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful, encouraging tutor. Provide clear, educational explanations for quiz answers. Be concise but informative.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.5,
            'max_tokens' => 300
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['choices'][0]['message']['content'])) {
                $explanation = trim($result['choices'][0]['message']['content']);

                // Clean up the explanation
                $explanation = preg_replace('/^(Explanation:|Answer:|Solution:)\s*/i', '', $explanation);

                return $explanation;
            }
        }

        throw new Exception('Failed to generate AI explanation');
    }

    /**
     * Get simple explanation for answers (fallback)
     */
    protected function getSimpleExplanation($question, $correctAnswer, $userAnswer, $options)
    {
        $correctOption = $options[$correctAnswer] ?? 'the correct option';

        if ($userAnswer === $correctAnswer) {
            return "🎉 Excellent work! You correctly selected '{$correctOption}'. This demonstrates a solid understanding of the topic. Keep up the great learning!";
        } else {
            $userOption = $userAnswer ? ($options[$userAnswer] ?? 'your selection') : 'no answer provided';

            if ($userAnswer) {
                return "Ah, unfortunately you got it wrong. You selected '{$userOption}', but the right answer is actually '{$correctOption}' (option {$correctAnswer}). This is a common mistake - '{$correctOption}' is the most accurate choice for this question. Review the material and you'll get it next time!";
            } else {
                return "Ah, unfortunately you didn't select an answer for this question. The right answer is actually '{$correctOption}' (option {$correctAnswer}). Make sure to read all options carefully and select the best choice. You've got this!";
            }
        }
    }

    /**
     * Analyze user performance and provide insights
     */
    public function analyzePerformance($attempts)
    {
        $insights = [];
        
        if (empty($attempts)) {
            return ['message' => 'Take more quizzes to get personalized insights!'];
        }

        // Calculate trends
        $recentScores = collect($attempts)->take(5)->pluck('percentage');
        $averageScore = $recentScores->avg();
        
        // Generate AI insights
        try {
            $prompt = "Analyze this quiz performance data and provide 2-3 helpful insights:\n";
            $prompt .= "Recent scores: " . $recentScores->implode(', ') . "%\n";
            $prompt .= "Average: " . round($averageScore, 1) . "%\n";
            $prompt .= "Total attempts: " . count($attempts);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an educational coach. Provide encouraging, actionable insights.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.6,
                'max_tokens' => 300
            ]);

            if ($response->successful()) {
                $insights['ai_analysis'] = $response->json()['choices'][0]['message']['content'];
            }

        } catch (Exception $e) {
            Log::error('AI Performance Analysis Error: ' . $e->getMessage());
        }

        // Add basic insights
        if ($averageScore >= 80) {
            $insights['performance'] = 'Excellent work! You\'re consistently performing well.';
        } elseif ($averageScore >= 60) {
            $insights['performance'] = 'Good progress! Focus on areas where you missed questions.';
        } else {
            $insights['performance'] = 'Keep practicing! Consider reviewing the material before retaking quizzes.';
        }

        return $insights;
    }

    /**
     * Build the prompt for question generation
     */
    protected function buildQuestionPrompt($topic, $count, $difficulty)
    {
        $difficultyNote = '';
        if ($difficulty === 'easy') {
            $difficultyNote = ' Make questions simple for beginners.';
        } elseif ($difficulty === 'hard') {
            $difficultyNote = ' Make questions advanced and challenging.';
        }

        return "Create {$count} multiple choice questions about {$topic}.{$difficultyNote} Focus on core concepts, not recent updates.

CRITICAL REQUIREMENTS:
- Each question must have exactly 4 options (A, B, C, D). Never use fewer than 4 options.
- ACCURACY IS PARAMOUNT: Ensure all correct answers are factually accurate and verifiable
- Double-check your knowledge before marking an answer as correct
- Make incorrect options plausible but clearly wrong to someone with proper knowledge
- Vary the correct answers - don't always use A. Mix between A, B, C, and D randomly.
- AVOID GIVEAWAYS: Never include the answer or obvious hints in the question text
- Example of BAD question: Which anime features Naruto Uzumaki? (answer: Naruto)
- Example of GOOD question: What is the main character name in the anime about a young ninja from Hidden Leaf Village?

Return only JSON:
[
  {
    \"question\": \"Question?\",
    \"options\": {
      \"A\": \"Option A\",
      \"B\": \"Option B\",
      \"C\": \"Option C\",
      \"D\": \"Option D\"
    },
    \"correct\": \"B\"
  }
]

FINAL CHECKS: Before finalizing, verify that:
1. Your marked correct answer is actually correct
2. The question does not give away the answer
3. All options are plausible to someone unfamiliar with the topic
Remember: Distribute correct answers randomly among A, B, C, and D options.";
    }

    /**
     * Build specialized prompt for OpenAI GPT-3.5 Turbo
     */
    protected function buildOpenAIPrompt($topic, $count, $difficulty)
    {
        $difficultyInstructions = '';
        switch ($difficulty) {
            case 'easy':
                $difficultyInstructions = 'Create questions suitable for beginners or high school level. Use clear, simple language and focus on basic concepts.';
                break;
            case 'hard':
                $difficultyInstructions = 'Create advanced questions that require deep understanding. Include complex scenarios and detailed knowledge.';
                break;
            default:
                $difficultyInstructions = 'Create questions at a medium difficulty level, suitable for someone with basic knowledge of the topic.';
        }

        // Generate a random pattern for correct answers to encourage variation
        $answerPattern = ['A', 'B', 'C', 'D'];
        shuffle($answerPattern);
        $suggestedAnswers = array_slice($answerPattern, 0, min($count, 4));
        $answerHint = implode(', ', $suggestedAnswers);

        return "Generate exactly {$count} multiple choice questions about: {$topic}

INSTRUCTIONS:
- {$difficultyInstructions}
- Each question must have exactly 4 answer options labeled A, B, C, D
- Only one option should be correct
- Make questions clear and unambiguous
- Focus on important concepts, not trivial details
- Ensure all options are plausible but only one is correct

ACCURACY REQUIREMENTS:
- CRITICAL: Verify that your marked correct answer is factually accurate
- Double-check your knowledge before selecting the correct option
- Ensure incorrect options are clearly wrong to someone with proper knowledge
- Base answers on well-established facts, not opinions or recent changes

QUESTION QUALITY REQUIREMENTS:
- AVOID GIVEAWAYS: Never include the answer or obvious hints in the question text
- Do not mention the correct answer directly in the question
- Use descriptive phrases instead of proper names when possible
- Make questions require actual knowledge, not just reading comprehension
- Bad examples:
  * Which anime features Naruto Uzumaki? (gives away answer: Naruto)
  * Which anime uses Death Notes? (gives away answer: Death Note)
  * Which language uses .py files? (gives away answer: Python)
- Good examples:
  * What is the main character name in the ninja anime from Hidden Leaf Village?
  * Which anime involves supernatural notebooks that can cause deaths?
  * Which programming language is known for its readable syntax and snake mascot?

CRITICAL ANSWER DISTRIBUTION REQUIREMENT:
- DO NOT make all correct answers 'A'
- Distribute correct answers across different options: {$answerHint}
- For multiple questions, use different correct answers (A, B, C, D)
- Avoid patterns - randomize which option is correct for each question

REQUIRED OUTPUT FORMAT:
Return ONLY a valid JSON array with this exact structure:

[
  {
    \"question\": \"Your question here?\",
    \"options\": {
      \"A\": \"First option\",
      \"B\": \"Second option\",
      \"C\": \"Third option\",
      \"D\": \"Fourth option\"
    },
    \"correct\": \"C\"
  }
]

FINAL CHECKS: Before submitting, confirm that:
1. Your correct answers are actually correct
2. Questions do NOT contain any words from the correct answer
3. Questions use descriptive phrases instead of proper names when possible
4. All options are unique and plausible
5. Someone could not answer correctly just by reading the question
Do not include any text before or after the JSON. Start your response with [ and end with ].";
    }

    /**
     * Parse AI-generated questions
     */
    protected function parseQuestions($content)
    {
        try {
            // Clean up the response (remove markdown formatting if present)
            $content = preg_replace('/```json\s*/', '', $content);
            $content = preg_replace('/```\s*$/', '', $content);
            $content = trim($content);

            $questions = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON format');
            }

            // Validate question structure
            foreach ($questions as $index => $question) {
                if (!isset($question['question'], $question['options'], $question['correct'])) {
                    throw new Exception('Invalid question structure at index ' . $index);
                }

                // Ensure exactly 4 options (A, B, C, D)
                if (!is_array($question['options']) || count($question['options']) !== 4) {
                    throw new Exception('Question at index ' . $index . ' must have exactly 4 options (A, B, C, D)');
                }

                $expectedKeys = ['A', 'B', 'C', 'D'];
                $actualKeys = array_keys($question['options']);
                if ($actualKeys !== $expectedKeys) {
                    throw new Exception('Question at index ' . $index . ' options must be labeled A, B, C, D');
                }
            }

            // Post-process to ensure answer variation
            $questions = $this->redistributeCorrectAnswers($questions);

            return $questions;

        } catch (Exception $e) {
            Log::error('Question parsing error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Redistribute correct answers to ensure variation across A, B, C, D
     */
    protected function redistributeCorrectAnswers($questions)
    {
        if (empty($questions)) {
            return $questions;
        }

        // Check if all answers are the same (common AI bias)
        $correctAnswers = array_column($questions, 'correct');
        $uniqueAnswers = array_unique($correctAnswers);

        // If there's good variation already (3+ different answers), keep as is
        if (count($uniqueAnswers) >= 3) {
            return $questions;
        }

        // If all answers are the same or limited variation, redistribute
        $options = ['A', 'B', 'C', 'D'];
        $redistributed = [];

        foreach ($questions as $index => $question) {
            // For the first 4 questions, ensure we use each option once
            if ($index < 4) {
                $newCorrect = $options[$index];
            } else {
                // For additional questions, randomly select
                $newCorrect = $options[array_rand($options)];
            }

            // If the correct answer is changing, we need to shuffle the options
            if ($newCorrect !== $question['correct']) {
                $question = $this->shuffleQuestionOptions($question, $newCorrect);
            }

            $redistributed[] = $question;
        }

        Log::info('Redistributed correct answers for better variation', [
            'original_distribution' => array_count_values($correctAnswers),
            'new_distribution' => array_count_values(array_column($redistributed, 'correct'))
        ]);

        return $redistributed;
    }

    /**
     * Shuffle question options to make a different option correct
     */
    protected function shuffleQuestionOptions($question, $newCorrect)
    {
        $currentCorrect = $question['correct'];
        $options = $question['options'];

        // Get the text of the currently correct answer
        $correctAnswerText = $options[$currentCorrect];

        // Swap the correct answer text to the new position
        $options[$newCorrect] = $correctAnswerText;

        // Fill the old correct position with one of the other options
        $otherOptions = array_diff_key($options, [$newCorrect => '']);
        $replacementText = array_values($otherOptions)[0];
        $options[$currentCorrect] = $replacementText;

        // Update the question
        $question['options'] = $options;
        $question['correct'] = $newCorrect;

        return $question;
    }

    /**
     * Call Ollama (FREE - runs locally)
     */
    protected function callOllama($topic, $count, $difficulty)
    {
        $prompt = $this->buildQuestionPrompt($topic, $count, $difficulty);

        $response = Http::timeout(45)->post($this->config['api_url'], [
            'model' => $this->config['model'],
            'prompt' => $prompt,
            'stream' => false
        ]);

        if ($response->successful()) {
            $content = $response->json()['response'] ?? '';
            return $this->parseQuestions($content);
        }

        throw new Exception('Ollama request failed');
    }

    /**
     * Call Groq (FREE - 100 requests/day)
     */
    protected function callGroq($topic, $count, $difficulty)
    {
        $prompt = $this->buildQuestionPrompt($topic, $count, $difficulty);

        try {
            // Use cURL directly for better compatibility
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->config['api_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->config['api_key'],
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For Windows compatibility

            $data = [
                'model' => $this->config['model'],
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert quiz creator who prioritizes accuracy above all else. Generate high-quality, factually correct multiple choice questions in valid JSON format. Always verify your correct answers are actually correct.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.2,
                'max_tokens' => 2000
            ];

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception('cURL Error: ' . $error);
            }

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                if (isset($result['choices'][0]['message']['content'])) {
                    $content = $result['choices'][0]['message']['content'];
                    Log::info('Groq API Success: Generated content', ['content' => substr($content, 0, 200)]);
                    return $this->parseQuestions($content);
                } else {
                    throw new Exception('Invalid response format from Groq');
                }
            } else {
                Log::error('Groq API Error', [
                    'status' => $httpCode,
                    'body' => $response
                ]);
                throw new Exception('Groq request failed with status: ' . $httpCode);
            }
        } catch (\Exception $e) {
            Log::error('Groq API Exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Call Together AI (FREE - $25 credit)
     */
    protected function callTogether($topic, $count, $difficulty)
    {
        $prompt = $this->buildQuestionPrompt($topic, $count, $difficulty);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config['api_key'],
            'Content-Type' => 'application/json',
        ])->post($this->config['api_url'], [
            'model' => $this->config['model'],
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert quiz creator who prioritizes factual accuracy. Always verify your correct answers are actually correct.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.2,
            'max_tokens' => 2000
        ]);

        if ($response->successful()) {
            $content = $response->json()['choices'][0]['message']['content'];
            return $this->parseQuestions($content);
        }

        throw new Exception('Together AI request failed');
    }

    /**
     * Call HuggingFace (FREE tier available)
     */
    protected function callHuggingFace($topic, $count, $difficulty)
    {
        $prompt = $this->buildQuestionPrompt($topic, $count, $difficulty);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config['api_key'],
            'Content-Type' => 'application/json',
        ])->post($this->config['api_url'], [
            'inputs' => $prompt,
            'parameters' => [
                'max_length' => 2000,
                'temperature' => 0.2,
                'return_full_text' => false
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $content = $result[0]['generated_text'] ?? '';
            return $this->parseQuestions($content);
        }

        throw new Exception('HuggingFace request failed');
    }

    /**
     * Call OpenAI GPT-3.5 Turbo
     */
    protected function callOpenAI($topic, $count, $difficulty)
    {
        // Use specialized prompt for OpenAI
        $prompt = $this->buildOpenAIPrompt($topic, $count, $difficulty);

        try {
            // Use cURL directly for better performance and compatibility
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->config['api_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->config['api_key'],
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For Windows compatibility

            $data = [
                'model' => $this->config['model'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert educational quiz creator who prioritizes factual accuracy. You create high-quality, factually correct multiple choice questions with exactly 4 options (A, B, C, D). Always verify your correct answers are actually correct before responding. Always respond with valid JSON format only.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.2,
                'max_tokens' => 3000,
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0
            ];

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception('cURL Error: ' . $error);
            }

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                if (isset($result['choices'][0]['message']['content'])) {
                    $content = $result['choices'][0]['message']['content'];
                    Log::info('OpenAI API Success: Generated content', ['content' => substr($content, 0, 200)]);
                    return $this->parseQuestions($content);
                } else {
                    throw new Exception('Invalid response format from OpenAI');
                }
            } else {
                Log::error('OpenAI API Error', [
                    'status' => $httpCode,
                    'body' => $response
                ]);
                throw new Exception('OpenAI request failed with status: ' . $httpCode);
            }
        } catch (\Exception $e) {
            Log::error('OpenAI API Exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate questions using templates (fallback when AI fails)
     */
    protected function generateTemplateQuestions($topic, $count, $difficulty)
    {
        $templates = $this->getQuestionTemplates($topic, $difficulty);
        $questions = [];

        // Generate the requested number of questions
        for ($i = 0; $i < $count; $i++) {
            $template = $templates[$i % count($templates)];
            $questions[] = $this->fillTemplate($template, $topic, $i + 1);
        }

        return $questions;
    }

    /**
     * Get question templates based on topic and difficulty
     */
    protected function getQuestionTemplates($topic, $difficulty)
    {
        $topicLower = strtolower($topic);

        // Topic-specific templates
        if (strpos($topicLower, 'programming') !== false || strpos($topicLower, 'javascript') !== false) {
            return $this->getProgrammingTemplates($difficulty);
        } elseif (strpos($topicLower, 'history') !== false) {
            return $this->getHistoryTemplates($difficulty);
        } elseif (strpos($topicLower, 'science') !== false || strpos($topicLower, 'biology') !== false) {
            return $this->getScienceTemplates($difficulty);
        } elseif (strpos($topicLower, 'geography') !== false) {
            return $this->getGeographyTemplates($difficulty);
        }

        // Generic templates
        return $this->getGenericTemplates($topic, $difficulty);
    }

    /**
     * Programming question templates
     */
    protected function getProgrammingTemplates($difficulty)
    {
        $easy = [
            [
                'question' => 'What does HTML stand for?',
                'options' => ['HyperText Markup Language', 'High Tech Modern Language', 'Home Tool Markup Language', 'Hyperlink and Text Markup Language'],
                'correct' => 0
            ],
            [
                'question' => 'Which symbol is used for comments in JavaScript?',
                'options' => ['//', '<!-- -->', '#', '/* */'],
                'correct' => 0
            ],
            [
                'question' => 'What does CSS stand for?',
                'options' => ['Computer Style Sheets', 'Cascading Style Sheets', 'Creative Style Sheets', 'Colorful Style Sheets'],
                'correct' => 1
            ],
            [
                'question' => 'Which of these is NOT a JavaScript data type?',
                'options' => ['String', 'Boolean', 'Float', 'Number'],
                'correct' => 2
            ],
            [
                'question' => 'What is the correct way to declare a variable in JavaScript?',
                'options' => ['variable x;', 'v x;', 'var x;', 'declare x;'],
                'correct' => 2
            ]
        ];

        $medium = [
            [
                'question' => 'What is the purpose of the "this" keyword in JavaScript?',
                'options' => ['Refers to the current object', 'Creates a new variable', 'Defines a function', 'Imports a module'],
                'correct' => 0
            ],
            [
                'question' => 'Which method is used to add an element to the end of an array?',
                'options' => ['pop()', 'push()', 'shift()', 'unshift()'],
                'correct' => 1
            ],
            [
                'question' => 'What does DOM stand for?',
                'options' => ['Data Object Model', 'Document Object Model', 'Dynamic Object Model', 'Display Object Model'],
                'correct' => 1
            ],
            [
                'question' => 'Which operator is used for strict equality in JavaScript?',
                'options' => ['=', '==', '===', '!='],
                'correct' => 2
            ],
            [
                'question' => 'What is the correct way to write a JavaScript array?',
                'options' => ['var colors = "red", "green", "blue"', 'var colors = (1:"red", 2:"green", 3:"blue")', 'var colors = 1 = ("red"), 2 = ("green"), 3 = ("blue")', 'var colors = ["red", "green", "blue"]'],
                'correct' => 3
            ]
        ];

        $hard = [
            [
                'question' => 'What is a closure in JavaScript?',
                'options' => ['A function with access to outer scope', 'A type of loop', 'An error handling mechanism', 'A data structure'],
                'correct' => 0
            ],
            [
                'question' => 'What is event bubbling?',
                'options' => ['Creating new events', 'Events propagating up the DOM tree', 'Deleting events', 'Events propagating down the DOM tree'],
                'correct' => 1
            ],
            [
                'question' => 'What is the difference between let and var?',
                'options' => ['No difference', 'let has function scope, var has block scope', 'let has block scope, var has function scope', 'let is faster than var'],
                'correct' => 2
            ]
        ];

        return $difficulty === 'easy' ? $easy : ($difficulty === 'medium' ? $medium : $hard);
    }

    /**
     * History question templates
     */
    protected function getHistoryTemplates($difficulty)
    {
        return [
            [
                'question' => 'In which year did World War II end?',
                'options' => ['1944', '1945', '1946', '1943'],
                'correct' => 1
            ],
            [
                'question' => 'Who was the first President of the United States?',
                'options' => ['George Washington', 'Thomas Jefferson', 'John Adams', 'Benjamin Franklin'],
                'correct' => 0
            ],
            [
                'question' => 'The Roman Empire fell in which century?',
                'options' => ['4th century', '6th century', '5th century', '3rd century'],
                'correct' => 2
            ],
            [
                'question' => 'Which ancient wonder was located in Alexandria?',
                'options' => ['Hanging Gardens', 'Colossus of Rhodes', 'Lighthouse of Alexandria', 'Statue of Zeus'],
                'correct' => 2
            ],
            [
                'question' => 'The French Revolution began in which year?',
                'options' => ['1788', '1789', '1790', '1791'],
                'correct' => 1
            ],
            [
                'question' => 'Who was known as the "Iron Lady"?',
                'options' => ['Angela Merkel', 'Margaret Thatcher', 'Golda Meir', 'Indira Gandhi'],
                'correct' => 1
            ]
        ];
    }

    /**
     * Science question templates
     */
    protected function getScienceTemplates($difficulty)
    {
        return [
            [
                'question' => 'What is the chemical symbol for water?',
                'options' => ['H2O', 'CO2', 'O2', 'H2SO4'],
                'correct' => 0
            ],
            [
                'question' => 'How many chambers does a human heart have?',
                'options' => ['4', '3', '2', '5'],
                'correct' => 0
            ],
            [
                'question' => 'What is the speed of light in vacuum?',
                'options' => ['299,792,458 m/s', '300,000,000 m/s', '299,000,000 m/s', '298,792,458 m/s'],
                'correct' => 0
            ]
        ];
    }

    /**
     * Geography question templates
     */
    protected function getGeographyTemplates($difficulty)
    {
        return [
            [
                'question' => 'What is the capital of Australia?',
                'options' => ['Canberra', 'Sydney', 'Melbourne', 'Perth'],
                'correct' => 0
            ],
            [
                'question' => 'Which is the longest river in the world?',
                'options' => ['Nile', 'Amazon', 'Mississippi', 'Yangtze'],
                'correct' => 0
            ],
            [
                'question' => 'How many continents are there?',
                'options' => ['7', '6', '8', '5'],
                'correct' => 0
            ]
        ];
    }

    /**
     * Generic question templates
     */
    protected function getGenericTemplates($topic, $difficulty)
    {
        return [
            [
                'question' => "What is a fundamental concept in {$topic}?",
                'options' => ['Basic principles', 'Advanced theories', 'Complex applications', 'Historical context'],
                'correct' => 0
            ],
            [
                'question' => "Which of the following is most important when studying {$topic}?",
                'options' => ['Memorizing facts', 'Understanding core concepts', 'Learning dates', 'Reading textbooks'],
                'correct' => 1
            ],
            [
                'question' => "What makes {$topic} an interesting subject?",
                'options' => ['Its complexity', 'Its history', 'Its practical applications', 'Its difficulty'],
                'correct' => 2
            ],
            [
                'question' => "When learning about {$topic}, what should you focus on first?",
                'options' => ['Advanced topics', 'Historical context', 'Complex theories', 'Basic fundamentals'],
                'correct' => 3
            ],
            [
                'question' => "What is the best approach to mastering {$topic}?",
                'options' => ['Regular practice and study', 'Memorizing everything', 'Reading once', 'Watching videos only'],
                'correct' => 0
            ],
            [
                'question' => "Why is {$topic} considered important?",
                'options' => ['It\'s easy to learn', 'It has real-world applications', 'It\'s popular', 'It\'s required'],
                'correct' => 1
            ]
        ];
    }

    /**
     * Fill template with specific data
     */
    protected function fillTemplate($template, $topic, $questionNumber)
    {
        $options = [];
        foreach ($template['options'] as $index => $option) {
            $letter = chr(65 + $index); // A, B, C, D
            $options[$letter] = $option;
        }

        $correctLetter = chr(65 + $template['correct']);

        return [
            'question' => $template['question'],
            'options' => $options,
            'correct' => $correctLetter
        ];
    }
}
