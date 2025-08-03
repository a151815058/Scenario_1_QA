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
        $outputText = flattenHtmlContent($finalText);
        return [
            'content' => $outputText ?? '抱歉，n8n 未提供明確的回應。',
            'isError' => false
        ];
    }
}

function flattenHtmlContent($html) {
    // 移除 script 和 style 等不必要標籤
    $html = preg_replace('#<(script|style)\b[^>]*>(.*?)</\1>#is', '', $html);

    // 替換常見區塊標籤為換行
    $replacements = [
        '<hr />' => "\n------------------------------\n",
        '<hr>'   => "\n------------------------------\n",
        '</p>'   => "</p>\n",
        '</div>' => "</div>\n",
        '</h3>'  => "</h3>\n",
        '</h2>'  => "</h2>\n",
        '</li>'  => "\n - ",    // 每個 li 開頭加項目符號
        '<li>'   => "",         // 清除開頭
        '<ul>'   => "\n",
        '</ul>'  => "\n",
    ];

    // 套用替換
    $html = str_replace(array_keys($replacements), array_values($replacements), $html);

    // 移除所有其他 HTML 標籤，但保留 <a>
    $html = preg_replace_callback('#<a href="(.*?)">(.*?)</a>#i', function ($match) {
        return $match[2] . '（' . $match[1] . '）';
    }, $html);

    // 移除剩餘標籤
    $html = strip_tags($html);

    // 清除多餘空行
    $html = preg_replace("/\n{2,}/", "\n\n", $html);

    return trim($html);
}



// 如果不是 AJAX 請求，返回訊息供前端使用
function getMessages() {
    return $_SESSION['messages'];
}
?>