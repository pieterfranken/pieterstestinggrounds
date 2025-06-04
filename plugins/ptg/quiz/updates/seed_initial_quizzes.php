<?php namespace PTG\Quiz\Updates;

use PTG\Quiz\Models\Quiz;
use PTG\Quiz\Models\Question;
use PTG\Quiz\Models\QuestionOption;
use October\Rain\Database\Updates\Seeder;

class SeedInitialQuizzes extends Seeder
{
    public function run()
    {
        // Create General Knowledge Quiz
        $generalQuiz = Quiz::create([
            'title' => 'PTG Knowledge Quiz',
            'description' => 'A comprehensive quiz covering general knowledge, programming, geography, and technology topics.',
            'difficulty' => 'Intermediate',
            'type' => 'general',
            'is_active' => true,
            'time_limit' => 15, // 15 minutes
        ]);

        // General Knowledge Questions (from quiz.htm)
        $generalQuestions = [
            [
                'question' => 'What is the capital of France?',
                'options' => ['A' => 'London', 'B' => 'Berlin', 'C' => 'Paris', 'D' => 'Madrid'],
                'correct' => 'C'
            ],
            [
                'question' => 'Which planet is known as the Red Planet?',
                'options' => ['A' => 'Venus', 'B' => 'Mars', 'C' => 'Jupiter', 'D' => 'Saturn'],
                'correct' => 'B'
            ],
            [
                'question' => 'What is the largest mammal in the world?',
                'options' => ['A' => 'Elephant', 'B' => 'Giraffe', 'C' => 'Blue Whale', 'D' => 'Polar Bear'],
                'correct' => 'C'
            ],
            [
                'question' => 'In which year did World War II end?',
                'options' => ['A' => '1944', 'B' => '1945', 'C' => '1946', 'D' => '1947'],
                'correct' => 'B'
            ],
            [
                'question' => 'What is the chemical symbol for gold?',
                'options' => ['A' => 'Go', 'B' => 'Gd', 'C' => 'Au', 'D' => 'Ag'],
                'correct' => 'C'
            ],
            [
                'question' => 'Which programming language is known for its use in web development and has a snake as its mascot?',
                'options' => ['A' => 'Java', 'B' => 'Python', 'C' => 'C++', 'D' => 'Ruby'],
                'correct' => 'B'
            ],
            [
                'question' => 'What does HTML stand for?',
                'options' => ['A' => 'HyperText Markup Language', 'B' => 'High Tech Modern Language', 'C' => 'Home Tool Markup Language', 'D' => 'Hyperlink and Text Markup Language'],
                'correct' => 'A'
            ],
            [
                'question' => 'Which company developed the React JavaScript library?',
                'options' => ['A' => 'Google', 'B' => 'Microsoft', 'C' => 'Facebook', 'D' => 'Apple'],
                'correct' => 'C'
            ],
            [
                'question' => 'What does CSS stand for?',
                'options' => ['A' => 'Computer Style Sheets', 'B' => 'Cascading Style Sheets', 'C' => 'Creative Style Sheets', 'D' => 'Colorful Style Sheets'],
                'correct' => 'B'
            ],
            [
                'question' => 'Which database management system is known for its use of SQL?',
                'options' => ['A' => 'MongoDB', 'B' => 'Redis', 'C' => 'MySQL', 'D' => 'Cassandra'],
                'correct' => 'C'
            ],
            [
                'question' => 'What does API stand for?',
                'options' => ['A' => 'Application Programming Interface', 'B' => 'Advanced Programming Integration', 'C' => 'Automated Program Interface', 'D' => 'Application Process Integration'],
                'correct' => 'A'
            ],
            [
                'question' => 'Which database query language is most commonly used?',
                'options' => ['A' => 'NoSQL', 'B' => 'SQL', 'C' => 'GraphQL', 'D' => 'MongoDB'],
                'correct' => 'B'
            ],
            [
                'question' => 'Which is the longest river in the world?',
                'options' => ['A' => 'Amazon River', 'B' => 'Nile River', 'C' => 'Mississippi River', 'D' => 'Yangtze River'],
                'correct' => 'B'
            ],
            [
                'question' => 'What is the smallest country in the world?',
                'options' => ['A' => 'Monaco', 'B' => 'Nauru', 'C' => 'Vatican City', 'D' => 'San Marino'],
                'correct' => 'C'
            ],
            [
                'question' => 'Which mountain range contains Mount Everest?',
                'options' => ['A' => 'Andes', 'B' => 'Rocky Mountains', 'C' => 'Alps', 'D' => 'Himalayas'],
                'correct' => 'D'
            ],
            [
                'question' => 'What is the largest ocean on Earth?',
                'options' => ['A' => 'Atlantic Ocean', 'B' => 'Indian Ocean', 'C' => 'Arctic Ocean', 'D' => 'Pacific Ocean'],
                'correct' => 'D'
            ],
            [
                'question' => 'Which company developed the iPhone?',
                'options' => ['A' => 'Samsung', 'B' => 'Google', 'C' => 'Apple', 'D' => 'Microsoft'],
                'correct' => 'C'
            ],
            [
                'question' => 'What does "IoT" stand for?',
                'options' => ['A' => 'Internet of Things', 'B' => 'Integration of Technology', 'C' => 'Interface of Tools', 'D' => 'Information on Technology'],
                'correct' => 'A'
            ],
            [
                'question' => 'Which cryptocurrency was the first to be created?',
                'options' => ['A' => 'Ethereum', 'B' => 'Bitcoin', 'C' => 'Litecoin', 'D' => 'Ripple'],
                'correct' => 'B'
            ],
            [
                'question' => 'What year was October CMS first released?',
                'options' => ['A' => '2010', 'B' => '2013', 'C' => '2015', 'D' => '2018'],
                'correct' => 'B'
            ]
        ];

        $this->seedQuestions($generalQuiz, $generalQuestions);

        // Create Story Quiz 1: The Mysterious Bug in the Digital Realm
        $storyQuiz1 = Quiz::create([
            'title' => 'The Mysterious Bug in the Digital Realm',
            'description' => 'Follow the debugging team as they track down a malicious bug causing chaos in Byteville.',
            'difficulty' => 'Beginner',
            'type' => 'story',
            'is_active' => true,
            'time_limit' => 10,
            'story_content' => 'In the bustling digital city of Byteville, where data flows like rivers and algorithms dance in perfect harmony, a mysterious bug has begun wreaking havoc. Every 12.57 seconds, systems would malfunction, causing chaos throughout the digital realm. The debugging team, led by Captain Exception and Binary Sleuth, must track down the source of this malicious code before it brings down the entire network infrastructure.'
        ]);

        $storyQuestions1 = [
            [
                'question' => 'What was the name of the city in the Digital Realm?',
                'options' => ['A' => 'Codeville', 'B' => 'Byteville', 'C' => 'Programton', 'D' => 'Algorithmia'],
                'correct' => 'B'
            ],
            [
                'question' => 'What was the time interval between malfunctions?',
                'options' => ['A' => '10.57 seconds', 'B' => '12.57 seconds', 'C' => '15.57 seconds', 'D' => '20.57 seconds'],
                'correct' => 'B'
            ],
            [
                'question' => 'Who was responsible for creating the bug?',
                'options' => ['A' => 'Captain Exception', 'B' => 'Binary Sleuth', 'C' => 'Infinite Loop', 'D' => 'Dr. Overflow'],
                'correct' => 'C'
            ],
            [
                'question' => 'What type of code was the malicious entity?',
                'options' => ['A' => 'Virus', 'B' => 'Trojan', 'C' => 'Bug', 'D' => 'Worm'],
                'correct' => 'C'
            ],
            [
                'question' => 'Who led the debugging team?',
                'options' => ['A' => 'Captain Exception and Binary Sleuth', 'B' => 'Dr. Overflow', 'C' => 'Infinite Loop', 'D' => 'The System Administrator'],
                'correct' => 'A'
            ]
        ];

        $this->seedQuestions($storyQuiz1, $storyQuestions1);

        // Create Story Quiz 2: The Cloud Kingdom's Lost Data
        $storyQuiz2 = Quiz::create([
            'title' => 'The Cloud Kingdom\'s Lost Data',
            'description' => 'Join the investigation to recover missing data in the Cloud Kingdom and uncover the royal conspiracy.',
            'difficulty' => 'Intermediate',
            'type' => 'story',
            'is_active' => true,
            'time_limit' => 12,
            'story_content' => 'High above the digital landscape floats the majestic Cloud Kingdom, ruled by Queen Redundancy. When critical data packets begin disappearing from the royal archives, a young data packet named Byte reports the theft to the Cloud Guards. The investigation reveals a conspiracy involving Prince Version, who has been secretly altering the timestamp server to cover his tracks while stealing valuable data for his own dark purposes.'
        ]);

        $storyQuestions2 = [
            [
                'question' => 'Who ruled the Cloud Kingdom?',
                'options' => ['A' => 'King Availability', 'B' => 'Queen Redundancy', 'C' => 'Emperor Bandwidth', 'D' => 'Princess Latency'],
                'correct' => 'B'
            ],
            [
                'question' => 'What was the name of the data packet who reported the missing data?',
                'options' => ['A' => 'Bit', 'B' => 'Packet', 'C' => 'Byte', 'D' => 'Megabyte'],
                'correct' => 'C'
            ],
            [
                'question' => 'How did Prince Version bypass the real-time monitoring?',
                'options' => ['A' => 'He used a backdoor in the firewall', 'B' => 'He altered the timestamp server', 'C' => 'He bribed the Encryption Wizard', 'D' => 'He created a decoy data cluster'],
                'correct' => 'B'
            ],
            [
                'question' => 'What was Prince Version stealing?',
                'options' => ['A' => 'Processing power', 'B' => 'Network bandwidth', 'C' => 'Valuable data', 'D' => 'Security certificates'],
                'correct' => 'C'
            ],
            [
                'question' => 'Who did Byte report the theft to?',
                'options' => ['A' => 'Queen Redundancy', 'B' => 'The Cloud Guards', 'C' => 'Prince Version', 'D' => 'The Encryption Wizard'],
                'correct' => 'B'
            ]
        ];

        $this->seedQuestions($storyQuiz2, $storyQuestions2);
    }

    private function seedQuestions($quiz, $questions)
    {
        foreach ($questions as $index => $questionData) {
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'question_text' => $questionData['question'],
                'correct_option' => $questionData['correct'],
                'order' => $index + 1,
                'is_active' => true,
            ]);

            foreach ($questionData['options'] as $key => $text) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_key' => $key,
                    'option_text' => $text,
                ]);
            }
        }

        $quiz->updateQuestionsCount();
    }
}
