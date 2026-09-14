<?php
require __DIR__.'/auth.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'error'=>'POST required']);
    exit;
}

$apiKey = getenv('OPENAI_API_KEY') ?: '';
if ($apiKey === '') {
    http_response_code(503);
    echo json_encode(['ok'=>false,'error'=>'AI is not configured. Set OPENAI_API_KEY on the server/Termux environment.']);
    exit;
}

$foodText = trim((string)($_POST['food_text'] ?? ''));
$image = $_FILES['food_image'] ?? null;

if ($foodText === '' && (!$image || $image['error'] !== UPLOAD_ERR_OK)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Enter food details or upload a food photo.']);
    exit;
}

$input = [];
$instruction = 'Analyze the meal carefully. Return ONLY valid JSON with this exact shape: {"food_item":"...","quantity":"...","calories":0,"protein":0,"carbs":0,"fat":0,"confidence":0,"notes":"..."}. Nutrition must be approximate for the identified serving. confidence is 0-100. Do not invent medical claims. If the image is unclear, explain briefly in notes and use conservative estimates.';

$content = [['type'=>'input_text','text'=>$instruction . ($foodText !== '' ? "\nUser food details: {$foodText}" : '')]];

if ($image && $image['error'] === UPLOAD_ERR_OK) {
    $mime = mime_content_type($image['tmp_name']) ?: 'image/jpeg';
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    if (!in_array($mime, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'Please upload a JPG, PNG, WEBP or GIF image.']);
        exit;
    }
    if ((int)$image['size'] > 8 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'Food image must be 8 MB or smaller.']);
        exit;
    }
    $data = base64_encode((string)file_get_contents($image['tmp_name']));
    $content[] = ['type'=>'input_image','image_url'=>'data:'.$mime.';base64,'.$data];
}

$payload = [
    'model' => getenv('OPENAI_FOOD_MODEL') ?: 'gpt-5.6-luna',
    'input' => [[ 'role'=>'user', 'content'=>$content ]],
    'max_output_tokens' => 500
];

$ch = curl_init('https://api.openai.com/v1/responses');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json','Authorization: Bearer '.$apiKey],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
    CURLOPT_TIMEOUT => 60,
]);
$response = curl_exec($ch);
$curlError = curl_error($ch);
$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'AI request failed: '.$curlError]);
    exit;
}

$data = json_decode($response, true);
if (!is_array($data)) {
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'Invalid AI response.']);
    exit;
}
if ($status >= 400) {
    http_response_code(502);
    $msg = $data['error']['message'] ?? 'AI service returned an error.';
    echo json_encode(['ok'=>false,'error'=>$msg]);
    exit;
}

$text = (string)($data['output_text'] ?? '');
if ($text === '') {
    foreach (($data['output'] ?? []) as $out) {
        foreach (($out['content'] ?? []) as $part) {
            if (($part['type'] ?? '') === 'output_text') $text .= (string)($part['text'] ?? '');
        }
    }
}
$text = trim(preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $text));
$result = json_decode($text, true);
if (!is_array($result)) {
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'AI returned unreadable nutrition data.']);
    exit;
}

$number = static function($v, $max) { $n = is_numeric($v) ? (float)$v : 0; return max(0, min($max, $n)); };
$result = [
    'food_item' => trim((string)($result['food_item'] ?? $foodText)),
    'quantity' => trim((string)($result['quantity'] ?? '')),
    'calories' => round($number($result['calories'] ?? 0, 10000), 1),
    'protein' => round($number($result['protein'] ?? 0, 1000), 1),
    'carbs' => round($number($result['carbs'] ?? 0, 1000), 1),
    'fat' => round($number($result['fat'] ?? 0, 1000), 1),
    'confidence' => round($number($result['confidence'] ?? 0, 100), 0),
    'notes' => trim((string)($result['notes'] ?? '')),
];

echo json_encode(['ok'=>true,'source'=>'ai','result'=>$result], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
