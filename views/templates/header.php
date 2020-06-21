<div class="header">
    <h1>
        <a href="/" title="MapGuesser">
            <img class="inline" width="1em" height="1em" src="<?= $_ENV['STATIC_ROOT'] ?>/img/icon.svg?rev=<?= REVISION ?>"><!--
         --><span>MapGuesser</span>
        </a>
    </h1>
    <p class="header">
        <?php if (Container::$request->user()) : ?>
            <span><a href="/profile" title="Profile">
                <?php /* Copyright (c) 2019 The Bootstrap Authors. License can be found in 'USED_SOFTWARE' in section 'Bootstrap Icons'. */ ?>
                <svg class="inline" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                </svg><!--
             --><?= Container::$request->user()->getDisplayName() ?><!--
            --></a></span><!--
            --><span><a href="/logout" title="Logout">Logout</a></span>
        <?php else : ?>
            <span><a href="/signup" title="Login">Sign up</a></span><!--
            --><span><a href="/login" title="Login">Login</a></span>
        <?php endif; ?>
    </p>
</div>