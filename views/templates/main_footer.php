    <script>
        (function () {
            var anchors = document.getElementsByTagName('a');
            for (var i = 0; i < anchors.length; i++) {
                var a = anchors[i];
                if (a.target !== '_blank') {
                    a.onclick = function () {
                        document.getElementById('loading').style.visibility = 'visible';
                    }
                }
            }
        })();
    </script>
</body>
</html>