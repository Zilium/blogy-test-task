<?php 

if (!function_exists('build_url')) {
    /**
     * Собирает URL с сохранением текущих GET-параметров
     * и заменой/добавлением новых параметров.
     *
     * Примеры:
     * - build_url('/category/1', $_GET, ['page' => 2])
     * - build_url('/category/1', $_GET, ['sort' => 'views', 'page' => 1])
     *
     * @param string $baseUrl
     * @param array $currentParams
     * @param array $replaceParams
     *
     * @return string
     */
    function build_url(string $baseUrl, array $currentParams = [], array $replaceParams = []): string
    {
        $baseUrl = rtrim(SITE, '/') . '/' . ltrim($baseUrl, '/');

        $params = array_merge($currentParams, $replaceParams);

        foreach ($params as $key => $value) {
            if ($value === null || $value === '') {
                unset($params[$key]);
            }
        }

        if (isset($params['page']) && (int) $params['page'] <= 1) {
            unset($params['page']);
        }

        if (empty($params)) {
            return $baseUrl;
        }

        return $baseUrl . '?' . http_build_query($params);
    }
}