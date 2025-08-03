@php
    // 包含邏輯檔案
    require_once 'C:\Users\wendyyao\Scenario_1_QA\config\qaprocess.php';
    $messages = getMessages();
@endphp

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>模擬題QA智能問答系統</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .chat-container {
            height: calc(100vh - 2rem);
        }
        
        .message-bubble {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .loading-dots {
            display: inline-flex;
            align-items: center;
        }
        
        .loading-dots::after {
            content: '';
            display: inline-block;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: currentColor;
            animation: dots 1.5s steps(5, end) infinite;
            margin-left: 4px;
        }
        
        @keyframes dots {
            0%, 20% { color: rgba(0,0,0,0.4); transform: scale(1); }
            40% { color: rgba(0,0,0,1); transform: scale(1.2); }
            60%, 100% { color: rgba(0,0,0,0.4); transform: scale(1); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="max-w-6xl mx-auto p-4 flex gap-4">
        <!-- 左側科室選擇面板 -->
        <div class="w-64 bg-white rounded-xl shadow-lg p-4 h-fit">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">選擇科室資料庫</h2>
            <div class="space-y-3">
                <div class="department-option">
                    <input type="radio" id="all" name="department" value="all" class="hidden peer" checked>
                    <label for="all" class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 peer-checked:bg-blue-100 peer-checked:border-blue-300 transition-all">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-3 opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                        <div>
                            <div class="font-medium text-gray-800">全部</div>
                            <div class="text-sm text-gray-500">模擬題相關資料</div>
                        </div>
                    </label>
                </div>
                <div class="department-option">
                    <input type="radio" id="service" name="department" value="service" class="hidden peer">
                    <label for="service" class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 peer-checked:bg-blue-100 peer-checked:border-blue-300 transition-all">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-3 opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                        <div>
                            <div class="font-medium text-gray-800">服務科</div>
                            <div class="text-sm text-gray-500">模擬題相關資料</div>
                        </div>
                    </label>
                </div>
                
                <div class="department-option">
                    <input type="radio" id="engineering" name="department" value="engineering" class="hidden peer">
                    <label for="engineering" class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 peer-checked:bg-blue-100 peer-checked:border-blue-300 transition-all">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-3 opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                        <div>
                            <div class="font-medium text-gray-800">工程科</div>
                            <div class="text-sm text-gray-500">模擬題相關資料</div>
                        </div>
                    </label>
                </div>
                
                <div class="department-option">
                    <input type="radio" id="planning" name="department" value="planning" class="hidden peer">
                    <label for="planning" class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 peer-checked:bg-blue-100 peer-checked:border-blue-300 transition-all">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-3 opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                        <div>
                            <div class="font-medium text-gray-800">企劃科</div>
                            <div class="text-sm text-gray-500">模擬題相關資料</div>
                        </div>
                    </label>
                </div>
                
                <div class="department-option">
                    <input type="radio" id="apartment" name="department" value="apartment" class="hidden peer">
                    <label for="apartment" class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 peer-checked:bg-blue-100 peer-checked:border-blue-300 transition-all">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-3 opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                        <div>
                            <div class="font-medium text-gray-800">公寓科</div>
                            <div class="text-sm text-gray-500">模擬題相關資料</div>
                        </div>
                    </label>
                </div>
            </div>
            
            <div class="mt-6 p-3 bg-blue-50 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm text-blue-700">當前選擇: <span id="currentDepartment" class="font-medium">全部科室</span></span>
                </div>
            </div>
        </div>
        
        <!-- 主要聊天區域 -->
        <div class="flex-1 chat-container bg-white rounded-xl shadow-lg flex flex-col">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 rounded-t-xl p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 003.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 00-3.09 3.09z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-semibold text-gray-800">模擬題QA問答視窗</h1>
                            <p class="text-sm text-gray-500">由 n8n QA Chain 驅動</p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="toggleSettings()" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </button>
                        <button onclick="clearChat()" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7H5m0 0l1.5-1.5M5 7l1.5 1.5M5 7v11a2 2 0 002 2h10a2 2 0 002-2V7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Settings Panel -->
                <div id="settingsPanel" class="mt-4 p-4 bg-gray-50 rounded-lg border hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        n8n Webhook URL
                    </label>
                    <input
                        type="url"
                        id="n8nUrl"
                        value="https://your-n8n-instance.com/webhook/qa-chain"
                        placeholder="https://your-n8n-instance.com/webhook/qa-chain"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        請輸入您的 n8n QA Chain webhook URL
                    </p>
                </div>
            </div>

            <!-- Messages Container -->
            <div id="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
                @foreach ($messages as $message)
                <div class="message-bubble flex items-start space-x-3 {{ $message['type'] === 'user' ? 'flex-row-reverse space-x-reverse' : '' }}">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 
                        {{ $message['type'] === 'user' 
                            ? 'bg-blue-500' 
                            : (isset($message['isError']) && $message['isError'] ? 'bg-red-500' : 'bg-gray-500') }}">
                        @if ($message['type'] === 'user')
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        @elseif (isset($message['isError']) && $message['isError'])
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 003.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 00-3.09 3.09z"></path>
                            </svg>
                        @endif
                    </div>

                    <div class="flex-1 max-w-xs sm:max-w-md lg:max-w-lg xl:max-w-xl {{ $message['type'] === 'user' ? 'text-right' : 'text-left' }}">
                        <div class="inline-block px-4 py-3 rounded-2xl 
                            {{ $message['type'] === 'user'
                                ? 'bg-blue-500 text-white'
                                : (isset($message['isError']) && $message['isError']
                                    ? 'bg-red-100 text-red-800 border border-red-200'
                                    : 'bg-white text-gray-800 shadow-sm border border-gray-200') }}">
                            <p class="prose prose-sm max-w-none">{!! $message['content'] !!}</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 px-1">
                            {{ date('H:i', strtotime($message['timestamp'])) }}
                        </p>
                    </div>
                </div>
                @endforeach

                <!-- Loading indicator -->
                <div id="loadingIndicator" class="hidden">
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 003.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 00-3.09 3.09z"></path>
                            </svg>
                        </div>
                        <div class="bg-white rounded-2xl px-4 py-3 shadow-sm border border-gray-200">
                            <div class="flex items-center space-x-2">
                                <div class="loading-dots text-gray-500 text-sm">正在思考中</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="bg-white border-t border-gray-200 rounded-b-xl p-4">
                <form id="messageForm" class="flex items-end space-x-3">
                    <div class="flex-1">
                        <textarea
                            id="messageInput"
                            placeholder="請輸入您的問題..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            rows="1"
                            style="min-height: 48px; max-height: 120px;"
                        ></textarea>
                    </div>
                    <button
                        type="submit"
                        id="sendButton"
                        class="px-4 py-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </form>
                
                <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                    <span>按 Enter 發送，Shift + Enter 換行</span>
                    <span class="flex items-center space-x-1">
                        <div id="connectionStatus" class="w-2 h-2 rounded-full bg-green-400"></div>
                        <span id="connectionText">已連接</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <script>
        let isLoading = false;

        // 自動調整 textarea 高度
        const messageInput = document.getElementById('messageInput');
        messageInput.addEventListener('input', function() {
            this.style.height = '48px';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // 處理表單提交
        document.getElementById('messageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            if (!isLoading) {
                sendMessage();
            }
        });

        // 處理 Enter 鍵
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!isLoading) {
                    sendMessage();
                }
            }
        });

        // 取得選中的科室
        function getSelectedDepartment() {
            const selectedRadio = document.querySelector('input[name="department"]:checked');
            return selectedRadio ? selectedRadio.value : 'service';
        }

        // 更新當前科室顯示
        function updateCurrentDepartment() {
            const selected = getSelectedDepartment();
            const departmentNames = {
                'service': '服務科',
                'engineering': '工程科', 
                'planning': '企劃科',
                'apartment': '公寓科'
            };
            document.getElementById('currentDepartment').textContent = departmentNames[selected];
        }

        // 監聽科室選擇變化
        document.querySelectorAll('input[name="department"]').forEach(radio => {
            radio.addEventListener('change', updateCurrentDepartment);
        });

        // 發送訊息
        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            setLoading(true);
            messageInput.value = '';
            messageInput.style.height = '48px';

            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('message', message);
            formData.append('department', getSelectedDepartment()); // 新增科室資訊
            formData.append('n8n_url', document.getElementById('n8nUrl').value);

            try {
                const response = await fetch('chat.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    updateMessages(data.messages);
                }
            } catch (error) {
                console.error('Error:', error);
                setConnectionStatus(false);
            } finally {
                setLoading(false);
            }
        }

        // 更新訊息顯示
        function updateMessages(messages) {
            const container = document.getElementById('messagesContainer');
            const loadingIndicator = document.getElementById('loadingIndicator');
            
            // 清空容器但保留載入指示器
            while (container.firstChild && container.firstChild !== loadingIndicator) {
                container.removeChild(container.firstChild);
            }

            messages.forEach(message => {
                const messageEl = createMessageElement(message);
                container.insertBefore(messageEl, loadingIndicator);
            });

            scrollToBottom();
        }

        // 創建訊息元素
        function createMessageElement(message) {
            const div = document.createElement('div');
            div.className = `message-bubble flex items-start space-x-3 ${message.type === 'user' ? 'flex-row-reverse space-x-reverse' : ''}`;
            
            const iconClass = message.type === 'user' 
                ? 'bg-blue-500' 
                : (message.isError ? 'bg-red-500' : 'bg-gray-500');
                
            const bubbleClass = message.type === 'user'
                ? 'bg-blue-500 text-white'
                : (message.isError 
                    ? 'bg-red-100 text-red-800 border border-red-200'
                    : 'bg-white text-gray-800 shadow-sm border border-gray-200');

            const time = new Date(message.timestamp).toLocaleTimeString('zh-TW', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });

            div.innerHTML = `
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 ${iconClass}">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${message.type === 'user' 
                            ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>'
                            : message.isError
                                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 003.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 00-3.09 3.09z"></path>'
                        }
                    </svg>
                </div>
                <div class="flex-1 max-w-xs sm:max-w-md lg:max-w-lg xl:max-w-xl ${message.type === 'user' ? 'text-right' : 'text-left'}">
                    <div class="inline-block px-4 py-3 rounded-2xl ${bubbleClass}">
                        <p class="whitespace-pre-wrap break-words">${escapeHtml(message.content)}</p>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 px-1">${time}</p>
                </div>
            `;

            return div;
        }

        // 設定載入狀態
        function setLoading(loading) {
            isLoading = loading;
            const loadingIndicator = document.getElementById('loadingIndicator');
            const sendButton = document.getElementById('sendButton');
            const connectionStatus = document.getElementById('connectionStatus');
            const connectionText = document.getElementById('connectionText');

            if (loading) {
                loadingIndicator.classList.remove('hidden');
                sendButton.disabled = true;
                connectionStatus.className = 'w-2 h-2 rounded-full bg-yellow-400';
                connectionText.textContent = '連接中';
                scrollToBottom();
            } else {
                loadingIndicator.classList.add('hidden');
                sendButton.disabled = false;
                connectionStatus.className = 'w-2 h-2 rounded-full bg-green-400';
                connectionText.textContent = '已連接';
                messageInput.focus();
            }
        }

        // 設定連接狀態
        function setConnectionStatus(connected) {
            const connectionStatus = document.getElementById('connectionStatus');
            const connectionText = document.getElementById('connectionText');
            
            if (connected) {
                connectionStatus.className = 'w-2 h-2 rounded-full bg-green-400';
                connectionText.textContent = '已連接';
            } else {
                connectionStatus.className = 'w-2 h-2 rounded-full bg-red-400';
                connectionText.textContent = '連接失敗';
            }
        }

        // 滾動到底部
        function scrollToBottom() {
            const container = document.getElementById('messagesContainer');
            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 100);
        }

        // 切換設定面板
        function toggleSettings() {
            const panel = document.getElementById('settingsPanel');
            panel.classList.toggle('hidden');
        }

        // 清除聊天記錄
        async function clearChat() {
            if (confirm('確定要清除所有聊天記錄嗎？')) {
                const formData = new FormData();
                formData.append('action', 'clear_chat');

                try {
                    const response = await fetch('/config/qaprocess.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();
                    if (data.success) {
                        updateMessages(data.messages);
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        }

        // HTML 跳脫
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // 頁面載入完成後聚焦輸入框
        document.addEventListener('DOMContentLoaded', function() {
            messageInput.focus();
            scrollToBottom();
        });
    </script>
</body>
</html>