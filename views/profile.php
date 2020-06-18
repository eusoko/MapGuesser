<?php
$jsFiles = [
    'js/profile.js',
];
?>
<?php require ROOT . '/views/templates/main_header.php'; ?>
<?php require ROOT . '/views/templates/header.php'; ?>
<div class="main">
    <h2>Profile</h2>
    <div class="box">
        <form id="profileForm" action="/profile" method="post">
            <?php /* TODO: disabled for the time being, email modification should be implemented */ ?>
            <input class="big fullWidth" type="email" name="email" placeholder="Email address" value="<?= $user['email'] ?>" disabled>
            <input class="big fullWidth marginTop" type="password" name="password_new" placeholder="New password" minlength="6" autofocus>
            <input class="big fullWidth marginTop" type="password" name="password_new_confirm" placeholder="New password confirmation" minlength="6">
            <hr>
            <input class="big fullWidth" type="password" name="password" placeholder="Current password">
            <p id="profileFormError" class="formError justify marginTop"></p>
            <div class="right marginTop">
                <button type="submit" name="save" disabled>Save</button>
            </div>
        </form>
    </div>
</div>
<?php require ROOT . '/views/templates/main_footer.php'; ?>