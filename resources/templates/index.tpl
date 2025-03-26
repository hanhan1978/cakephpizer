<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="/favicon.ico" type="image/x-icon" rel="icon"/><link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
    <meta name="author"/>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <title>Cakephpizer</title>
    <style>
        .cake-blue {
            background-color: #006CF3;
        }
        .soda-blue {
            background-color: #0293df;
        }
        .btn-cake {
            background-color: #006CF3;
            color: white;
            transition: all 0.3s;
        }
        .btn-cake:hover {
            background-color: #0056cc;
            transform: translateY(-2px);
        }
        .loader {
            border-top-color: #006CF3;
            -webkit-animation: spinner 1.5s linear infinite;
            animation: spinner 1.5s linear infinite;
        }
        @-webkit-keyframes spinner {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }
        @keyframes spinner {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto bg-white rounded-lg shadow-lg p-8">
            <div class="flex items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Cakephpizer</h1>
                <span class="ml-3 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-md">beta 0.1.0</span>
            </div>
            
            <div id="error-message" class="mb-4 p-3 bg-red-100 text-red-700 rounded-md hidden">
                {% if errorMessage != '' %}
                {{ errorMessage }}
                {% endif %}
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- 左側: フォーム -->
                <div class="space-y-6">
                    <form id="image-form" class="space-y-6">
                        <div class="space-y-2">
                            <label for="icon_url" class="block text-sm font-medium text-gray-700">画像URL</label>
                            <input type='text' id="icon_url" name='icon_url' class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder='https://example.com/image.png (PNG, JPG, GIF対応)' required />
                        </div>
                        
                        <div class="space-y-2">
                            <p class="block text-sm font-medium text-gray-700">カラースタイル</p>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type='radio' name='type' value='1' class="h-4 w-4 text-blue-600 style-radio" required checked />
                                    <span class="ml-2 cake-blue w-4 h-4 rounded-full inline-block"></span>
                                    <span class="ml-1">cakephpize (#006CF3)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type='radio' name='type' value='2' class="h-4 w-4 text-blue-400 style-radio" required />
                                    <span class="ml-2 soda-blue w-4 h-4 rounded-full inline-block"></span>
                                    <span class="ml-1">sodize (#0293df)</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <p class="block text-sm font-medium text-gray-700">画像オプション</p>
                            <div class="space-y-2">
                                <label class="inline-flex items-center">
                                    <input type='checkbox' id="inverse" name='inverse' value='true' class="h-4 w-4 text-blue-600" />
                                    <span class="ml-2">色を反転する</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <p class="block text-sm font-medium text-gray-700 border-b pb-2">文字入れ</p>
                            
                            <div class="space-y-2">
                                <label for="text_overlay" class="text-sm font-medium text-gray-700">テキスト</label>
                                <input type='text' id="text_overlay" name='text_overlay' class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                       placeholder='入れたい文字列（任意）' />
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label for="font_size" class="text-sm font-medium text-gray-700">フォントサイズ</label>
                                    <select id="font_size" name="font_size" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="12">小 (12pt)</option>
                                        <option value="24" selected>中 (24pt)</option>
                                        <option value="36">大 (36pt)</option>
                                        <option value="48">特大 (48pt)</option>
                                    </select>
                                </div>
                                
                                <div class="space-y-2">
                                    <label for="text_color" class="text-sm font-medium text-gray-700">文字色</label>
                                    <select id="text_color" name="text_color" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="white" selected>白</option>
                                        <option value="black">黒</option>
                                        <option value="red">赤</option>
                                        <option value="yellow">黄</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">テキスト位置</label>
                                <div class="grid grid-cols-3 gap-2 mt-1 text-center">
                                    <button type="button" class="text-position-btn py-2 px-3 border border-gray-300 rounded-md shadow-sm bg-gray-50 hover:bg-gray-100" data-position="top-left">左上</button>
                                    <button type="button" class="text-position-btn py-2 px-3 border border-gray-300 rounded-md shadow-sm bg-gray-50 hover:bg-gray-100" data-position="top-center">上</button>
                                    <button type="button" class="text-position-btn py-2 px-3 border border-gray-300 rounded-md shadow-sm bg-gray-50 hover:bg-gray-100" data-position="top-right">右上</button>
                                    
                                    <button type="button" class="text-position-btn py-2 px-3 border border-gray-300 rounded-md shadow-sm bg-gray-50 hover:bg-gray-100" data-position="middle-left">左</button>
                                    <button type="button" class="text-position-btn py-2 px-3 border border-gray-300 rounded-md shadow-sm bg-gray-50 hover:bg-gray-100 active" data-position="center">中央</button>
                                    <button type="button" class="text-position-btn py-2 px-3 border border-gray-300 rounded-md shadow-sm bg-gray-50 hover:bg-gray-100" data-position="middle-right">右</button>
                                    
                                    <button type="button" class="text-position-btn py-2 px-3 border border-gray-300 rounded-md shadow-sm bg-gray-50 hover:bg-gray-100" data-position="bottom-left">左下</button>
                                    <button type="button" class="text-position-btn py-2 px-3 border border-gray-300 rounded-md shadow-sm bg-gray-50 hover:bg-gray-100" data-position="bottom-center">下</button>
                                    <button type="button" class="text-position-btn py-2 px-3 border border-gray-300 rounded-md shadow-sm bg-gray-50 hover:bg-gray-100" data-position="bottom-right">右下</button>
                                </div>
                                <input type="hidden" name="text_position" id="text_position" value="center">
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="threshold" class="block text-sm font-medium text-gray-700">色閾値</label>
                            <input type='range' id="threshold" name='threshold' min="0.1" max="0.9" step="0.01" value='0.5' 
                                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" />
                            <div class="text-center text-sm text-gray-500" id="threshold-value">0.5</div>
                        </div>

                        <div class="space-y-4 pt-4 border-t">
                            <div class="flex items-center">
                                <input type='checkbox' id="enable_face_detect" name='enable_face_detect' value='true' class="h-4 w-4 text-blue-600" />
                                <label for="enable_face_detect" class="ml-2 block text-sm font-medium text-gray-700">顔検出モード（耳線描画）</label>
                            </div>
                            
                            <div id="face-detect-options" class="space-y-4 ml-6 hidden">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">左耳の調整</label>
                                        <div class="grid grid-cols-1 gap-2">
                                            <div>
                                                <label for="adjust_left_down" class="text-xs text-gray-500">垂直調整 (%)</label>
                                                <input type="number" id="adjust_left_down" name="adjust_left_down" 
                                                       value="4" min="-20" max="20" step="1"
                                                       class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm text-sm" />
                                            </div>
                                            <div>
                                                <label for="adjust_left_horizontal" class="text-xs text-gray-500">水平調整 (%)</label>
                                                <input type="number" id="adjust_left_horizontal" name="adjust_left_horizontal" 
                                                       value="0" min="-20" max="20" step="1"
                                                       class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm text-sm" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-gray-700">右耳の調整</label>
                                        <div class="grid grid-cols-1 gap-2">
                                            <div>
                                                <label for="adjust_right_down" class="text-xs text-gray-500">垂直調整 (%)</label>
                                                <input type="number" id="adjust_right_down" name="adjust_right_down" 
                                                       value="4" min="-20" max="20" step="1"
                                                       class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm text-sm" />
                                            </div>
                                            <div>
                                                <label for="adjust_right_horizontal" class="text-xs text-gray-500">水平調整 (%)</label>
                                                <input type="number" id="adjust_right_horizontal" name="adjust_right_horizontal" 
                                                       value="0" min="-20" max="20" step="1"
                                                       class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm text-sm" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">接合点の調整</label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="join_point_x" class="text-xs text-gray-500">水平位置 (%)</label>
                                            <input type="number" id="join_point_x" name="join_point_x" 
                                                   value="50" min="0" max="100" step="1"
                                                   class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm text-sm" />
                                        </div>
                                        <div>
                                            <label for="join_point_y" class="text-xs text-gray-500">垂直位置 (%)</label>
                                            <input type="number" id="join_point_y" name="join_point_y" 
                                                   value="90" min="0" max="100" step="1"
                                                   class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm text-sm" />
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <label for="join_bottom_curve" class="text-sm font-medium text-gray-700">下線曲線の調整</label>
                                    <input type="range" id="join_bottom_curve" name="join_bottom_curve" 
                                           value="0" min="-100" max="100" step="1"
                                           class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" />
                                    <div class="flex justify-between text-xs text-gray-500">
                                        <span>←左に曲がる (-100%)</span>
                                        <span>直線 (0%)</span>
                                        <span>右に曲がる (100%)→</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <button type='button' id="preview-button" class="w-full btn-cake py-2 px-4 rounded-md shadow-md font-semibold">
                                プレビューする
                            </button>
                        </div>

                    </form>
                </div>
                
                <!-- 右側: プレビュー -->
                <div class="space-y-4">
                    <div id="preview-container" class="hidden">
                        <h2 class="text-xl font-bold text-blue-600 mb-4" id="result-message"></h2>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <!-- 変換後の画像 -->
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 flex flex-col items-center">
                                <p class="text-sm text-gray-500 mb-2">変換後</p>
                                <!-- 動的高さのコンテナ -->
                                <div id="result-container" class="w-full min-h-64 relative flex justify-center overflow-hidden transition-all duration-300 ease-in-out">
                                    <!-- ローディング -->
                                    <div id="loading" class="hidden absolute inset-0 flex justify-center items-center bg-gray-100 min-h-64">
                                        <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12"></div>
                                    </div>
                                    <!-- プレースホルダー -->
                                    <div id="preview-placeholder" class="absolute inset-0 bg-gray-100 flex items-center justify-center min-h-64">
                                        <p class="text-gray-400">URLを入力してプレビューボタンをクリックしてください</p>
                                    </div>
                                    <!-- 結果画像 -->
                                    <div id="result-image-wrapper" class="hidden w-full flex items-center justify-center">
                                        <img id="result-image" class="max-w-full object-contain rounded shadow-sm" src="" alt="変換後の画像" />
                                    </div>
                                </div>
                                <!-- 操作エリア -->
                                <div class="mt-4 w-full flex flex-col items-center">
                                    <a id="download-link" href="#" download="" 
                                       class="btn-cake py-2 px-4 rounded-md shadow-md font-semibold inline-flex items-center hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        ダウンロード
                                    </a>
                                    <p class="text-xs text-red-500 mt-2 hidden" id="expiry-message">※画像は10分後に自動的に削除されます</p>
                                </div>
                            </div>
                            
                            <!-- オリジナル画像 -->
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 flex flex-col items-center">
                                <p class="text-sm text-gray-500 mb-2">オリジナル</p>
                                <!-- 動的高さのコンテナ -->
                                <div id="original-container" class="w-full min-h-64 relative flex justify-center overflow-hidden transition-all duration-300 ease-in-out">
                                    <!-- プレースホルダー -->
                                    <div id="original-placeholder" class="absolute inset-0 bg-gray-100 flex items-center justify-center min-h-64">
                                        <p class="text-gray-400">URLを入力するとオリジナル画像が表示されます</p>
                                    </div>
                                    <!-- オリジナル画像 -->
                                    <div id="original-image-wrapper" class="hidden w-full flex items-center justify-center">
                                        <img id="original-image" class="max-w-full object-contain rounded shadow-sm" src="" alt="オリジナル画像" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // フォーム要素
        const form = document.getElementById('image-form');
        const iconUrlInput = document.getElementById('icon_url');
        const thresholdSlider = document.getElementById('threshold');
        const thresholdValue = document.getElementById('threshold-value');
        const inverseCheckbox = document.getElementById('inverse');
        const styleRadios = document.querySelectorAll('.style-radio');
        const previewButton = document.getElementById('preview-button');
        
        // プレビュー要素
        const previewContainer = document.getElementById('preview-container');
        const previewPlaceholder = document.getElementById('preview-placeholder');
        const originalPlaceholder = document.getElementById('original-placeholder');
        const resultImageWrapper = document.getElementById('result-image-wrapper');
        const originalImageWrapper = document.getElementById('original-image-wrapper');
        const resultImage = document.getElementById('result-image');
        const originalImage = document.getElementById('original-image');
        const resultContainer = document.getElementById('result-container');
        const originalContainer = document.getElementById('original-container');
        const resultMessage = document.getElementById('result-message');
        const downloadLink = document.getElementById('download-link');
        const expiryMessage = document.getElementById('expiry-message');
        const loadingIndicator = document.getElementById('loading');
        const errorMessage = document.getElementById('error-message');
        
        // 閾値スライダー値の表示更新
        thresholdSlider.oninput = function() {
            thresholdValue.innerHTML = this.value;
            // 有効なURLが入力されている場合は自動的にプレビュー更新
            if (isValidImageUrl(iconUrlInput.value) && resultImage.src) {
                debounce(generatePreview, 500)();
            }
        }
        
        // デバウンス関数
        function debounce(func, wait) {
            let timeout;
            return function() {
                const context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }
        
        // URL入力時のプレビュー
        iconUrlInput.addEventListener('input', debounce(function(e) {
            const url = e.target.value;
            if (isValidImageUrl(url)) {
                showOriginalImage(url);
            } else {
                hideOriginalImage();
            }
        }, 300));
        
        // ラジオボタンやチェックボックスの変更時にプレビュー更新
        styleRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (isValidImageUrl(iconUrlInput.value) && resultImage.src) {
                    generatePreview();
                }
            });
        });
        
        inverseCheckbox.addEventListener('change', function() {
            if (isValidImageUrl(iconUrlInput.value) && resultImage.src) {
                generatePreview();
            }
        });
        
        // プレビューボタンクリック
        previewButton.addEventListener('click', function() {
            if (!isValidImageUrl(iconUrlInput.value)) {
                showError('正しい画像URLを入力してください。JPEG, GIF, PNG に対応しています。');
                return;
            }
            
            generatePreview();
        });
        
        
        // 画像URLバリデーション
        function isValidImageUrl(url) {
            const pattern = /^(https?:\/\/.*\.(jpeg|jpg|gif|png))$/i;
            return pattern.test(url);
        }
        
        // エラー表示（ローディング状態もリセット）
        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.classList.remove('hidden');
            loadingIndicator.classList.add('hidden');
            previewPlaceholder.classList.remove('hidden');
            resultImageWrapper.classList.add('hidden');
            console.error('エラー:', message);
            setTimeout(() => {
                errorMessage.classList.add('hidden');
            }, 5000);
        }
        
        // オリジナル画像表示
        function showOriginalImage(url) {
            originalImage.src = url;
            originalImage.onload = function() {
                // 画像が読み込まれたらラッパーを表示
                originalPlaceholder.classList.add('hidden');
                originalImageWrapper.classList.remove('hidden');
                // コンテナの高さを画像に合わせて調整
                adjustContainerHeight(originalContainer, originalImage);
            };
            originalImage.onerror = function() {
                hideOriginalImage();
                showError('画像の読み込みに失敗しました。URLを確認してください。');
            };
        }
        
        // オリジナル画像非表示
        function hideOriginalImage() {
            originalPlaceholder.classList.remove('hidden');
            originalImageWrapper.classList.add('hidden');
        }
        
        // コンテナの高さを画像に合わせて調整する関数
        function adjustContainerHeight(container, image) {
            // 画像の自然な高さをコンテナに設定（最小高さは確保）
            const imageHeight = Math.max(image.naturalHeight, 256);
            container.style.height = imageHeight + 'px';
        }
        
        // プレビュー生成
        function generatePreview() {
            const formData = new FormData(form);
            const url = iconUrlInput.value;
            
            // Inverse checkbox handling
            formData.set('inverse', inverseCheckbox.checked ? 'true' : 'false');
            
            // ローディング表示（ダウンロードボタンと通知は残す）
            previewContainer.classList.remove('hidden');
            previewPlaceholder.classList.add('hidden');
            resultImageWrapper.classList.add('hidden');
            loadingIndicator.classList.remove('hidden');
            
            // 顔検出モードのチェック状態を強制的にフォームデータに設定
            formData.set('enable_face_detect', document.getElementById('enable_face_detect').checked ? 'true' : 'false');
            
            // デバッグ用の情報をコンソールに出力
            console.log('フォームデータ:', {
                url: iconUrlInput.value,
                threshold: thresholdSlider.value,
                type: document.querySelector('input[name="type"]:checked').value,
                inverse: inverseCheckbox.checked,
                text_overlay: document.getElementById('text_overlay').value,
                text_position: document.getElementById('text_position').value,
                enable_face_detect: document.getElementById('enable_face_detect').checked,
                adjust_left_down: document.getElementById('adjust_left_down').value,
                adjust_left_horizontal: document.getElementById('adjust_left_horizontal').value,
                adjust_right_down: document.getElementById('adjust_right_down').value,
                adjust_right_horizontal: document.getElementById('adjust_right_horizontal').value,
                join_point_x: document.getElementById('join_point_x').value,
                join_point_y: document.getElementById('join_point_y').value,
                join_bottom_curve: document.getElementById('join_bottom_curve').value
            });
            
            // APIエンドポイントを使用
            fetch('/api/cakephpize', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // レスポンス本文をテキストとして取得
                return response.text().then(text => {
                    console.log('APIレスポンス:', text.substring(0, 100));
                    try {
                        const data = JSON.parse(text);
                        if (!response.ok) {
                            throw new Error(data.error || '画像の変換に失敗しました');
                        }
                        return data;
                    } catch (e) {
                        console.error('JSONパースエラー:', text.substring(0, 200));
                        throw new Error('サーバーからの応答が不正です: ' + text.substring(0, 50));
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    // 画像読み込み完了を待つ
                    const img = new Image();
                    img.onload = function() {
                        // 画像の読み込みが完了してから表示を切り替え
                        resultImage.src = data.imageUrl;
                        loadingIndicator.classList.add('hidden');
                        resultImageWrapper.classList.remove('hidden');
                        // コンテナの高さを調整
                        adjustContainerHeight(resultContainer, img);
                        // ダウンロードリンクを更新するだけで、表示状態は変えない
                        downloadLink.href = data.imageUrl;
                        downloadLink.download = data.filename;
                        
                        // もし初めてなら表示する
                        if (downloadLink.classList.contains('hidden')) {
                            downloadLink.classList.remove('hidden');
                            expiryMessage.classList.remove('hidden');
                        }
                        resultMessage.textContent = data.message;
                    };
                    img.onerror = function() {
                        throw new Error('生成された画像の読み込みに失敗しました');
                    };
                    img.src = data.imageUrl + '?t=' + new Date().getTime(); // キャッシュ回避
                } else {
                    throw new Error(data.error || '画像の変換に失敗しました');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError(error.message);
                
                // エラーメッセージを表示するだけ
            });
        }

        // テキスト位置選択の処理
        const textPositionButtons = document.querySelectorAll('.text-position-btn');
        const textPositionInput = document.getElementById('text_position');
        
        // ボタンがクリックされたときの処理
        textPositionButtons.forEach(button => {
            button.addEventListener('click', function() {
                // すべてのボタンからactiveクラスを削除
                textPositionButtons.forEach(btn => {
                    btn.classList.remove('bg-blue-100', 'text-blue-700', 'active');
                    btn.classList.add('bg-gray-50');
                });
                
                // クリックされたボタンにactiveクラスを追加
                this.classList.remove('bg-gray-50');
                this.classList.add('bg-blue-100', 'text-blue-700', 'active');
                
                // 隠しフィールドに値を設定
                textPositionInput.value = this.getAttribute('data-position');
                
                // すでにプレビューが表示されている場合は更新
                if (isValidImageUrl(iconUrlInput.value) && resultImage.src) {
                    generatePreview();
                }
            });
        });
        
        // 初期表示時
        if (iconUrlInput.value && isValidImageUrl(iconUrlInput.value)) {
            showOriginalImage(iconUrlInput.value);
        }
        
        // 初期状態でcenterボタンをアクティブに
        const centerButton = document.querySelector('[data-position="center"]');
        if (centerButton) {
            centerButton.classList.add('bg-blue-100', 'text-blue-700');
            centerButton.classList.remove('bg-gray-50');
        }

        // 顔検出モードの表示切り替え
        const enableFaceDetectCheckbox = document.getElementById('enable_face_detect');
        const faceDetectOptions = document.getElementById('face-detect-options');
        
        enableFaceDetectCheckbox.addEventListener('change', function() {
            if (this.checked) {
                faceDetectOptions.classList.remove('hidden');
            } else {
                faceDetectOptions.classList.add('hidden');
            }
            
            // すでにプレビューが表示されている場合は更新
            if (isValidImageUrl(iconUrlInput.value) && resultImage.src) {
                generatePreview();
            }
        });
        
        // 顔検出パラメータが変更された時のイベントリスナーを追加
        const faceDetectParams = [
            'adjust_left_down', 'adjust_left_horizontal', 
            'adjust_right_down', 'adjust_right_horizontal',
            'join_point_x', 'join_point_y'
        ];
        
        // 数値入力フィールドの変更検知
        faceDetectParams.forEach(paramId => {
            const paramInput = document.getElementById(paramId);
            if (paramInput) {
                paramInput.addEventListener('change', debounce(function() {
                    if (isValidImageUrl(iconUrlInput.value) && resultImage.src && enableFaceDetectCheckbox.checked) {
                        generatePreview();
                    }
                }, 500));
            }
        });
        
        // スライダーの変更検知
        const joinBottomCurveSlider = document.getElementById('join_bottom_curve');
        if (joinBottomCurveSlider) {
            joinBottomCurveSlider.addEventListener('input', debounce(function() {
                if (isValidImageUrl(iconUrlInput.value) && resultImage.src && enableFaceDetectCheckbox.checked) {
                    generatePreview();
                }
            }, 500));
        }
    </script>
</body>
</html>
