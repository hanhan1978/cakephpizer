<?php
/**
 * Cakephpizerコントローラー
 * 
 * 画像処理APIエンドポイントのコントローラー
 */

declare(strict_types=1);

namespace App\Controller;

use App\Service\Image\ImageProcessorService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * CakephpizeApiController
 * 
 * 画像変換処理を行うAPIエンドポイントのコントローラー
 */
class CakephpizeApiController
{
    /**
     * @var LoggerInterface ロガーインスタンス
     */
    private LoggerInterface $logger;
    
    /**
     * @var ImageProcessorService 画像処理サービス
     */
    private ImageProcessorService $imageProcessor;

    /**
     * コンストラクタ
     * 
     * @param LoggerInterface $logger ロガーインスタンス
     * @param ImageProcessorService $imageProcessor 画像処理サービス
     */
    public function __construct(LoggerInterface $logger, ImageProcessorService $imageProcessor)
    {
        $this->logger = $logger;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * コントローラーの実行
     * 
     * シングルアクションコントローラーの呼び出しメソッド
     * 
     * @param Request $request リクエスト
     * @param Response $response レスポンス
     * @param array $args ルートパラメータ
     * @return Response JSONレスポンス
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Content-Type ヘッダーを設定
        $response = $response->withHeader('Content-Type', 'application/json');
        
        $params = (array)$request->getParsedBody();
        
        // 基本パラメータの取得と検証
        $threshold = is_numeric($params['threshold'] ?? 0.5) ? (float)$params['threshold'] : 0.5;
        $iconUrl = $params['icon_url'] ?? '';
        
        // URL検証 - セキュリティ上の理由でhttpとhttpsのみ許可
        if (empty($iconUrl) || !filter_var($iconUrl, FILTER_VALIDATE_URL)) {
            $errorResponse = json_encode([
                'success' => false,
                'error' => '有効な画像URLを入力してください'
            ]);
            $response->getBody()->write($errorResponse);
            return $response->withStatus(400);
        }
        
        // URL scheme check
        $urlParts = parse_url($iconUrl);
        if (!isset($urlParts['scheme']) || !in_array($urlParts['scheme'], ['http', 'https'])) {
            $errorResponse = json_encode([
                'success' => false,
                'error' => 'URLはhttpまたはhttpsで始まる必要があります'
            ]);
            $response->getBody()->write($errorResponse);
            return $response->withStatus(400);
        }
        
        // 拡張子チェック（ただしImagickは実際のファイル内容に基づいて処理するため厳密な検証は不要）
        $extensionPattern = '/\.(png|gif|jpg|jpeg)\z/iu';
        if (!preg_match($extensionPattern, strtolower($iconUrl))) {
            $errorResponse = json_encode([
                'success' => false,
                'error' => '画像URLはJPEG、GIF、PNGのいずれかである必要があります'
            ]);
            $response->getBody()->write($errorResponse);
            return $response->withStatus(400);
        }
        
        // 各種パラメータの処理
        $type = in_array($params['type'] ?? '1', ['1', '2']) ? (int)$params['type'] : 1;
        $inverse = isset($params['inverse']) && ($params['inverse'] === 'true' || $params['inverse'] === '1');
        
        // テキストオーバーレイのパラメータ
        $textOverlay = trim($params['text_overlay'] ?? '');
        $fontSize = max(12, min(72, intval($params['font_size'] ?? 24))); // 最大値を48から72に拡大
        $textPosition = preg_match('/^(top|middle|bottom)-(left|center|right)$|^center$/', $params['text_position'] ?? '') 
                        ? $params['text_position'] : 'center';
        $textColor = in_array($params['text_color'] ?? '', ['white', 'black', 'red', 'yellow', 'blue', 'green'])
                    ? $params['text_color'] : 'white';
        
        // 顔検出関連のパラメータ
        $enableFaceDetect = isset($params['enable_face_detect']) && ($params['enable_face_detect'] === 'true' || $params['enable_face_detect'] === '1');
        $adjustLeftDown = is_numeric($params['adjust_left_down'] ?? 4) ? (float)$params['adjust_left_down'] : 4;
        $adjustLeftHorizontal = is_numeric($params['adjust_left_horizontal'] ?? 0) ? (float)$params['adjust_left_horizontal'] : 0;
        $adjustRightDown = is_numeric($params['adjust_right_down'] ?? 4) ? (float)$params['adjust_right_down'] : 4;
        $adjustRightHorizontal = is_numeric($params['adjust_right_horizontal'] ?? 0) ? (float)$params['adjust_right_horizontal'] : 0;
        $joinPointX = is_numeric($params['join_point_x'] ?? 50) ? (int)$params['join_point_x'] : 50;
        $joinPointY = is_numeric($params['join_point_y'] ?? 90) ? (int)$params['join_point_y'] : 90;
        $joinBottomCurve = is_numeric($params['join_bottom_curve'] ?? 0) ? (int)$params['join_bottom_curve'] : 0;

        try {
            // 画像処理の実行
            $startTime = microtime(true);
            $result = $this->imageProcessor->processImage(
                $iconUrl, 
                $threshold, 
                $type, 
                $inverse, 
                $textOverlay, 
                $fontSize, 
                $textPosition, 
                $textColor,
                $enableFaceDetect,
                $adjustLeftDown,
                $adjustLeftHorizontal,
                $adjustRightDown,
                $adjustRightHorizontal,
                $joinPointX,
                $joinPointY,
                $joinBottomCurve
            );
            $processingTime = round((microtime(true) - $startTime) * 1000);
            
            // 処理時間のログ記録（大きな画像の場合のパフォーマンス監視用）
            $this->logger->info("Image processing took {$processingTime}ms for URL: {$iconUrl}");
            
            // 成功レスポンスの構築
            $successResponse = json_encode([
                'success' => true,
                'filename' => $result['filename'],
                'imageUrl' => '/images/' . $result['filename'],
                'message' => $result['message'],
                'processingTimeMs' => $processingTime
            ]);
            $response->getBody()->write($successResponse);
            return $response;
        } catch (\Exception $e) {
            // エラーコードの取得（デフォルトは500）
            $statusCode = $e->getCode();
            if (!is_int($statusCode) || $statusCode < 400 || $statusCode > 599) {
                $statusCode = 500;
            }
            
            // エラーのログ記録
            $this->logger->error('Image processing error: ' . $e->getMessage() . ' URL: ' . $iconUrl);
            
            // エラーレスポンスの構築
            $errorResponse = json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            $response->getBody()->write($errorResponse);
            return $response->withStatus($statusCode);
        }
    }
}