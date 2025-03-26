<?php
/**
 * アプリケーション初期化ファイル
 */

// オートローダーの読み込み
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} else {
    die('Composer のオートローダーが見つかりません。`composer install` を実行してください。');
}

// エラー設定（アプリケーション設定に基づいて動的に設定）
$appConfig = config('app');
$isDebugMode = $appConfig['debug'] ?? false;

if ($isDebugMode) {
    // 開発モードでのエラー設定
    error_reporting(E_ALL & ~E_DEPRECATED);
    ini_set('display_errors', '1');
} else {
    // 本番モードでのエラー設定
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
}

// アプリケーションの依存関係のインポート
use App\Core\Logger\LoggerFactory;
use App\Service\Image\ServiceProvider as ImageServiceProvider;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;

// 設定ファイルの読み込み
require_once __DIR__ . '/../../config/config.php';

/**
 * Slimアプリケーションを生成する
 * 
 * @return \Slim\App 設定済みのSlimアプリインスタンス
 */
function createApp(): \Slim\App
{
    // DIコンテナの作成
    $container = new Container();
    
    // アプリケーションロガーの設定
    $container->set(LoggerInterface::class, function () {
        $appConfig = config('app');
        $isDebugMode = $appConfig['debug'] ?? false;
        
        // デバッグモードの場合のみロギングを有効化
        return LoggerFactory::createLogger('app', \Monolog\Level::Debug, $isDebugMode);
    });
    
    // 各種サービスの登録
    registerServices($container);
    
    // コンテナを使用してアプリを作成
    AppFactory::setContainer($container);
    $app = AppFactory::create();
    
    // ベースパスを設定
    $app->setBasePath('');
    
    // ルーティングミドルウェアを追加
    $app->addRoutingMiddleware();
    
    // ルート設定の読み込み
    requireRoutes();
    
    // ルート定義を登録
    registerApiRoutes($app);
    registerRoutes($app);

    return $app;
}

/**
 * 全てのサービスをDIコンテナに登録
 *
 * @param Container $container DIコンテナ
 * @return void
 */
function registerServices(Container $container): void
{
    // 画像処理サービスを登録
    ImageServiceProvider::register($container);
    
    // 必要に応じて他のサービスプロバイダーを追加
}

/**
 * ルート定義ファイルを読み込む
 * 
 * @return void
 */
function requireRoutes(): void
{
    $configFiles = [
        // ルート定義を読み込む
        __DIR__ . '/../../config/routes.php',
        __DIR__ . '/../../config/api_routes.php',
    ];
    
    foreach ($configFiles as $file) {
        if (file_exists($file)) {
            require_once $file;
        }
    }
}