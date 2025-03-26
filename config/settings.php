<?php
/**
 * アプリケーション設定ファイル
 * 
 * このファイルにはアプリケーションの設定値を定義します。
 * 環境によって異なる設定は.envファイルに定義することを推奨します。
 */

return [
    // アプリケーション全般設定
    'app' => [
        'name' => 'cakephpizer',
        'version' => '1.0.0',
        'debug' => false,  // 開発環境ではtrue、本番環境ではfalse
    ],
    
    // 画像処理設定
    'image' => [
        'max_width' => 800,
        'max_height' => 800,
        'default_threshold' => 0.5,
        'allowed_formats' => ['jpg', 'jpeg', 'png', 'gif'],
        'default_text_size' => 24,
        'default_text_position' => 'center',
        'default_text_color' => 'white',
    ],
    
    // テンプレート設定
    'templates' => [
        'path' => __DIR__ . '/../resources/templates',
        'cache' => __DIR__ . '/../var/cache/templates',  // 本番環境用キャッシュ設定
    ],
];