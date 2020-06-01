(function () {
    var MapEditor = {
        map: null,
        panorama: null,
        selectedMarker: null,

        loadPano: function (data, status) {
            document.getElementById('loading').style.visibility = 'hidden';

            if (status !== google.maps.StreetViewStatus.OK) {
                document.getElementById('noPano').style.visibility = 'visible';
                return;
            }

            MapEditor.panorama.setVisible(true);
            MapEditor.panorama.setPov({ heading: 0, pitch: 0 });
            MapEditor.panorama.setZoom(0);
            MapEditor.panorama.setPano(data.location.pano);
        },

        select: function (marker) {
            document.getElementById('loading').style.visibility = 'visible';

            document.getElementById('map').classList.add('selected');
            document.getElementById('noPano').style.visibility = 'hidden';
            document.getElementById('panorama').style.display = 'block';
            document.getElementById('placeControl').style.visibility = 'visible';

            MapEditor.resetSelected();
            MapEditor.selectedMarker = marker;

            marker.setIcon(L.icon({
                iconUrl: '/static/img/markers/marker-blue.svg',
                iconSize: [24, 32],
                iconAnchor: [12, 32]
            }));
            marker.setZIndexOffset(2000);

            MapEditor.map.invalidateSize(true);
            MapEditor.map.panTo(marker.getLatLng());

            MapEditor.panorama.setVisible(false);

            var sv = new google.maps.StreetViewService();
            sv.getPanorama({ location: marker.getLatLng(), preference: google.maps.StreetViewPreference.NEAREST, source: google.maps.StreetViewSource.OUTDOOR }, MapEditor.loadPano);
        },

        resetSelected: function () {
            if (!MapEditor.selectedMarker) {
                return;
            }

            MapEditor.selectedMarker.setIcon(L.icon({
                iconUrl: '/static/img/markers/marker-green.svg',
                iconSize: [24, 32],
                iconAnchor: [12, 32]
            }));
            MapEditor.selectedMarker.setZIndexOffset(1000);
        }
    };

    MapEditor.map = L.map('map', {
        attributionControl: false,
        zoomControl: false
    });

    L.tileLayer(tileUrl, {
        minZoom: 0,
        maxZoom: 20
    }).addTo(MapEditor.map);

    MapEditor.map.fitBounds(L.latLngBounds({ lat: mapBounds.south, lng: mapBounds.west }, { lat: mapBounds.north, lng: mapBounds.east }));

    for (var i = 0; i < places.length; ++i) {
        var marker = L.marker({ lat: places[i].lat, lng: places[i].lng }, {
            icon: L.icon({
                iconUrl: '/static/img/markers/marker-green.svg',
                iconSize: [24, 32],
                iconAnchor: [12, 32]
            }),
            zIndexOffset: 1000
        })
            .addTo(MapEditor.map)
            .on('click', function () {
                MapEditor.select(this);
            });
    }

    MapEditor.panorama = new google.maps.StreetViewPanorama(document.getElementById('panorama'), {
        // switch off fullscreenControl because positioning doesn't work
        fullscreenControl: false,
        fullscreenControlOptions: {
            position: google.maps.ControlPosition.LEFT_TOP
        }
    });

    document.getElementById('cancelButton').onclick = function () {
        document.getElementById('map').classList.remove('selected');
        document.getElementById('noPano').style.visibility = 'hidden';
        document.getElementById('panorama').style.display = 'none';
        document.getElementById('placeControl').style.visibility = 'hidden';

        MapEditor.resetSelected();
        MapEditor.selectedMarker = null;

        MapEditor.map.invalidateSize(true);
    };
})();
