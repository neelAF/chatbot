<?php
// Set headers
header('Content-Type: application/json');

// Get user query
$query = isset($_POST['query']) ? trim($_POST['query']) : '';
if (!$query) {
    echo json_encode(['answer' => 'Please enter a valid question.']);
    exit;
}

// Load data from college_data.txt
$collegeDataFile = 'college_data.txt';
$collegeData = json_decode(file_get_contents($collegeDataFile), true);

// Function to search in the array (recursive search)
function searchCollegeData($data, $query) {
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $found = searchCollegeData($value, $query);
            if ($found) return $found;
        } else {
            if (stripos($key, $query) !== false || stripos($value, $query) !== false) {
                return "$key: $value";
            }
        }
    }
    return false;
}

// Try finding answer in college data
$answerFromData = searchCollegeData($collegeData, $query);

if ($answerFromData) {
    echo json_encode(['answer' => "📚 From College Data:\n" . $answerFromData]);
    exit;
}

// Fallback to OpenAI GPT-4
$openaiApiKey = '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';
$openaiEndpoint = 'https://api.openai.com/v1/chat/completions';

$ch = curl_init($openaiEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $openaiApiKey"
]);

$payload = json_encode([
    "model" => "gpt-4",
    "messages" => [
        ["role" => "system", "content" => "You are a helpful assistant for RKDF University chatbot. Answer briefly and accurately."],
        ["role" => "user", "content" => $query]
    ],
    "temperature" => 0.7
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['answer' => 'Error fetching AI response.']);
    exit;
}

$result = json_decode($response, true);
$aiAnswer = $result['choices'][0]['message']['content'] ?? "Sorry, I couldn’t find an answer.";

echo json_encode(['answer' => "🤖 AI Response:\n" . $aiAnswer]);

