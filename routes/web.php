<?php

use Core\Routing\Router;

Router::add('^/$', [
    'controller' => 'home',
    'action' => 'index',
]);

Router::add('^/category/(?P<id>.+)$', [
    'controller' => 'category',
    'action' => 'index',
]);

Router::add('^/article/(?P<id>.+)$', [
    'controller' => 'article',
    'action' => 'index',
]);