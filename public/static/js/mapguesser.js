var MapGuesser = {
    httpRequest: function (method, url, callback, data) {
        var xhr = new XMLHttpRequest();

        xhr.responseType = 'json';
        xhr.onload = callback;

        xhr.open(method, url, true);

        if (method === 'POST') {
            if (typeof data === 'undefined') {
                data = new FormData();
            }

            data.append('anti_csrf_token', ANTI_CSRF_TOKEN);

            xhr.send(data);
        } else {
            xhr.send();
        }
    },

    showModal: function (id) {
        document.getElementById(id).style.visibility = 'visible';
        document.getElementById('cover').style.visibility = 'visible';
    },

    showModalWithContent: function (title, body, extraButtons) {
        if (typeof extraButtons === 'undefined') {
            extraButtons = [];
        }

        MapGuesser.showModal('modal');

        document.getElementById('modalTitle').textContent = title;

        if (typeof body === 'object') {
            document.getElementById('modalText').appendChild(body);
        } else {
            document.getElementById('modalText').textContent = body;
        }

        var buttons = document.getElementById('modalButtons');
        buttons.textContent = '';

        for (var i = 0; i < extraButtons.length; i++) {
            var extraButton = extraButtons[i];
            var button = document.createElement(extraButton.type);

            if (extraButton.type === 'a') {
                button.classList.add('button');
            }

            for (var i = 0; i < extraButton.classNames.length; i++) {
                button.classList.add(extraButton.classNames[i]);
            }

            button.classList.add('marginTop');
            button.classList.add('marginRight');
            button.textContent = extraButton.text;

            if (extraButton.type === 'a') {
                button.href = extraButton.href;
            } else if (extraButton.type === 'button') {
                button.onclick = extraButton.onclick;
            }

            buttons.appendChild(button);
        }

        var closeButton = document.createElement('button');

        closeButton.classList.add('gray');
        closeButton.classList.add('marginTop');
        closeButton.textContent = 'Close';
        closeButton.onclick = function () {
            MapGuesser.hideModal();
        };

        buttons.appendChild(closeButton);
    },

    hideModal: function () {
        var modals = document.getElementsByClassName('modal');

        for (var i = 0; i < modals.length; i++) {
            modals[i].style.visibility = 'hidden';
        }

        document.getElementById('cover').style.visibility = 'hidden';
    },

    toggleDisableOnChange: function (input, button) {
        if (input.defaultValue !== input.value) {
            button.disabled = false;
        } else {
            button.disabled = true;
        }
    }
};

(function () {
    var anchors = document.getElementsByTagName('a');
    for (var i = 0; i < anchors.length; i++) {
        var a = anchors[i];
        if (a.href !== 'javascript:;' && a.target !== '_blank') {
            a.onclick = function () {
                document.getElementById('loading').style.visibility = 'visible';
            }
        }
    }

    document.getElementById('cover').onclick = function () {
        MapGuesser.hideModal();
    };
})();
