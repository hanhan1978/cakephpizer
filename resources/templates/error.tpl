<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="/favicon.ico" type="image/x-icon" rel="icon"/><link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
    <meta name="author"/>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <title>Cakephpizer - エラー {{ code }}</title>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="relative">
                    <img alt="error" src="/images/dog.jpeg" class="w-full h-full object-cover" />
                    <div class="absolute bottom-0 right-0 p-2">
                        <a href="https://www.pakutaso.com" title="フリー写真素材ぱくたそ" class="text-xs text-gray-300 hover:text-gray-400">
                            Photo フリー写真素材ぱくたそ
                        </a>
                    </div>
                </div>
                <div class="p-8 flex flex-col justify-center">
                    <div class="space-y-6">
                        <h1 class="text-5xl font-bold text-red-600">{{ code }}</h1>
                        <div class="space-y-2">
                            <p class="text-gray-700">サーバーで問題が発生しました。しばらく経ってからもう一度お試しください。</p>
                            <p class="font-semibold text-gray-800">
                                <span class="font-bold">エラー:</span> {{ message }}
                            </p>
                        </div>
                        <div>
                            <a href="/" class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                トップページに戻る
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
