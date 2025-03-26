# Cakephpizer

<p align="center">
  <img src="icon.png" alt="Cakephpizer Logo" width="200">
</p>

## 概要

Cakephpizerは画像処理アプリケーションです。画像を特定の閾値で処理し、ケーキPHPer風やそだい風の画像に変換します。顔検出機能も備えており、検出した顔に特徴的な耳を追加することもできます。

## 機能

- 画像の閾値処理
- テキストオーバーレイ
- 顔検出と特徴的な耳の追加
- 画像サイズの最適化

## 技術スタック

- PHP 8.3以上
- Slim Framework 4
- Twig テンプレートエンジン
- Imagick 画像処理ライブラリ
- Docker/Docker Compose

## 始め方

### 必要条件

- PHP 8.3以上
- Composer
- Docker および Docker Compose（オプション）

### インストール方法

1. リポジトリをクローン
   ```
   git clone https://github.com/yourusername/cakephpizer.git
   cd cakephpizer
   ```

2. Composerで依存関係をインストール
   ```
   composer install
   ```

3. Dockerを使って起動
   ```
   docker-compose up -d
   ```

4. ブラウザで `http://localhost:8080` にアクセス

## 使い方

1. トップページで画像URLを入力
2. 処理タイプを選択（Cakephper風 または Sodai風）
3. 必要に応じてテキストオーバーレイや顔検出のオプションを設定
4. 「変換」ボタンをクリック
5. 処理された画像が表示されます

## 設定

環境設定は `config/settings.php` で管理されています。主な設定項目：

- アプリケーションのデバッグモード
- 画像処理パラメータ（最大サイズ、デフォルト閾値など）
- テンプレートキャッシュ設定

## APIリファレンス

### 画像変換API

```
POST /api/cakephpize
```

#### パラメータ

- `icon_url` - 処理する画像のURL（必須）
- `threshold` - 閾値（デフォルト: 0.5）
- `type` - 処理タイプ（1=Cakephper風, 2=Sodai風）
- `inverse` - 色反転フラグ
- `text_overlay` - オーバーレイテキスト
- `font_size` - フォントサイズ
- `text_position` - テキスト位置
- `text_color` - テキスト色
- `enable_face_detect` - 顔検出有効フラグ

## ライセンス

このプロジェクトはApache License 2.0のもとで公開されています。詳細は[LICENSE](LICENSE.txt)ファイルをご覧ください。