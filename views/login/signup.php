<?php
$jsFiles = [
    'js/login/signup.js',
];
?>
<?php require ROOT . '/views/templates/main_header.php'; ?>
<?php require ROOT . '/views/templates/header.php'; ?>
    <h2>Sign up</h2>
    <div class="box">
        <form id="signupForm" action="/signup" method="post">
            <?php if (isset($email)): ?>
                <p class="justify">No user found with the given email address. Sign up with one click!</p>
                <input class="big fullWidth marginTop" type="email" name="email" placeholder="Email address" value="<?= $email ?>" required>
                <input class="big fullWidth marginTop" type="password" name="password" placeholder="Password confirmation" required minlength="6" autofocus>
            <?php else: ?>
                <input class="big fullWidth" type="email" name="email" placeholder="Email address" required autofocus>
                <input class="big fullWidth marginTop" type="password" name="password" placeholder="Password" required minlength="6">
                <input class="big fullWidth marginTop" type="password" name="password_confirm" placeholder="Password confirmation" minlength="6">
            <?php endif; ?>
            <p id="signupFormError" class="formError justify marginTop"></p>
            <div class="right">
                <button class="marginTop" type="submit">Sign up</button><!--
             --><?php if (isset($email)): ?><!--
                 --><button id="resetSignupButton" class="gray marginTop marginLeft" type="button">Reset</button>
                <?php endif; ?>
            </div>
            <hr>
            <div class="center">
                <a class="button yellow" href="/login/google" title="Signup with Google">Signup with Google</a>
            </div>
        </form>
    </div>
<?php require ROOT . '/views/templates/footer.php'; ?>
<?php require ROOT . '/views/templates/main_footer.php'; ?>