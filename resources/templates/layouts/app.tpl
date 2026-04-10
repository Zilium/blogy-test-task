<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="format-detection" content="telephone=no">

        <title>{$meta.title|escape}</title>

        {if $meta.description}
            <meta name="description" content="{$meta.description|escape}">
        {/if}

        {if $meta.keywords}
            <meta name="keywords" content="{$meta.keywords|escape}">
        {/if}

        <link rel="icon" href="/favicon.ico" type="image/x-icon">
        <meta name="theme-color" content="#fff">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

        {if !empty($styles)}
            {foreach $styles as $style}
                <link rel="stylesheet" href="{$style}">
            {/foreach}
        {/if}
    </head>
    <body>
        <div class="wrapper">
            {include file='partials/header.tpl'}

            <main class="main">
                {$content nofilter}
            </main>

            {include file='partials/footer.tpl'}
        </div>
    </body>
</html>