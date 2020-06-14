<?php
$jsFiles = [
    'js/signup.js',
];
?>
<?php require ROOT . '/views/templates/main_header.php'; ?>
<?php require ROOT . '/views/templates/header.php'; ?>
<div class="main">
    <h2>Sign up</h2>
    <div class="box">
        <form id="signupForm" action="/signup" method="post">
            <input class="big fullWidth" type="email" name="email" placeholder="Email address" autofocus>
            <input class="big fullWidth marginTop" type="password" name="password" placeholder="Password">
            <input class="big fullWidth marginTop" type="password" name="password_confirm" placeholder="Password confirmation">
            <p id="signupFormError" class="formError justify marginTop"></p>
            <div class="right marginTop">
                <button type="submit">Sign up</button>
            </div>
        </form>
    </div>
</div>
<?php require ROOT . '/views/templates/main_footer.php'; ?>