Math.deg2rad = function (deg) {
    return deg * (this.PI / 180.0);
};

var Util = {
    EARTH_RADIUS_IN_METER: 6371000,

    calculateDistance: function (position1, position2) {
        var lat1 = Math.deg2rad(position1.lat);
        var lng1 = Math.deg2rad(position1.lng);
        var lat2 = Math.deg2rad(position2.lat);
        var lng2 = Math.deg2rad(position2.lng);

        var latDelta = lat2 - lat1;
        var lonDelta = lng2 - lng1;

        var angle = 2 * Math.asin(
            Math.sqrt(
                Math.pow(Math.sin(latDelta / 2), 2) +
                Math.cos(lat1) * Math.cos(lat2) * Math.pow(Math.sin(lonDelta / 2), 2)
            )
        );

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

var MapManipulator = {
    rewriteGoogleLink: function () {
        if (!googleLink) {
            var anchors = document.getElementById('panorama').getElementsByTagName('a');
            for (var i = 0; i < anchors.length; i++) {
                var a = anchors[i];
                if (a.href.indexOf('maps.google.com/maps') !== -1) {
                    googleLink = a;
                    break;
                }
            }
        }

        setTimeout(function () {
            googleLink.title = 'Google Maps'
            googleLink.href = 'https://maps.google.com/maps'
        }, 1);
    }
};

var realPosition;
var panorama;
var guessMap;
var guessMarker;
var resultMap;
var resultMarkers = { guess: null, real: null };
var googleLink;

function initialize() {
    guessMap = new google.maps.Map(document.getElementById('guessMap'), {
        disableDefaultUI: true,
        clickableIcons: false,
        draggableCursor: 'crosshair'
    });

    guessMap.fitBounds(guessMapBounds);

    guessMap.addListener('click', function (e) {
        if (guessMarker) {
            guessMarker.setPosition(e.latLng);
            return;
        }

        guessMarker = new google.maps.Marker({
            map: guessMap,
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

    panorama = new google.maps.StreetViewPanorama(document.getElementById('panorama'), {
        disableDefaultUI: true,
        linksControl: true,
        showRoadLabels: false
    });

    panorama.addListener('position_changed', function () {
        MapManipulator.rewriteGoogleLink();
    });

    panorama.addListener('pov_changed', function () {
        MapManipulator.rewriteGoogleLink();
    });

    resultMap = new google.maps.Map(document.getElementById('resultMap'), {
        disableDefaultUI: true,
        clickableIcons: false,
    });

    getNewPosition();
}

function getNewPosition() {
    var xhr = new XMLHttpRequest();
    xhr.responseType = 'json';
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            realPosition = this.response.position;

            var sv = new google.maps.StreetViewService();
            sv.getPanorama({ location: this.response.position, preference: google.maps.StreetViewPreference.NEAREST }, loadPano);
        }
    };
    xhr.open('GET', 'getNewPosition.json', true);
    xhr.send();
}

function loadPano(data, status) {
    if (status !== google.maps.StreetViewStatus.OK) {
        getNewPosition();
        return;
    }

    panorama.setPano(data.location.pano);
}

document.getElementById('guessButton').onclick = function () {
    if (!guessMarker) {
        return;
    }

    var guessedPosition = guessMarker.getPosition();

    this.disabled = true;
    guessMarker.setMap(null);
    guessMarker = null;

    var distance = Util.calculateDistance(realPosition, { lat: guessedPosition.lat(), lng: guessedPosition.lng() });

    document.getElementById('guess').style.visibility = 'hidden';
    document.getElementById('result').style.visibility = 'visible';

    var resultBounds = new google.maps.LatLngBounds();
    resultBounds.extend(realPosition);
    resultBounds.extend(guessedPosition);

    resultMap.fitBounds(resultBounds);

    resultMarkers.real = new google.maps.Marker({
        map: resultMap,
        position: realPosition,
        clickable: true,
        draggable: false
    });
    resultMarkers.guess = new google.maps.Marker({
        map: resultMap,
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

    resultMarkers.real.addListener('click', function () {
        window.open('https://www.google.com/maps/search/?api=1&query=' + realPosition.lat + ',' + realPosition.lng, '_blank');
    });

    document.getElementById('distance').innerHTML = Util.printDistanceForHuman(distance);
}

document.getElementById('continueButton').onclick = function () {
    resultMarkers.real.setMap(null);
    resultMarkers.real = null;
    resultMarkers.guess.setMap(null);
    resultMarkers.guess = null;

    document.getElementById('guess').style.visibility = 'visible';
    document.getElementById('result').style.visibility = 'hidden';

    guessMap.fitBounds(guessMapBounds);

    getNewPosition();
}
