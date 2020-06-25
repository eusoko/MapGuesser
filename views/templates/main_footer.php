    <script>
        const STATIC_ROOT = '<?= $_ENV['STATIC_ROOT'] ?>';
        const REVISION = '<?= REVISION ?>';
        var ANTI_CSRF_TOKEN = '<?= \Container::$request->session()->get('anti_csrf_token') ?>';
        <?php if (!empty($_ENV['GOOGLE_ANALITICS_ID'])): ?>
            const GOOGLE_ANALITICS_ID = '<?= $_ENV['GOOGLE_ANALITICS_ID'] ?>';
        <?php endif; ?>
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
                // we don't want user to agree cookies when clicking on the notice itself
                document.getElementById('cookiesNotice').onclick = function (e) {
                    e.stopPropagation();
                };

                document.getElementById('agreeCookiesButton').onclick = function () {
                    MapGuesser.agreeCookies();

                    document.getElementById('cookiesNotice').style.display = 'none';
                };

                window.onclick = function () {
                    MapGuesser.agreeCookies();
                };
            })();
        </script>
    <?php else: ?>
        <?php if (!empty($_ENV['GOOGLE_ANALITICS_ID'])): ?>
            <script>
                (function () {
                    MapGuesser.initGoogleAnalitics();
                })();
            </script>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>