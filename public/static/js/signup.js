(function () {
    var form = document.getElementById('signupForm');

    form.onsubmit = function (e) {
        document.getElementById('loading').style.visibility = 'visible';

        e.preventDefault();

        var formData = new FormData(form);

        MapGuesser.httpRequest('POST', form.action, function () {
            document.getElementById('loading').style.visibility = 'hidden';

            if (this.response.error) {
                var errorText;
                switch (this.response.error) {
                    case 'passwords_too_short':
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

                var signupFormError = document.getElementById('signupFormError');
                signupFormError.style.display = 'block';
                signupFormError.innerHTML = errorText;

                form.elements.email.select();

                return;
            }

            document.getElementById('signupFormError').style.display = 'none';
            form.reset();
            form.elements.email.focus();

            MapGuesser.showModalWithContent('Sign up successful', 'Sign up was successful. Please check your email and click on the activation link to activate your account!');
        }, formData);
    };
})();
