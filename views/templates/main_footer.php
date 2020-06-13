    <script>
        const STATIC_ROOT = '<?= $_ENV['STATIC_ROOT'] ?>';
        const REVISION = '<?= REVISION ?>';
        const ANTI_CSRF_TOKEN = '<?= $_SESSION['anti_csrf_token'] ?>';
    </script>
    <script src="<?= $_ENV['STATIC_ROOT'] ?>/js/mapguesser.js?rev=<?= REVISION ?>"></script>
    <?php if (isset($jsFiles)) : ?>
        <?php foreach ($jsFiles as $jsFile) : ?>
            <?php
            if (!preg_match('/^http(s)?/', $jsFile)) {
                $jsFile = $_ENV['STATIC_ROOT'] .  '/' . $jsFile . '?rev=' . REVISION;
            }
            ?>
            <script src="<?= $jsFile ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>