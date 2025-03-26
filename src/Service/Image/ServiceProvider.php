<?php
/**
 * Image Service Provider
 * 
 * 画像処理関連サービスの生成と依存性注入を管理
 */

declare(strict_types=1);

namespace App\Service\Image;

use DI\Container;
use Psr\Log\LoggerInterface;
use App\Core\Logger\LoggerFactory;

/**
 * ServiceProvider
 * 
 * 画像処理関連のサービスをコンテナに登録するプロバイダクラス
 */
class ServiceProvider
{
    /**
     * サービスの登録
     * 
     * @param Container $container DIコンテナ
     * @return void
     */
    public static function register(Container $container): void
    {
        // 画像処理用専用ロガー
        $container->set('image.logger', function () {
            return LoggerFactory::createLogger('image_processor');
        });
        
        // 閾値処理サービス
        $container->set(ImageThresholdProcessor::class, function () {
            return new ImageThresholdProcessor();
        });
        
        // 顔検出サービス
        $container->set(FaceDetector::class, function () {
            return new FaceDetector();
        });
        
        // テキストオーバーレイサービス
        $container->set(TextOverlayProcessor::class, function () {
            return new TextOverlayProcessor();
        });
        
        // 耳描画サービス
        $container->set(EarDrawingProcessor::class, function () {
            return new EarDrawingProcessor();
        });
        
        // メインの画像処理サービス
        $container->set(ImageProcessorService::class, function (Container $c) {
            return new ImageProcessorService(
                $c->get(ImageThresholdProcessor::class),
                $c->get(FaceDetector::class),
                $c->get(TextOverlayProcessor::class),
                $c->get(EarDrawingProcessor::class),
                $c->get('image.logger')
            );
        });
    }
}