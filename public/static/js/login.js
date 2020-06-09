(function () {
    var form = document.getElementById('loginForm');

    form.onsubmit = function (e) {
        document.getElementById('loading').style.visibility = 'visible';

        e.preventDefault();

        var formData = new FormData(form);

        var xhr = new XMLHttpRequest();
        xhr.responseType = 'json';
        xhr.onload = function () {
            document.getElementById('loading').style.visibility = 'hidden';

            if (this.response.error) {
                var errorText;
                switch (this.response.error) {
                    case 'user_not_found':
                        errorText = 'No user found with the given email address.';
                        break;
                    case 'password_not_match':
                        errorText = 'The given password is wrong.'
                        break;
                }

                var loginFormError = document.getElementById('loginFormError');
                loginFormError.style.display = 'block';
                loginFormError.innerHTML = errorText;

                form.elements.email.select();

                return;
            }

            window.location.replace('/');
        };

        xhr.open('POST', form.action, true);
        xhr.send(formData);
    };
})();
