(function () {
    var form = document.getElementById('profileForm');

    form.elements.password_new.onkeyup = function () {
        MapGuesser.toggleDisableOnChange(this, form.elements.save);
    };

    form.elements.password_new_confirm.onkeyup = function () {
        MapGuesser.toggleDisableOnChange(this, form.elements.save);
    };

    form.onsubmit = function (e) {
        document.getElementById('loading').style.visibility = 'visible';

        e.preventDefault();

        var formData = new FormData(form);

        MapGuesser.httpRequest('POST', form.action, function () {
            document.getElementById('loading').style.visibility = 'hidden';

            if (this.response.error) {
                var errorText;
                switch (this.response.error) {
                    case 'password_not_match':
                        errorText = 'The given current password is wrong.'
                        break;
                    case 'passwords_too_short':
                        errorText = 'The given new password is too short. Please choose a password that is at least 6 characters long!'
                        break;
                    case 'passwords_not_match':
                        errorText = 'The given new passwords do not match.'
                        break;
                }

                var profileFormError = document.getElementById('profileFormError');
                profileFormError.style.display = 'block';
                profileFormError.innerHTML = errorText;

                form.elements.password_new.select();

                return;
            }

            document.getElementById('profileFormError').style.display = 'none';
            form.reset();
            form.elements.save.disabled = true;
            form.elements.password_new.focus();
        }, formData);
    };
})();
