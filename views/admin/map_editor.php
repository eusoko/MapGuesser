<?php $cssFiles = ['/static/node_modules/leaflet/dist/leaflet.css', '/static/css/map_editor.css']; ?>
<?php require ROOT . '/views/templates/main_header.php'; ?>
<div class="header small">
    <div class="grid">
        <h1>
            <a href="/maps" title="Back to playable maps">
                <img class="inline" src="/static/img/icon.svg">
                <span>MapGuesser</span>
            </a>
        </h1>
        <p>Added: <span id="added" class="mono bold">0</span> | Edited: <span id="edited" class="mono bold">0</span> | Deleted: <span id="deleted" class="mono bold">0</span></p>
    </div>
</div>
<div id="map"></div>
<div id="control">
    <button id="saveButton" class="fullWidth">Save</button>
    <a class="button gray fullWidth marginTop" href="/admin/maps" title="Back to maps">Back to maps</a>
</div>
<div id="panorama"></div>
<div id="noPano">
    <p class="bold">No panorama is available for this location.</p>
</div>
<div id="placeControl">
    <button id="applyButton" class="fullWidth">Apply</button>
    <button id="cancelButton" class="gray fullWidth marginTop">Cancel</button>
    <button id="deleteButton" class="red fullWidth marginTop">Delete</button>
</div>
<script>
    var tileUrl = '<?= $_ENV['LEAFLET_TILESERVER_URL'] ?>';
    var mapId = '<?= $mapId ?>';
    var mapBounds = <?= json_encode($bounds) ?>;
    var places = <?= json_encode($places) ?>;
</script>
<script src="/static/node_modules/leaflet/dist/leaflet.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= $_ENV['GOOGLE_MAPS_JS_API_KEY'] ?>"></script>
<script src="/static/js/map_editor.js"></script>
<?php require ROOT . '/views/templates/main_footer.php'; ?>