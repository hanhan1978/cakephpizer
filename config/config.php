<?php
/**
 * 設定読み込みファイル
 * 
 * このファイルは、アプリケーションのすべての設定をロードする中央ポイントです。
 */

// 定数を読み込み
require_once __DIR__ . '/constants.php';

/**
 * 設定を取得する
 * 
 * @param string|null $key 取得したい設定のキー（ドット記法をサポート）
 * @return mixed 設定値、またはキーが指定されていない場合は設定全体の配列
 */
function config(string $key = null)
{
    static $settings = null;
    
    // 設定がまだロードされていなければロード
    if ($settings === null) {
        $settings = require __DIR__ . '/settings.php';
    }
    
    // キーが指定されていなければ設定全体を返す
    if ($key === null) {
        return $settings;
    }
    
    // ドット記法をサポート (例: app.debug)
    $parts = explode('.', $key);
    $value = $settings;
    
    foreach ($parts as $part) {
        if (!isset($value[$part])) {
            return null; // キーが存在しない場合はnullを返す
        }
        $value = $value[$part];
    }
    
    return $value;
}