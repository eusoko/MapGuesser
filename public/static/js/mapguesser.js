var MapGuesser = {
    cookiesAgreed: false,
    sessionAvailableHooks: {},

    initGoogleAnalitics: function () {
        if (typeof GOOGLE_ANALITICS_ID === 'undefined') {
            return;
        }

        // Global site tag (gtag.js) - Google Analytics
        var script = document.createElement('script');
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + GOOGLE_ANALITICS_ID;
        script.async = true;

        document.head.appendChild(script);

        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', GOOGLE_ANALITICS_ID);
    },

    agreeCookies: function () {
        if (MapGuesser.cookiesAgreed) {
            return;
        }

        var expirationDate = new Date(new Date().getTime() + 20 * 365 * 24 * 60 * 60 * 1000).toUTCString();
        document.cookie = 'COOKIES_CONSENT=1; expires=' + expirationDate + '; path=/';

        MapGuesser.initGoogleAnalitics();
        MapGuesser.httpRequest('GET', '/startSession.json', function () {
            ANTI_CSRF_TOKEN = this.response.antiCsrfToken;

            for (var hookId in MapGuesser.sessionAvailableHooks) {
                if (!MapGuesser.sessionAvailableHooks.hasOwnProperty(hookId)) {
                    continue;
                }

                MapGuesser.sessionAvailableHooks[hookId]();
            }
        });

        MapGuesser.cookiesAgreed = true;
    },

    httpRequest: function (method, url, callback, data) {
        var xhr = new XMLHttpRequest();

        xhr.onload = callback;

        xhr.open(method, url, true);

        xhr.responseType = 'json';

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

    setOnsubmitForForm: function (form, redirectOnSuccess) {
        form.onsubmit = function (e) {
            e.preventDefault();

            document.getElementById('loading').style.visibility = 'visible';

            var formData = new FormData(form);
            var formError = form.getElementsByClassName('formError')[0];
            var pageLeaveOnSuccess = typeof redirectOnSuccess === 'string';

            MapGuesser.httpRequest('POST', form.action, function () {
                if (!pageLeaveOnSuccess) {
                    document.getElementById('loading').style.visibility = 'hidden';
                }

                if (this.response.error) {
                    if (pageLeaveOnSuccess) {
                        document.getElementById('loading').style.visibility = 'hidden';
                    }

                    formError.style.display = 'block';
                    formError.innerHTML = this.response.error.errorText;

                    return;
                }

                if (!pageLeaveOnSuccess) {
                    formError.style.display = 'none';
                    form.reset();
                } else {
                    if (redirectOnSuccess === '') {
                        window.location.reload();
                    } else {
                        window.location.replace(redirectOnSuccess);
                    }
                }
            }, formData);
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
    },

    toggleFormSubmitButtonDisableOnChange: function (form, observedInputs) {
        for (var i = 0; i < observedInputs.length; i++) {
            var input = form.elements[observedInputs[i]];

            switch (input.tagName) {
                case 'INPUT':
                case 'TEXTAREA':
                    input.oninput = function () {
                        MapGuesser.toggleDisableOnChange(this, form.elements.submit);
                    };
                    break;
                case 'SELECT':
                    input.onchange = function () {
                        MapGuesser.toggleDisableOnChange(this, form.elements.submit);
                    };
                    break;
            }
        }

        form.onreset = function () {
            form.elements.submit.disabled = true;
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
