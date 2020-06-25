(function () {
    var resetSignupButton = document.getElementById('resetSignupButton');
    if (resetSignupButton) {
        resetSignupButton.onclick = function () {
            document.getElementById('loading').style.visibility = 'visible';

            MapGuesser.httpRequest('POST', '/signup/reset', function () {
                window.location.reload();
            });
        };
    }
})();
