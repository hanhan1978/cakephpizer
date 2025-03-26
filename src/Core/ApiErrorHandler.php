<?php
/**
 * APIエラーハンドラクラス
 * 
 * API用のエラー処理を担当するクラス
 */

declare(strict_types=1);

namespace App\Core;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;
use Throwable;

/**
 * ApiErrorHandler クラス
 * 
 * API用のエラーハンドラ。JSONでエラー情報を返します。
 */
class ApiErrorHandler
{
    /**
     * @var ?LoggerInterface ロガーインスタンス
     */
    private ?LoggerInterface $logger;
    
    /**
     * コンストラクタ
     * 
     * @param LoggerInterface|null $logger ロガーインスタンス
     */
    public function __construct(?LoggerInterface $logger = null)
    {
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
        
        // JSONレスポンスの生成
        return $this->generateJsonResponse($statusCode, $errorMessage);
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
        $message = 'API エラー発生: ' . $exception->getMessage();
        
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
        // 開発環境でのみ詳細なエラーメッセージを表示
        if ($displayErrorDetails) {
            return $exception->getMessage();
        }
        
        // 本番環境では一般的なエラーメッセージを表示
        $code = $this->determineStatusCode($exception);
        
        // ステータスコードに応じた一般的なメッセージ
        switch ($code) {
            case 400:
                return 'リクエストの形式が正しくありません';
            case 401:
                return '認証が必要です';
            case 403:
                return 'アクセスが禁止されています';
            case 404:
                return 'リソースが見つかりません';
            case 405:
                return '許可されていないメソッドです';
            case 429:
                return 'リクエスト制限を超えました。しばらく待ってから再試行してください';
            case 500:
            default:
                return 'サーバーエラーが発生しました';
        }
    }
    
    /**
     * JSONレスポンスを生成する
     * 
     * @param int $statusCode HTTPステータスコード
     * @param string $message エラーメッセージ
     * @return ResponseInterface レスポンス
     */
    private function generateJsonResponse(int $statusCode, string $message): ResponseInterface
    {
        $response = new Response();
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withStatus($statusCode);
        
        $error = [
            'success' => false,
            'error' => $message,
            'status' => $statusCode
        ];
        
        $response->getBody()->write(json_encode($error));
        return $response;
    }
}