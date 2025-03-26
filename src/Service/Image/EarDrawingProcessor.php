<?php
/**
 * EarDrawingProcessor
 * 
 * 耳を描画するプロセッサクラス
 */

declare(strict_types=1);

namespace App\Service\Image;

/**
 * EarDrawingProcessor
 * 
 * 検出された顔に基づいて耳を描画するクラス
 */
class EarDrawingProcessor
{
    /**
     * 耳と接続線を描画する
     * 
     * @param \Imagick $img 描画対象の画像
     * @param array $ears 耳の位置情報
     * @param int $imageWidth 画像の幅
     * @param int $imageHeight 画像の高さ
     * @param int $joinPointX 接合点X座標（パーセンテージ）
     * @param int $joinPointY 接合点Y座標（パーセンテージ）
     * @param int $joinBottomCurve 下部曲線の度合い
     * @return void
     */
    public function drawEars(
        \Imagick $img, 
        array $ears, 
        int $imageWidth, 
        int $imageHeight, 
        int $joinPointX, 
        int $joinPointY, 
        int $joinBottomCurve
    ): void {
        if (empty($ears)) {
            return;
        }
        
        // 耳の位置から線を描画するための設定
        $draw = new \ImagickDraw();
        $draw->setStrokeColor('white');  // 線の色を白に
        $draw->setStrokeWidth(1);        // 線の太さ
        $draw->setStrokeAntialias(true); // アンチエイリアス有効
        $draw->setFillOpacity(0);        // 塗りつぶし完全に無効化
        
        // 左右の耳に分ける
        $leftEars = [];
        $rightEars = [];
        
        foreach ($ears as $ear) {
            if ($ear['side'] === 'left') {
                $leftEars[] = $ear;
            } else {
                $rightEars[] = $ear;
            }
        }
        
        // 塗りつぶし有りの白い耳用の描画オブジェクト（楕円のみ塗りつぶす）
        $earDraw = new \ImagickDraw();
        $earDraw->setFillColor('white');
        $earDraw->setStrokeColor('white');
        $earDraw->setStrokeWidth(1);
        
        // 耳の楕円を描画
        $radiusX = 2.5; // X方向の半径
        $radiusY = 5;   // Y方向の半径
        
        foreach ($ears as $ear) {
            $earX = (int)$ear['x'];
            $earY = (int)$ear['y'];
            
            // 縦長の白い楕円を描画（塗りつぶしあり）
            $earDraw->ellipse(
                $earX,    // 中心X座標
                $earY,    // 中心Y座標
                $radiusX, // X方向の半径
                $radiusY, // Y方向の半径
                0,        // 回転角度
                360       // 終了角度（完全な楕円）
            );
        }
        
        // 楕円を先に描画
        $img->drawImage($earDraw);
        
        // 左右の耳から中央に向かって曲線を描く
        // 左右の耳が存在する場合のみ
        if (count($leftEars) > 0 && count($rightEars) > 0) {
            $this->drawConnectionsBetweenEars(
                $img, 
                $draw, 
                $leftEars[0], 
                $rightEars[0], 
                $radiusY, 
                $imageWidth, 
                $imageHeight, 
                $joinPointX, 
                $joinPointY, 
                $joinBottomCurve
            );
        } else {
            // 左右の耳が揃わない場合は、各耳から垂直線を引く
            $this->drawVerticalLines($img, $draw, $ears, $radiusY, $imageHeight);
        }
        
        // 線のみの描画を実行
        $img->drawImage($draw);
    }
    
    /**
     * 左右の耳を接続する曲線を描画
     * 
     * @param \Imagick $img 描画対象の画像
     * @param \ImagickDraw $draw 描画オブジェクト
     * @param array $leftEar 左耳の位置情報
     * @param array $rightEar 右耳の位置情報
     * @param int $radiusY 耳の縦半径
     * @param int $imageWidth 画像の幅
     * @param int $imageHeight 画像の高さ
     * @param int $joinPointX 接合点X座標（パーセンテージ）
     * @param int $joinPointY 接合点Y座標（パーセンテージ）
     * @param int $joinBottomCurve 下部曲線の度合い
     * @return void
     */
    private function drawConnectionsBetweenEars(
        \Imagick $img, 
        \ImagickDraw $draw, 
        array $leftEar, 
        array $rightEar, 
        int $radiusY, 
        int $imageWidth, 
        int $imageHeight, 
        int $joinPointX, 
        int $joinPointY, 
        int $joinBottomCurve
    ): void {
        $leftX = (int)$leftEar['x'];
        $leftY = (int)$leftEar['y'] + $radiusY; // 楕円の下端
        
        $rightX = (int)$rightEar['x'];
        $rightY = (int)$rightEar['y'] + $radiusY; // 楕円の下端
        
        // 曲線の接合点座標を計算
        $middleX = $imageWidth * ($joinPointX / 100); // 画像幅に対する割合で横位置を計算
        $bottomY = $imageHeight * ($joinPointY / 100); // 画像高さに対する割合で縦位置を計算
        
        // 左耳から中央下部へのポイントを計算
        $leftPoints = [];
        $steps = 20;
        for ($i = 0; $i <= $steps; $i++) {
            $t = $i / $steps;
            // ベジェ曲線の式で座標を計算
            $x = $leftX * (1 - $t) * (1 - $t) + 
                 $leftX * 2 * (1 - $t) * $t + 
                 $middleX * $t * $t;
            
            $y = $leftY * (1 - $t) * (1 - $t) + 
                 (($leftY + $bottomY) / 2) * 2 * (1 - $t) * $t + 
                 $bottomY * $t * $t;
            
            $leftPoints[] = ['x' => $x, 'y' => $y];
        }
        
        // 中央下部から右耳へのポイントを計算
        $rightPoints = [];
        for ($i = 0; $i <= $steps; $i++) {
            $t = $i / $steps;
            // ベジェ曲線の式で座標を計算
            $x = $middleX * (1 - $t) * (1 - $t) + 
                 $rightX * 2 * (1 - $t) * $t + 
                 $rightX * $t * $t;
            
            $y = $bottomY * (1 - $t) * (1 - $t) + 
                 (($rightY + $bottomY) / 2) * 2 * (1 - $t) * $t + 
                 $rightY * $t * $t;
            
            $rightPoints[] = ['x' => $x, 'y' => $y];
        }
        
        // 左半分の曲線を描画（線分の連続で近似）
        for ($i = 0; $i < count($leftPoints) - 1; $i++) {
            $draw->line(
                $leftPoints[$i]['x'], $leftPoints[$i]['y'],
                $leftPoints[$i+1]['x'], $leftPoints[$i+1]['y']
            );
        }
        
        // 右半分の曲線を描画（線分の連続で近似）
        for ($i = 0; $i < count($rightPoints) - 1; $i++) {
            $draw->line(
                $rightPoints[$i]['x'], $rightPoints[$i]['y'],
                $rightPoints[$i+1]['x'], $rightPoints[$i+1]['y']
            );
        }
        
        // 接合点から下に伸びる線を描画（曲線の度合いを調整可能）
        if ($joinBottomCurve == 0) {
            // 曲線度合い0の場合は直線
            $draw->line(
                $middleX,                    // 接合点のX座標
                $bottomY,                    // 接合点のY座標
                $middleX,                    // 同じX座標（垂直線）
                $imageHeight - 1             // 画像の下端まで
            );
        } else {
            // 曲線を描画
            $curveDirection = ($joinBottomCurve > 0) ? 1 : -1; // 正の値は右方向、負の値は左方向
            $curveStrength = abs($joinBottomCurve) / 100 * $imageWidth * 0.5; // 画像幅の50%を最大値として曲がり具合を計算
            
            // 下への曲線のポイントを計算
            $curvePoints = [];
            $steps = 20; // 曲線の滑らかさ
            
            // 接合点から画像下端までの曲線のポイントを計算
            for ($i = 0; $i <= $steps; $i++) {
                $t = $i / $steps;
                $y = $bottomY + ($imageHeight - 1 - $bottomY) * $t; // Y座標は一定の割合で下へ
                
                // 曲がり方の調整（指定方向にのみ曲がるように）
                if ($curveDirection > 0) {
                    // 右方向の場合、X座標は接合点から右側にのみ広がる
                    $curveOffset = $curveStrength * $t * $t;
                } else {
                    // 左方向の場合、X座標は接合点から左側にのみ広がる
                    $curveOffset = $curveStrength * $t * $t * -1;
                }
                
                $x = $middleX + $curveOffset;
                
                $curvePoints[] = ['x' => $x, 'y' => $y];
            }
            
            // 曲線を連続線分として描画
            for ($i = 0; $i < count($curvePoints) - 1; $i++) {
                $draw->line(
                    $curvePoints[$i]['x'], $curvePoints[$i]['y'],
                    $curvePoints[$i+1]['x'], $curvePoints[$i+1]['y']
                );
            }
        }
    }
    
    /**
     * 各耳から垂直線を描画
     * 
     * @param \Imagick $img 描画対象の画像
     * @param \ImagickDraw $draw 描画オブジェクト
     * @param array $ears 耳の位置情報配列
     * @param int $radiusY 耳の縦半径
     * @param int $imageHeight 画像の高さ
     * @return void
     */
    private function drawVerticalLines(
        \Imagick $img, 
        \ImagickDraw $draw, 
        array $ears, 
        int $radiusY, 
        int $imageHeight
    ): void {
        foreach ($ears as $ear) {
            $earX = (int)$ear['x'];
            $earY = (int)$ear['y'] + $radiusY; // 楕円の下端
            
            $draw->line(
                $earX,        // 開始X座標（楕円の中心）
                $earY,        // 開始Y座標（楕円の下端）
                $earX,        // 終了X座標（同じX座標）
                $imageHeight - 1 // 終了Y座標（画像の一番下）
            );
        }
    }
}