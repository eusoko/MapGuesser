<?php
$jsFiles = [
    'js/account/delete.js',
];
?>
<?php require ROOT . '/views/templates/main_header.php'; ?>
<?php require ROOT . '/views/templates/header.php'; ?>
    <h2>Delete account</h2>
    <div class="box">
        <form id="deleteAccountForm" action="/account/delete" method="post">
            <p class="justify">Are you sure you want to delete your account? This cannot be undone!</p>
            <input class="big fullWidth marginTop" type="password" name="password" placeholder="Current password" required minlength="6" autofocus>
            <p id="deleteAccountFormError" class="formError justify marginTop"></p>
            <div class="right marginTop">
                <button class="red" type="submit" name="submit">Delete account</button>
            </div>
        </form>
    </div>
<?php require ROOT . '/views/templates/footer.php'; ?>
<?php require ROOT . '/views/templates/main_footer.php'; ?>