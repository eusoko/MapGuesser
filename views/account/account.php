<?php require ROOT . '/views/templates/main_header.php'; ?>
<?php require ROOT . '/views/templates/header.php'; ?>
    <h2>Account</h2>
    <div class="box">
        <form id="accountForm" action="/account" method="post" data-observe-inputs="password_new,password_new_confirm">
            <input class="big fullWidth" type="password" name="password" placeholder="Current password" required minlength="6" autofocus>
            <hr>
            <?php /* TODO: disabled for the time being, email modification should be implemented */ ?>
            <input class="big fullWidth" type="email" name="email" placeholder="Email address" value="<?= $user['email'] ?>" disabled>
            <input class="big fullWidth marginTop" type="password" name="password_new" placeholder="New password" minlength="6">
            <input class="big fullWidth marginTop" type="password" name="password_new_confirm" placeholder="New password confirmation" minlength="6">
            <p id="accountFormError" class="formError justify marginTop"></p>
            <div class="right marginTop">
                <button type="submit" name="submit" disabled>Save</button>
            </div>
            <hr>
            <div class="center">
                <a class="button red" href="/account/delete" title="Delete account">Delete account</a>
            </div>
        </form>
    </div>
<?php require ROOT . '/views/templates/footer.php'; ?>
<?php require ROOT . '/views/templates/main_footer.php'; ?>