<?php namespace MapGuesser\Controller;

use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Interfaces\Response\IRedirect;
use MapGuesser\Response\JsonContent;
use MapGuesser\Response\Redirect;

class HomeController
{
    private IRequest $request;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
    }

    public function getIndex(): IRedirect
    {
        return new Redirect(\Container::$routeCollection->getRoute('maps')->generateLink(), IRedirect::TEMPORARY);
    }

    public function startSession(): IContent
    {
        // session starts with the request, this method just sends valid data to the client

        $data = ['antiCsrfToken' => $this->request->session()->get('anti_csrf_token')];
        return new JsonContent($data);
    }
}
