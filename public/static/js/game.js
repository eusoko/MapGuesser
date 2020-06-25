'use strict';

(function () {
    var Game = {
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
            document.getElementById('panoCover').style.visibility = 'visible';
            document.getElementById('currentRound').innerHTML = '1/' + String(Game.NUMBER_OF_ROUNDS);
            document.getElementById('currentScoreSum').innerHTML = '0/0';

            Game.map.setOptions({
                draggableCursor: 'crosshair'
            });
            Game.map.fitBounds(mapBounds);

            MapGuesser.httpRequest('GET', '/game/' + mapId + '/newPlace.json', function () {
                document.getElementById('loading').style.visibility = 'hidden';
                document.getElementById('panoCover').style.visibility = 'hidden';

                if (this.response.error) {
                    //TODO: handle this error
                    return;
                }

                Game.panoId = this.response.panoId;

                if (this.response.history) {
                    for (var i = 0; i < this.response.history.length; ++i) {
                        var round = this.response.history[i];
                        Game.rounds.push({ position: round.position, guessPosition: round.guessPosition, realMarker: null, guessMarker: null, line: null });
                        Game.addRealGuessPair(round.position, round.guessPosition, true);
                        Game.scoreSum += round.score;
                    }

                    document.getElementById('currentRound').innerHTML = String(Game.rounds.length) + '/' + String(Game.NUMBER_OF_ROUNDS);
                    document.getElementById('currentScoreSum').innerHTML = String(Game.scoreSum) + '/' + String(Game.rounds.length * Game.MAX_SCORE);
                }

                Game.startNewRound();
            });
        },

        reset: function () {
            if (Game.guessMarker) {
                Game.guessMarker.setMap(null);
                Game.guessMarker = null;
            }

            for (var i = 0; i < Game.rounds.length; ++i) {
                var round = Game.rounds[i];

                if (round.realMarker && round.guessMarker && round.line) {
                    round.realMarker.setMap(null);
                    round.guessMarker.setMap(null);
                    round.line.setMap(null);
                }
            }

            Game.rounds = [];
            Game.scoreSum = 0;

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

            Game.initialize();
        },

        resetRound: function () {
            document.getElementById('scoreBar').style.width = null;

            if (Game.rounds.length > 0) {
                var lastRound = Game.rounds[Game.rounds.length - 1];

                lastRound.realMarker.setVisible(false);
                lastRound.guessMarker.setVisible(false);
                lastRound.line.setVisible(false);
            }

            document.getElementById('panoCover').style.visibility = 'hidden';
            document.getElementById('showGuessButton').style.visibility = null;
            document.getElementById('guess').style.visibility = null;
            document.getElementById('guess').classList.remove('result')

            Game.map.setOptions({
                draggableCursor: 'crosshair'
            });
            Game.map.fitBounds(mapBounds);

            Game.startNewRound();
        },

        startNewRound: function () {
            Game.rounds.push({ position: null, guessPosition: null, realMarker: null, guessMarker: null, line: null });

            document.getElementById('currentRound').innerHTML = String(Game.rounds.length) + '/' + String(Game.NUMBER_OF_ROUNDS);

            Game.loadPano(Game.panoId);
        },

        handleErrorResponse: function (error) {
            // for the time being we only handle the "no_session_found" error and reset the game

            MapGuesser.httpRequest('GET', '/game/' + mapId + '/json', function () {
                mapBounds = this.response.bounds;

                Game.reset();
            });
        },

        loadPano: function (panoId) {
            if (Game.adaptGuess) {
                document.getElementById('guess').classList.add('adapt');
            }

            Game.panorama.setPov({ heading: 0, pitch: 0 });
            Game.panorama.setZoom(0);
            Game.panorama.setPano(panoId);
        },

        evaluateGuess: function () {
            if (!Game.guessMarker) {
                return;
            }

            var guessPosition = Game.guessMarker.getPosition().toJSON();
            Game.rounds[Game.rounds.length - 1].guessPosition = guessPosition;

            document.getElementById('guessButton').disabled = true;
            if (Game.adaptGuess) {
                document.getElementById('guess').classList.remove('adapt');
            }
            document.getElementById('loading').style.visibility = 'visible';
            document.getElementById('panoCover').style.visibility = 'visible';

            var data = new FormData();
            data.append('lat', String(guessPosition.lat));
            data.append('lng', String(guessPosition.lng));

            MapGuesser.httpRequest('POST', '/game/' + mapId + '/guess.json', function () {
                if (this.response.error) {
                    Game.handleErrorResponse(this.response.error);
                    return;
                }

                Game.guessMarker.setMap(null);
                Game.guessMarker = null;

                document.getElementById('loading').style.visibility = 'hidden';
                document.getElementById('guess').classList.add('result');

                Game.scoreSum += this.response.result.score;
                document.getElementById('currentScoreSum').innerHTML = String(Game.scoreSum) + '/' + String(Game.rounds.length * Game.MAX_SCORE);

                Game.rounds[Game.rounds.length - 1].position = this.response.result.position;
                Game.addRealGuessPair(this.response.result.position, guessPosition);

                var resultBounds = new google.maps.LatLngBounds();
                resultBounds.extend(this.response.result.position);
                resultBounds.extend(guessPosition);

                Game.map.setOptions({
                    draggableCursor: 'grab'
                });
                Game.map.fitBounds(resultBounds);

                document.getElementById('distance').innerHTML = Util.printDistanceForHuman(this.response.result.distance);
                document.getElementById('score').innerHTML = this.response.result.score;

                var scoreBarProperties = Game.calculateScoreBarProperties(this.response.result.score, Game.MAX_SCORE);
                var scoreBar = document.getElementById('scoreBar');
                scoreBar.style.backgroundColor = scoreBarProperties.backgroundColor;
                scoreBar.style.width = scoreBarProperties.width;

                if (Game.rounds.length === Game.NUMBER_OF_ROUNDS) {
                    document.getElementById('continueButton').style.display = 'none';
                    document.getElementById('showSummaryButton').style.display = 'block';
                }

                Game.panoId = this.response.panoId;
            }, data);
        },

        addRealGuessPair: function (position, guessPosition, hidden) {
            var round = Game.rounds[Game.rounds.length - 1];

            round.realMarker = new google.maps.Marker({
                map: Game.map,
                visible: !hidden,
                position: position,
                title: 'Open in Google Maps',
                zIndex: Game.rounds.length * 2,
                clickable: true,
                draggable: false,
                icon: {
                    url: STATIC_ROOT + '/img/markers/marker-green.svg?rev=' + REVISION,
                    size: new google.maps.Size(24, 32),
                    scaledSize: new google.maps.Size(24, 32),
                    anchor: new google.maps.Point(12, 32)
                },
            });

            round.realMarker.addListener('click', function () {
                window.open('https://www.google.com/maps/search/?api=1&query=' + this.getPosition().toUrlValue(), '_blank');
            });

            round.guessMarker = new google.maps.Marker({
                map: Game.map,
                visible: !hidden,
                position: guessPosition,
                zIndex: Game.rounds.length,
                clickable: false,
                draggable: false,
                icon: {
                    url: STATIC_ROOT + '/img/markers/marker-gray-empty.svg?rev=' + REVISION,
                    size: new google.maps.Size(24, 32),
                    scaledSize: new google.maps.Size(24, 32),
                    anchor: new google.maps.Point(12, 32),
                    labelOrigin: new google.maps.Point(12, 14)
                },
                label: {
                    color: '#ffffff',
                    fontFamily: 'Roboto',
                    fontSize: '16px',
                    fontWeight: '500',
                    text: '?'
                }
            });

            round.line = new google.maps.Polyline({
                map: Game.map,
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

            for (var i = 0; i < Game.rounds.length; ++i) {
                var round = Game.rounds[i];

                round.realMarker.setIcon({
                    url: STATIC_ROOT + '/img/markers/marker-green-empty.svg?rev=' + REVISION,
                    size: new google.maps.Size(24, 32),
                    scaledSize: new google.maps.Size(24, 32),
                    anchor: new google.maps.Point(12, 32),
                    labelOrigin: new google.maps.Point(12, 14)
                });
                round.realMarker.setLabel({
                    color: '#285624',
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

            Game.map.fitBounds(resultBounds);

            document.getElementById('scoreSum').innerHTML = String(Game.scoreSum);

            var scoreBarProperties = Game.calculateScoreBarProperties(Game.scoreSum, Game.NUMBER_OF_ROUNDS * Game.MAX_SCORE);
            var scoreBar = document.getElementById('scoreBar');
            scoreBar.style.backgroundColor = scoreBarProperties.backgroundColor;
            scoreBar.style.width = scoreBarProperties.width;
        },

        rewriteGoogleLink: function () {
            if (!Game.googleLink) {
                var anchors = document.getElementById('panorama').getElementsByTagName('a');
                for (var i = 0; i < anchors.length; i++) {
                    var a = anchors[i];
                    if (a.href.indexOf('maps.google.com/maps') !== -1) {
                        Game.googleLink = a;
                        break;
                    }
                }
            }

            setTimeout(function () {
                if (Game.googleLink) {
                    Game.googleLink.title = 'Google Maps';
                    Game.googleLink.href = 'https://maps.google.com/maps';
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

    MapGuesser.sessionAvailableHooks.reinitializeGame = function () {
        MapGuesser.httpRequest('GET', '/game/' + mapId + '/json', function () {
            mapBounds = this.response.bounds;

            Game.initialize();
        });
    };

    if (!('ontouchstart' in document.documentElement)) {
        Game.adaptGuess = true;
    }

    Game.map = new google.maps.Map(document.getElementById('map'), {
        disableDefaultUI: true,
        clickableIcons: false,
        draggingCursor: 'grabbing'
    });

    Game.map.addListener('click', function (e) {
        if (Game.rounds[Game.rounds.length - 1].guessPosition) {
            return;
        }

        if (Game.guessMarker) {
            Game.guessMarker.setPosition(e.latLng);
            return;
        }

        Game.guessMarker = new google.maps.Marker({
            map: Game.map,
            position: e.latLng,
            clickable: false,
            draggable: true,
            icon: {
                url: STATIC_ROOT + '/img/markers/marker-gray-empty.svg?rev=' + REVISION,
                size: new google.maps.Size(24, 32),
                scaledSize: new google.maps.Size(24, 32),
                anchor: new google.maps.Point(12, 32),
                labelOrigin: new google.maps.Point(12, 14)
            },
            label: {
                color: '#ffffff',
                fontFamily: 'Roboto',
                fontSize: '16px',
                fontWeight: '500',
                text: '?'
            }
        });

        document.getElementById('guessButton').disabled = false;
    });

    Game.panorama = new google.maps.StreetViewPanorama(document.getElementById('panorama'), {
        disableDefaultUI: true,
        linksControl: true,
        showRoadLabels: false,
        motionTracking: false
    });

    Game.panorama.addListener('position_changed', function () {
        Game.rewriteGoogleLink();
    });

    Game.panorama.addListener('pov_changed', function () {
        Game.rewriteGoogleLink();
    });

    Game.initialize();

    document.getElementById('showGuessButton').onclick = function () {
        this.style.visibility = 'hidden';
        document.getElementById('guess').style.visibility = 'visible';
    }

    document.getElementById('closeGuessButton').onclick = function () {
        document.getElementById('showGuessButton').style.visibility = null;
        document.getElementById('guess').style.visibility = null;
    }

    document.getElementById('guessButton').onclick = function () {
        Game.evaluateGuess();
    }

    document.getElementById('continueButton').onclick = function () {
        Game.resetRound();
    }

    document.getElementById('showSummaryButton').onclick = function () {
        Game.showSummary();
    }

    document.getElementById('startNewGameButton').onclick = function () {
        Game.reset();
    }
})();
