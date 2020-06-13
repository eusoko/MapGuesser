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
})();
