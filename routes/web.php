<?php

use Core\Routing\Router;

Router::add('^/$', [
    'controller' => 'home',
    'action' => 'index',
]);

Router::add('^/category/(?P<id>\d+)$', [
    'controller' => 'category',
    'action' => 'index',
]);

Router::add('^/article/(?P<id>\d+)$', [
    'controller' => 'article',
    'action' => 'index',
]);