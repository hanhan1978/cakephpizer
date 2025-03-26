# cakephpizer 開発ガイド

## コマンド
- **セットアップ**: `composer install`
- **起動**: `docker-compose up`
- **Dockerビルド**: `docker-compose build`
- **コンテナシェル**: `docker-compose exec php bash`

## コードスタイルガイドライン
- **フレームワーク**: Slim 4.x、テンプレートにはTwig
- **PHPバージョン**: 8.4
- **型宣言**: `declare(strict_types=1)` による厳格な型付け
- **エラー処理**: 具体的な例外を使用したtry/catch
- **命名規則**: メソッドはキャメルケース、変数はスネークケース
- **コメント**: コード内のコメントは日本語可
- **画像処理**: 閾値ベースの変換にImageckを使用
- **定数**: 定数は大文字で（例：`BLACK`、`CAKEPHPER_BLUE`）
- **コントローラー**: シングルアクションコントローラーパターンを使用

## アーキテクチャ
- **ルート**: config/routes.phpで定義
- **テンプレート**: resources/templates/*.tplに配置
- **アセット**: 公開ファイルはpublic/cssとpublic/imagesに配置
- **ライフサイクル**: 生成された画像は10分後に自動削除
- **コントローラー設計**: 各エンドポイントにシングルアクションコントローラーを使用

### シングルアクションコントローラー
- 1つのコントローラークラスが1つのエンドポイント/アクションを担当
- `__invoke` メソッドを実装し、Slimのルート定義で直接クラス名を指定
- リクエスト/レスポンス処理を1つのクラスにカプセル化
- 命名規則: 機能名+「Controller」（例: `HomeController`, `CakephpizeApiController`）

```php
// コントローラー実装例
class ExampleController
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // 処理を実装
        return $response;
    }
}

// ルート定義例
$app->get('/path', ExampleController::class);
```

## Gitワークフロー
- 内容を表す説明的なブランチ名を作成
  - 新機能追加: `feature/機能名`
  - バグ修正: `fix/バグ説明`
  - リファクタリング: `refactor/内容`
  - ドキュメント: `docs/内容`
- ブランチに変更をコミット
- **必ず** no-fast-forwardを使用してmainにマージする: `git merge --no-ff ブランチ名`
  - これにより変更の履歴とコンテキストが維持される
  - マージコミットには変更の概要を記述する

## リファクタリング計画

### 現在の課題
- すべてのコードが`public/index.php`に集中している
- MVCアーキテクチャが未導入
- 画像処理機能が単一の長い関数に実装されている
- `src/`ディレクトリが未使用
- エラー処理が不十分（`error_reporting(0)`）
- テストが未実装

### リファクタリング方針
1. **MVCアーキテクチャの導入**
   - `src/Controller/` - ルート処理とリクエスト/レスポンス管理
   - `src/Model/` - ビジネスロジックとデータ処理
   - `src/Service/` - 画像処理などの共通サービス

2. **クラス設計**
   - `ImageProcessor` - 画像処理ロジックの分離
   - `FaceDetector` - 顔検出機能の分離
   - `TextOverlay` - テキスト描画機能の分離
   - `FileManager` - 一時ファイル管理とクリーンアップ

3. **依存性注入の活用**
   - コンテナを使用したサービスの管理
   - インターフェースを用いた実装の分離

4. **エラー処理の改善**
   - 適切な例外処理と具体的なエラーメッセージ
   - ロギングの強化

5. **セキュリティ対策**
   - URL検証とサニタイズの強化
   - ファイルアクセス制限

6. **テスト環境の構築**
   - PHPUnitによる単体テスト
   - モックを使用したサービステスト

### 実装フェーズ
1. プロジェクト構造のリファクタリング
2. 画像処理機能のサービスへの抽出
3. ルーティングとコントローラーの分離
4. テンプレート処理の強化
5. テスト実装

### ディレクトリ構造案
```
/cakephpizer
├── public/
│   ├── index.php     # フロントコントローラー（エントリーポイント）
│   ├── css/
│   └── images/
├── resources/
│   └── templates/    # Twigテンプレート
├── src/
│   ├── Controller/   # Slimルートハンドラー
│   ├── Model/        # ドメインモデル
│   ├── Service/      # サービスクラス
│   └── Core/         # フレームワーク拡張
├── tests/            # テストコード
└── config/           # 設定ファイル
```