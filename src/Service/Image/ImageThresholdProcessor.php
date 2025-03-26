<?php
/**
 * ImageThresholdProcessor
 * 
 * 画像の閾値処理を行うプロセッサクラス
 */

declare(strict_types=1);

namespace App\Service\Image;

/**
 * ImageThresholdProcessor
 * 
 * 画像の閾値処理を行うクラス
 */
class ImageThresholdProcessor
{
    /**
     * 画像に閾値処理を適用する
     *
     * @param \Imagick $img 処理する画像
     * @param float $threshold 閾値
     * @param string $from 変換元色
     * @param string $to 変換先色
     * @return void
     */
    public function process(\Imagick $img, float $threshold, string $from, string $to): void
    {
        // 閾値処理
        $img->thresholdImage($threshold * \Imagick::getQuantum());
        
        // ピクセルイテレーターを使用して色変換
        $img->setImageFormat('png'); // 変換前にPNG形式に設定して色の正確さを確保
        
        // メモリ使用量の最適化のため、画像サイズに基づいて処理方法を選択
        $totalPixels = $img->getImageWidth() * $img->getImageHeight();
        
        if ($totalPixels > 250000) { // 大きな画像の場合はより効率的な方法を使用
            // 色置換マップの作成（ルックアップテーブル）
            $colorMap = [
                '#000000' => $to,
                '#FFFFFF' => $from
            ];
            
            // クローンを作成してオペレーションの副作用を避ける
            $clone = clone $img;
            // 2値化のためのコントラスト調整
            $clone->contrastImage(1);
            
            // 色置換マップを使った直接変換
            foreach ($colorMap as $srcColor => $destColor) {
                $clone->opaquePaintImage(
                    $srcColor, 
                    $destColor, 
                    0.1 * \Imagick::getQuantum(), 
                    false
                );
            }
            
            // 処理済み画像を元の変数に戻す
            $img = $clone;
        } else {
            // 小さい画像には従来のピクセル単位処理を使用
            $imageIterator = $img->getPixelIterator();
            foreach ($imageIterator as $row => $pixels) {
                foreach ($pixels as $column => $pixel) {
                    $lum = (int)$pixel->getHSL()['luminosity'];
                    $newColor = $lum === 0 ? $to : $from;
                    $pixel->setColor($newColor);
                }
                $imageIterator->syncIterator();
            }
        }
    }
}