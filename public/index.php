<?php
/**
 * cakephpizer アプリケーションのフロントコントローラー
 * 
 * このファイルはウェブリクエストのエントリーポイントとして機能し、
 * アプリケーション初期化とルーティングを行います。
 */

declare(strict_types=1);

// アプリケーション初期化ファイルの読み込み
require_once __DIR__ . '/../src/Core/bootstrap.php';

// Slimアプリケーションの作成
$app = createApp();

// アプリを実行
$app->run();