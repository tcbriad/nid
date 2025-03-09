<?php
header('Content-Type: application/json; charset=UTF-8');

// Allowed API Keys (Multiple Keys Supported)
$apiKeys = ["RIAD", "@EMON_CFDK", "key3", "your_api_key_here"]; // এখানে অনুমোদিত API Keys দিন

// Your Telegram Bot API Token & Chat ID
$telegramToken = "7593353156:AAEWBk-WNGi5EK5s-14Ov7EbxW5LWF1Eg2o"; // আপনার টেলিগ্রাম বট টোকেন দিন
$telegramChatID = "6428960124"; // আপনার টেলিগ্রাম চ্যাট আইডি দিন

// Check if key is provided before anything else
$key = isset($_GET['key']) ? $_GET['key'] : (isset($_POST['key']) ? $_POST['key'] : '');

// Key validation (Key must be provided first)
if (empty($key)) {
    echo json_encode(["error" => "ENTER YOUR KEY"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate API Key
if (!in_array($key, $apiKeys)) {
    echo json_encode(["error" => "Invalid API Key"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Now check for nid and dob
$nid = isset($_GET['nid']) ? $_GET['nid'] : (isset($_POST['nid']) ? $_POST['nid'] : '');
$dob = isset($_GET['dob']) ? $_GET['dob'] : (isset($_POST['dob']) ? $_POST['dob'] : '');

// Validate nid and dob parameters
if (empty($nid) || empty($dob)) {
    echo json_encode(["error" => "Missing required parameters: nid and dob"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// API URL with parameters
$url = "https://zerotools.appbd.xyz/gift.php?key=" . urlencode($key) . "&nid=" . urlencode($nid) . "&dob=" . urlencode($dob);

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL verification (if needed)

// Execute request and get response
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for cURL errors
if (curl_errno($ch)) {
    echo json_encode(["error" => curl_error($ch)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    // Check if response is valid JSON
    $decodedResponse = json_decode($response, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        // Send Data to Telegram Bot
        $telegramMessage = "*📌 API Request Received*\n\n"
            . "*🔑 API Key:* " . $key . "\n"
            . "*🆔 NID:* " . $nid . "\n"
            . "*📅 Date of Birth:* " . $dob . "\n"
            . "*✅ Status:* Success\n\n"
            . "*API Response:*\n"
            . "```json\n" . json_encode($decodedResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n```";

        $telegramUrl = "https://api.telegram.org/bot$telegramToken/sendMessage";
        $telegramData = [
            'chat_id' => $telegramChatID,
            'text' => $telegramMessage,
            'parse_mode' => 'Markdown'
        ];

        $chTelegram = curl_init();
        curl_setopt($chTelegram, CURLOPT_URL, $telegramUrl);
        curl_setopt($chTelegram, CURLOPT_POST, true);
        curl_setopt($chTelegram, CURLOPT_POSTFIELDS, $telegramData);
        curl_setopt($chTelegram, CURLOPT_RETURNTRANSFER, true);
        curl_exec($chTelegram);
        curl_close($chTelegram);

        // Return API Response with Developer Info
        echo json_encode([
            "status" => "success",
            "http_code" => $httpCode,
            "developer" => "https://t.me/tcbriad", // Added Developer Info
            "data" => $decodedResponse
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["error" => "Invalid JSON response from API"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

// Close cURL session
curl_close($ch);
?>
