<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Core\Controller as BaseController;

use App\Foundation\Support\ScssBuilder;

/**
 * Базовый контроллер.
 */
class Controller extends BaseController
{
    /**
     * Шаблон публичной части.
     *
     * @var string|bool
     */
    protected string|bool $layout = 'app';

    /**
     * Общие CSS-бандлы.
     *
     * @var string[]
     */
    protected array $styleBundles = ['common'];

    /**
     * Выполняет базовую инициализацию контроллера.
     * 
     * @return void
     */
    protected function boot(): void
    {
        parent::boot();
    }

    /**
     * Инициализирует layout и общие данные представления.
     * 
     * @return void
     */
    protected function init(): void
    {
        $this->view->setLayout($this->layout);

        $this->view->share('app', [
            'site' => SITE,
            'current_year' => date('Y'),
        ]);

        $this->view->share('route', $this->route);
    }

    /**
     * Подключает CSS-бандлы и передает ссылки на стили в представление.
     * 
     * @param array $bundles
     * 
     * @return void
     */
    protected function shareStyleBundles(array $bundles = []): void
    {
        foreach ($bundles as $bundle) {
            if (!in_array($bundle, $this->styleBundles, true)) {
                $this->styleBundles[] = $bundle;
            }
        }

        /** @var ScssBuilder $scssBuilder */
        $scssBuilder = $this->container->make(ScssBuilder::class, [
            'devMode' => (bool) config('app.scss.dev_mode', false)
        ]);
        $scssBuilder->buildBundles($this->styleBundles);

        $this->view->share(
            'styles',
            $scssBuilder->makeStyleLinks($this->styleBundles)
        );
    }
}