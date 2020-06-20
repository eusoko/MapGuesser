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
            <input class="big fullWidth" type="email" name="email" placeholder="Email address" required autofocus>
            <input class="big fullWidth marginTop" type="password" name="password" placeholder="Password" required minlength="6">
            <input class="big fullWidth marginTop" type="password" name="password_confirm" placeholder="Password confirmation" required minlength="6">
            <p id="signupFormError" class="formError justify marginTop"></p>
            <div class="right marginTop">
                <button type="submit">Sign up</button>
            </div>
            <hr>
            <div class="center">
                <a class="button yellow" href="/login/google" title="Signup with Google">Signup with Google</a>
            </div>
        </form>
    </div>
</div>
<?php require ROOT . '/views/templates/main_footer.php'; ?>