<?php
$cssFiles = [
    'css/maps.css'
];
$jsFiles = [
    'js/maps.js',
];
if ($isAdmin) {
    $jsFiles[] = 'js/maps_admin.js';
}
?>
<?php require ROOT . '/views/templates/main_header.php'; ?>
<?php require ROOT . '/views/templates/header.php'; ?>
    <div id="mapContainer">
        <?php foreach ($maps as $map): ?>
            <div class="mapItem">
                <div class="title">
                    <p class="title"><?= $map['name'] ?></p>
                </div>
                <div class="imgContainer" data-bound-south-lat="<?= $map['bound_south_lat'] ?>" data-bound-west-lng="<?= $map['bound_west_lng'] ?>" data-bound-north-lat="<?= $map['bound_north_lat'] ?>" data-bound-east-lng="<?= $map['bound_east_lng'] ?>"></div>
                <div class="inner">
                    <div class="info">
                        <p>
                            <?php /* Copyright (c) 2019 The Bootstrap Authors. License can be found in 'USED_SOFTWARE' in section 'Bootstrap Icons'. */ ?>
                            <svg class="inline" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                            </svg><!--
                         --><?= $map['num_places'] ?> places
                        </p>
                        <p>
                            <?php /* Copyright (c) 2019 The Bootstrap Authors. License can be found in 'USED_SOFTWARE' in section 'Bootstrap Icons'. */ ?>
                            <svg class="inline" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M12.5 2h-9V1h9v1zm-10 1.5v9h-1v-9h1zm11 9v-9h1v9h-1zM3.5 14h9v1h-9v-1z" />
                                <path fill-rule="evenodd" d="M14 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 1a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 11a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 1a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM2 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 1a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 11a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 1a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" />
                            </svg><!--
                         -->~ <?= $map['area'][0] ?> <?= $map['area'][1] ?><sup>2</sup>
                        </p>
                    </div>
                    <div class="description marginTop">
                        <p class="small center"><?= $map['description'] ?></p>
                    </div>
                </div>
                <?php if ($isAdmin): ?>
                    <div class="buttonContainer">
                        <a class="button fullWidth noRightRadius" href="/game/<?= $map['id']; ?>" title="Play map '<?= $map['name'] ?>'">Play this map</a>
                        <a class="button yellow fullWidth noLeftRadius noRightRadius" href="/admin/mapEditor/<?= $map['id']; ?>" title="Edit map '<?= $map['name'] ?>'">Edit</a>
                        <button class="button red fullWidth noLeftRadius deleteButton" data-map-id="<?= $map['id'] ?>" data-map-name="<?= htmlspecialchars($map['name']) ?>" title="Delete map '<?= $map['name'] ?>'">Delete</button>
                    </div>
                <?php else: ?>
                    <a class="button fullWidth" href="/game/<?= $map['id']; ?>" title="Play map '<?= $map['name'] ?>'">Play this map</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if ($isAdmin): ?>
            <div class="mapItem new">
                <a class="button green fullWidth" href="/admin/mapEditor" title="Add new map">
                    Add new map
                </a>
            </div>
        <?php else: ?>
            <div class="mapItem"></div>
        <?php endif; ?>
        <?php if (count($maps) < 3): ?>
            <?php for ($i = 0; $i < 3 - count($maps); ++$i): ?>
                <div class="mapItem"></div>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
<?php require ROOT . '/views/templates/footer.php'; ?>
<?php require ROOT . '/views/templates/main_footer.php'; ?>