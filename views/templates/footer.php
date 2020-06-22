</main>
<footer>
    <p><span class="bold"><?= $_ENV['APP_NAME'] ?></span> <?= str_replace('Release_', '', VERSION) ?></p><!--
 --><p>&copy; Pőcze Bence <?= (new DateTime(REVISION_DATE))->format('Y') ?></p>
</footer>