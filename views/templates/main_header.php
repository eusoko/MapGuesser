<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $_ENV['APP_NAME'] ?></title>
    <link href="<?= $_ENV['STATIC_ROOT'] ?>/css/mapguesser.css?rev=<?= REVISION ?>" rel="stylesheet">
    <?php if (isset($cssFiles)) : ?>
        <?php foreach ($cssFiles as $cssFile) : ?>
            <?php
            if (!preg_match('/^http(s)?/', $cssFile)) {
                $cssFile = $_ENV['STATIC_ROOT'] .  '/' . $cssFile . '?rev=' . REVISION;
            }
            ?>
            <link href="<?= $cssFile ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;500&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= $_ENV['STATIC_ROOT'] ?>/img/favicon/192x192.png?rev=<?= REVISION ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= $_ENV['STATIC_ROOT'] ?>/img/favicon/96x96.png?rev=<?= REVISION ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $_ENV['STATIC_ROOT'] ?>/img/favicon/32x32.png?rev=<?= REVISION ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $_ENV['STATIC_ROOT'] ?>/img/favicon/16x16.png?rev=<?= REVISION ?>">
</head>
<body>
    <div id="loading">
        <img src="<?= $_ENV['STATIC_ROOT'] ?>/img/loading.svg?rev=<?= REVISION ?>" width="64" height="64">
    </div>
    <div id="cover"></div>
    <div id="modal" class="modal">
        <h2 id="modalTitle"></h2>
        <p id="modalText" class="justify marginTop"></p>
        <div id="modalButtons" class="right"></div>
    </div>