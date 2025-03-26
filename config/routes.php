<?php
/**
 * アプリケーションのルート定義
 * 
 * このファイルはSlimフレームワークのルート定義を管理します。
 */

use App\Controller\HomeController;

/**
 * アプリケーションのルートを設定する
 * 
 * @param \Slim\App $app Slimアプリケーションインスタンス
 * @return void
 */
function registerRoutes(\Slim\App $app): void
{
    // アプリケーション設定の取得
    $debug = config('app.debug');
    $templatePath = config('templates.path');
    // トップページのルート
    $app->get('/', HomeController::class);
    $middleware = $app->addErrorMiddleware($debug, $debug, $debug);

    // ロガーの作成
    $logger = \App\Core\Logger\LoggerFactory::createLogger('app');

    // HTMLエラーハンドラ（通常のウェブページ用）
    $errorHandler = new \App\Core\ErrorHandler($templatePath, $logger);
    $middleware->setDefaultErrorHandler($errorHandler);
}