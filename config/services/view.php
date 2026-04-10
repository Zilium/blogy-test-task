<?php

declare(strict_types=1);

use Core\Container;
use Core\View;

return function (Container $container): void {
    $container->singleton(Smarty::class, function () {
        $smarty = new Smarty();

        $smarty->setTemplateDir(RESOURCES_DIR . '/templates/');
        $smarty->setCompileDir(STORAGE_DIR . '/smarty/compile/');
        $smarty->setCacheDir(STORAGE_DIR . '/smarty/cache/');
        $smarty->setConfigDir(RESOURCES_DIR . '/smarty/configs/');
        
        $smarty->caching = config('app.smarty.caching', false)
            ? Smarty::CACHING_LIFETIME_CURRENT
            : Smarty::CACHING_OFF;
        $smarty->compile_check = (bool) config('app.smarty.compile_check', false);
        $smarty->force_compile = (bool) config('app.smarty.force_compile', false);

        $smarty->registerPlugin(
            'function',
            'build_url',
            static function (array $params): string {
                $baseUrl = (string) ($params['base_url'] ?? '');
                $currentParams = $params['params'] ?? [];
                
                if (!is_array($currentParams)) {
                    $currentParams = [];
                }

                unset($params['base_url'], $params['params']);

                return build_url($baseUrl, $currentParams, $params);
            }
        );

        return $smarty;
    });

    $container->singleton(View::class, function () use ($container) {
        return new View(
            $container->get(Smarty::class)
        );
    });
};