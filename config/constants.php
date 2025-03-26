<?php
/**
 * アプリケーションの定数定義
 * 
 * このファイルにはアプリケーション全体で使用される定数を定義します。
 */

// 画像処理用の色定義
define('CAKEPHPER_BLUE', '#006CF3'); // ケーキPHPer青
define('SODAI_BLUE', '#0293df');     // そだいそー青 
define('BLACK', '#000000');          // 黒

// ファイル管理用の設定
define('IMAGE_TEMP_DIR', __DIR__ . '/../public/images'); // 一時画像保存ディレクトリ
define('IMAGE_EXPIRATION_TIME', 600);                    // 画像保持期間（秒）

// 顔検出用の設定
define('FACE_CASCADE_PATH', '/usr/local/share/opencv4/haarcascades/haarcascade_frontalface_default.xml');
define('PROFILE_CASCADE_PATH', '/usr/local/share/opencv4/haarcascades/haarcascade_profileface.xml');
define('EYE_CASCADE_PATH', '/usr/local/share/opencv4/haarcascades/haarcascade_eye.xml');

// フォント設定
define('DEFAULT_FONT_PATH', '/usr/share/fonts/truetype/noto/NotoSansCJK-Bold.ttc');
define('FALLBACK_FONT', 'Helvetica-Bold');