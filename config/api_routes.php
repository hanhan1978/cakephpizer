<?php
/**
 * API用のルート定義
 * 
 * このファイルはAPIエンドポイントのルート定義を管理します。
 */

use App\Controller\CakephpizeApiController;
use App\Core\ApiErrorHandler;
use App\Core\Logger\LoggerFactory;
use App\Service\Image\ImageProcessorService;
use Psr\Container\ContainerInterface;

/**
 * APIルートを設定する
 * 
 * @param \Slim\App $app Slimアプリケーションインスタンス
 * @return void
 */
function registerApiRoutes(\Slim\App $app): void
{
    // DI Containerを取得
    $container = $app->getContainer();
    
    // API専用のロガーを作成
    $apiLogger = LoggerFactory::createLogger('api');
    $container->set('api.logger', $apiLogger);

    // APIのルートグループ
    $app->group('/api', function ($group) use ($container) {
        // cakephpize APIエンドポイント
        $group->post('/cakephpize', function ($request, $response, $args) use ($container) {
            $controller = new CakephpizeApiController(
                $container->get('api.logger'),
                $container->get(ImageProcessorService::class)
            );
            return $controller($request, $response, $args);
        });
        
        // 将来的に他のAPIエンドポイントを追加する場合はここに記述
        // 例: $group->get('/status', [ApiController::class, 'getStatus']);
    });
    
    $debug = config('app.debug');
    $middleware = $app->addErrorMiddleware($debug, $debug, $debug);
    $apiErrorHandler = new ApiErrorHandler($container->get('api.logger'));
    $middleware->setDefaultErrorHandler($apiErrorHandler);
}