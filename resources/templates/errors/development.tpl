<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="format-detection" content="telephone=no">

        <title>Ошибка</title>
        <link rel="icon" href="/favicon.ico" type="image/x-icon">
        <meta name="theme-color" content="#fff">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    </head>
    <body>
        <div class="wrapper">
            <main class="main">
                <h1>Произошла ошибка</h1>
                <p><b>Код ошибки:</b> <?= $data['errno']; ?></p>
                <p><b>Текст ошибки:</b> <?= $data['message']; ?></p>
                <p><b>Файл, в котором произошла ошибка:</b> <?= $data['file']; ?></p>
                <p><b>Строка, в которой произошла ошибка:</b> <?= $data['line']; ?></p>
                <p><b>Статус:</b> <?= $data['status']; ?></p>
            </main>
        </div>
    </body>
</html>
