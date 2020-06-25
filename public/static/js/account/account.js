(function () {
    var form = document.getElementById('accountForm');

    MapGuesser.toggleFormSubmitButtonDisableOnChange(form, ['password_new', 'password_new_confirm'])

    MapGuesser.setOnsubmitForForm(form);
})();
