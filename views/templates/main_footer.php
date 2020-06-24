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
    <?php if (!isset($_COOKIE['COOKIES_CONSENT'])): ?>
        <script>
            (function () {
                var MapGuesser = {
                    cookiesAgreed: false,

                    agreeCookies: function () {
                        if (MapGuesser.cookiesAgreed) {
                            return;
                        }

                        var expirationDate = new Date(new Date().getTime() + 20 * 365 * 24 * 60 * 60 * 1000).toUTCString();
                        document.cookie = 'COOKIES_CONSENT=1; expires=' + expirationDate + '; path=/';

                        MapGuesser.cookiesAgreed = true;
                    }
                };

                document.getElementById('agreeCookies').onclick = function () {
                    document.getElementById('cookiesNotice').style.display = 'none';
                };

                window.onclick = function () {
                    MapGuesser.agreeCookies();
                };
            })();
        </script>
    <?php endif; ?>
</body>
</html>