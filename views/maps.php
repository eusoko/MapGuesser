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
    <div id="loading">
        <img src="static/img/loading.svg">
    </div>
    <div class="header">
        <h1>
            <!-- Copyright (c) 2019 The Bootstrap Authors. License can be found in 'USED_SOFTWARE' in section 'Bootstrap Icons'. -->
            <svg class="inline" width="1em" height="1em" viewBox="0 0 16 16" fill="#28a745" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M15.817.613A.5.5 0 0 1 16 1v13a.5.5 0 0 1-.402.49l-5 1a.502.502 0 0 1-.196 0L5.5 14.51l-4.902.98A.5.5 0 0 1 0 15V2a.5.5 0 0 1 .402-.49l5-1a.5.5 0 0 1 .196 0l4.902.98 4.902-.98a.5.5 0 0 1 .415.103zM10 2.41l-4-.8v11.98l4 .8V2.41zm1 11.98l4-.8V1.61l-4 .8v11.98zm-6-.8V1.61l-4 .8v11.98l4-.8z" />
            </svg>
            MapGuesser
        </h1>
    </div>
    <div class="main">
        <h2>Playable maps</h2>
        <div class="mapContainer">
            <?php foreach ($maps as $map) : ?>
                <div class="mapItem">
                    <div class="title">
                        <p class="title"><?= $map['name'] ?></p>
                    </div>
                    <img src="https://maps.googleapis.com/maps/api/staticmap?size=350x175&visible=<?= $map['bound_south_lat'] . ',' . $map['bound_west_lng'] . '|' . $map['bound_north_lat'] . ',' . $map['bound_east_lng'] ?>&key=<?= $_ENV['GOOGLE_MAPS_JS_API_KEY'] ?>">
                    <div class="inner">
                        <div class="info">
                            <p>
                                <!-- Copyright (c) 2019 The Bootstrap Authors. License can be found in 'USED_SOFTWARE' in section 'Bootstrap Icons'. -->
                                <svg class="inline" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                                </svg>
                                <?= $map['num_places'] ?> places
                            </p>
                            <p>
                                <!-- Copyright (c) 2019 The Bootstrap Authors. License can be found in 'USED_SOFTWARE' in section 'Bootstrap Icons'. -->
                                <svg class="inline" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M12.5 2h-9V1h9v1zm-10 1.5v9h-1v-9h1zm11 9v-9h1v9h-1zM3.5 14h9v1h-9v-1z" />
                                    <path fill-rule="evenodd" d="M14 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 1a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 11a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 1a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM2 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 1a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 11a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 1a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" />
                                </svg>
                                ~ <?= $map['area'][0] ?> <?= $map['area'][1] ?><sup>2</sup>
                            </p>
                        </div>
                        <p class="small justify marginTop"><?= $map['description'] ?></p>
                    </div>
                    <a class="button fullWidth" href="game?map=<?= $map['id']; ?>" title="Play map '<?= $map['name'] ?>'">Play this map</a>
                </div>
            <?php endforeach; ?>
            <?php if (count($maps) < 4): ?>
                <?php for ($i = 0; $i < 4 - count($maps); ++$i): ?>
                    <div class="mapItem"></div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>
    <script>
        document.getElementById('loading').style.visibility = 'hidden';

        window.addEventListener('beforeunload', function (e) {
            document.getElementById('loading').style.visibility = 'visible';
        });
    </script>
</body>
</html>
