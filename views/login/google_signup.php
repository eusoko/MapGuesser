<?php require ROOT . '/views/templates/main_header.php'; ?>
<?php require ROOT . '/views/templates/header.php'; ?>
<div class="main">
    <h2>Sign up</h2>
    <div class="box">
        <form id="googleSignupForm" action="/signup/google" method="post">
            <?php if ($found): ?>
                <p class="justify">Please confirm that you link your account to your Google account.</p>
            <?php else: ?>
                <p class="justify">Please confirm your sign up request. Your account will be linked to your Google account.</p>
            <?php endif; ?>
            <input class="big fullWidth marginTop" type="email" name="email" placeholder="Email address" value="<?= $email ?>" disabled>
            <div class="right marginTop">
                <button type="submit">
                    <?php if ($found): ?>
                        Link
                    <?php else: ?>
                        Sign up
                    <?php endif; ?>
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    (function () {
        var form = document.getElementById('googleSignupForm');

        form.onsubmit = function (e) {
            document.getElementById('loading').style.visibility = 'visible';

            e.preventDefault();

            MapGuesser.httpRequest('POST', form.action, function () {
                window.location.replace('/');
            });
        };
    })();
</script>
<?php require ROOT . '/views/templates/main_footer.php'; ?>