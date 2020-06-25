(function () {
    document.getElementById('cancelGoogleSignupButton').onclick = function () {
        document.getElementById('loading').style.visibility = 'visible';

        MapGuesser.httpRequest('POST', '/signup/google/reset', function () {
            window.location.replace('/signup');
        });
    };
})();
