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

var panorama;
var guessMap;
var guessMarker;
var googleLink;

function initialize() {
    panorama = new google.maps.StreetViewPanorama(document.getElementById('panorama'), {
        position: realPosition,
        pov: {
            heading: 34,
            pitch: 10
        },
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
            draggable: true
        });

        document.getElementById('guessButton').disabled = false;
    });
}

document.getElementById('guessButton').onclick = function () {
    if (!guessMarker) {
        return;
    }

    var guessedPosition = guessMarker.getPosition();
    var distance = Util.calculateDistance(realPosition, { lat: guessedPosition.lat(), lng: guessedPosition.lng() });

    alert('You were ' + distance + 'm close!');

    this.blur();
}
