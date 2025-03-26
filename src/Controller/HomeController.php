<?php
/**
 * ホームページコントローラー
 * 
 * メインページを表示するコントローラー
 */

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

/**
 * HomeController
 * 
 * メインページを表示するコントローラー
 */
class HomeController
{
    /**
     * コントローラーの実行
     * 
     * シングルアクションコントローラーの呼び出しメソッド
     * 
     * @param Request $request リクエスト
     * @param Response $response レスポンス
     * @param array $args ルートパラメータ
     * @return Response HTMLレスポンス
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // 10分以上前に生成された画像は消す
        $dir = opendir(IMAGE_TEMP_DIR);

        while ($file = readdir($dir)) {
            if ($file === '.' || $file === '..' || $file === 'dog.jpeg') {
                continue;
            }
            $filepath = IMAGE_TEMP_DIR . '/' . $file;

            if ((time() - filemtime($filepath)) > IMAGE_EXPIRATION_TIME) {
                @unlink($filepath);
            }
        }

        $params = $request->getQueryParams();
        $errorMessage = '';
        if (isset($params['error']) && $params['error'] === '1') {
            $errorMessage = 'ちゃんとした画像URL入れてください。JPEG, GIF, PNG に対応しているはず';
        }
        
        $twig = Twig::create(config('templates.path'));
        return $twig->render($response, 'index.tpl', ['errorMessage' => $errorMessage]);
    }
}