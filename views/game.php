<?php
$cssFiles = [
    'css/game.css'
];
$jsFiles = [
    'https://maps.googleapis.com/maps/api/js?key=' . $_ENV['GOOGLE_MAPS_JS_API_KEY'],
    'js/game.js',
];
?>
<?php require ROOT . '/views/templates/main_header.php'; ?>
<header class="small">
    <h1>
        <a href="/" title="<?= $_ENV['APP_NAME'] ?>">
            <img class="inline" width="1em" height="1em" src="<?= $_ENV['STATIC_ROOT'] ?>/img/icon.svg?rev=<?= REVISION ?>"><!--
         --><span><?= $_ENV['APP_NAME'] ?></span>
        </a>
    </h1>
    <p class="header">
        <span id="mapName" class="bold"><?= $mapName ?></span><!--
        --><span>Round <span id="currentRound" class="bold"></span></span><!--
        --><span>Score <span id="currentScoreSum" class="bold"></span></span>
    </p>
</header>
<div class="full">
    <div id="panoCover"></div>
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
</div>
<script>
    var mapId = <?= $mapId ?>;
    var mapBounds = <?= json_encode($bounds) ?>;
</script>
<?php require ROOT . '/views/templates/main_footer.php'; ?>