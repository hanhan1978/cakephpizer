<?php
/**
 * エラーハンドラクラス
 * 
 * アプリケーション全体のエラー処理を担当するクラス
 */

declare(strict_types=1);

namespace App\Core;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use Throwable;

/**
 * ErrorHandler クラス
 * 
 * Slim ミドルウェアとして使用できるエラーハンドラ
 */
class ErrorHandler
{
    /**
     * @var string エラーテンプレートのパス
     */
    private string $templatePath;
    
    /**
     * @var ?LoggerInterface ロガーインスタンス
     */
    private ?LoggerInterface $logger;
    
    /**
     * コンストラクタ
     * 
     * @param string $templatePath テンプレートのパス
     * @param LoggerInterface|null $logger ロガーインスタンス
     */
    public function __construct(string $templatePath, ?LoggerInterface $logger = null)
    {
        $this->templatePath = $templatePath;
        $this->logger = $logger;
    }
    
    /**
     * エラー発生時に呼び出される処理
     * 
     * @param ServerRequestInterface $request リクエスト
     * @param Throwable $exception 発生した例外
     * @param bool $displayErrorDetails エラー詳細を表示するかどうか
     * @param bool $logErrors エラーをログに記録するかどうか
     * @param bool $logErrorDetails エラーの詳細をログに記録するかどうか
     * @return ResponseInterface レスポンス
     */
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        // エラーログ記録
        if ($logErrors && $this->logger) {
            $this->logError($exception, $request, $logErrorDetails);
        }
        
        // エラーコードとメッセージの処理
        $statusCode = $this->determineStatusCode($exception);
        $errorMessage = $this->getErrorMessage($exception, $displayErrorDetails);
        
        // HTMLレスポンスの生成
        return $this->generateHtmlResponse($statusCode, $errorMessage);
    }
    
    /**
     * エラーをログに記録する
     * 
     * @param Throwable $exception 発生した例外
     * @param ServerRequestInterface $request リクエスト
     * @param bool $logErrorDetails エラーの詳細をログに記録するかどうか
     * @return void
     */
    private function logError(
        Throwable $exception,
        ServerRequestInterface $request,
        bool $logErrorDetails
    ): void {
        $message = 'エラーが発生しました: ' . $exception->getMessage();
        
        if ($logErrorDetails) {
            $message .= PHP_EOL . $exception->getTraceAsString();
            $message .= PHP_EOL . 'リクエストURI: ' . $request->getUri();
            $message .= PHP_EOL . 'HTTPメソッド: ' . $request->getMethod();
        }
        
        $this->logger->error($message);
    }
    
    /**
     * HTTPステータスコードを決定する
     * 
     * @param Throwable $exception 発生した例外
     * @return int HTTPステータスコード
     */
    private function determineStatusCode(Throwable $exception): int
    {
        $code = $exception->getCode();
        
        // 例外のコードが有効なHTTPステータスコードかどうかチェック
        if (is_int($code) && $code >= 400 && $code < 600) {
            return $code;
        }
        
        // デフォルトは500エラー
        return 500;
    }
    
    /**
     * エラーメッセージを取得する
     * 
     * @param Throwable $exception 発生した例外
     * @param bool $displayErrorDetails エラー詳細を表示するかどうか
     * @return string エラーメッセージ
     */
    private function getErrorMessage(Throwable $exception, bool $displayErrorDetails): string
    {
        if ($displayErrorDetails) {
            return $exception->getMessage();
        }
        
        $code = $this->determineStatusCode($exception);
        if ($code === 500) {
            return 'サーバーエラーが発生しました';
        }
        
        return $exception->getMessage() ?: 'エラーが発生しました';
    }
    
    /**
     * HTMLレスポンスを生成する
     * 
     * @param int $statusCode HTTPステータスコード
     * @param string $message エラーメッセージ
     * @return ResponseInterface レスポンス
     */
    private function generateHtmlResponse(int $statusCode, string $message): ResponseInterface
    {
        $twig = Twig::create($this->templatePath);
        $response = new \Slim\Psr7\Response();
        
        return $twig->render(
            $response->withStatus($statusCode),
            'error.tpl',
            [
                'code' => $statusCode,
                'message' => $message
            ]
        );
    }
}