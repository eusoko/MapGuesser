(function () {
    var Core = {
        MAX_SCORE: 1000,

        realPosition: null,
        panorama: null,
        guessMap: null,
        guessMarker: null,
        resultMap: null,
        resultMarkers: { guess: null, real: null },
        googleLink: null,

        getNewPosition: function () {
            var xhr = new XMLHttpRequest();
            xhr.responseType = 'json';
            xhr.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    Core.realPosition = this.response.position;

                    var sv = new google.maps.StreetViewService();
                    sv.getPanorama({ location: this.response.position, preference: google.maps.StreetViewPreference.NEAREST }, Core.loadPano);
                }
            };
            xhr.open('GET', 'getNewPosition.json', true);
            xhr.send();
        },

        loadPano: function (data, status) {
            if (status !== google.maps.StreetViewStatus.OK) {
                Core.getNewPosition();
                return;
            }

            Core.panorama.setPov({ heading: 0, pitch: 0, zoom: 1 });
            Core.panorama.setPano(data.location.pano);
        },

        calculateScore: function (distance) {
            var goodness = 1.0 - distance / Math.sqrt(mapArea);

            return Math.pow(Core.MAX_SCORE, goodness);
        },

        calculateScoreBarProperties: function (score) {
            var percent = Math.round((score / Core.MAX_SCORE) * 100);

            var color;
            if (percent >= 90) {
                color = '#11ca00';
            } else if (percent >= 10) {
                color = '#ea9000';
            } else {
                color = '#ca1100';
            }

            return { width: percent + '%', backgroundColor: color };
        },

        rewriteGoogleLink: function () {
            if (!Core.googleLink) {
                var anchors = document.getElementById('panorama').getElementsByTagName('a');
                for (var i = 0; i < anchors.length; i++) {
                    var a = anchors[i];
                    if (a.href.indexOf('maps.google.com/maps') !== -1) {
                        Core.googleLink = a;
                        break;
                    }
                }
            }

            setTimeout(function () {
                Core.googleLink.title = 'Google Maps'
                Core.googleLink.href = 'https://maps.google.com/maps'
            }, 1);
        }
    };

    var Util = {
        EARTH_RADIUS_IN_METER: 6371000,

        deg2rad: function (deg) {
            return deg * (Math.PI / 180.0);
        },

        calculateDistance: function (position1, position2) {
            var lat1 = Util.deg2rad(position1.lat);
            var lng1 = Util.deg2rad(position1.lng);
            var lat2 = Util.deg2rad(position2.lat);
            var lng2 = Util.deg2rad(position2.lng);

            var angleCos = Math.cos(lat1) * Math.cos(lat2) * Math.cos(lng2 - lng1) +
                Math.sin(lat1) * Math.sin(lat2);

            if (angleCos > 1.0) {
                angleCos = 1.0;
            }

            var angle = Math.acos(angleCos);

            return angle * Util.EARTH_RADIUS_IN_METER;
        },

        printDistanceForHuman: function (distance) {
            if (distance < 1000) {
                return Number.parseFloat(distance).toFixed(0) + ' m';
            } else if (distance < 10000) {
                return Number.parseFloat(distance / 1000).toFixed(2) + ' km';
            } else if (distance < 100000) {
                return Number.parseFloat(distance / 1000).toFixed(1) + ' km';
            } else {
                return Number.parseFloat(distance / 1000).toFixed(0) + ' km';
            }
        }
    };

    Core.guessMap = new google.maps.Map(document.getElementById('guessMap'), {
        disableDefaultUI: true,
        clickableIcons: false,
        draggableCursor: 'crosshair'
    });

    Core.guessMap.fitBounds(guessMapBounds);

    Core.guessMap.addListener('click', function (e) {
        if (Core.guessMarker) {
            Core.guessMarker.setPosition(e.latLng);
            return;
        }

        Core.guessMarker = new google.maps.Marker({
            map: Core.guessMap,
            position: e.latLng,
            clickable: false,
            draggable: true,
            label: {
                color: '#ffffff',
                fontFamily: 'Roboto',
                fontSize: '18px',
                fontWeight: '500',
                text: '?'
            }
        });

        document.getElementById('guessButton').disabled = false;
    });

    Core.panorama = new google.maps.StreetViewPanorama(document.getElementById('panorama'), {
        disableDefaultUI: true,
        linksControl: true,
        showRoadLabels: false
    });

    Core.panorama.addListener('position_changed', function () {
        Core.rewriteGoogleLink();
    });

    Core.panorama.addListener('pov_changed', function () {
        Core.rewriteGoogleLink();
    });

    Core.resultMap = new google.maps.Map(document.getElementById('resultMap'), {
        disableDefaultUI: true,
        clickableIcons: false,
    });

    Core.getNewPosition();

    document.getElementById('guessButton').onclick = function () {
        if (!Core.guessMarker) {
            return;
        }

        var guessedPosition = Core.guessMarker.getPosition();

        this.disabled = true;
        Core.guessMarker.setMap(null);
        Core.guessMarker = null;

        var distance = Util.calculateDistance(Core.realPosition, { lat: guessedPosition.lat(), lng: guessedPosition.lng() });

        document.getElementById('guess').style.visibility = 'hidden';
        document.getElementById('result').style.visibility = 'visible';

        var resultBounds = new google.maps.LatLngBounds();
        resultBounds.extend(Core.realPosition);
        resultBounds.extend(guessedPosition);

        Core.resultMap.fitBounds(resultBounds);

        Core.resultMarkers.real = new google.maps.Marker({
            map: Core.resultMap,
            position: Core.realPosition,
            clickable: true,
            draggable: false
        });
        Core.resultMarkers.guess = new google.maps.Marker({
            map: Core.resultMap,
            position: guessedPosition,
            clickable: false,
            draggable: false,
            label: {
                color: '#ffffff',
                fontFamily: 'Roboto',
                fontSize: '18px',
                fontWeight: '500',
                text: '?'
            }
        });

        Core.resultMarkers.real.addListener('click', function () {
            window.open('https://www.google.com/maps/search/?api=1&query=' + Core.realPosition.lat + ',' + Core.realPosition.lng, '_blank');
        });

        document.getElementById('distance').innerHTML = Util.printDistanceForHuman(distance);

        var score = Core.calculateScore(distance);
        var scoreBarProperties = Core.calculateScoreBarProperties(score);

        document.getElementById('score').innerHTML = Number.parseFloat(score).toFixed(0);

        var scoreBar = document.getElementById('scoreBar');
        scoreBar.style.backgroundColor = scoreBarProperties.backgroundColor;
        scoreBar.style.width = scoreBarProperties.width;
    }

    document.getElementById('continueButton').onclick = function () {
        document.getElementById('scoreBar').style.width = '0';

        Core.resultMarkers.real.setMap(null);
        Core.resultMarkers.real = null;
        Core.resultMarkers.guess.setMap(null);
        Core.resultMarkers.guess = null;

        document.getElementById('guess').style.visibility = 'visible';
        document.getElementById('result').style.visibility = 'hidden';

        Core.guessMap.fitBounds(guessMapBounds);

        Core.getNewPosition();
    }
})();
