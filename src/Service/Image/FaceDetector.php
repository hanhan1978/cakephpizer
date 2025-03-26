<?php
/**
 * FaceDetector
 * 
 * 顔検出機能を提供するプロセッサクラス
 */

declare(strict_types=1);

namespace App\Service\Image;

/**
 * FaceDetector
 * 
 * 画像内の顔を検出するクラス
 */
class FaceDetector
{
    /**
     * 画像内の顔を検出し、耳の位置を推定する
     * 
     * @param \Imagick $img 処理する画像
     * @param float $adjustLeftDown 左耳の下方向調整値
     * @param float $adjustLeftHorizontal 左耳の水平方向調整値
     * @param float $adjustRightDown 右耳の下方向調整値
     * @param float $adjustRightHorizontal 右耳の水平方向調整値
     * @return array 検出結果（耳の位置情報と一時ファイルパスを含む）
     */
    public function detectFaces(
        \Imagick $img,
        float $adjustLeftDown,
        float $adjustLeftHorizontal,
        float $adjustRightDown,
        float $adjustRightHorizontal
    ): array {
        $faces = [];
        $eyes = [];
        $profiles = [];
        $ears = [];
        $originalImagePath = '';
        
        // オリジナル画像を一時ファイルに保存（顔検出処理用）
        $tempDir = sys_get_temp_dir();
        $originalImagePath = $tempDir . '/original_' . uniqid() . '.jpg';
        $img->writeImage($originalImagePath);
        
        // 顔検出パラメータの設定
        $adjustLeftDownPercentage = $adjustLeftDown / 100;
        $adjustLeftHorizontalPercentage = $adjustLeftHorizontal / 100;
        $adjustRightDownPercentage = $adjustRightDown / 100;
        $adjustRightHorizontalPercentage = $adjustRightHorizontal / 100;
        
        // 顔の正面検出
        $face_data = face_detect($originalImagePath, FACE_CASCADE_PATH);
        
        // 横顔検出（耳の位置推定用）
        $profile_data = face_detect($originalImagePath, PROFILE_CASCADE_PATH);
        
        // 目の検出
        $eye_data = face_detect($originalImagePath, EYE_CASCADE_PATH);
        
        // 顔データを整形
        if ($face_data !== false) {
            foreach ($face_data as $face) {
                $faces[] = [
                    'x' => $face[0] ?? $face['x'] ?? 0,
                    'y' => $face[1] ?? $face['y'] ?? 0,
                    'width' => $face[2] ?? $face['w'] ?? 0,
                    'height' => $face[3] ?? $face['h'] ?? 0,
                    'confidence' => isset($face[4]) ? $face[4] : (isset($face['confidence']) ? $face['confidence'] : 0)
                ];
            }
        }
        
        // 目のデータを収集
        if ($eye_data !== false) {
            foreach ($eye_data as $eye) {
                $eyes[] = [
                    'x' => $eye[0] ?? $eye['x'] ?? 0,
                    'y' => $eye[1] ?? $eye['y'] ?? 0,
                    'width' => $eye[2] ?? $eye['w'] ?? 0,
                    'height' => $eye[3] ?? $eye['h'] ?? 0
                ];
            }
        }
        
        // 横顔データを収集（耳の推定用）
        if ($profile_data !== false) {
            foreach ($profile_data as $profile) {
                $profiles[] = [
                    'x' => $profile[0] ?? $profile['x'] ?? 0,
                    'y' => $profile[1] ?? $profile['y'] ?? 0,
                    'width' => $profile[2] ?? $profile['w'] ?? 0,
                    'height' => $profile[3] ?? $profile['h'] ?? 0
                ];
            }
        }
        
        // 耳の位置を推定
        $ears = $this->estimateEarPositions(
            $faces,
            $profiles,
            $eyes,
            $adjustLeftDownPercentage,
            $adjustLeftHorizontalPercentage,
            $adjustRightDownPercentage,
            $adjustRightHorizontalPercentage
        );
        
        return [
            'ears' => $ears,
            'originalImagePath' => $originalImagePath
        ];
    }
    
    /**
     * 顔、横顔、目のデータから耳の位置を推定する
     * 
     * @param array $faces 検出された顔データ
     * @param array $profiles 検出された横顔データ
     * @param array $eyes 検出された目データ
     * @param float $adjustLeftDownPercentage 左耳の下方向調整値（パーセント）
     * @param float $adjustLeftHorizontalPercentage 左耳の水平方向調整値（パーセント）
     * @param float $adjustRightDownPercentage 右耳の下方向調整値（パーセント）
     * @param float $adjustRightHorizontalPercentage 右耳の水平方向調整値（パーセント）
     * @return array 推定された耳の位置情報
     */
    private function estimateEarPositions(
        array $faces,
        array $profiles,
        array $eyes,
        float $adjustLeftDownPercentage,
        float $adjustLeftHorizontalPercentage,
        float $adjustRightDownPercentage,
        float $adjustRightHorizontalPercentage
    ): array {
        $ears = [];
        
        // 方法1: 横顔から耳の位置を推定
        foreach ($profiles as $profile) {
            // 横顔の右側1/3あたりに右耳がある可能性が高い
            $baseEarX = $profile['x'] + ($profile['width'] * 0.8);
            // 水平方向の調整（右耳用）
            $horizontalAdjustment = $profile['width'] * $adjustRightHorizontalPercentage; // 正の値で外側（右）
            $earX = $baseEarX + $horizontalAdjustment; // 右方向なのでプラス
            
            // 基本位置（中央）+ 下方向への調整（右耳用）
            $earY = $profile['y'] + ($profile['height'] * 0.5) + ($profile['height'] * $adjustRightDownPercentage);
            
            $ears[] = [
                'x' => $earX,
                'y' => $earY,
                'side' => 'right'
            ];
            
            // もし横顔があれば、反対側にも耳があるかもしれない
            if (count($faces) > 0) {
                foreach ($faces as $face) {
                    // 正面顔の左端に左耳が位置すると仮定
                    $baseLeftEarX = $face['x'];
                    // 水平方向の調整（左耳用）
                    $leftHorizontalAdjustment = $face['width'] * $adjustLeftHorizontalPercentage; // 正の値で外側（左）
                    $leftEarX = $baseLeftEarX - $leftHorizontalAdjustment; // 左方向なのでマイナス
                    
                    // 基本位置（中央）+ 下方向への調整（左耳用）
                    $leftEarY = $face['y'] + ($face['height'] * 0.5) + ($face['height'] * $adjustLeftDownPercentage);
                    
                    $ears[] = [
                        'x' => $leftEarX,
                        'y' => $leftEarY,
                        'side' => 'left'
                    ];
                }
            }
        }
        
        // 方法2: 正面顔から耳の位置を推定（横顔が検出されなかった場合）
        if (count($profiles) == 0 && count($faces) > 0) {
            foreach ($faces as $face) {
                // 左耳の推定位置
                $baseLeftEarX = $face['x'];
                // 水平方向の調整（左耳用）
                $leftHorizontalAdjustment = $face['width'] * $adjustLeftHorizontalPercentage; // 正の値で外側（左）
                $leftEarX = $baseLeftEarX - $leftHorizontalAdjustment; // 左方向なのでマイナス
                
                // 基本位置（中央）+ 下方向への調整（左耳用）
                $leftEarY = $face['y'] + ($face['height'] * 0.5) + ($face['height'] * $adjustLeftDownPercentage);
                
                $ears[] = [
                    'x' => $leftEarX,
                    'y' => $leftEarY,
                    'side' => 'left'
                ];
                
                // 右耳の推定位置
                $baseRightEarX = $face['x'] + $face['width'];
                // 水平方向の調整（右耳用）
                $rightHorizontalAdjustment = $face['width'] * $adjustRightHorizontalPercentage; // 正の値で外側（右）
                $rightEarX = $baseRightEarX + $rightHorizontalAdjustment; // 右方向なのでプラス
                
                // 基本位置（中央）+ 下方向への調整（右耳用）
                $rightEarY = $face['y'] + ($face['height'] * 0.5) + ($face['height'] * $adjustRightDownPercentage);
                
                $ears[] = [
                    'x' => $rightEarX,
                    'y' => $rightEarY,
                    'side' => 'right'
                ];
            }
        }
        
        // 方法3: 目の位置から耳の位置を推定
        if (count($eyes) >= 2) {
            // 左右の目を識別（単純に左側にあるものを左目とする）
            usort($eyes, function($a, $b) {
                return $a['x'] - $b['x'];
            });
            
            $leftEye = $eyes[0];
            $rightEye = $eyes[1];
            
            // 顔の高さと幅の推定
            $estimatedFaceHeight = 0;
            $estimatedFaceWidth = $rightEye['x'] - $leftEye['x'] + $rightEye['width'] + $leftEye['width'];
            
            // 顔が検出されていれば、その高さを使う
            if (count($faces) > 0) {
                $estimatedFaceHeight = $faces[0]['height'];
                $estimatedFaceWidth = $faces[0]['width'];
            } else {
                // 顔が検出されていなければ、目のサイズから推定
                $estimatedFaceHeight = max($leftEye['height'], $rightEye['height']) * 8;
            }
            
            // 目の位置から横に延長して左耳の位置を推定
            $baseLeftEarX = $leftEye['x'] - ($rightEye['x'] - $leftEye['x']) * 0.8;
            // 水平方向の調整（左耳用）
            $leftHorizontalAdjustment = $estimatedFaceWidth * $adjustLeftHorizontalPercentage; // 正の値で外側（左）
            $leftEarX = $baseLeftEarX - $leftHorizontalAdjustment; // 左方向なのでマイナス
            
            // 基本位置 + 下方向への調整（左耳用）
            $leftEarY = $leftEye['y'] + ($estimatedFaceHeight * $adjustLeftDownPercentage);
            
            // 目の位置から横に延長して右耳の位置を推定
            $baseRightEarX = $rightEye['x'] + ($rightEye['x'] - $leftEye['x']) * 0.8;
            // 水平方向の調整（右耳用）
            $rightHorizontalAdjustment = $estimatedFaceWidth * $adjustRightHorizontalPercentage; // 正の値で外側（右）
            $rightEarX = $baseRightEarX + $rightHorizontalAdjustment; // 右方向なのでプラス
            
            // 基本位置 + 下方向への調整（右耳用）
            $rightEarY = $rightEye['y'] + ($estimatedFaceHeight * $adjustRightDownPercentage);
            
            $ears[] = [
                'x' => $leftEarX,
                'y' => $leftEarY,
                'side' => 'left'
            ];
            
            $ears[] = [
                'x' => $rightEarX,
                'y' => $rightEarY,
                'side' => 'right'
            ];
        }
        
        return $ears;
    }
}