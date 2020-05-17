<?php namespace MapGuesser\Controller;

use MapGuesser\Util\Geo\Bounds;
use MapGuesser\Util\Geo\Position;

class GuessController extends BaseController
{
    protected string $view = 'guess';

    protected function operate() : void
    {
        // demo position
        $realPosition = new Position(47.85239, 13.35101);

        // demo bounds
        $bounds = new Bounds($realPosition);
        $bounds->extend(new Position(48.07683,7.35758));
        $bounds->extend(new Position(47.57496, 19.08077));

        $this->variables = compact('realPosition', 'bounds');
    }
}
