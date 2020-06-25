(function () {
    var Maps = {
        descriptionDivs: null,

        addStaticMaps: function () {
            var imgContainers = document.getElementById('mapContainer').getElementsByClassName('imgContainer');
            for (var i = 0; i < imgContainers.length; i++) {
                var imgContainer = imgContainers[i];

                var imgSrc = 'https://maps.googleapis.com/maps/api/staticmap?size=350x175&' +
                    'scale=' + (window.devicePixelRatio >= 2 ? 2 : 1) + '&' +
                    'visible=' + imgContainer.dataset.boundSouthLat + ',' + imgContainer.dataset.boundWestLng + '|' +
                    imgContainer.dataset.boundNorthLat + ',' + imgContainer.dataset.boundEastLng +
                    '&key=' + GOOGLE_MAPS_JS_API_KEY;

                imgContainer.style.backgroundImage = 'url("' + imgSrc + '")';
            }
        },

        initializeDescriptionDivs: function () {
            Maps.descriptionDivs = document.getElementById('mapContainer').getElementsByClassName('description');

            for (var i = 0; i < Maps.descriptionDivs.length; i++) {
                var description = Maps.descriptionDivs[i];
                var boundingClientRect = description.getBoundingClientRect();

                description.defaultHeight = boundingClientRect.height;
            }
        },

        calculateDescriptionDivHeights: function () {
            var currentY;
            var rows = [];
            for (var i = 0; i < Maps.descriptionDivs.length; i++) {
                var description = Maps.descriptionDivs[i];
                var boundingClientRect = description.getBoundingClientRect();

                if (currentY !== boundingClientRect.y) {
                    rows.push([]);
                }

                rows[rows.length - 1].push(description);

                currentY = boundingClientRect.y;
            }

            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];

                var maxHeight = 0;
                for (var j = 0; j < row.length; j++) {
                    var description = row[j];

                    if (description.defaultHeight > maxHeight) {
                        maxHeight = description.defaultHeight;
                    }
                }

                for (var j = 0; j < row.length; j++) {
                    var description = row[j];

                    description.style.height = maxHeight + 'px';
                }
            }
        }
    };

    Maps.addStaticMaps();

    Maps.initializeDescriptionDivs();
    Maps.calculateDescriptionDivHeights();

    window.onresize = function () {
        Maps.calculateDescriptionDivHeights();
    };
})();
