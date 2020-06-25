(function () {
    Maps = {
        deleteMap: function (mapId, mapName) {
            MapGuesser.showModalWithContent('Delete map', 'Are you sure you want to delete map \'' + mapName + '\'?', [{
                type: 'button',
                classNames: ['red'],
                text: 'Delete',
                onclick: function () {
                    document.getElementById('loading').style.visibility = 'visible';

                    MapGuesser.httpRequest('POST', '/admin/deleteMap/' + mapId, function () {
                        if (this.response.error) {
                            document.getElementById('loading').style.visibility = 'hidden';

                            //TODO: handle this error
                            return;
                        }

                        window.location.reload();
                    });
                }
            }]);
        }
    };

    var buttons = document.getElementById('mapContainer').getElementsByClassName('deleteButton');
    for (var i = 0; i < buttons.length; i++) {
        var button = buttons[i];

        button.onclick = function () {
            Maps.deleteMap(this.dataset.mapId, this.dataset.mapName);
        };
    }
})();
