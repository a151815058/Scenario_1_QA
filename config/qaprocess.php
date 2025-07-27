<?php
session_start();

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
        $n8nUrl = 'https://255328a44752.ngrok-free.app/webhook/qaproccess';
        
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
    // 科室對應的向量資料庫配置
    $departmentConfig = [
        'service' => [
            'name' => '服務科',
            'database' => 'service_vector_db',
            'collection' => 'SDT_FQA_Data'
        ],
        'engineering' => [
            'name' => '工程科', 
            'database' => 'engineering_vector_db',
            'collection' => 'EDT_FQA_Data'
        ],
        'planning' => [
            'name' => '企劃科',
            'database' => 'planning_vector_db', 
            'collection' => 'DDT_FQA_Data'
        ],
        'apartment' => [
            'name' => '公寓科',
            'database' => 'apartment_vector_db',
            'collection' => 'PDT_FQA_Data'
        ]
    ];
    
    $selectedDept = $departmentConfig[$department] ?? $departmentConfig['service'];
    
    $postData = json_encode([
        'question' => $question,
        'department' => $department,
        'departmentName' => $selectedDept['name'],
        'vectorDatabase' => $selectedDept['database'],
        'collection' => $selectedDept['collection'],
        'timestamp' => date('c'),
        'sessionId' => 'session_' . session_id()
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n" .
                       "Content-Length: " . strlen($postData) . "\r\n",
            'content' => $postData,
            'timeout' => 30
        ]
    ]);
    
    $response = @file_get_contents($n8nUrl, false, $context);
    
    if ($response === false) {
        return [
            'content' => '抱歉，連接 n8n 服務時發生錯誤。請檢查網址設定或稍後再試。',
            'isError' => true
        ];
    }
    
    $data = json_decode($response, true);
    if (!$data) {
        return [
            'content' => '抱歉，無法解析服務回應。',
            'isError' => true
        ];
    }
    
    $answer = $data['answer'] ?? $data['response'] ?? $data['result'] ?? '抱歉，無法獲得回應';
    
    return [
        'content' => $answer,
        'isError' => false
    ];
}

// 如果不是 AJAX 請求，返回訊息供前端使用
function getMessages() {
    return $_SESSION['messages'];
}
?>