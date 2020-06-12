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
    <script>
        (function () {
            var anchors = document.getElementsByTagName('a');
            for (var i = 0; i < anchors.length; i++) {
                var a = anchors[i];
                if (a.href !== 'javascript:;' && a.target !== '_blank') {
                    a.onclick = function () {
                        document.getElementById('loading').style.visibility = 'visible';
                    }
                }
            }
        })();
    </script>
</body>
</html>