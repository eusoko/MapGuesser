'use strict';

(function () {
    var Core = {
        NUMBER_OF_ROUNDS: 5,
        MAX_SCORE: 1000,

        rounds: [],
        scoreSum: 0,
        panoId: null,
        panorama: null,
        map: null,
        guessMarker: null,
        adaptGuess: false,
        googleLink: null,

        initialize: function () {
            document.getElementById('loading').style.visibility = 'visible';
            document.getElementById('currentRound').innerHTML = '1/' + String(Core.NUMBER_OF_ROUNDS);
            document.getElementById('currentScoreSum').innerHTML = '0/0';

            Core.map.setOptions({
                draggableCursor: 'crosshair'
            });
            Core.map.fitBounds(mapBounds);

            var xhr = new XMLHttpRequest();
            xhr.responseType = 'json';
            xhr.onload = function () {
                if (this.response.error) {
                    //TODO: handle this error
                    return;
                }

                document.getElementById('loading').style.visibility = 'hidden';

                Core.panoId = this.response.panoId;

                if (this.response.history) {
                    for (var i = 0; i < this.response.history.length; ++i) {
                        var round = this.response.history[i];
                        Core.rounds.push({ position: round.position, guessPosition: round.guessPosition, realMarker: null, guessMarker: null, line: null });
                        Core.addRealGuessPair(round.position, round.guessPosition, true);
                        Core.scoreSum += round.score;
                    }

                    document.getElementById('currentRound').innerHTML = String(Core.rounds.length) + '/' + String(Core.NUMBER_OF_ROUNDS);
                    document.getElementById('currentScoreSum').innerHTML = String(Core.scoreSum) + '/' + String(Core.rounds.length * Core.MAX_SCORE);
                }

                Core.startNewRound();
            };

            xhr.open('GET', 'position.json?map=' + mapId, true);
            xhr.send();
        },

        resetGame: function () {
            if (Core.guessMarker) {
                Core.guessMarker.setMap(null);
                Core.guessMarker = null;
            }

            for (var i = 0; i < Core.rounds.length; ++i) {
                var round = Core.rounds[i];

                if (round.realMarker && round.guessMarker && round.line) {
                    round.realMarker.setMap(null);
                    round.guessMarker.setMap(null);
                    round.line.setMap(null);
                }
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

            document.getElementById('showGuessButton').style.visibility = null;
            document.getElementById('guess').style.visibility = null;
            document.getElementById('guess').classList.remove('result');

            Core.initialize();
        },

        resetRound: function () {
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
            Core.map.fitBounds(mapBounds);

            Core.startNewRound();
        },

        startNewRound: function () {
            Core.rounds.push({ position: null, guessPosition: null, realMarker: null, guessMarker: null, line: null });

            document.getElementById('currentRound').innerHTML = String(Core.rounds.length) + '/' + String(Core.NUMBER_OF_ROUNDS);

            Core.loadPano(Core.panoId);
        },

        handleErrorResponse: function (error) {
            // for the time being we only handle the "no_session_found" error and reset the game

            var xhr = new XMLHttpRequest();
            xhr.responseType = 'json';
            xhr.onload = function () {
                mapBounds = this.response.bounds;

                Core.resetGame();
            };

            xhr.open('GET', 'game.json?map=' + mapId, true);
            xhr.send();
        },

        loadPano: function (panoId) {
            if (Core.adaptGuess) {
                document.getElementById('guess').classList.add('adapt');
            }

            Core.panorama.setPov({ heading: 0, pitch: 0 });
            Core.panorama.setZoom(0);
            Core.panorama.setPano(panoId);
        },

        evaluateGuess: function () {
            var guessPosition = Core.guessMarker.getPosition().toJSON();
            Core.rounds[Core.rounds.length - 1].guessPosition = guessPosition;

            if (Core.adaptGuess) {
                document.getElementById('guess').classList.remove('adapt');
            }
            document.getElementById('loading').style.visibility = 'visible';

            var data = new FormData();
            data.append('guess', '1');
            data.append('lat', String(guessPosition.lat));
            data.append('lng', String(guessPosition.lng));

            var xhr = new XMLHttpRequest();
            xhr.responseType = 'json';
            xhr.onload = function () {
                if (this.response.error) {
                    Core.handleErrorResponse(this.response.error);
                    return;
                }

                Core.guessMarker.setMap(null);
                Core.guessMarker = null;

                document.getElementById('loading').style.visibility = 'hidden';
                document.getElementById('guess').classList.add('result');

                Core.scoreSum += this.response.result.score;
                document.getElementById('currentScoreSum').innerHTML = String(Core.scoreSum) + '/' + String(Core.rounds.length * Core.MAX_SCORE);

                Core.rounds[Core.rounds.length - 1].position = this.response.result.position;
                Core.addRealGuessPair(this.response.result.position, guessPosition);

                var resultBounds = new google.maps.LatLngBounds();
                resultBounds.extend(this.response.result.position);
                resultBounds.extend(guessPosition);

                Core.map.setOptions({
                    draggableCursor: 'grab'
                });
                Core.map.fitBounds(resultBounds);

                document.getElementById('distance').innerHTML = Util.printDistanceForHuman(this.response.result.distance);
                document.getElementById('score').innerHTML = this.response.result.score;

                var scoreBarProperties = Core.calculateScoreBarProperties(this.response.result.score, Core.MAX_SCORE);
                var scoreBar = document.getElementById('scoreBar');
                scoreBar.style.backgroundColor = scoreBarProperties.backgroundColor;
                scoreBar.style.width = scoreBarProperties.width;

                if (Core.rounds.length === Core.NUMBER_OF_ROUNDS) {
                    document.getElementById('continueButton').style.display = 'none';
                    document.getElementById('showSummaryButton').style.display = 'block';
                }

                Core.panoId = this.response.panoId;
            };

            xhr.open('POST', 'position.json?map=' + mapId, true);
            xhr.send(data);
        },

        addRealGuessPair: function (position, guessPosition, hidden) {
            var round = Core.rounds[Core.rounds.length - 1];

            round.realMarker = new google.maps.Marker({
                map: Core.map,
                visible: !hidden,
                position: position,
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
                    position,
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

                resultBounds.extend(round.position);
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
        }
    };

    var Util = {
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
        draggingCursor: 'grabbing'
    });

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
        Core.resetRound();
    }

    document.getElementById('showSummaryButton').onclick = function () {
        Core.showSummary();
    }

    document.getElementById('startNewGameButton').onclick = function () {
        Core.resetGame();
    }

    window.onbeforeunload = function (e) {
        if (Core.rounds[Core.rounds.length - 1].position) {
            return;
        }

        e.preventDefault();
        e.returnValue = '';
    };
})();
