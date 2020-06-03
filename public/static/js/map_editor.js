(function () {
    var MapEditor = {
        map: null,
        panorama: null,
        selectedMarker: null,

        getPlace: function (placeId, marker) {
            var xhr = new XMLHttpRequest();
            xhr.responseType = 'json';
            xhr.onload = function () {
                document.getElementById('loading').style.visibility = 'hidden';

                if (!this.response.panoId) {
                    document.getElementById('noPano').style.visibility = 'visible';

                    marker.noPano = true;

                    return;
                }

                MapEditor.loadPano(this.response.panoId);
            };

            xhr.open('GET', '/admin/place.json/' + placeId, true);
            xhr.send();
        },

        loadPano: function (panoId) {
            MapEditor.panorama.setVisible(true);
            MapEditor.panorama.setPov({ heading: 0, pitch: 0 });
            MapEditor.panorama.setZoom(0);
            MapEditor.panorama.setPano(panoId);
        },

        select: function (marker) {
            document.getElementById('loading').style.visibility = 'visible';

            document.getElementById('map').classList.add('selected');
            document.getElementById('control').classList.add('selected');
            document.getElementById('noPano').style.visibility = 'hidden';
            document.getElementById('panorama').style.visibility = 'visible';
            document.getElementById('placeControl').style.visibility = 'visible';

            MapEditor.resetSelected();
            MapEditor.selectedMarker = marker;

            marker.setIcon(IconCollection.iconBlue);
            marker.setZIndexOffset(2000);

            MapEditor.map.invalidateSize(true);
            MapEditor.map.panTo(marker.getLatLng());

            MapEditor.panorama.setVisible(false);

            MapEditor.getPlace(marker.placeId, marker);
        },

        resetSelected: function () {
            if (!MapEditor.selectedMarker) {
                return;
            }

            MapEditor.selectedMarker.setIcon(MapEditor.selectedMarker.noPano ? IconCollection.iconRed : IconCollection.iconGreen);
            MapEditor.selectedMarker.setZIndexOffset(1000);
        }
    };

    var IconCollection = {
        iconGreen: L.icon({
            iconUrl: '/static/img/markers/marker-green.svg',
            iconSize: [24, 32],
            iconAnchor: [12, 32]
        }),
        iconRed: L.icon({
            iconUrl: '/static/img/markers/marker-red.svg',
            iconSize: [24, 32],
            iconAnchor: [12, 32]
        }),
        iconBlue: L.icon({
            iconUrl: '/static/img/markers/marker-blue.svg',
            iconSize: [24, 32],
            iconAnchor: [12, 32]
        }),
    };

    var Util = {
        getHighResData: function () {
            if (window.devicePixelRatio >= 4) {
                return { ppi: 320, tileSize: 128, zoomOffset: 1 };
            } else if (window.devicePixelRatio >= 2) {
                return { ppi: 250, tileSize: 256, zoomOffset: 0 };
            } else {
                return { ppi: 72, tileSize: 512, zoomOffset: -1 };
            }
        }
    };

    MapEditor.map = L.map('map', {
        attributionControl: false,
        zoomControl: false
    });

    var highResData = Util.getHighResData();

    L.tileLayer(tileUrl, {
        subdomains: '1234',
        ppi: highResData.ppi,
        tileSize: highResData.tileSize,
        zoomOffset: highResData.zoomOffset,
        minZoom: 0,
        maxZoom: 20
    }).addTo(MapEditor.map);

    MapEditor.map.fitBounds(L.latLngBounds({ lat: mapBounds.south, lng: mapBounds.west }, { lat: mapBounds.north, lng: mapBounds.east }));

    for (var i = 0; i < places.length; ++i) {
        var marker = L.marker({ lat: places[i].lat, lng: places[i].lng }, {
            icon: places[i].noPano ? IconCollection.iconRed : IconCollection.iconGreen,
            zIndexOffset: 1000
        })
            .addTo(MapEditor.map)
            .on('click', function () {
                if (MapEditor.selectedMarker === this) {
                    return;
                }

                MapEditor.select(this);
            });

        marker.placeId = places[i].id;
        marker.noPano = places[i].noPano;
    }

    MapEditor.panorama = new google.maps.StreetViewPanorama(document.getElementById('panorama'), {
        // switch off fullscreenControl because positioning doesn't work
        fullscreenControl: false,
        fullscreenControlOptions: {
            position: google.maps.ControlPosition.LEFT_TOP
        },
        motionTracking: false
    });

    document.getElementById('cancelButton').onclick = function () {
        document.getElementById('map').classList.remove('selected');
        document.getElementById('control').classList.remove('selected');
        document.getElementById('noPano').style.visibility = 'hidden';
        document.getElementById('panorama').style.visibility = 'hidden';
        document.getElementById('placeControl').style.visibility = 'hidden';

        MapEditor.resetSelected();
        MapEditor.selectedMarker = null;

        MapEditor.map.invalidateSize(true);
    };
})();
