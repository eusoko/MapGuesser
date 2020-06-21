<?php require ROOT . '/views/templates/main_header.php'; ?>
<?php require ROOT . '/views/templates/header.php'; ?>
    <h2>Account cancellation</h2>
    <div class="box">
        <?php if ($success) : ?>
            <p class="justify">Cancellation was successfull. You can <a href="/signup" title="Sign up">sign up</a> any time if you want!</p>
        <?php else: ?>
            <p class="error justify">Cancellation failed. Please check the link you entered! Maybe the account was already deleted, in this case no further action is required.</p>
        <?php endif; ?>
    </div>
<?php require ROOT . '/views/templates/footer.php'; ?>
<?php require ROOT . '/views/templates/main_footer.php'; ?>