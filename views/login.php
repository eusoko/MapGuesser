<?php require ROOT . '/views/templates/main_header.php'; ?>
<?php require ROOT . '/views/templates/header.php'; ?>
<div class="main">
    <h2>Login</h2>
    <div class="box">
        <form id="loginForm" action="/login" method="post">
            <input class="big fullWidth" type="email" name="email" placeholder="Email address" autofocus>
            <input class="big fullWidth marginTop" type="password" name="password" placeholder="Password">
            <p id="loginFormError" class="formError marginTop"></p>
            <div class="right marginTop">
                <button type="submit">Login</button>
            </div>
        </form>
    </div>
</div>
<script src="/static/js/login.js"></script>
<?php require ROOT . '/views/templates/main_footer.php'; ?>