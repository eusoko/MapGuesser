<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MapGuesser</title>
    <link href="static/css/mapguesser.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;500&family=Roboto+Mono:wght@300;500&display=swap" rel="stylesheet">
</head>
<body>
    <div class="header small">
        <div class="grid">
        <h1>
            <a href="maps" title="Back to playable maps">
                <!-- Copyright (c) 2019 The Bootstrap Authors. License can be found in 'USED_SOFTWARE' in section 'Bootstrap Icons'. -->
                <svg class="inline" width="1em" height="1em" viewBox="0 0 16 16" fill="#28a745" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M15.817.613A.5.5 0 0 1 16 1v13a.5.5 0 0 1-.402.49l-5 1a.502.502 0 0 1-.196 0L5.5 14.51l-4.902.98A.5.5 0 0 1 0 15V2a.5.5 0 0 1 .402-.49l5-1a.5.5 0 0 1 .196 0l4.902.98 4.902-.98a.5.5 0 0 1 .415.103zM10 2.41l-4-.8v11.98l4 .8V2.41zm1 11.98l4-.8V1.61l-4 .8v11.98zm-6-.8V1.61l-4 .8v11.98l4-.8z" />
                </svg>
                <span>MapGuesser</span>
            </a>
        </h1>
        <p id="roundInfo">Round: <span id="currentRound" class="mono bold"></span> | Score: <span id="currentScoreSum" class="mono bold"></span></p>
    </div>
    </div>
    <div id="loading">
        <img src="static/img/loading.svg">
    </div>
    <div id="panorama"></div>
    <div id="showGuessButtonContainer">
        <button id="showGuessButton" class="fullWidth">Show guess map</button>
    </div>
    <div id="guess">
        <div id="closeGuessButtonContainer" class="buttonContainer marginBottom">
            <button id="closeGuessButton" class="fullWidth gray">Close</button>
        </div>
        <div id="map"></div>
        <div id="guessButtonContainer" class="buttonContainer marginTop">
            <button id="guessButton" class="fullWidth" disabled>Guess</button>
        </div>
        <div id="resultInfo">
            <div id="distanceInfo">
                <p>You were <span id="distance" class="bold"></span> close.</p>
                <p class="bold">Game finished.</p>
            </div>
            <div id="scoreInfo">
                <p>You earned <span id="score" class="bold"></span> points.</p>
                <p>You got <span id="scoreSum" class="bold"></span> points in total.</p>
            </div>
            <div>
                <div id="scoreBarBase">
                    <div id="scoreBar"></div>
                </div>
            </div>
        </div>
        <div id="continueButtonContainer" class="buttonContainer marginTop">
            <button id="continueButton" class="fullWidth">Continue</button>
            <button id="showSummaryButton" class="fullWidth">Show summary</button>
            <button id="startNewGameButton" class="fullWidth">Play this map again</button>
        </div>
    </div>
    <script>
        var mapId = '<?= $mapId ?>';
        var mapBounds = <?= json_encode($bounds) ?>;
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= $_ENV['GOOGLE_MAPS_JS_API_KEY'] ?>"></script>
    <script src="static/js/game.js"></script>
</body>
</html>
