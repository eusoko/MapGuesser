<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MapGuesser</title>
    <link href="static/css/mapguesser.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;500&display=swap" rel="stylesheet">
</head>
<body>
    <div id="panorama"></div>
    <div id="guess">
        <div id="guessMap"></div>
        <div id="guessButtonContainer">
            <button id="guessButton" disabled>Guess</button>
        </div>
    </div>
    <div id="result">
        <div id="resultMap"></div>
        <div id="resultInfo">
            <div>
                <p>You were <span id="distance" class="bold"></span> close.</p>
            </div>
            <div>
                <p>You earned <span id="score" class="bold"></span> points.</p>
            </div>
            <div>
                <div id="scoreBarBase"><div id="scoreBar"></div></div>
            </div>
            <div>
                <button id="continueButton">Continue</button>
            </div>
        </div>
    </div>
    <script>
        var mapArea = <?= $bounds->calculateApproximateArea() ?>;
        var guessMapBounds = <?= $bounds->toJson() ?>;
    </script>
    <script src="static/js/mapguesser.js" async defer></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= $_ENV['GOOGLE_MAPS_JS_API_KEY'] ?>&callback=initialize" async defer></script>
</body>
</html>
