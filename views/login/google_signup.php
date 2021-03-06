<?php
$jsFiles = [
    'js/login/google_signup.js',
];
?>
<?php require ROOT . '/views/templates/main_header.php'; ?>
<?php require ROOT . '/views/templates/header.php'; ?>
    <h2>Sign up</h2>
    <div class="box">
        <form id="googleSignupForm" action="/signup/google" method="post" data-redirect-on-success="/">
            <?php if ($found): ?>
                <p class="justify">Please confirm that you link your account to your Google account.</p>
            <?php else: ?>
                <p class="justify">Please confirm your sign up request. Your account will be linked to your Google account.</p>
            <?php endif; ?>
            <input class="big fullWidth marginTop" type="email" name="email" placeholder="Email address" value="<?= $email ?>" disabled>
            <div class="right">
                <button class="marginTop marginRight" type="submit">
                    <?php if ($found): ?>
                        Link
                    <?php else: ?>
                        Sign up
                    <?php endif; ?>
                </button><!--
             --><button id="cancelGoogleSignupButton" class="gray marginTop" type="button">Cancel</button>
            </div>
        </form>
    </div>
<?php require ROOT . '/views/templates/footer.php'; ?>
<?php require ROOT . '/views/templates/main_footer.php'; ?>