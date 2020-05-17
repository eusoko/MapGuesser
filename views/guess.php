<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MapGuesser</title>
    <link rel="stylesheet" type="text/css" href="static/css/mapguesser.css">
</head>
<body>
    <div id="panorama"></div>
    <div id="guess">
        <div id="guessMap"></div>
        <div id="guessButtonContainer">
            <button id="guessButton" disabled>Guess</button>
        </div>
    </div>
    <script>
        var realPosition = <?= $realPosition->toJson() ?>;
        var guessMapBounds = <?= $bounds->toJson() ?>;
    </script>
    <script src="static/js/mapguesser.js" async defer></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= $_ENV['GOOGLE_MAPS_JS_API_KEY'] ?>&callback=initialize" async defer></script>
</body>
</html>
