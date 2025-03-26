<?php
/**
 * LoggerFactory クラス
 * 
 * アプリケーションのロガーを生成するファクトリークラス
 */

declare(strict_types=1);

namespace App\Core\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Psr\Log\LoggerInterface;

/**
 * LoggerFactory
 * 
 * Monologロガーインスタンスを作成するファクトリークラス
 */
class LoggerFactory
{
    /**
     * アプリケーション用のロガーを作成
     * 
     * @param string $name ロガー名
     * @param Level $level ログレベル（デフォルトはデバッグ）
     * @param bool $enableLogging ロギングを有効にするかどうか（デフォルトは無効）
     * @return LoggerInterface ロガーインスタンス
     */
    public static function createLogger(
        string $name, 
        Level $level = Level::Debug,
        bool $enableLogging = false
    ): LoggerInterface {
        // ロガーの作成
        $logger = new Logger($name);
        
        // ロギングが有効な場合のみハンドラを追加
        if ($enableLogging) {
            // ログフォーマットの設定
            $dateFormat = "Y-m-d H:i:s";
            $output = "[%datetime%] %level_name%: %message% %context% %extra%\n";
            $formatter = new LineFormatter($output, $dateFormat, true, true);
            
            // 標準出力用ストリームハンドラの作成
            $stdoutHandler = new StreamHandler('php://stdout', $level);
            $stdoutHandler->setFormatter($formatter);
            
            // ハンドラの追加
            $logger->pushHandler($stdoutHandler);
        } else {
            // NullHandlerを追加（ログを破棄）
            $logger->pushHandler(new \Monolog\Handler\NullHandler());
        }
        
        return $logger;
    }
}