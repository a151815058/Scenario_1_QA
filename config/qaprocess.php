<?php
session_start();

use League\CommonMark\CommonMarkConverter;

require __DIR__ . '/../vendor/autoload.php';


// 初始化聊天記錄
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [
        [
            'id' => 1,
            'type' => 'bot',
            'content' => '您好！我是您的智能助手，有什麼問題想要詢問嗎？',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ];
}

// 處理 AJAX 請求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'send_message') {
        $userMessage = trim($_POST['message']);
        $department = $_POST['department'] ?? 'service'; // 新增科室參數
        $n8nUrl = 'https://n8n.geosense.tw/webhook-test/qaproccess';
        
        if (!empty($userMessage)) {
            // 添加用戶訊息
            $userMsgId = time();
            $_SESSION['messages'][] = [
                'id' => $userMsgId,
                'type' => 'user',
                'content' => $userMessage,
                'timestamp' => date('Y-m-d H:i:s'),
                'department' => $department // 記錄使用的科室
            ];
            
            // 發送到 n8n
            $response = sendToN8N($userMessage, $department, $n8nUrl);
            
            // 添加機器人回應
            $_SESSION['messages'][] = [
                'id' => $userMsgId + 1,
                'type' => 'bot',
                'content' => $response['content'],
                'timestamp' => date('Y-m-d H:i:s'),
                'isError' => $response['isError'],
                'department' => $department
            ];
        }
        
        echo json_encode(['success' => true, 'messages' => $_SESSION['messages']]);
        exit;
    }
    
    if ($_POST['action'] === 'get_messages') {
        echo json_encode(['messages' => $_SESSION['messages']]);
        exit;
    }
    
    if ($_POST['action'] === 'clear_chat') {
        $_SESSION['messages'] = [
            [
                'id' => time(),
                'type' => 'bot',
                'content' => '聊天記錄已清除。有什麼新問題想要詢問嗎？',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
        echo json_encode(['success' => true, 'messages' => $_SESSION['messages']]);
        exit;
    }
}

// 發送訊息到 n8n 的函數
function sendToN8N($question, $department, $n8nUrl) {
    $postData = json_encode([
        'question' => $question,
        'department' => $department,
        'timestamp' => date('c'),
        'sessionId' => 'session_' . session_id()
    ]);
    
    $ch = curl_init($n8nUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return [
            'content' => '抱歉，無法連接到 n8n 服務。',
            'isError' => true
        ];
    }

    // 嘗試解析最外層 JSON
    $responseData = json_decode($response, true);
    if (!$responseData) {
        return [
            'content' => '抱歉，n8n 回應格式錯誤。',
            'isError' => true
        ];
    }

    // 如果 content 本身是 JSON 字串，需要再次解碼
    $finalText = $responseData['output'] ?? $responseData['content'] ?? null;

    if ($finalText && is_string($finalText)) {
        $converter = new CommonMarkConverter();
        $finalText =(string) $converter->convertToHtml($finalText);
        return [
            'content' => $finalText ?? '抱歉，n8n 未提供明確的回應。',
            'isError' => false
        ];
    }
}



// 如果不是 AJAX 請求，返回訊息供前端使用
function getMessages() {
    return $_SESSION['messages'];
}
?>