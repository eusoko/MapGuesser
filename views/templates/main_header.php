<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MapGuesser</title>
    <link href="/static/css/mapguesser.css" rel="stylesheet">
    <?php if (isset($cssFiles)) : ?>
        <?php foreach ($cssFiles as $cssFile) : ?>
            <link href="<?= $cssFile ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;500&family=Roboto+Mono:wght@300;500&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="192x192" href="/static/img/favicon/192x192.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/static/img/favicon/96x96.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/static/img/favicon/32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/static/img/favicon/16x16.png">
</head>
<body>
    <div id="loading">
        <img src="/static/img/loading.svg">
    </div>