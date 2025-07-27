<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>模擬題上傳至向量資料庫</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .form-select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            color: #333;
            background-color: #fff;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 45px;
        }

        .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
            cursor: pointer;
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-display {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            border: 2px dashed #e1e5e9;
            border-radius: 12px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            min-height: 120px;
            flex-direction: column;
            gap: 10px;
        }

        .file-input-display:hover {
            border-color: #667eea;
            background-color: #f0f4ff;
        }

        .file-input:focus + .file-input-display {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .file-icon {
            width: 48px;
            height: 48px;
            color: #667eea;
        }

        .file-text {
            color: #666;
            font-size: 16px;
            text-align: center;
        }

        .file-selected {
            color: #667eea;
            font-weight: 500;
        }

        .submit-btn {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* 狀態訊息樣式 */
        .message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
            display: none;
            animation: slideIn 0.3s ease;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        .message.show {
            display: block;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 進度條樣式 */
        .progress-container {
            width: 100%;
            height: 6px;
            background-color: #e1e5e9;
            border-radius: 3px;
            margin-top: 15px;
            overflow: hidden;
            display: none;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 3px;
            transition: width 0.3s ease;
            width: 0%;
        }

        .upload-stats {
            display: none;
            margin-top: 10px;
            font-size: 14px;
            color: #666;
            text-align: center;
        }

        /* 彈跳視窗樣式 */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            animation: modalFadeIn 0.3s ease;
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease;
            position: relative;
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }

        .modal-icon.success {
            color: #28a745;
        }

        .modal-icon.error {
            color: #dc3545;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .modal-message {
            font-size: 16px;
            color: #666;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        .modal-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 100px;
        }

        .modal-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: #333;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
                margin: 10px;
            }

            .header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📚 模擬題上傳</h2>
            <p>請選擇科室並上傳 PDF 檔案</p>
        </div>
        
        <form action="http://localhost:5678/webhook-test/upload-vector" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="division" class="form-label">選擇科室</label>
                <select name="division" id="division" class="form-select" required>
                    <option value="">請選擇科室...</option>
                    <option value="服務科-SDT_FQA_Data" >服務科</option>
                    <option value="工程科-EDT_FQA_Data" >工程科</option>
                    <option value="公寓科-PDT_FQA_Data" >公寓科</option>
                    <option value="企劃科-DDT_FQA_Data">企劃科</option>
                </select>
            </div>

            <div class="form-group">
                <label for="pdf" class="form-label">選擇 PDF 檔案</label>
                <div class="file-input-wrapper">
                    <input type="file" name="pdf_file" id="pdf" class="file-input" accept="application/pdf" required>
                    <div class="file-input-display">
                        <svg class="file-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <div class="file-text">
                            <span id="file-text">點擊選擇 PDF 檔案或拖拽至此</span>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" id="submitBtn" class="submit-btn">
                📤 上傳並送出
            </button>
            
            <!-- 進度條 -->
            <div class="progress-container" id="progressContainer">
                <div class="progress-bar" id="progressBar"></div>
            </div>
            <div class="upload-stats" id="uploadStats"></div>
        </div>
    </div>

    <!-- 彈跳視窗 -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="modal-close" id="modalClose">&times;</span>
            <div class="modal-header">
                <span id="modalIcon" class="modal-icon"></span>
                <div id="modalTitle" class="modal-title"></div>
            </div>
            <div id="modalMessage" class="modal-message"></div>
            <button id="modalButton" class="modal-button">確定</button>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('pdf');
        const fileText = document.getElementById('file-text');
        const divisionSelect = document.getElementById('division');
        const submitBtn = document.getElementById('submitBtn');
        const messageDiv = document.getElementById('message');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const uploadStats = document.getElementById('uploadStats');
        const modal = document.getElementById('modal');
        const modalIcon = document.getElementById('modalIcon');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalButton = document.getElementById('modalButton');
        const modalClose = document.getElementById('modalClose');
        
        // N8N API 端點
        const N8N_API_URL = 'http://localhost:5678/webhook-test/upload-vector';
        
        // 顯示彈跳視窗
        function showModal(title, message, type = 'success') {
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            
            if (type === 'success') {
                modalIcon.textContent = '✅';
                modalIcon.className = 'modal-icon success';
            } else {
                modalIcon.textContent = '❌';
                modalIcon.className = 'modal-icon error';
            }
            
            modal.style.display = 'block';
            
            // 讓按鈕獲得焦點，方便用 Enter 鍵關閉
            setTimeout(() => modalButton.focus(), 100);
        }
        
        // 關閉彈跳視窗
        function closeModal() {
            modal.style.display = 'none';
        }
        
        // 顯示訊息的函式 (保留原有的訊息顯示，錯誤時使用)
        function showMessage(text, type = 'success') {
            if (type === 'success') {
                // 成功訊息使用彈跳視窗
                showModal('上傳成功', text.replace('✅ ', ''), 'success');
            } else {
                // 錯誤訊息保持原有顯示方式
                messageDiv.textContent = text;
                messageDiv.className = `message ${type} show`;
                setTimeout(() => {
                    messageDiv.classList.remove('show');
                }, 5000);
            }
        }
        
        // 重置表單狀態
        function resetForm() {
            submitBtn.disabled = false;
            submitBtn.textContent = '📤 上傳並送出';
            progressContainer.style.display = 'none';
            uploadStats.style.display = 'none';
            progressBar.style.width = '0%';
        }
        
        // 驗證表單
        function validateForm() {
            if (!divisionSelect.value) {
                showMessage('請選擇科室', 'error');
                return false;
            }
            
            if (!fileInput.files.length) {
                showMessage('請選擇 PDF 檔案', 'error');
                return false;
            }
            
            const file = fileInput.files[0];
            if (file.type !== 'application/pdf') {
                showMessage('請選擇 PDF 格式的檔案', 'error');
                return false;
            }
            
            // 檢查檔案大小 (例如限制 50MB)
            const maxSize = 50 * 1024 * 1024; // 50MB
            if (file.size > maxSize) {
                showMessage('檔案大小不能超過 50MB', 'error');
                return false;
            }
            
            return true;
        }
        
        // 檔案選擇事件
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const fileName = e.target.files[0].name;
                const fileSize = (e.target.files[0].size / 1024 / 1024).toFixed(2);
                fileText.textContent = `已選擇：${fileName} (${fileSize} MB)`;
                fileText.classList.add('file-selected');
                
                // 清除之前的訊息
                messageDiv.classList.remove('show');
            } else {
                fileText.textContent = '點擊選擇 PDF 檔案或拖拽至此';
                fileText.classList.remove('file-selected');
            }
        });

        // 上傳檔案到 N8N API
        async function uploadToN8N() {
            if (!validateForm()) {
                return;
            }
            
            // 準備 FormData
            const formData = new FormData();
            formData.append('division', divisionSelect.value);
            formData.append('pdf_file', fileInput.files[0]);
            
            // 更新按鈕狀態
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ 準備上傳...';
            
            // 顯示進度條
            progressContainer.style.display = 'block';
            uploadStats.style.display = 'block';
            
            try {
                // 使用 XMLHttpRequest 來追蹤上傳進度
                const xhr = new XMLHttpRequest();
                
                // 建立 Promise 來處理 XMLHttpRequest
                const uploadPromise = new Promise((resolve, reject) => {
                    // 上傳進度事件
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            progressBar.style.width = percentComplete + '%';
                            uploadStats.textContent = `上傳進度：${Math.round(percentComplete)}% (${(e.loaded / 1024 / 1024).toFixed(2)} MB / ${(e.total / 1024 / 1024).toFixed(2)} MB)`;
                            
                            if (percentComplete === 100) {
                                submitBtn.textContent = '🔄 處理中...';
                                uploadStats.textContent = '檔案上傳完成，正在處理中...';
                            }
                        }
                    });
                    
                    // 請求完成事件
                    xhr.addEventListener('load', function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            resolve(xhr.responseText);
                        } else {
                            reject(new Error(`HTTP ${xhr.status}: ${xhr.statusText}`));
                        }
                    });
                    
                    // 網路錯誤事件
                    xhr.addEventListener('error', function() {
                        reject(new Error('網路連線錯誤'));
                    });
                    
                    // 超時事件
                    xhr.addEventListener('timeout', function() {
                        reject(new Error('上傳超時'));
                    });
                });
                
                // 設定並發送請求
                xhr.open('POST', N8N_API_URL);
                xhr.timeout = 300000; // 5分鐘超時
                xhr.send(formData);
                
                // 等待上傳完成
                const responseText = await uploadPromise;
                
                // 處理成功回應
                let successMessage = '✅ 檔案已成功上傳並處理完成！';
                
                try {
                    const response = JSON.parse(responseText);
                    if (response.message) {
                        successMessage = `✅ ${response.message}`;
                    }
                } catch (e) {
                    // 如果回應不是 JSON 格式，使用預設訊息
                    console.log('Server response:', responseText);
                }
                
                showMessage(successMessage, 'success');
                
                // 重置表單
                divisionSelect.value = '';
                fileInput.value = '';
                fileText.textContent = '點擊選擇 PDF 檔案或拖拽至此';
                fileText.classList.remove('file-selected');
                
            } catch (error) {
                // 錯誤處理
                let errorMessage = '❌ 上傳失敗，請稍後再試';
                
                if (error.message.includes('網路連線錯誤')) {
                    errorMessage = '❌ 網路連線錯誤，請檢查網路連線後重試';
                } else if (error.message.includes('上傳超時')) {
                    errorMessage = '❌ 上傳超時，請檢查網路連線或稍後再試';
                } else if (error.message.includes('HTTP 413')) {
                    errorMessage = '❌ 檔案太大，請選擇較小的檔案';
                } else if (error.message.includes('HTTP 415')) {
                    errorMessage = '❌ 不支援的檔案格式，請上傳 PDF 檔案';
                } else if (error.message.includes('HTTP 5')) {
                    errorMessage = '❌ 伺服器錯誤，請稍後再試';
                }
                
                // 嘗試解析錯誤回應中的詳細訊息
                try {
                    if (error.message.includes('HTTP')) {
                        // 這裡可能需要從 xhr.responseText 中解析錯誤訊息
                        // 但在 catch 區塊中我們沒有 xhr 物件，所以保持簡單的錯誤處理
                    }
                } catch (e) {
                    // 忽略解析錯誤
                }
                
                showMessage(errorMessage, 'error');
                console.error('Upload error:', error);
            } finally {
                // 重置按鈕狀態
                resetForm();
            }
        }

        // 按鈕點擊事件
        submitBtn.addEventListener('click', uploadToN8N);
        
        // 彈跳視窗事件監聽
        modalButton.addEventListener('click', closeModal);
        modalClose.addEventListener('click', closeModal);
        
        // 點擊視窗外部關閉彈跳視窗
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
        
        // 支援 Enter 鍵提交和關閉彈跳視窗
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                if (modal.style.display === 'block') {
                    closeModal();
                } else if (!submitBtn.disabled) {
                    uploadToN8N();
                }
            } else if (e.key === 'Escape' && modal.style.display === 'block') {
                closeModal();
            }
        });
        
        // 拖拽支援
        const fileInputDisplay = document.querySelector('.file-input-display');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileInputDisplay.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            fileInputDisplay.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileInputDisplay.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight(e) {
            fileInputDisplay.style.borderColor = '#667eea';
            fileInputDisplay.style.backgroundColor = '#f0f4ff';
        }
        
        function unhighlight(e) {
            fileInputDisplay.style.borderColor = '#e1e5e9';
            fileInputDisplay.style.backgroundColor = '#f8f9fa';
        }
        
        fileInputDisplay.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                // 觸發 change 事件
                fileInput.dispatchEvent(new Event('change'));
            }
        }
    </script>
</body>
</html>