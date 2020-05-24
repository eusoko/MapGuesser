'use strict';

(function () {
    var Core = {
        NUMBER_OF_ROUNDS: 5,
        MAX_SCORE: 1000,

        rounds: [],
        scoreSum: 0,
        realPosition: null,
        panorama: null,
        map: null,
        guessMarker: null,
        adaptGuess: false,
        googleLink: null,

        initialize: function () {
            if (sessionStorage.rounds) {
                var roundsLoaded = JSON.parse(sessionStorage.rounds);
                for (var i = 0; i < roundsLoaded.length; ++i) {
                    var round = roundsLoaded[i];
                    Core.rounds.push({ realPosition: round.realPosition, guessPosition: round.guessPosition, realMarker: null, guessMarker: null, line: null });
                    if (round.guessPosition) {
                        Core.addRealGuessPair(round.realPosition, round.guessPosition, true);
                    }
                }
            }

            if (sessionStorage.scoreSum) {
                Core.scoreSum = parseInt(sessionStorage.scoreSum);
            }

            if (Core.rounds.length > 0 && !Core.rounds[Core.rounds.length - 1].guessPosition) {
                Core.realPosition = Core.rounds[Core.rounds.length - 1].realPosition;
                Core.loadPositionInfo(Core.realPosition);

                document.getElementById('currentRound').innerHTML = String(Core.rounds.length) + '/' + String(Core.NUMBER_OF_ROUNDS);
                document.getElementById('currentScoreSum').innerHTML = String(Core.scoreSum) + '/' + String((Core.rounds.length - 1) * Core.MAX_SCORE);
            } else {
                Core.startNewRound();

                document.getElementById('currentScoreSum').innerHTML = String(0);
            }
        },

        startNewGame: function () {
            for (var i = 0; i < Core.rounds.length; ++i) {
                var round = Core.rounds[i];

                round.realMarker.setMap(null);
                round.guessMarker.setMap(null);
                round.line.setMap(null);
            }

            Core.rounds = [];
            Core.scoreSum = 0;

            var distanceInfo = document.getElementById('distanceInfo');
            distanceInfo.children[0].style.display = null;
            distanceInfo.children[1].style.display = null;
            var scoreInfo = document.getElementById('scoreInfo');
            scoreInfo.children[0].style.display = null;
            scoreInfo.children[1].style.display = null;
            document.getElementById('continueButton').style.display = null;
            document.getElementById('startNewGameButton').style.display = null;

            Core.prepareNewRound();

            document.getElementById('currentScoreSum').innerHTML = String(0);
        },

        prepareNewRound: function () {
            document.getElementById('scoreBar').style.width = null;

            if (Core.rounds.length > 0) {
                var lastRound = Core.rounds[Core.rounds.length - 1];

                lastRound.realMarker.setVisible(false);
                lastRound.guessMarker.setVisible(false);
                lastRound.line.setVisible(false);
            }

            document.getElementById('showGuessButton').style.visibility = null;
            document.getElementById('guess').style.visibility = null;
            document.getElementById('guess').classList.remove('result')

            Core.map.setOptions({
                draggableCursor: 'crosshair'
            });
            Core.map.fitBounds(guessMapBounds);

            Core.startNewRound();
        },

        startNewRound: function () {
            Core.rounds.push({ realPosition: null, guessPosition: null, realMarker: null, guessMarker: null, line: null });

            Core.panorama.setVisible(false);
            document.getElementById('loading').style.visibility = 'visible';

            document.getElementById('currentRound').innerHTML = String(Core.rounds.length) + '/' + String(Core.NUMBER_OF_ROUNDS);

            Core.getNewPosition();
        },

        getNewPosition: function () {
            var xhr = new XMLHttpRequest();
            xhr.responseType = 'json';
            xhr.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    Core.realPosition = this.response.position;
                    Core.rounds[Core.rounds.length - 1].realPosition = this.response.position;
                    Core.loadPositionInfo(this.response.position);

                    Core.saveToSession();
                }
            };
            xhr.open('GET', 'getNewPosition.json', true);
            xhr.send();
        },

        loadPositionInfo: function (position) {
            var sv = new google.maps.StreetViewService();
            sv.getPanorama({ location: position, preference: google.maps.StreetViewPreference.NEAREST }, Core.loadPano);
        },

        loadPano: function (data, status) {
            if (status !== google.maps.StreetViewStatus.OK) {
                Core.getNewPosition();
                return;
            }

            document.getElementById('loading').style.visibility = 'hidden';

            if (Core.adaptGuess) {
                document.getElementById('guess').classList.add('adapt');
            }

            Core.panorama.setVisible(true);
            Core.panorama.setPov({ heading: 0, pitch: 0 });
            Core.panorama.setZoom(0);
            Core.panorama.setPano(data.location.pano);
        },

        evaluateGuess: function () {
            var guessPosition = Core.guessMarker.getPosition().toJSON();
            Core.rounds[Core.rounds.length - 1].guessPosition = guessPosition;

            var distance = Util.calculateDistance(Core.realPosition, guessPosition);
            var score = Core.calculateScore(distance);
            Core.scoreSum += score;

            document.getElementById('currentScoreSum').innerHTML = String(Core.scoreSum) + '/' + String(Core.rounds.length * Core.MAX_SCORE);

            Core.saveToSession();

            Core.guessMarker.setMap(null);
            Core.guessMarker = null;

            if (Core.adaptGuess) {
                document.getElementById('guess').classList.remove('adapt');
            }
            document.getElementById('guess').classList.add('result');

            Core.addRealGuessPair(Core.realPosition, guessPosition);

            var resultBounds = new google.maps.LatLngBounds();
            resultBounds.extend(Core.realPosition);
            resultBounds.extend(guessPosition);

            Core.map.setOptions({
                draggableCursor: 'grab'
            });
            Core.map.fitBounds(resultBounds);

            document.getElementById('distance').innerHTML = Util.printDistanceForHuman(distance);
            document.getElementById('score').innerHTML = score;

            var scoreBarProperties = Core.calculateScoreBarProperties(score, Core.MAX_SCORE);
            var scoreBar = document.getElementById('scoreBar');
            scoreBar.style.backgroundColor = scoreBarProperties.backgroundColor;
            scoreBar.style.width = scoreBarProperties.width;

            if (Core.rounds.length == Core.NUMBER_OF_ROUNDS) {
                document.getElementById('continueButton').style.display = 'none';
                document.getElementById('showSummaryButton').style.display = 'block';
            }
        },

        addRealGuessPair: function (realPosition, guessPosition, hidden) {
            var round = Core.rounds[Core.rounds.length - 1];

            round.realMarker = new google.maps.Marker({
                map: Core.map,
                visible: !hidden,
                position: realPosition,
                title: 'Open in Google Maps',
                zIndex: Core.rounds.length * 2,
                clickable: true,
                draggable: false
            });

            round.realMarker.addListener('click', function () {
                window.open('https://www.google.com/maps/search/?api=1&query=' + this.getPosition().toUrlValue(), '_blank');
            });

            round.guessMarker = new google.maps.Marker({
                map: Core.map,
                visible: !hidden,
                position: guessPosition,
                zIndex: Core.rounds.length,
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

            round.line = new google.maps.Polyline({
                map: Core.map,
                visible: !hidden,
                path: [
                    realPosition,
                    guessPosition
                ],
                geodesic: true,
                strokeOpacity: 0,
                icons: [{
                    icon: {
                        path: 'M 0,-1 0,1',
                        strokeOpacity: 1,
                        strokeWeight: 2,
                        scale: 2
                    },
                    offset: '0',
                    repeat: '10px'
                }],
                clickable: false,
                draggable: false,
                editable: false
            });
        },

        calculateScore: function (distance) {
            var goodness = 1.0 - distance / Math.sqrt(mapArea);

            return Math.round(Math.pow(Core.MAX_SCORE, goodness));
        },

        calculateScoreBarProperties: function (score, maxScore) {
            var percent = Math.floor((score / maxScore) * 100);

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

        showSummary: function () {
            var distanceInfo = document.getElementById('distanceInfo');
            distanceInfo.children[0].style.display = 'none';
            distanceInfo.children[1].style.display = 'block';
            var scoreInfo = document.getElementById('scoreInfo');
            scoreInfo.children[0].style.display = 'none';
            scoreInfo.children[1].style.display = 'block';
            document.getElementById('showSummaryButton').style.display = null;
            document.getElementById('startNewGameButton').style.display = 'block';

            var resultBounds = new google.maps.LatLngBounds();

            for (var i = 0; i < Core.rounds.length; ++i) {
                var round = Core.rounds[i];

                round.realMarker.setLabel({
                    color: '#812519',
                    fontFamily: 'Roboto',
                    fontSize: '16px',
                    fontWeight: '500',
                    text: String(i + 1)
                });
                round.realMarker.setVisible(true);
                round.guessMarker.setVisible(true);
                round.line.setVisible(true);

                resultBounds.extend(round.realPosition);
                resultBounds.extend(round.guessPosition);
            }

            Core.map.fitBounds(resultBounds);

            document.getElementById('scoreSum').innerHTML = String(Core.scoreSum);

            var scoreBarProperties = Core.calculateScoreBarProperties(Core.scoreSum, Core.NUMBER_OF_ROUNDS * Core.MAX_SCORE);
            var scoreBar = document.getElementById('scoreBar');
            scoreBar.style.backgroundColor = scoreBarProperties.backgroundColor;
            scoreBar.style.width = scoreBarProperties.width;
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
                if (Core.googleLink) {
                    Core.googleLink.title = 'Google Maps';
                    Core.googleLink.href = 'https://maps.google.com/maps';
                }
            }, 1);
        },

        saveToSession: function () {
            if (Core.rounds.length == Core.NUMBER_OF_ROUNDS && Core.rounds[Core.rounds.length - 1].guessPosition) {
                sessionStorage.removeItem('rounds');
                sessionStorage.removeItem('scoreSum');
                return;
            }

            var roundsToSave = [];
            for (var i = 0; i < Core.rounds.length; ++i) {
                var round = Core.rounds[i];
                roundsToSave.push({ realPosition: round.realPosition, guessPosition: round.guessPosition });
            }

            sessionStorage.setItem('rounds', JSON.stringify(roundsToSave));
            sessionStorage.setItem('scoreSum', String(Core.scoreSum));
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

    if (!('ontouchstart' in document.documentElement)) {
        Core.adaptGuess = true;
    }

    Core.map = new google.maps.Map(document.getElementById('map'), {
        disableDefaultUI: true,
        clickableIcons: false,
        draggableCursor: 'crosshair',
        draggingCursor: 'grabbing'
    });

    Core.map.fitBounds(guessMapBounds);

    Core.map.addListener('click', function (e) {
        if (Core.rounds[Core.rounds.length - 1].guessPosition) {
            return;
        }

        if (Core.guessMarker) {
            Core.guessMarker.setPosition(e.latLng);
            return;
        }

        Core.guessMarker = new google.maps.Marker({
            map: Core.map,
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
        showRoadLabels: false,
        motionTracking: false
    });

    Core.panorama.addListener('position_changed', function () {
        Core.rewriteGoogleLink();
    });

    Core.panorama.addListener('pov_changed', function () {
        Core.rewriteGoogleLink();
    });

    Core.initialize();

    document.getElementById('showGuessButton').onclick = function () {
        this.style.visibility = 'hidden';
        document.getElementById('guess').style.visibility = 'visible';
    }

    document.getElementById('closeGuessButton').onclick = function () {
        document.getElementById('showGuessButton').style.visibility = null;
        document.getElementById('guess').style.visibility = null;
    }

    document.getElementById('guessButton').onclick = function () {
        if (!Core.guessMarker) {
            return;
        }

        this.disabled = true;

        Core.evaluateGuess();
    }

    document.getElementById('continueButton').onclick = function () {
        Core.prepareNewRound();
    }

    document.getElementById('showSummaryButton').onclick = function () {
        Core.showSummary();
    }

    document.getElementById('startNewGameButton').onclick = function () {
        Core.startNewGame();
    }

    window.onbeforeunload = function (e) {
        e.preventDefault();
        e.returnValue = '';
    };
})();
