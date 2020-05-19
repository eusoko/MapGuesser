<?php namespace MapGuesser\Controller;

use MapGuesser\View\ViewBase;

interface ControllerInterface
{
    public function run(): ViewBase;
}
