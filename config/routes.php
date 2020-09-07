<?php
use Cake\Core\Plugin;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\Router;

Router::defaultRouteClass(DashedRoute::class);

// タグ付けられたアクションのために追加された新しいルート。
// 末尾の `*` は、このアクションがパラメーターを渡されることを
// CakePHP に伝えます。
Router::scope(
    '/articles',
    ['controller' => 'Articles'],
    function ($routes) {
        $routes->connect('/tagged/*', ['action' => 'tags']);
    }
);

Router::scope('/', function ($routes) {
    // デフォルトの home と /pages/* ルートを接続。
    $routes->connect('/', [
        'controller' => 'Pages',
        'action' => 'display', 'home'
    ]);
    $routes->connect('/pages/*', [
        'controller' => 'Pages',
        'action' => 'display'
    ]);

    // 規約に基づいたデフォルトルートを接続。
    $routes->fallbacks();
});

Plugin::routes();