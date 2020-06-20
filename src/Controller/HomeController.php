<?php namespace MapGuesser\Controller;

use MapGuesser\Interfaces\Response\IRedirect;
use MapGuesser\Response\Redirect;

class HomeController
{
    public function getIndex(): IRedirect
    {
        return new Redirect(\Container::$routeCollection->getRoute('maps')->generateLink(), IRedirect::TEMPORARY);
    }
}
