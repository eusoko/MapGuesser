'use strict';

(function () {
    var MapEditor = {
        metadata: {
            name: null,
            description: null
        },
        map: null,
        panorama: null,
        selectedMarker: null,
        added: {},
        edited: {},
        deleted: {},

        editMetadata: function () {
            var form = document.getElementById('metadataForm');

            MapEditor.metadata.name = form.elements.name.value;
            MapEditor.metadata.description = form.elements.description.value;

            document.getElementById('mapName').innerHTML = form.elements.name.value ? form.elements.name.value : '[unnamed map]';

            document.getElementById('metadata').style.visibility = 'hidden';

            document.getElementById('saveButton').disabled = false;
        },

        getPlace: function (placeId, marker) {
            var xhr = new XMLHttpRequest();
            xhr.responseType = 'json';
            xhr.onload = function () {
                document.getElementById('loading').style.visibility = 'hidden';

                if (!this.response.panoId) {
                    document.getElementById('noPano').style.visibility = 'visible';

                    places[marker.placeId].panoId = -1;
                    places[marker.placeId].noPano = true;

                    return;
                }

                places[marker.placeId].panoId = this.response.panoId;
                places[marker.placeId].noPano = false;

                MapEditor.loadPano(this.response.panoId, places[marker.placeId].pov);
            };

            xhr.open('GET', '/admin/place.json/' + placeId, true);
            xhr.send();
        },

        loadPano: function (panoId, pov) {
            MapEditor.panorama.setVisible(true);
            MapEditor.panorama.setPov({ heading: pov.heading, pitch: pov.pitch });
            MapEditor.panorama.setZoom(pov.zoom);
            MapEditor.panorama.setPano(panoId);
        },

        loadPanoForNewPlace: function (panoLocationData) {
            var placeId = MapEditor.selectedMarker.placeId;

            if (!panoLocationData) {
                places[placeId].panoId = -1;
                places[placeId].noPano = true;

                document.getElementById('noPano').style.visibility = 'visible';

                return;
            }

            var latLng = panoLocationData.latLng;

            places[placeId].panoId = panoLocationData.pano;
            places[placeId].lat = latLng.lat();
            places[placeId].lng = latLng.lng();

            MapEditor.selectedMarker.setLatLng({ lat: places[placeId].lat, lng: places[placeId].lng });
            MapEditor.map.panTo(MapEditor.selectedMarker.getLatLng());

            MapEditor.panorama.setVisible(true);
            MapEditor.panorama.setPov({ heading: 0.0, pitch: 0.0 });
            MapEditor.panorama.setZoom(0.0);
            MapEditor.panorama.setPano(panoLocationData.pano);
        },

        requestPanoData: function (location, canBeIndoor) {
            var sv = new google.maps.StreetViewService();

            sv.getPanorama({
                location: location,
                preference: google.maps.StreetViewPreference.NEAREST,
                radius: 100,
                source: canBeIndoor ? google.maps.StreetViewSource.DEFAULT : google.maps.StreetViewSource.OUTDOOR
            }, function (data, status) {
                var panoLocationData = status === google.maps.StreetViewStatus.OK ? data.location : null;

                if (panoLocationData === null && !canBeIndoor) {
                    MapEditor.requestPanoData(location, true);
                    return;
                }

                document.getElementById('loading').style.visibility = 'hidden';

                MapEditor.loadPanoForNewPlace(panoLocationData);
            });
        },

        select: function (marker) {
            if (MapEditor.selectedMarker === marker) {
                MapEditor.closePlace();
                return;
            }

            document.getElementById('metadata').classList.add('selected');
            document.getElementById('map').classList.add('selected');
            document.getElementById('control').classList.add('selected');
            document.getElementById('noPano').style.visibility = 'hidden';
            document.getElementById('panorama').style.visibility = 'visible';
            document.getElementById('placeControl').style.visibility = 'visible';

            MapEditor.resetSelected();
            MapEditor.selectedMarker = marker;

            MapEditor.map.invalidateSize(true);
            MapEditor.map.panTo(marker.getLatLng());

            MapEditor.panorama.setVisible(false);

            if (marker.placeId) {
                marker.setIcon(IconCollection.iconBlue);
                marker.setZIndexOffset(2000);

                document.getElementById('deleteButton').style.display = 'block';

                if (places[marker.placeId].panoId) {
                    if (places[marker.placeId].panoId === -1) {
                        document.getElementById('noPano').style.visibility = 'visible';
                    } else {
                        MapEditor.loadPano(places[marker.placeId].panoId, places[marker.placeId].pov);
                    }

                    return;
                }

                document.getElementById('loading').style.visibility = 'visible';

                MapEditor.getPlace(marker.placeId, marker);
            } else {
                marker.placeId = 'new_' + new Date().getTime();

                var latLng = marker.getLatLng();

                places[marker.placeId] = { id: null, lat: latLng.lat, lng: latLng.lng, panoId: null, pov: { heading: 0.0, pitch: 0.0, zoom: 0 }, noPano: false };

                document.getElementById('loading').style.visibility = 'visible';

                MapEditor.requestPanoData(latLng);
            }
        },

        resetSelected: function (del) {
            if (!MapEditor.selectedMarker) {
                return;
            }

            var placeId = MapEditor.selectedMarker.placeId

            if (places[placeId].id && !del) {
                MapEditor.selectedMarker.setIcon(places[placeId].noPano ? IconCollection.iconRed : IconCollection.iconGreen);
                MapEditor.selectedMarker.setZIndexOffset(1000);
            } else {
                delete places[placeId];
                MapEditor.map.removeLayer(MapEditor.selectedMarker);
            }

            document.getElementById('deleteButton').style.display = 'none';
        },

        applyPlace: function () {
            var placeId = MapEditor.selectedMarker.placeId;

            if (!places[placeId].noPano) {
                var latLng = MapEditor.panorama.getPosition();
                var pov = MapEditor.panorama.getPov();
                var zoom = MapEditor.panorama.getZoom();

                places[placeId].lat = latLng.lat();
                places[placeId].lng = latLng.lng();
                places[placeId].panoId = MapEditor.panorama.getPano();
                places[placeId].pov = { heading: pov.heading, pitch: pov.pitch, zoom: zoom };
            }

            if (!places[placeId].id) {
                places[placeId].id = placeId;
                MapEditor.added[placeId] = places[placeId];

                document.getElementById('added').innerHTML = String(Object.keys(MapEditor.added).length);

                document.getElementById('deleteButton').style.display = 'block';
            } else {
                if (!MapEditor.added[placeId]) {
                    MapEditor.edited[placeId] = places[placeId];

                    document.getElementById('edited').innerHTML = String(Object.keys(MapEditor.edited).length);
                } else {
                    MapEditor.added[placeId] = places[placeId];
                }
            }

            MapEditor.selectedMarker.setLatLng({ lat: places[placeId].lat, lng: places[placeId].lng });

            document.getElementById('saveButton').disabled = false;
        },

        closePlace: function (del) {
            document.getElementById('metadata').classList.remove('selected')
            document.getElementById('map').classList.remove('selected');
            document.getElementById('control').classList.remove('selected');
            document.getElementById('noPano').style.visibility = 'hidden';
            document.getElementById('panorama').style.visibility = 'hidden';
            document.getElementById('placeControl').style.visibility = 'hidden';

            MapEditor.resetSelected(del);
            MapEditor.selectedMarker = null;

            MapEditor.map.invalidateSize(true);
        },

        deletePlace: function () {
            var placeId = MapEditor.selectedMarker.placeId;

            if (places[placeId].id && !MapEditor.added[placeId]) {
                MapEditor.deleted[placeId] = places[placeId];

                document.getElementById('deleted').innerHTML = String(Object.keys(MapEditor.deleted).length);
            }

            MapEditor.closePlace(true);

            delete MapEditor.added[placeId];
            delete MapEditor.edited[placeId];

            document.getElementById('added').innerHTML = String(Object.keys(MapEditor.added).length);
            document.getElementById('edited').innerHTML = String(Object.keys(MapEditor.edited).length);

            document.getElementById('saveButton').disabled = false;
        },

        saveMap: function () {
            document.getElementById('loading').style.visibility = 'visible';

            var data = new FormData();

            if (MapEditor.metadata.name !== null) {
                data.append('name', MapEditor.metadata.name);
            }
            if (MapEditor.metadata.description !== null) {
                data.append('description', MapEditor.metadata.description);
            }

            for (var placeId in MapEditor.added) {
                if (!MapEditor.added.hasOwnProperty(placeId)) {
                    continue;
                }
                data.append('added[]', JSON.stringify(MapEditor.added[placeId]));
            }
            for (var placeId in MapEditor.edited) {
                if (!MapEditor.edited.hasOwnProperty(placeId)) {
                    continue;
                }
                data.append('edited[]', JSON.stringify(MapEditor.edited[placeId]));
            }
            for (var placeId in MapEditor.deleted) {
                if (!MapEditor.deleted.hasOwnProperty(placeId)) {
                    continue;
                }
                data.append('deleted[]', JSON.stringify(MapEditor.deleted[placeId]));
            }

            var xhr = new XMLHttpRequest();
            xhr.responseType = 'json';
            xhr.onload = function () {
                document.getElementById('loading').style.visibility = 'hidden';

                if (this.response.error) {
                    //TODO: handle this error
                    return;
                }

                MapEditor.replacePlaceIdsToReal(this.response.added);

                if (mapId === 0) {
                    mapId = this.response.mapId;
                    window.history.replaceState(null, '', '/admin/mapEditor/' + mapId);
                }

                MapEditor.added = {};
                MapEditor.edited = {};
                MapEditor.deleted = {};

                document.getElementById('added').innerHTML = '0';
                document.getElementById('edited').innerHTML = '0';
                document.getElementById('deleted').innerHTML = '0';

                document.getElementById('saveButton').disabled = true;
            };

            xhr.open('POST', '/admin/saveMap/' + mapId + '/json', true);
            xhr.send(data);
        },

        replacePlaceIdsToReal: function (addedPlaces) {
            for (var i = 0; i < addedPlaces.length; ++i) {
                var tempId = addedPlaces[i].tempId;
                var placeId = addedPlaces[i].id;
                places[tempId].id = placeId;
            }
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

    MapEditor.map.on('click', function (e) {
        var marker = L.marker(e.latlng, {
            icon: IconCollection.iconBlue,
            zIndexOffset: 2000
        })
            .addTo(MapEditor.map)
            .on('click', function () {
                MapEditor.select(this);
            });

        MapEditor.select(marker);
    });

    var highResData = Util.getHighResData();

    L.tileLayer(tileUrl, {
        subdomains: '1234',
        ppi: highResData.ppi,
        tileSize: highResData.tileSize,
        zoomOffset: highResData.zoomOffset,
        minZoom: 2,
        maxZoom: 20
    }).addTo(MapEditor.map);

    MapEditor.map.fitBounds(L.latLngBounds({ lat: mapBounds.south, lng: mapBounds.west }, { lat: mapBounds.north, lng: mapBounds.east }));

    for (var placeId in places) {
        if (!places.hasOwnProperty(placeId)) {
            continue;
        }

        var place = places[placeId];

        var marker = L.marker({ lat: place.lat, lng: place.lng }, {
            icon: place.noPano ? IconCollection.iconRed : IconCollection.iconGreen,
            zIndexOffset: 1000
        })
            .addTo(MapEditor.map)
            .on('click', function () {
                MapEditor.select(this);
            });

        marker.placeId = place.id;
    }

    MapEditor.panorama = new google.maps.StreetViewPanorama(document.getElementById('panorama'), {
        // switch off fullscreenControl because positioning doesn't work
        fullscreenControl: false,
        fullscreenControlOptions: {
            position: google.maps.ControlPosition.LEFT_TOP
        },
        motionTracking: false
    });

    document.getElementById('mapName').onclick = function (e) {
        e.preventDefault();

        var metadata = document.getElementById('metadata');

        if (metadata.style.visibility === 'visible') {
            metadata.style.visibility = 'hidden';
        } else {
            metadata.style.visibility = 'visible';
            document.getElementById('metadataForm').elements.name.select();
        }
    };

    document.getElementById('metadataForm').onsubmit = function (e) {
        e.preventDefault();

        MapEditor.editMetadata();
    };

    document.getElementById('closeMetadataButton').onclick = function () {
        document.getElementById('metadata').style.visibility = 'hidden';
    };

    document.getElementById('saveButton').onclick = function () {
        MapEditor.saveMap();
    };

    document.getElementById('applyButton').onclick = function () {
        MapEditor.applyPlace();
    };

    document.getElementById('closeButton').onclick = function () {
        MapEditor.closePlace();
    };

    document.getElementById('deleteButton').onclick = function () {
        MapEditor.deletePlace();
    };
})();
