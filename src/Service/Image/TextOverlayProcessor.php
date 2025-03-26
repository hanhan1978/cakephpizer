<?php
/**
 * TextOverlayProcessor
 * 
 * 画像にテキストを追加するプロセッサクラス
 */

declare(strict_types=1);

namespace App\Service\Image;

/**
 * TextOverlayProcessor
 * 
 * 画像にテキストオーバーレイを追加するクラス
 */
class TextOverlayProcessor
{
    /**
     * 画像にテキストを追加する
     * 
     * @param \Imagick $img 処理する画像
     * @param string $text 追加するテキスト
     * @param int $fontSize フォントサイズ
     * @param string $position テキスト位置
     * @param string $color テキスト色
     * @param int $imageWidth 画像の幅
     * @param int $imageHeight 画像の高さ
     * @return void
     */
    public function addText(
        \Imagick $img, 
        string $text, 
        int $fontSize, 
        string $position, 
        string $color, 
        int $imageWidth, 
        int $imageHeight
    ): void {
        $draw = new \ImagickDraw();
        
        // フォント設定
        if (file_exists(DEFAULT_FONT_PATH)) {
            $draw->setFont(DEFAULT_FONT_PATH);
        } else {
            $draw->setFont(FALLBACK_FONT);
        }
        
        $draw->setFontSize($fontSize);
        $draw->setFillColor($color);
        $draw->setTextAlignment(\Imagick::ALIGN_CENTER);
        
        // 輪郭なし、太字のみ
        $draw->setStrokeWidth(0);
        
        // パディング（端からの距離）
        $padding = 20;
        
        // 位置に応じて座標を計算
        switch ($position) {
            // 上段
            case 'top-left':
                $x = $padding;
                $y = $padding + $fontSize;
                $draw->setTextAlignment(\Imagick::ALIGN_LEFT);
                break;
            case 'top-center':
                $x = $imageWidth / 2;
                $y = $padding + $fontSize;
                break;
            case 'top-right':
                $x = $imageWidth - $padding;
                $y = $padding + $fontSize;
                $draw->setTextAlignment(\Imagick::ALIGN_RIGHT);
                break;
            
            // 中段
            case 'middle-left':
                $x = $padding;
                $y = $imageHeight / 2;
                $draw->setTextAlignment(\Imagick::ALIGN_LEFT);
                break;
            case 'center':
                $x = $imageWidth / 2;
                $y = $imageHeight / 2;
                break;
            case 'middle-right':
                $x = $imageWidth - $padding;
                $y = $imageHeight / 2;
                $draw->setTextAlignment(\Imagick::ALIGN_RIGHT);
                break;
            
            // 下段
            case 'bottom-left':
                $x = $padding;
                $y = $imageHeight - $padding;
                $draw->setTextAlignment(\Imagick::ALIGN_LEFT);
                break;
            case 'bottom-center':
                $x = $imageWidth / 2;
                $y = $imageHeight - $padding;
                break;
            case 'bottom-right':
                $x = $imageWidth - $padding;
                $y = $imageHeight - $padding;
                $draw->setTextAlignment(\Imagick::ALIGN_RIGHT);
                break;
            
            default:
                // デフォルトは中央
                $x = $imageWidth / 2;
                $y = $imageHeight / 2;
        }
        
        // 文字を描画
        $img->annotateImage($draw, $x, $y, 0, $text);
    }
}