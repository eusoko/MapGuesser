(function () {
    var form = document.getElementById('signupForm');

    form.onsubmit = function (e) {
        document.getElementById('loading').style.visibility = 'visible';

        e.preventDefault();

        var formData = new FormData(form);

        MapGuesser.httpRequest('POST', form.action, function () {
            if (this.response.error) {
                var errorText;
                switch (this.response.error) {
                    case 'email_not_valid':
                        errorText = 'The given email address is not valid.'
                        break;
                    case 'password_too_short':
                        errorText = 'The given password is too short. Please choose a password that is at least 6 characters long!'
                        break;
                    case 'passwords_not_match':
                        errorText = 'The given passwords do not match.'
                        break;
                    case 'user_found':
                        errorText = 'There is a user already registered with the given email address. Please <a href="/login" title="Login">login here</a>!';
                        break;
                    case 'not_active_user_found':
                        errorText = 'There is a user already registered with the given email address. Please check your email and click on the activation link!';
                        break;
                }

                document.getElementById('loading').style.visibility = 'hidden';

                var signupFormError = document.getElementById('signupFormError');
                signupFormError.style.display = 'block';
                signupFormError.innerHTML = errorText;

                return;
            }

            window.location.replace('/signup/success');
        }, formData);
    };
})();
