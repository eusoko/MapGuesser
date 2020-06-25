(function () {
    var form = document.getElementById('loginForm');

    form.onsubmit = function (e) {
        document.getElementById('loading').style.visibility = 'visible';

        e.preventDefault();

        var formData = new FormData(form);

        MapGuesser.httpRequest('POST', form.action, function () {
            if (this.response.error) {
                if (this.response.error === 'user_not_found') {
                    window.location.replace('/signup');
                    return;
                }

                var errorText;
                switch (this.response.error) {
                    case 'password_too_short':
                        errorText = 'The given password is too short. Please choose a password that is at least 6 characters long!'
                        break;
                    case 'user_not_active':
                        errorText = 'User found with the given email address, but the account is not activated. Please check your email and click on the activation link!';
                        break;
                    case 'password_not_match':
                        errorText = 'The given password is wrong.'
                        break;
                }

                document.getElementById('loading').style.visibility = 'hidden';

                var loginFormError = document.getElementById('loginFormError');
                loginFormError.style.display = 'block';
                loginFormError.innerHTML = errorText;

                return;
            }

            window.location.replace('/');
        }, formData);
    };
})();
