<?php
/**
 * ImageProcessorService
 * 
 * 画像処理のメイン機能を提供するサービスクラス
 */

declare(strict_types=1);

namespace App\Service\Image;

use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * ImageProcessorService
 * 
 * 画像処理の中心的なサービスクラス
 */
class ImageProcessorService
{
    /**
     * @var ImageThresholdProcessor 閾値処理を行うプロセッサ
     */
    private ImageThresholdProcessor $thresholdProcessor;
    
    /**
     * @var FaceDetector 顔検出処理を行うプロセッサ
     */
    private FaceDetector $faceDetector;
    
    /**
     * @var TextOverlayProcessor テキストオーバーレイ処理を行うプロセッサ
     */
    private TextOverlayProcessor $textOverlayProcessor;
    
    /**
     * @var EarDrawingProcessor 耳描画処理を行うプロセッサ
     */
    private EarDrawingProcessor $earDrawingProcessor;
    
    /**
     * @var LoggerInterface ロガー
     */
    private LoggerInterface $logger;

    /**
     * コンストラクタ
     * 
     * @param ImageThresholdProcessor $thresholdProcessor
     * @param FaceDetector $faceDetector
     * @param TextOverlayProcessor $textOverlayProcessor
     * @param EarDrawingProcessor $earDrawingProcessor
     * @param LoggerInterface $logger
     */
    public function __construct(
        ImageThresholdProcessor $thresholdProcessor,
        FaceDetector $faceDetector,
        TextOverlayProcessor $textOverlayProcessor,
        EarDrawingProcessor $earDrawingProcessor,
        LoggerInterface $logger
    ) {
        $this->thresholdProcessor = $thresholdProcessor;
        $this->faceDetector = $faceDetector;
        $this->textOverlayProcessor = $textOverlayProcessor;
        $this->earDrawingProcessor = $earDrawingProcessor;
        $this->logger = $logger;
    }

    /**
     * 画像処理の実行
     * 
     * 元の processImage 関数の機能を提供
     * 
     * @param string $iconUrl 画像URL
     * @param float $threshold 閾値
     * @param int $type 画像タイプ (1=cakephper, 2=sodai)
     * @param bool $inverse 色反転フラグ
     * @param string $textOverlay オーバーレイテキスト
     * @param int $fontSize フォントサイズ
     * @param string $textPosition テキスト位置
     * @param string $textColor テキスト色
     * @param bool $enableFaceDetect 顔検出有効フラグ
     * @param float $adjustLeftDown 左耳の下方向調整値
     * @param float $adjustLeftHorizontal 左耳の水平方向調整値
     * @param float $adjustRightDown 右耳の下方向調整値
     * @param float $adjustRightHorizontal 右耳の水平方向調整値
     * @param int $joinPointX 接合点X座標
     * @param int $joinPointY 接合点Y座標
     * @param int $joinBottomCurve 下部曲線の度合い
     * @return array 処理結果（ファイル名、拡張子、メッセージを含む連想配列）
     * @throws \Exception 画像処理エラーが発生した場合
     */
    public function processImage(
        string $iconUrl, 
        float $threshold, 
        int $type, 
        bool $inverse,
        string $textOverlay = '',
        int $fontSize = 24,
        string $textPosition = 'center',
        string $textColor = 'white',
        bool $enableFaceDetect = false,
        float $adjustLeftDown = 4,
        float $adjustLeftHorizontal = 0,
        float $adjustRightDown = 4,
        float $adjustRightHorizontal = 0,
        int $joinPointX = 50,
        int $joinPointY = 90,
        int $joinBottomCurve = 0
    ): array {
        $blue = $type === 1 ? CAKEPHPER_BLUE : SODAI_BLUE;

        if ($inverse) {
            $from = BLACK;
            $to = $blue;
        } else {
            $from = $blue;
            $to = BLACK;
        }

        $uuid = Uuid::uuid4();
        
        try {
            // 画像読み込み
            $img = new \Imagick();
            
            // タイムアウト設定（3秒）- DoS攻撃対策
            $img->setOption('timeout', '3');
            
            // 画像フォーマット制限（セキュリティ対策）
            $img->setOption('allowed-formats', 'jpg,jpeg,png,gif');
            
            // 画像読み込み
            $img->readImage($iconUrl);
            
            // アニメーションGIFの場合は最初のフレームだけ使用
            if ($img->getNumberImages() > 1) {
                $img = $img->coalesceImages();
                foreach ($img as $frame) {
                    $firstFrame = $frame;
                    break;
                }
                $img = $firstFrame;
            }
            
            // 画像サイズの制限（メモリ使用量削減）
            if ($img->getImageHeight() > 1000 || $img->getImageWidth() > 1000) {
                $img->thumbnailImage(1000, 1000, true);
            } else if ($img->getImageHeight() > 400) {
                $img->scaleImage(400, 0);
            }
            
            // 顔検出を実行（有効な場合）
            $ears = [];
            $originalImagePath = '';
            
            if ($enableFaceDetect && extension_loaded('facedetect')) {
                // 顔検出実行
                $detectionResult = $this->faceDetector->detectFaces(
                    $img,
                    $adjustLeftDown,
                    $adjustLeftHorizontal,
                    $adjustRightDown,
                    $adjustRightHorizontal
                );
                
                $ears = $detectionResult['ears'];
                $originalImagePath = $detectionResult['originalImagePath'];
            }
            
            // 閾値処理
            $this->thresholdProcessor->process($img, $threshold, $from, $to);
            
            // 画像のサイズを取得
            $imageWidth = $img->getImageWidth();
            $imageHeight = $img->getImageHeight();
            
            // 文字入れ処理
            if (!empty($textOverlay)) {
                $this->textOverlayProcessor->addText(
                    $img,
                    $textOverlay,
                    $fontSize,
                    $textPosition,
                    $textColor,
                    $imageWidth,
                    $imageHeight
                );
            }
            
            // 顔検出モードが有効で、耳の位置が特定できた場合に耳線を描画
            if ($enableFaceDetect && extension_loaded('facedetect') && count($ears) > 0) {
                $this->earDrawingProcessor->drawEars(
                    $img,
                    $ears,
                    $imageWidth,
                    $imageHeight,
                    $joinPointX,
                    $joinPointY,
                    $joinBottomCurve
                );
                
                // 一時ファイルをクリーンアップ
                if (file_exists($originalImagePath)) {
                    @unlink($originalImagePath);
                }
            }
            
            // 保存前に最終フォーマット設定
            $ext = strtolower($img->getImageFormat());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $ext = 'png'; // サポートされていない形式の場合はPNGに変換
                $img->setImageFormat('png');
            }
            
            // 画像の最適化
            if ($ext == 'jpg' || $ext == 'jpeg') {
                $img->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $img->setImageCompressionQuality(85);
            } elseif ($ext == 'png') {
                $img->setImageCompression(\Imagick::COMPRESSION_ZIP);
                $img->setOption('png:compression-level', '9');
            }
            
            // ファイル名生成と保存
            $filename = $uuid->toString() . '.' . $ext;
            $imagePath = IMAGE_TEMP_DIR . '/' . $filename;
            
            // ディレクトリの存在確認
            if (!is_dir(IMAGE_TEMP_DIR)) {
                mkdir(IMAGE_TEMP_DIR, 0755, true);
            }
            
            // 画像書き込み
            $img->writeImage($imagePath);
            $img->clear();
            
            return [
                'filename' => $filename,
                'ext' => $ext,
                'message' => $type === 1 ? 'Cakephpized !!' : 'Sodaized !!'
            ];
        } catch (\ImagickException $e) {
            $this->logger->error('画像処理エラー: ' . $e->getMessage());
            throw new \Exception('画像処理エラー: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            $this->logger->error('予期せぬエラー: ' . $e->getMessage());
            throw new \Exception('予期せぬエラー: ' . $e->getMessage(), 500);
        }
    }
}