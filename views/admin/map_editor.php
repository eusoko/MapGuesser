<?php
$cssFiles = [
    'node_modules/leaflet/dist/leaflet.css',
    'css/map_editor.css',
    'node_modules/leaflet.markercluster/dist/MarkerCluster.css',
    'node_modules/leaflet.markercluster/dist/MarkerCluster.Default.css'
];
$jsFiles = [
    'node_modules/leaflet/dist/leaflet.js',
    'node_modules/leaflet.markercluster/dist/leaflet.markercluster.js',
    'https://maps.googleapis.com/maps/api/js?key=' . $_ENV['GOOGLE_MAPS_JS_API_KEY'],
    'js/map_editor.js',
];
?>
<?php require ROOT . '/views/templates/main_header.php'; ?>
<div class="header small">
    <div class="grid">
        <h1>
            <a href="/maps" title="Back to playable maps">
                <img class="inline" width="1em" height="1em" src="<?= $_ENV['STATIC_ROOT'] ?>/img/icon.svg?rev=<?= REVISION ?>">
                <span>MapGuesser</span>
            </a>
        </h1>
        <p>
            <span class="bold"><a href="javascript:;" id="mapName" title="Edit map data"><?= $mapName ?></a></span><!--
         --><span><!--
                <?php /* Copyright (c) 2019 The Bootstrap Authors. License can be found in 'USED_SOFTWARE' in section 'Bootstrap Icons'. */ ?>
             --><svg class="inline" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M8 3.5a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5H4a.5.5 0 0 1 0-1h3.5V4a.5.5 0 0 1 .5-.5z"/>
                    <path fill-rule="evenodd" d="M7.5 8a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1H8.5V12a.5.5 0 0 1-1 0V8z"/>
                    <path fill-rule="evenodd" d="M14 1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                </svg>
                <span id="added" class="bold">0</span><!--
         --></span><!--
         --><span><!--
                <?php /* Copyright (c) 2019 The Bootstrap Authors. License can be found in 'USED_SOFTWARE' in section 'Bootstrap Icons'. */ ?>
             --><svg class="inline" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.502 1.94a.5.5 0 0 1 0 .706a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456l-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>                    <path fill-rule="evenodd" d="M14 1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                </svg>
                <span id="edited" class="bold">0</span><!--
         --></span><!--
         --><span><!--
                <?php /* Copyright (c) 2019 The Bootstrap Authors. License can be found in 'USED_SOFTWARE' in section 'Bootstrap Icons'. */ ?>
             --><svg class="inline" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M3.5 8a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.5-.5z"/>
                    <path fill-rule="evenodd" d="M14 1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                </svg>
                <span id="deleted" class="bold">0</span><!--
         --></span>
        </p>
    </div>
</div>
<div id="metadata" class="modal">
    <h2>Edit map data</h2>
    <form id="metadataForm" class="marginTop">
        <input class="fullWidth" type="text" name="name" value="<?= $mapName ?>" placeholder="Name of the map">
        <textarea  class="fullWidth marginTop" name="description" rows="4" placeholder="Description of the map"><?= $mapDescription ?></textarea>
        <div class="right">
            <button class="marginTop marginRight" type="submit">Apply</button><!--
         --><button id="closeMetadataButton" class="gray marginTop" type="button">Close</button>
        </div>
    </form>
</div>
<div id="map"></div>
<div id="control">
    <button id="saveButton" class="fullWidth" disabled>Save</button>
</div>
<div id="panorama"></div>
<div id="noPano">
    <p class="bold">No panorama is available for this location.</p>
</div>
<div id="placeControl">
    <button id="applyButton" class="fullWidth">Apply</button>
    <button id="closeButton" class="gray fullWidth marginTop">Close</button>
    <button id="deleteButton" class="red fullWidth marginTop">Delete</button>
</div>
<script>
    var tileUrl = '<?= $_ENV['LEAFLET_TILESERVER_URL'] ?>';
    var tileAttribution = '<?= $_ENV['LEAFLET_TILESERVER_ATTRIBUTION'] ?>';
    var mapId = <?= $mapId ?>;
    var mapBounds = <?= json_encode($bounds) ?>;
    var places = <?= json_encode($places, JSON_FORCE_OBJECT) ?>;
</script>
<?php require ROOT . '/views/templates/main_footer.php'; ?>