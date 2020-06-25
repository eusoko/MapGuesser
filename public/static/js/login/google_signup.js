(function () {
    var form = document.getElementById('googleSignupForm');

    form.onsubmit = function (e) {
        document.getElementById('loading').style.visibility = 'visible';

        e.preventDefault();

        MapGuesser.httpRequest('POST', form.action, function () {
            window.location.replace('/');
        });
    };

    document.getElementById('cancelGoogleSignupButton').onclick = function () {
        document.getElementById('loading').style.visibility = 'visible';

        MapGuesser.httpRequest('POST', '/signup/google/reset', function () {
            window.location.replace('/signup');
        });
    };
})();
