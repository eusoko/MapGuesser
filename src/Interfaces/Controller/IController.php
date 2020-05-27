<?php namespace MapGuesser\Interfaces\Controller;

use MapGuesser\Interfaces\View\IView;

interface IController
{
    public function run(): IView;
}
