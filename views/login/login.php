<?php
$jsFiles = [
    'js/login.js',
];
?>
<?php require ROOT . '/views/templates/main_header.php'; ?>
<?php require ROOT . '/views/templates/header.php'; ?>
    <h2>Login</h2>
    <div class="box">
        <form id="loginForm" action="/login" method="post">
            <input class="big fullWidth" type="email" name="email" placeholder="Email address" required autofocus>
            <input class="big fullWidth marginTop" type="password" name="password" placeholder="Password" required minlength="6">
            <p id="loginFormError" class="formError justify marginTop"></p>
            <div class="right marginTop">
                <button type="submit">Login</button>
            </div>
            <hr>
            <div class="center">
                <a class="button yellow" href="/login/google" title="Login with Google">Login with Google</a>
            </div>
        </form>
    </div>
<?php require ROOT . '/views/templates/footer.php'; ?>
<?php require ROOT . '/views/templates/main_footer.php'; ?>